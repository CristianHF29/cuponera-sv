<?php
/*
 * Validador de formularios.
 *
 * Se usa encadenando reglas:
 *   $v = new Validator($_POST);
 *   $v->requerido('correo', 'Escribe tu correo.')->correo('correo');
 *   if (!$v->valido()) { volver_con_errores('/registro', $v->errores()); }
 *
 * Cada campo guarda solo su primer error, así no se llena la pantalla de mensajes.
 * Las reglas (menos requerido) no revisan campos vacíos: para eso está requerido().
 */
class Validator
{
    private array $datos = [];
    private array $errores = [];

    public function __construct(array $datos)
    {
        foreach ($datos as $campo => $valor) {
            // las contraseñas no se recortan: un espacio también cuenta
            $esClave = str_starts_with((string) $campo, 'clave');
            $this->datos[$campo] = (is_string($valor) && !$esClave) ? trim($valor) : $valor;
        }
    }

    public function valor(string $campo)
    {
        return $this->datos[$campo] ?? '';
    }

    public function valido(): bool
    {
        return empty($this->errores);
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function agregarError(string $campo, string $mensaje): self
    {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = $mensaje;
        }
        return $this;
    }

    // true si no hay que revisar el campo: ya tiene error o viene vacío
    private function saltar(string $campo): bool
    {
        $valor = $this->valor($campo);
        return isset($this->errores[$campo]) || $valor === '' || $valor === null;
    }

    /* ---------------- reglas ---------------- */

    public function requerido(string $campo, string $mensaje): self
    {
        $valor = $this->valor($campo);
        if ($valor === '' || $valor === null || $valor === []) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function longitud(string $campo, int $minimo, int $maximo, string $nombre): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $largo = mb_strlen((string) $this->valor($campo));
        if ($largo < $minimo) {
            $this->agregarError($campo, ucfirst($nombre) . " debe tener al menos $minimo caracteres.");
        } elseif ($largo > $maximo) {
            $this->agregarError($campo, ucfirst($nombre) . " no puede pasar de $maximo caracteres.");
        }
        return $this;
    }

    public function patron(string $campo, string $expresion, string $mensaje): self
    {
        if (!$this->saltar($campo) && !preg_match($expresion, (string) $this->valor($campo))) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function correo(string $campo): self
    {
        if (!$this->saltar($campo) && !filter_var($this->valor($campo), FILTER_VALIDATE_EMAIL)) {
            $this->agregarError($campo, 'Ese correo no parece válido. Revisa que tenga @ y un dominio, como nombre@correo.com.');
        }
        return $this;
    }

    public function usuario(string $campo): self
    {
        return $this->patron(
            $campo,
            '/^[a-zA-Z0-9._]{4,30}$/',
            'El usuario debe tener de 4 a 30 caracteres y solo puede llevar letras, números, punto o guion bajo.'
        );
    }

    public function clave(string $campo): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $clave = (string) $this->valor($campo);
        if (mb_strlen($clave) < 8) {
            $this->agregarError($campo, 'La contraseña debe tener al menos 8 caracteres.');
        } elseif (!preg_match('/\p{L}/u', $clave) || !preg_match('/\d/', $clave)) {
            $this->agregarError($campo, 'Usa al menos una letra y un número en la contraseña.');
        }
        return $this;
    }

    public function confirmacion(string $campo, string $campoOriginal): self
    {
        if ($this->valor($campo) !== $this->valor($campoOriginal)) {
            $this->agregarError($campo, 'Las contraseñas no coinciden.');
        }
        return $this;
    }

    public function dui(string $campo): self
    {
        return $this->patron($campo, '/^\d{8}-\d$/', 'El DUI lleva 9 dígitos con este formato: 00000000-0.');
    }

    public function nit(string $campo): self
    {
        return $this->patron($campo, '/^\d{4}-\d{6}-\d{3}-\d$/', 'El NIT lleva 14 dígitos con este formato: 0000-000000-000-0.');
    }

    public function telefono(string $campo): self
    {
        return $this->patron(
            $campo,
            '/^[267]\d{3}-\d{4}$/',
            'Escribe un teléfono de El Salvador de 8 dígitos, por ejemplo 2222-0000 o 7000-0000.'
        );
    }

    public function fecha(string $campo, string $mensaje = 'Escribe una fecha válida.'): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $texto = (string) $this->valor($campo);
        $fecha = DateTime::createFromFormat('!Y-m-d', $texto);
        if (!$fecha || $fecha->format('Y-m-d') !== $texto) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function mayorDeEdad(string $campo, int $edadMinima = 18): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $nacimiento = DateTime::createFromFormat('!Y-m-d', (string) $this->valor($campo));
        if (!$nacimiento) {
            return $this;
        }
        $hoy = new DateTime('today');
        $edad = $nacimiento->diff($hoy)->y;

        if ($nacimiento > $hoy) {
            $this->agregarError($campo, 'La fecha de nacimiento no puede estar en el futuro.');
        } elseif ($edad < $edadMinima) {
            $this->agregarError($campo, "Para crear una cuenta necesitas tener $edadMinima años o más.");
        } elseif ($edad > 110) {
            $this->agregarError($campo, 'Revisa el año de nacimiento, parece que hay un error.');
        }
        return $this;
    }

    public function numero(string $campo, float $minimo, float $maximo, string $mensaje): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $valor = $this->valor($campo);
        if (!is_numeric($valor) || (float) $valor < $minimo || (float) $valor > $maximo) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function entero(string $campo, int $minimo, int $maximo, string $mensaje): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $opciones = ['options' => ['min_range' => $minimo, 'max_range' => $maximo]];
        if (filter_var($this->valor($campo), FILTER_VALIDATE_INT, $opciones) === false) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function enLista(string $campo, array $opciones, string $mensaje): self
    {
        if (!in_array($this->valor($campo), $opciones, true)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    // Revisa que el valor no exista ya en la tabla. $tabla y $columna los ponemos nosotros, nunca el usuario.
    public function unico(string $campo, string $tabla, string $columna, string $mensaje, ?int $exceptoId = null): self
    {
        if ($this->saltar($campo)) {
            return $this;
        }
        $sql = "SELECT COUNT(*) FROM `$tabla` WHERE `$columna` = ?";
        $parametros = [$this->valor($campo)];
        if ($exceptoId !== null) {
            $sql .= ' AND id <> ?';
            $parametros[] = $exceptoId;
        }
        $consulta = Database::conexion()->prepare($sql);
        $consulta->execute($parametros);
        if ((int) $consulta->fetchColumn() > 0) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    // Para fechas en formato Y-m-d: el campo no puede ser antes que el otro
    public function noAntesDe(string $campo, string $otroCampo, string $mensaje): self
    {
        if ($this->saltar($campo) || isset($this->errores[$otroCampo]) || $this->valor($otroCampo) === '') {
            return $this;
        }
        if ($this->valor($campo) < $this->valor($otroCampo)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    public function menorQue(string $campo, string $otroCampo, string $mensaje): self
    {
        if ($this->saltar($campo) || isset($this->errores[$otroCampo]) || $this->valor($otroCampo) === '') {
            return $this;
        }
        if ((float) $this->valor($campo) >= (float) $this->valor($otroCampo)) {
            $this->agregarError($campo, $mensaje);
        }
        return $this;
    }

    /* ---------------- formatos ----------------
     * Dejan los datos como se guardan en la base, aunque la persona
     * los escriba sin guiones o con espacios.
     */

    public static function soloDigitos(string $texto): string
    {
        return preg_replace('/\D/', '', $texto);
    }

    public static function formatoDui(string $texto): string
    {
        $d = self::soloDigitos($texto);
        return strlen($d) === 9 ? substr($d, 0, 8) . '-' . substr($d, 8) : trim($texto);
    }

    public static function formatoNit(string $texto): string
    {
        $d = self::soloDigitos($texto);
        if (strlen($d) !== 14) {
            return trim($texto);
        }
        return substr($d, 0, 4) . '-' . substr($d, 4, 6) . '-' . substr($d, 10, 3) . '-' . substr($d, 13);
    }

    public static function formatoTelefono(string $texto): string
    {
        $d = self::soloDigitos($texto);
        if (strlen($d) === 11 && str_starts_with($d, '503')) {
            $d = substr($d, 3); // le quitamos el +503
        }
        return strlen($d) === 8 ? substr($d, 0, 4) . '-' . substr($d, 4) : trim($texto);
    }
}

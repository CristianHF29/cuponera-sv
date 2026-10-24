<?php
/*
 * Inicio y cierre de sesión para clientes, empresas y administradores.
 */
class AuthController extends Controller
{
    private const INTENTOS_PERMITIDOS = 5;
    private const MINUTOS_DE_ESPERA = 2;

    public function formulario(): void
    {
        if (Auth::usuario()) {
            redirigir(Auth::panel());
        }
        $tipo = $_GET['tipo'] ?? 'cliente';
        if (!isset(Auth::TIPOS[$tipo])) {
            $tipo = 'cliente';
        }
        vista('auth/login', [
            'titulo' => 'Inicia sesión',
            'tipoElegido' => anterior('tipo', $tipo),
        ]);
    }

    public function entrar(): void
    {
        $this->validarFormulario('/login');

        $espera = $this->minutosDeBloqueo();
        if ($espera > 0) {
            volver_con_errores('/login', [
                'clave' => "Hubo demasiados intentos fallidos. Espera $espera " . ($espera === 1 ? 'minuto' : 'minutos') . ' y vuelve a probar.',
            ]);
        }

        $v = new Validator($_POST);
        $v->enLista('tipo', array_keys(Auth::TIPOS), 'Elige con qué tipo de cuenta vas a entrar.')
          ->requerido('usuario', 'Escribe tu usuario o tu correo.')
          ->requerido('clave', 'Escribe tu contraseña.');

        if (!$v->valido()) {
            volver_con_errores('/login', $v->errores());
        }

        $tipo = $v->valor('tipo');
        $cuenta = Auth::modelo($tipo)->buscarPorUsuarioOCorreo($v->valor('usuario'));

        if (!$cuenta || !password_verify($v->valor('clave'), $cuenta['contrasena'])) {
            $this->contarIntentoFallido();
            volver_con_errores('/login', [
                'clave' => 'El usuario o la contraseña no coinciden. Revisa también que elegiste el tipo de cuenta correcto.',
            ]);
        }

        // una empresa solo entra cuando un administrador ya aprobó su solicitud
        if ($tipo === 'empresa' && $cuenta['estado'] !== 'aprobada') {
            $mensaje = $cuenta['estado'] === 'pendiente'
                ? 'Tu solicitud todavía está en revisión. Vas a poder entrar en cuanto un administrador la apruebe.'
                : 'Tu solicitud fue rechazada. Motivo: ' . ($cuenta['motivo_rechazo'] ?: 'no se indicó.');
            volver_con_errores('/login', ['usuario' => $mensaje]);
        }

        unset($_SESSION['intentos_login']);
        Auth::iniciar($tipo, $cuenta);

        $saludo = match ($tipo) {
            'cliente' => '¡Hola, ' . primer_nombre($cuenta['nombre_completo']) . '! Qué bueno verte de nuevo.',
            'empresa' => 'Bienvenido al panel de ' . $cuenta['nombre'] . '.',
            default => 'Hola, ' . primer_nombre($cuenta['nombre']) . '. Ya estás en el panel de administración.',
        };
        flash('exito', $saludo);
        redirigir(Auth::destinoDespuesDeEntrar());
    }

    public function salir(): void
    {
        $this->validarFormulario('/');
        Auth::cerrar();
        flash('info', 'Cerraste sesión. ¡Vuelve pronto!');
        redirigir('/');
    }

    // Después de varios intentos fallidos seguidos, bloqueamos un rato el formulario
    private function contarIntentoFallido(): void
    {
        $intentos = $_SESSION['intentos_login'] ?? ['cantidad' => 0, 'hasta' => 0];
        $intentos['cantidad']++;
        if ($intentos['cantidad'] >= self::INTENTOS_PERMITIDOS) {
            $intentos = ['cantidad' => 0, 'hasta' => time() + self::MINUTOS_DE_ESPERA * 60];
        }
        $_SESSION['intentos_login'] = $intentos;
    }

    private function minutosDeBloqueo(): int
    {
        $hasta = $_SESSION['intentos_login']['hasta'] ?? 0;
        return $hasta > time() ? (int) ceil(($hasta - time()) / 60) : 0;
    }
}

<?php
/*
 * Recuperación de contraseña para los tres tipos de cuenta.
 *  1. La persona pide el enlace con su correo.
 *  2. Guardamos el hash de un token que vence en unos minutos y le enviamos el enlace.
 *  3. Con el enlace crea una contraseña nueva. El enlace sirve una sola vez.
 */
class RecuperarController extends Controller
{
    public function formulario(): void
    {
        $tipo = $_GET['tipo'] ?? 'cliente';
        vista('auth/recuperar', [
            'titulo' => 'Recupera tu contraseña',
            'tipoElegido' => anterior('tipo', isset(Auth::TIPOS[$tipo]) ? $tipo : 'cliente'),
        ]);
    }

    public function enviar(): void
    {
        $this->validarFormulario('/recuperar');

        $datos = $_POST;
        $datos['correo'] = mb_strtolower(trim((string) ($datos['correo'] ?? '')));

        $v = new Validator($datos);
        $v->enLista('tipo', array_keys(Auth::TIPOS), 'Elige el tipo de cuenta.')
          ->requerido('correo', 'Escribe el correo con el que te registraste.')
          ->correo('correo');
        if (!$v->valido()) {
            volver_con_errores('/recuperar', $v->errores(), $datos);
        }

        $tipo = $v->valor('tipo');
        $cuenta = Auth::modelo($tipo)->buscarPorCorreo($v->valor('correo'));
        if (!$cuenta) {
            volver_con_errores('/recuperar', [
                'correo' => 'No encontramos una cuenta de ' . mb_strtolower(Auth::TIPOS[$tipo]) . ' con ese correo. Revisa cómo lo escribiste o si elegiste otro tipo de cuenta.',
            ], $datos);
        }

        $token = bin2hex(random_bytes(32));
        (new RecuperacionContrasena())->crear($tipo, (int) $cuenta['id'], hash('sha256', $token), MINUTOS_ENLACE_RECUPERACION);

        $enlace = url_absoluta('/restablecer?token=' . $token);
        $nombre = $tipo === 'cliente' ? primer_nombre($cuenta['nombre_completo']) : $cuenta['nombre'];
        $html = plantilla('correos/recuperar', [
            'nombre' => $nombre,
            'enlace' => $enlace,
            'minutos' => MINUTOS_ENLACE_RECUPERACION,
        ]);

        $resultado = Mailer::enviar($cuenta['correo'], $nombre, 'Recupera tu contraseña de La Cuponera SV', $html);
        if ($resultado === 'error') {
            volver_con_errores('/recuperar', [
                'correo' => 'No pudimos enviar el correo en este momento. Intenta de nuevo en unos minutos.',
            ], $datos);
        }

        $_SESSION['recuperacion'] = [
            'correo' => $cuenta['correo'],
            // en modo de pruebas el correo no sale de la computadora, así que mostramos el enlace
            'enlace' => ($resultado === 'archivo' && APP_ENTORNO === 'desarrollo') ? $enlace : null,
        ];
        redirigir('/recuperar/enviado');
    }

    public function enviado(): void
    {
        $info = $_SESSION['recuperacion'] ?? null;
        if (!$info) {
            redirigir('/recuperar');
        }
        vista('auth/enlace_enviado', [
            'titulo' => 'Revisa tu correo',
            'correo' => $info['correo'],
            'enlacePrueba' => $info['enlace'],
        ]);
    }

    public function formularioNueva(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if (!$this->solicitudVigente($token)) {
            vista('auth/enlace_invalido', ['titulo' => 'Este enlace ya no sirve']);
            return;
        }
        vista('auth/restablecer', ['titulo' => 'Crea tu contraseña nueva', 'token' => $token]);
    }

    public function guardarNueva(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $volver = '/restablecer?token=' . urlencode($token);
        $this->validarFormulario($volver);

        $solicitud = $this->solicitudVigente($token);
        if (!$solicitud) {
            vista('auth/enlace_invalido', ['titulo' => 'Este enlace ya no sirve']);
            return;
        }

        $v = new Validator($_POST);
        $v->requerido('clave', 'Escribe tu contraseña nueva.')
          ->clave('clave')
          ->confirmacion('clave_confirmacion', 'clave');
        if (!$v->valido()) {
            volver_con_errores($volver, $v->errores());
        }

        Auth::modelo($solicitud['tipo_usuario'])->cambiarClave((int) $solicitud['usuario_id'], $v->valor('clave'));
        (new RecuperacionContrasena())->marcarUsado((int) $solicitud['id']);
        unset($_SESSION['recuperacion']);

        flash('exito', 'Listo, tu contraseña cambió. Ya puedes entrar con la nueva.');
        redirigir('/login?tipo=' . $solicitud['tipo_usuario']);
    }

    private function solicitudVigente(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return (new RecuperacionContrasena())->buscarVigente(hash('sha256', $token));
    }
}

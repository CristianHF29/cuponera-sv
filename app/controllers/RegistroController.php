<?php
/*
 * Registro de clientes y de empresas ofertantes.
 * Los clientes pueden comprar en cuanto se registran.
 * Las empresas quedan "pendientes" hasta que un administrador las apruebe.
 */
class RegistroController extends Controller
{
    public function elegir(): void
    {
        vista('registro/elegir', ['titulo' => 'Crea tu cuenta']);
    }

    public function cliente(): void
    {
        if (Auth::usuario()) {
            redirigir(Auth::panel());
        }
        vista('registro/cliente', ['titulo' => 'Crea tu cuenta de cliente']);
    }

    public function guardarCliente(): void
    {
        $this->validarFormulario('/registro/cliente');

        $datos = $_POST;
        $datos['dui'] = Validator::formatoDui((string) ($datos['dui'] ?? ''));
        $datos['correo'] = mb_strtolower(trim((string) ($datos['correo'] ?? '')));

        $soloLetras = "/^[\p{L} '.-]+$/u";
        $v = new Validator($datos);
        $v->requerido('nombre_completo', 'Escribe tu nombre.')
          ->longitud('nombre_completo', 2, 100, 'tu nombre')
          ->patron('nombre_completo', $soloLetras, 'El nombre solo puede llevar letras y espacios.')
          ->requerido('apellidos', 'Escribe tus apellidos.')
          ->longitud('apellidos', 2, 100, 'tus apellidos')
          ->patron('apellidos', $soloLetras, 'Los apellidos solo pueden llevar letras y espacios.')
          ->requerido('dui', 'Escribe tu número de DUI.')
          ->dui('dui')
          ->unico('dui', 'clientes', 'dui', 'Ya hay una cuenta registrada con ese DUI.')
          ->requerido('fecha_nacimiento', 'Indica tu fecha de nacimiento.')
          ->fecha('fecha_nacimiento', 'Escribe una fecha de nacimiento válida.')
          ->mayorDeEdad('fecha_nacimiento', 18)
          ->requerido('usuario', 'Elige un nombre de usuario.')
          ->usuario('usuario')
          ->unico('usuario', 'clientes', 'usuario', 'Ese usuario ya está ocupado, prueba con otro.')
          ->requerido('correo', 'Escribe tu correo electrónico.')
          ->correo('correo')
          ->unico('correo', 'clientes', 'correo', 'Ese correo ya tiene una cuenta. ¿Quieres iniciar sesión?')
          ->requerido('clave', 'Crea una contraseña.')
          ->clave('clave')
          ->confirmacion('clave_confirmacion', 'clave');

        if (!$v->valido()) {
            volver_con_errores('/registro/cliente', $v->errores(), $datos);
        }

        $modelo = new Cliente();
        $id = $modelo->registrar([
            'usuario' => $v->valor('usuario'),
            'correo' => $v->valor('correo'),
            'clave' => $v->valor('clave'),
            'nombre_completo' => $v->valor('nombre_completo'),
            'apellidos' => $v->valor('apellidos'),
            'dui' => $v->valor('dui'),
            'fecha_nacimiento' => $v->valor('fecha_nacimiento'),
        ]);

        // queda con la sesión iniciada para que pueda comprar de una vez
        Auth::iniciar('cliente', $modelo->buscarPorId($id));
        flash('exito', '¡Listo, ' . primer_nombre($v->valor('nombre_completo')) . '! Tu cuenta ya está creada. Elige tu primer cupón.');
        redirigir(Auth::destinoDespuesDeEntrar());
    }

    public function empresa(): void
    {
        if (Auth::usuario()) {
            redirigir(Auth::panel());
        }
        vista('registro/empresa', ['titulo' => 'Registra tu empresa']);
    }

    public function guardarEmpresa(): void
    {
        $this->validarFormulario('/registro/empresa');

        $datos = $_POST;
        $datos['nit'] = Validator::formatoNit((string) ($datos['nit'] ?? ''));
        $datos['telefono'] = Validator::formatoTelefono((string) ($datos['telefono'] ?? ''));
        $datos['correo'] = mb_strtolower(trim((string) ($datos['correo'] ?? '')));

        $v = new Validator($datos);
        $v->requerido('nombre', 'Escribe el nombre de la empresa.')
          ->longitud('nombre', 3, 150, 'el nombre')
          ->requerido('nit', 'Escribe el NIT de la empresa.')
          ->nit('nit')
          ->unico('nit', 'empresas', 'nit', 'Ese NIT ya está registrado. Si es tu empresa, inicia sesión o recupera tu contraseña.')
          ->requerido('direccion', 'Escribe la dirección del negocio.')
          ->longitud('direccion', 10, 255, 'la dirección')
          ->requerido('telefono', 'Escribe un teléfono de contacto.')
          ->telefono('telefono')
          ->requerido('correo', 'Escribe el correo de la empresa.')
          ->correo('correo')
          ->unico('correo', 'empresas', 'correo', 'Ese correo ya está registrado con otra empresa.')
          ->requerido('usuario', 'Elige un usuario para entrar al panel.')
          ->usuario('usuario')
          ->unico('usuario', 'empresas', 'usuario', 'Ese usuario ya está ocupado, prueba con otro.')
          ->requerido('clave', 'Crea una contraseña.')
          ->clave('clave')
          ->confirmacion('clave_confirmacion', 'clave');

        if (!$v->valido()) {
            volver_con_errores('/registro/empresa', $v->errores(), $datos);
        }

        (new Empresa())->registrar([
            'nombre' => $v->valor('nombre'),
            'nit' => $v->valor('nit'),
            'direccion' => $v->valor('direccion'),
            'telefono' => $v->valor('telefono'),
            'correo' => $v->valor('correo'),
            'usuario' => $v->valor('usuario'),
            'clave' => $v->valor('clave'),
        ]);

        $_SESSION['empresa_registrada'] = $v->valor('nombre');
        redirigir('/registro/empresa/enviada');
    }

    public function solicitudEnviada(): void
    {
        $nombre = $_SESSION['empresa_registrada'] ?? null;
        if (!$nombre) {
            redirigir('/registro/empresa');
        }
        vista('registro/enviada', ['titulo' => 'Recibimos tu solicitud', 'nombreEmpresa' => $nombre]);
    }
}

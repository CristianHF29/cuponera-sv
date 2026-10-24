<?php
/*
 * Panel del administrador: resumen, solicitudes de empresas
 * y registro de nuevos administradores.
 * (Los reportes están en ReporteController.)
 */
class AdminController extends Controller
{
    public function __construct()
    {
        Auth::exigir('administrador');
    }

    public function inicio(): void
    {
        $empresas = new Empresa();
        vista('admin/inicio', [
            'titulo' => 'Panel de administración',
            'conteo' => $empresas->conteoPorEstado(),
            'pendientes' => array_slice($empresas->porEstado('pendiente'), 0, 4),
            'clientes' => (new Cliente())->total(),
            'cupones' => (new Compra())->totalCupones(),
        ]);
    }

    public function solicitudes(): void
    {
        $estado = $_GET['estado'] ?? 'pendiente';
        if (!in_array($estado, ['pendiente', 'aprobada', 'rechazada'], true)) {
            $estado = 'pendiente';
        }
        $empresas = new Empresa();
        vista('admin/solicitudes', [
            'titulo' => 'Solicitudes de empresas',
            'estado' => $estado,
            'empresas' => $empresas->porEstado($estado),
            'conteo' => $empresas->conteoPorEstado(),
        ]);
    }

    public function aprobar(string $id): void
    {
        $this->validarFormulario('/admin/solicitudes');
        $id = (int) $id;

        $v = new Validator($_POST);
        $v->requerido('comision', 'Escribe el porcentaje de comisión antes de aprobar.')
          ->numero('comision', 0.01, 100, 'La comisión debe ser un número mayor que 0 y hasta 100.');

        // cada tarjeta de la lista tiene su propio formulario, por eso el error lleva el id
        if (!$v->valido()) {
            volver_con_errores('/admin/solicitudes', ['comision_' . $id => $v->errores()['comision']], ['comision_' . $id => $v->valor('comision')]);
        }

        $modelo = new Empresa();
        $empresa = $modelo->buscarPorId($id);
        if (!$empresa || !$modelo->aprobar($id, round((float) $v->valor('comision'), 2), Auth::id())) {
            flash('aviso', 'Esa solicitud ya había sido revisada por otro administrador.');
            redirigir('/admin/solicitudes');
        }

        flash('exito', $empresa['nombre'] . ' ya puede publicar ofertas, con una comisión de ' . porcentaje($v->valor('comision')) . ' por cupón vendido.');
        redirigir('/admin/solicitudes');
    }

    public function rechazar(string $id): void
    {
        $this->validarFormulario('/admin/solicitudes');
        $id = (int) $id;

        $v = new Validator($_POST);
        $v->requerido('motivo', 'Escribe el motivo: la empresa lo verá cuando intente iniciar sesión.')
          ->longitud('motivo', 10, 255, 'el motivo');
        if (!$v->valido()) {
            volver_con_errores('/admin/solicitudes', ['motivo_' . $id => $v->errores()['motivo']], ['motivo_' . $id => $v->valor('motivo')]);
        }

        $modelo = new Empresa();
        $empresa = $modelo->buscarPorId($id);
        if (!$empresa || !$modelo->rechazar($id, $v->valor('motivo'), Auth::id())) {
            flash('aviso', 'Esa solicitud ya había sido revisada por otro administrador.');
            redirigir('/admin/solicitudes');
        }

        flash('info', 'Rechazaste la solicitud de ' . $empresa['nombre'] . '.');
        redirigir('/admin/solicitudes');
    }

    public function administradores(): void
    {
        vista('admin/administradores', [
            'titulo' => 'Administradores',
            'administradores' => (new Administrador())->listado(),
        ]);
    }

    public function guardarAdministrador(): void
    {
        $this->validarFormulario('/admin/administradores');

        $datos = $_POST;
        $datos['correo'] = mb_strtolower(trim((string) ($datos['correo'] ?? '')));

        $v = new Validator($datos);
        $v->requerido('nombre', 'Escribe el nombre de la persona.')
          ->longitud('nombre', 3, 100, 'el nombre')
          ->requerido('correo', 'Escribe su correo.')
          ->correo('correo')
          ->unico('correo', 'administradores', 'correo', 'Ya hay un administrador con ese correo.')
          ->requerido('usuario', 'Elige un usuario para esta persona.')
          ->usuario('usuario')
          ->unico('usuario', 'administradores', 'usuario', 'Ese usuario ya está en uso.')
          ->requerido('clave', 'Asigna una contraseña temporal.')
          ->clave('clave')
          ->confirmacion('clave_confirmacion', 'clave');

        if (!$v->valido()) {
            volver_con_errores('/admin/administradores', $v->errores(), $datos);
        }

        (new Administrador())->crear([
            'nombre' => $v->valor('nombre'),
            'correo' => $v->valor('correo'),
            'usuario' => $v->valor('usuario'),
            'clave' => $v->valor('clave'),
        ], Auth::id());

        flash('exito', $v->valor('nombre') . ' ya es administrador. Compártele su usuario y la contraseña temporal por un medio seguro.');
        redirigir('/admin/administradores');
    }
}

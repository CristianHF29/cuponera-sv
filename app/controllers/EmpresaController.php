<?php
/*
 * Panel de la empresa: ver sus ofertas, publicar nuevas, editarlas y pausarlas.
 * Solo entran las empresas aprobadas (eso se revisa al iniciar sesión).
 */
class EmpresaController extends Controller
{
    public function __construct()
    {
        Auth::exigir('empresa');
    }

    public function panel(): void
    {
        vista('empresa/panel', [
            'titulo' => 'Mis ofertas',
            'empresa' => (new Empresa())->buscarPorId(Auth::id()),
            'ofertas' => (new Oferta())->deEmpresa(Auth::id()),
        ]);
    }

    public function nueva(): void
    {
        vista('empresa/formulario', [
            'titulo' => 'Publicar una oferta',
            'oferta' => null,
            'empresa' => (new Empresa())->buscarPorId(Auth::id()),
        ]);
    }

    public function guardar(): void
    {
        $this->validarFormulario('/empresa/ofertas/nueva');

        $v = $this->validar($_POST, null);
        if (!$v->valido()) {
            volver_con_errores('/empresa/ofertas/nueva', $v->errores());
        }

        $datos = $this->datosLimpios($v);
        (new Oferta())->crear(Auth::id(), $datos);

        $visibleHoy = $datos['estado'] === 'disponible' && $datos['fecha_inicio'] <= date('Y-m-d');
        flash('exito', $visibleHoy
            ? 'Tu oferta «' . $datos['titulo'] . '» ya está publicada en el sitio.'
            : 'Guardamos «' . $datos['titulo'] . '». Se verá en el sitio cuando esté disponible y llegue su fecha de inicio.');
        redirigir('/empresa');
    }

    public function editar(string $id): void
    {
        $oferta = (new Oferta())->buscarDeEmpresa((int) $id, Auth::id());
        if (!$oferta) {
            pagina_de_error(404);
        }
        vista('empresa/formulario', [
            'titulo' => 'Editar oferta',
            'oferta' => $oferta,
            'empresa' => (new Empresa())->buscarPorId(Auth::id()),
        ]);
    }

    public function actualizar(string $id): void
    {
        $id = (int) $id;
        $volver = '/empresa/ofertas/' . $id . '/editar';
        $this->validarFormulario($volver);

        $modelo = new Oferta();
        $oferta = $modelo->buscarDeEmpresa($id, Auth::id());
        if (!$oferta) {
            pagina_de_error(404);
        }

        $v = $this->validar($_POST, $oferta);
        if (!$v->valido()) {
            volver_con_errores($volver, $v->errores());
        }

        $datos = $this->datosLimpios($v);
        $modelo->actualizar($id, Auth::id(), $datos);
        flash('exito', 'Guardamos los cambios de «' . $datos['titulo'] . '».');
        redirigir('/empresa');
    }

    public function cambiarEstado(string $id): void
    {
        $this->validarFormulario('/empresa');

        $modelo = new Oferta();
        $oferta = $modelo->buscarDeEmpresa((int) $id, Auth::id());
        if (!$oferta) {
            pagina_de_error(404);
        }

        $nuevo = $oferta['estado'] === 'disponible' ? 'no disponible' : 'disponible';
        $modelo->cambiarEstado((int) $id, Auth::id(), $nuevo);
        flash('info', $nuevo === 'disponible'
            ? 'La oferta «' . $oferta['titulo'] . '» volvió a estar disponible.'
            : 'Pausaste «' . $oferta['titulo'] . '»: ya no aparece en el sitio.');
        redirigir('/empresa');
    }

    private function validar(array $datos, ?array $oferta): Validator
    {
        $v = new Validator($datos);
        $v->requerido('titulo', 'Ponle un título a la oferta.')
          ->longitud('titulo', 5, 150, 'el título')
          ->requerido('precio_regular', 'Escribe el precio regular.')
          ->numero('precio_regular', 0.01, 99999.99, 'El precio regular debe ser un número mayor que 0.')
          ->requerido('precio_oferta', 'Escribe el precio con descuento.')
          ->numero('precio_oferta', 0.01, 99999.99, 'El precio de oferta debe ser un número mayor que 0.')
          ->menorQue('precio_oferta', 'precio_regular', 'El precio de oferta tiene que ser menor que el precio regular.')
          ->requerido('fecha_inicio', 'Indica desde cuándo estará la oferta.')
          ->fecha('fecha_inicio')
          ->requerido('fecha_fin', 'Indica hasta cuándo estará la oferta.')
          ->fecha('fecha_fin')
          ->noAntesDe('fecha_fin', 'fecha_inicio', 'La fecha de fin no puede ser antes de la fecha de inicio.')
          ->requerido('fecha_limite_canje', 'Indica hasta cuándo se pueden canjear los cupones.')
          ->fecha('fecha_limite_canje')
          ->noAntesDe('fecha_limite_canje', 'fecha_fin', 'La fecha límite para canjear debe ser igual o posterior al fin de la oferta.')
          ->entero('cantidad_cupones', 1, 100000, 'La cantidad de cupones debe ser un número entero mayor que 0. Déjala vacía si no hay límite.')
          ->requerido('descripcion', 'Describe la oferta para que la gente sepa qué incluye.')
          ->longitud('descripcion', 20, 2000, 'la descripción')
          ->enLista('estado', ['disponible', 'no disponible'], 'Elige si la oferta estará disponible o no.');

        // una oferta nueva no puede nacer ya vencida
        if ($oferta === null && !isset($v->errores()['fecha_fin']) && $v->valor('fecha_fin') < date('Y-m-d')) {
            $v->agregarError('fecha_fin', 'Esa fecha ya pasó. Elige una fecha de fin de hoy en adelante.');
        }
        // al editar, la cantidad no puede quedar por debajo de lo que ya se vendió
        if ($oferta !== null && $v->valor('cantidad_cupones') !== '' && (int) $v->valor('cantidad_cupones') < (int) $oferta['vendidos']) {
            $v->agregarError('cantidad_cupones', 'Ya se vendieron ' . (int) $oferta['vendidos'] . ' cupones, la cantidad no puede ser menor.');
        }
        return $v;
    }

    private function datosLimpios(Validator $v): array
    {
        return [
            'titulo' => $v->valor('titulo'),
            'precio_regular' => round((float) $v->valor('precio_regular'), 2),
            'precio_oferta' => round((float) $v->valor('precio_oferta'), 2),
            'fecha_inicio' => $v->valor('fecha_inicio'),
            'fecha_fin' => $v->valor('fecha_fin'),
            'fecha_limite_canje' => $v->valor('fecha_limite_canje'),
            'cantidad_cupones' => $v->valor('cantidad_cupones') === '' ? null : (int) $v->valor('cantidad_cupones'),
            'descripcion' => $v->valor('descripcion'),
            'estado' => $v->valor('estado'),
        ];
    }
}

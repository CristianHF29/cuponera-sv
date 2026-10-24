<?php
/*
 * Compra simulada de cupones, factura e historial del cliente.
 * No hay pasarela de pago real: solo validamos los datos de la tarjeta
 * y que la fecha de vencimiento sea posterior a hoy.
 */
class CompraController extends Controller
{
    public function comprar(string $id): void
    {
        $ofertaId = (int) $id;
        $volver = '/oferta/' . $ofertaId;

        if (!Auth::usuario()) {
            $_SESSION['volver_a'] = ['ruta' => $volver, 'tipo' => 'cliente'];
            flash('aviso', 'Inicia sesión o crea tu cuenta para comprar este cupón.');
            redirigir('/login?tipo=cliente');
        }
        Auth::exigir('cliente');
        $this->validarFormulario($volver);

        $datos = $_POST;
        $datos['numero_tarjeta'] = Validator::soloDigitos((string) ($datos['numero_tarjeta'] ?? ''));

        $v = new Validator($datos);
        $v->requerido('cantidad', 'Elige cuántos cupones quieres.')
          ->entero('cantidad', 1, MAX_CUPONES_POR_OFERTA, 'Puedes comprar de 1 a ' . MAX_CUPONES_POR_OFERTA . ' cupones por oferta.')
          ->requerido('numero_tarjeta', 'Escribe el número de tu tarjeta.')
          ->patron('numero_tarjeta', '/^\d{13,19}$/', 'El número de tarjeta debe tener entre 13 y 19 dígitos.')
          ->requerido('vencimiento', 'Escribe la fecha de vencimiento como aparece en la tarjeta (MM/AA).')
          ->patron('vencimiento', '#^(0[1-9]|1[0-2])/\d{2}$#', 'Escribe el vencimiento con el formato MM/AA, por ejemplo 08/28.')
          ->requerido('cvv', 'Escribe el código de seguridad de la tarjeta.')
          ->patron('cvv', '/^\d{3,4}$/', 'El código de seguridad (CVV) tiene 3 o 4 dígitos.');

        // si el formato ya está bien, revisamos que la tarjeta no esté vencida
        if (!isset($v->errores()['vencimiento']) && !$this->tarjetaVigente($v->valor('vencimiento'))) {
            $v->agregarError('vencimiento', 'Esa tarjeta ya venció. La fecha de vencimiento tiene que ser posterior a hoy.');
        }

        if (!$v->valido()) {
            volver_con_errores($volver, $v->errores(), $datos);
        }

        try {
            $compraId = (new Compra())->registrar(
                Auth::id(),
                $ofertaId,
                (int) $v->valor('cantidad'),
                substr($v->valor('numero_tarjeta'), -4)
            );
        } catch (DomainException $e) {
            volver_con_errores($volver, ['cantidad' => $e->getMessage()], $datos);
        }

        flash('exito', '¡Compra lista! Presenta tu código en el negocio para canjear el cupón.');
        redirigir('/factura/' . $compraId);
    }

    public function factura(string $id): void
    {
        Auth::exigir('cliente');
        $compra = (new Compra())->factura((int) $id, Auth::id());
        if (!$compra) {
            pagina_de_error(404);
        }
        vista('cliente/factura', [
            'titulo' => 'Factura ' . $compra['numero_factura'],
            'compra' => $compra,
        ]);
    }

    public function historial(): void
    {
        Auth::exigir('cliente');
        vista('cliente/historial', [
            'titulo' => 'Mis cupones',
            'compras' => (new Compra())->historial(Auth::id()),
        ]);
    }

    // La tarjeta sirve hasta el último día de su mes de vencimiento
    private function tarjetaVigente(string $vencimiento): bool
    {
        [$mes, $anio] = array_map('intval', explode('/', $vencimiento));
        $ultimoDia = (new DateTime())->setDate(2000 + $anio, $mes, 1)->modify('last day of this month')->setTime(23, 59, 59);
        return $ultimoDia > new DateTime();
    }
}

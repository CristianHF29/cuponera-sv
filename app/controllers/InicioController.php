<?php
/*
 * Parte pública del sitio: catálogo de ofertas y detalle de cada una.
 * Cualquiera puede verlas; para comprar hay que iniciar sesión como cliente.
 */
class InicioController extends Controller
{
    public function inicio(): void
    {
        $busqueda = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);

        vista('inicio/catalogo', [
            'titulo' => 'Cupones de descuento en El Salvador',
            'ofertas' => (new Oferta())->disponibles($busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    public function oferta(string $id): void
    {
        $oferta = (new Oferta())->detalle((int) $id);

        // las ofertas "no disponibles", las que aún no empiezan y las de empresas sin aprobar no se muestran
        if (!$oferta || $oferta['empresa_estado'] !== 'aprobada' || in_array(Oferta::situacion($oferta), ['pausada', 'programada'], true)) {
            pagina_de_error(404);
        }

        $yaCompradas = Auth::es('cliente') ? (new Compra())->cuponesDelCliente(Auth::id(), (int) $oferta['id']) : 0;

        // si inicia sesión o se registra desde aquí, lo regresamos a esta misma oferta
        if (!Auth::usuario()) {
            $_SESSION['volver_a'] = ['ruta' => '/oferta/' . (int) $oferta['id'], 'tipo' => 'cliente'];
        }

        vista('inicio/oferta', [
            'titulo' => $oferta['titulo'],
            'oferta' => $oferta,
            'situacion' => Oferta::situacion($oferta),
            'restantes' => Oferta::restantes($oferta),
            'yaCompradas' => $yaCompradas,
        ]);
    }
}

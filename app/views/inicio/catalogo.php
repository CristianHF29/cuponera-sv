<section class="portada">
    <div class="contenedor portada__interior">
        <div class="portada__texto">
            <h1>Descuentos de verdad en negocios de aquí</h1>
            <p class="portada__bajada">
                Compra tu cupón en línea, preséntalo en el local y listo. Pupusas, café de altura,
                clases de surf y más, con precios que dan gusto.
            </p>
            <form class="buscador" method="get" action="<?= url('/') ?>" role="search">
                <?php if (!URL_AMIGABLES): ?>
                    <input type="hidden" name="r" value="">
                <?php endif; ?>
                <label class="sr-only" for="q">Buscar ofertas</label>
                <input type="search" id="q" name="q" value="<?= e($busqueda) ?>" placeholder="¿Qué se te antoja? Pupusas, spa, surf...">
                <button class="boton boton--primario" type="submit">Buscar</button>
            </form>
        </div>

        <div class="portada__boletos" aria-hidden="true">
            <div class="boleto boleto--uno boleto--anil">
                <div class="boleto__forma">
                    <div class="boleto__cuerpo"><span>Café de altura</span><strong>−30%</strong></div>
                    <div class="boleto__talon"><span>cupón</span></div>
                </div>
            </div>
            <div class="boleto boleto--dos boleto--mango">
                <div class="boleto__forma">
                    <div class="boleto__cuerpo"><span>Pupusas</span><strong>2x1</strong></div>
                    <div class="boleto__talon"><span>cupón</span></div>
                </div>
            </div>
            <div class="boleto boleto--tres boleto--torogoz">
                <div class="boleto__forma">
                    <div class="boleto__cuerpo"><span>Surf en El Tunco</span><strong>−35%</strong></div>
                    <div class="boleto__talon"><span>cupón</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="contenedor">
    <div class="corte" aria-hidden="true"><span>&#9986;</span></div>

    <div class="encabezado-seccion">
        <h2><?= $busqueda !== '' ? 'Resultados para «' . e($busqueda) . '»' : 'Ofertas disponibles' ?></h2>
        <p>
            <?= count($ofertas) === 1 ? '1 oferta' : count($ofertas) . ' ofertas' ?><?php if ($busqueda !== ''): ?>,
                <a href="<?= url('/') ?>">ver todas</a>
            <?php endif; ?>
        </p>
    </div>

    <?php if ($ofertas): ?>
        <div class="rejilla-cupones">
            <?php foreach ($ofertas as $oferta): ?>
                <?php require RAIZ . '/app/views/partials/cupon.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php elseif ($busqueda !== ''): ?>
        <div class="vacio">
            <h3>No encontramos ofertas con «<?= e($busqueda) ?>»</h3>
            <p>Prueba con otra palabra, como el nombre del negocio o lo que se te antoja.</p>
            <a class="boton boton--suave" href="<?= url('/') ?>">Ver todas las ofertas</a>
        </div>
    <?php else: ?>
        <div class="vacio">
            <h3>Por ahora no hay ofertas publicadas</h3>
            <p>Vuelve pronto. Y si tienes un negocio, puedes ser el primero en publicar.</p>
            <a class="boton boton--primario" href="<?= url('/registro/empresa') ?>">Registrar mi negocio</a>
        </div>
    <?php endif; ?>

    <?php if (!Auth::usuario()): ?>
        <section class="banda-negocios">
            <div>
                <h2>¿Tienes un negocio?</h2>
                <p>Publica tus ofertas en La Cuponera y llega a clientes nuevos. Solo pagas una comisión por cada cupón vendido.</p>
            </div>
            <a class="boton boton--anil" href="<?= url('/registro/empresa') ?>">Registrar mi empresa</a>
        </section>
    <?php endif; ?>
</div>

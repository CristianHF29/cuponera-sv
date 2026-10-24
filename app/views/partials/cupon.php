<?php
// Tarjeta con forma de cupón para el catálogo. Necesita $oferta.
$coloresCupon = ['anil', 'pitahaya', 'torogoz', 'mango'];
$colorCupon = $coloresCupon[(int) $oferta['id'] % count($coloresCupon)];
$restantesCupon = Oferta::restantes($oferta);
?>
<article class="cupon cupon--<?= $colorCupon ?>">
    <a class="cupon__enlace" href="<?= url('/oferta/' . (int) $oferta['id']) ?>">
        <div class="cupon__cuerpo">
            <p class="cupon__empresa"><?= e($oferta['empresa']) ?></p>
            <h3 class="cupon__titulo"><?= e($oferta['titulo']) ?></h3>
            <p class="cupon__precios">
                <span class="cupon__precio"><?= dinero($oferta['precio_oferta']) ?></span>
                <s class="cupon__antes"><span class="sr-only">Precio regular </span><?= dinero($oferta['precio_regular']) ?></s>
            </p>
            <p class="cupon__detalle">
                Hasta el <?= fecha_corta($oferta['fecha_fin']) ?>
                <?php if ($restantesCupon !== null && $restantesCupon <= 15): ?>
                    <br><span class="cupon__quedan">Quedan <?= $restantesCupon ?></span>
                <?php endif; ?>
            </p>
        </div>
        <div class="cupon__talon">
            <span class="cupon__porcentaje">−<?= porcentaje_descuento($oferta['precio_regular'], $oferta['precio_oferta']) ?>%</span>
            <span class="cupon__ahorro">ahorras <?= dinero($oferta['precio_regular'] - $oferta['precio_oferta']) ?></span>
        </div>
    </a>
</article>

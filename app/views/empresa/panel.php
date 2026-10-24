<?php
$visibles = 0;
$vendidos = 0;
$ventas = 0.0;
foreach ($ofertas as $o) {
    if (Oferta::situacion($o) === 'disponible') {
        $visibles++;
    }
    $vendidos += (int) $o['vendidos'];
    $ventas += (float) $o['ingresos'];
}

$nombresSituacion = [
    'disponible' => 'Disponible',
    'pausada' => 'No disponible',
    'programada' => 'Programada',
    'vencida' => 'Finalizada',
    'agotada' => 'Agotada',
];
?>
<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Mis ofertas</h1>
            <p><?= e($empresa['nombre']) ?>. Comisión acordada: <?= porcentaje($empresa['porcentaje_comision']) ?> por cupón vendido.</p>
        </div>
        <a class="boton boton--primario" href="<?= url('/empresa/ofertas/nueva') ?>">Publicar oferta</a>
    </header>

    <section class="indicadores" aria-label="Resumen de tus ofertas">
        <div class="indicador">
            <p class="indicador__etiqueta">Ofertas visibles en el sitio</p>
            <p class="indicador__valor"><?= $visibles ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Cupones vendidos</p>
            <p class="indicador__valor"><?= number_format($vendidos) ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Ventas</p>
            <p class="indicador__valor"><?= dinero($ventas) ?></p>
        </div>
    </section>

    <?php if (!$ofertas): ?>
        <div class="vacio">
            <h2>Todavía no has publicado ofertas</h2>
            <p>Publica la primera: aparece en el sitio en cuanto llega su fecha de inicio.</p>
            <a class="boton boton--primario" href="<?= url('/empresa/ofertas/nueva') ?>">Publicar mi primera oferta</a>
        </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Oferta</th>
                        <th scope="col" class="numero">Precio</th>
                        <th scope="col">Vigencia</th>
                        <th scope="col" class="numero">Vendidos</th>
                        <th scope="col">Estado</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ofertas as $oferta): ?>
                        <?php $situacion = Oferta::situacion($oferta); ?>
                        <tr>
                            <td>
                                <span class="tabla__titulo"><?= e($oferta['titulo']) ?></span>
                                <span class="tabla__sub">Canje hasta el <?= fecha_corta($oferta['fecha_limite_canje']) ?></span>
                            </td>
                            <td class="numero">
                                <?= dinero($oferta['precio_oferta']) ?>
                                <span class="tabla__sub"><s><?= dinero($oferta['precio_regular']) ?></s></span>
                            </td>
                            <td><?= fecha_corta($oferta['fecha_inicio']) ?> al <?= fecha_corta($oferta['fecha_fin']) ?></td>
                            <td class="numero">
                                <?= (int) $oferta['vendidos'] ?><?= $oferta['cantidad_cupones'] !== null ? ' de ' . (int) $oferta['cantidad_cupones'] : '' ?>
                            </td>
                            <td><span class="etiqueta etiqueta--<?= $situacion ?>"><?= $nombresSituacion[$situacion] ?></span></td>
                            <td>
                                <div class="acciones">
                                    <a class="boton boton--borde boton--chico" href="<?= url('/empresa/ofertas/' . (int) $oferta['id'] . '/editar') ?>">Editar</a>
                                    <form method="post" action="<?= url('/empresa/ofertas/' . (int) $oferta['id'] . '/estado') ?>">
                                        <?= campo_csrf() ?>
                                        <button class="boton boton--suave boton--chico" type="submit">
                                            <?= $oferta['estado'] === 'disponible' ? 'Pausar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Reportes</h1>
            <p>
                Ventas de cupones por empresa al <?= fecha_larga(date('Y-m-d')) ?>.
                Las ganancias se calculan con la comisión que se fijó al aprobar cada empresa.
            </p>
        </div>
        <button class="boton boton--borde no-imprimir" type="button" data-imprimir>Imprimir reporte</button>
    </header>

    <section class="indicadores" aria-label="Totales generales">
        <div class="indicador">
            <p class="indicador__etiqueta">Total de cupones vendidos</p>
            <p class="indicador__valor"><?= number_format($totales['cupones']) ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Total de ventas</p>
            <p class="indicador__valor"><?= dinero($totales['ventas']) ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Total de ganancias obtenidas</p>
            <p class="indicador__valor"><?= dinero($totales['ganancias']) ?></p>
        </div>
    </section>

    <?php if (!$empresas): ?>
        <div class="vacio">
            <h2>Aún no hay datos para mostrar</h2>
            <p>Los reportes aparecen cuando haya empresas aprobadas con ventas.</p>
        </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <caption class="sr-only">Cupones vendidos, ventas y ganancias por empresa</caption>
                <thead>
                    <tr>
                        <th scope="col">Empresa</th>
                        <th scope="col" class="numero">Comisión</th>
                        <th scope="col" class="numero">Cupones vendidos</th>
                        <th scope="col" class="numero">Total de ventas</th>
                        <th scope="col" class="numero">Ganancias</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $fila): ?>
                        <tr>
                            <th scope="row"><?= e($fila['nombre']) ?></th>
                            <td class="numero"><?= porcentaje($fila['porcentaje_comision']) ?></td>
                            <td class="numero"><?= number_format((int) $fila['cupones_vendidos']) ?></td>
                            <td class="numero"><?= dinero($fila['total_ventas']) ?></td>
                            <td class="numero"><?= dinero($fila['ganancias']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row">Total</th>
                        <td></td>
                        <td class="numero"><?= number_format($totales['cupones']) ?></td>
                        <td class="numero"><?= dinero($totales['ventas']) ?></td>
                        <td class="numero"><?= dinero($totales['ganancias']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

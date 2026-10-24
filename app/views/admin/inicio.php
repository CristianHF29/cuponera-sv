<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Hola, <?= e(primer_nombre(Auth::usuario()['nombre'])) ?></h1>
            <p>Así va La Cuponera hoy, <?= fecha_larga(date('Y-m-d')) ?>.</p>
        </div>
        <div class="grupo-botones">
            <a class="boton boton--suave" href="<?= url('/admin/reportes') ?>">Ver reportes</a>
            <a class="boton boton--primario" href="<?= url('/admin/solicitudes') ?>">Revisar solicitudes</a>
        </div>
    </header>

    <section class="indicadores" aria-label="Resumen general">
        <div class="indicador">
            <p class="indicador__etiqueta">Solicitudes por revisar</p>
            <p class="indicador__valor"><?= $conteo['pendiente'] ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Empresas aprobadas</p>
            <p class="indicador__valor"><?= $conteo['aprobada'] ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Clientes registrados</p>
            <p class="indicador__valor"><?= $clientes ?></p>
        </div>
        <div class="indicador">
            <p class="indicador__etiqueta">Cupones vendidos</p>
            <p class="indicador__valor"><?= number_format($cupones) ?></p>
        </div>
    </section>

    <section class="tarjeta">
        <div class="tarjeta__encabezado">
            <h2 class="tarjeta__titulo">Empresas esperando respuesta</h2>
            <?php if ($conteo['pendiente'] > count($pendientes)): ?>
                <a href="<?= url('/admin/solicitudes') ?>">Ver las <?= $conteo['pendiente'] ?></a>
            <?php endif; ?>
        </div>

        <?php if ($pendientes): ?>
            <ul class="lista-simple">
                <?php foreach ($pendientes as $empresa): ?>
                    <li>
                        <div>
                            <strong><?= e($empresa['nombre']) ?></strong>
                            <span class="texto-suave">Envió su solicitud el <?= fecha_y_hora($empresa['creado_en']) ?></span>
                        </div>
                        <a class="boton boton--suave boton--chico" href="<?= url('/admin/solicitudes') ?>#empresa-<?= (int) $empresa['id'] ?>">Revisar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="texto-suave">No hay solicitudes pendientes. Cuando una empresa se registre, va a aparecer aquí.</p>
        <?php endif; ?>
    </section>
</div>

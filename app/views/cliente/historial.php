<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Mis cupones</h1>
            <p>Presenta el código en el negocio antes de la fecha límite. Cada código se canjea una sola vez.</p>
        </div>
        <a class="boton boton--suave" href="<?= url('/') ?>">Ver más ofertas</a>
    </header>

    <?php if (!$compras): ?>
        <div class="vacio">
            <h2>Todavía no tienes cupones</h2>
            <p>Cuando compres uno, aquí vas a encontrar sus códigos y la factura.</p>
            <a class="boton boton--primario" href="<?= url('/') ?>">Elegir mi primer cupón</a>
        </div>
    <?php else: ?>
        <div class="compras">
            <?php foreach ($compras as $compra): ?>
                <?php $vencido = $compra['fecha_limite_canje'] < date('Y-m-d'); ?>
                <article class="compra-item">
                    <div>
                        <p class="compra-item__empresa"><?= e($compra['empresa']) ?></p>
                        <h2 class="compra-item__titulo"><?= e($compra['titulo']) ?></h2>
                        <p class="compra-item__meta">
                            Comprado el <?= fecha_larga($compra['creado_en']) ?>.
                            <?= $vencido
                                ? 'El plazo para canjear terminó el ' . fecha_larga($compra['fecha_limite_canje']) . '.'
                                : 'Canjéalo hasta el ' . fecha_larga($compra['fecha_limite_canje']) . '.' ?>
                        </p>
                        <ul class="codigos" aria-label="Códigos de cupón">
                            <?php foreach ($compra['cupones'] as $cupon): ?>
                                <?php $estadoCupon = $cupon['estado'] === 'canjeado' ? 'canjeado' : ($vencido ? 'vencido' : 'vigente'); ?>
                                <li class="codigo codigo--<?= $estadoCupon ?>">
                                    <span class="codigo__texto"><?= e($cupon['codigo']) ?></span>
                                    <?php if ($estadoCupon !== 'vigente'): ?>
                                        <span class="codigo__estado"><?= $estadoCupon ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="compra-item__lado">
                        <span class="compra-item__total"><?= dinero($compra['total']) ?></span>
                        <span class="texto-suave"><?= (int) $compra['cantidad'] === 1 ? '1 cupón' : (int) $compra['cantidad'] . ' cupones' ?></span>
                        <a class="boton boton--borde boton--chico" href="<?= url('/factura/' . (int) $compra['id']) ?>">Ver factura</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

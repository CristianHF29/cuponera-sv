<?php
$vencido = $compra['fecha_limite_canje'] < date('Y-m-d');
$ahorro = ((float) $compra['precio_regular'] - (float) $compra['precio_unitario']) * (int) $compra['cantidad'];
?>
<div class="contenedor">
    <div class="grupo-botones barra-factura no-imprimir">
        <a class="boton boton--borde boton--chico" href="<?= url('/mis-cupones') ?>">Ir a mis cupones</a>
        <button class="boton boton--anil boton--chico" type="button" data-imprimir>Imprimir o guardar en PDF</button>
    </div>

    <article class="factura">
        <header class="factura__cabecera">
            <div>
                <span class="marca">
                    <img class="marca__icono" src="<?= asset('img/logo.svg') ?>" alt="" width="40" height="28">
                    <span>La Cuponera <span class="marca__pais">SV</span></span>
                </span>
                <p class="factura__lema">Comprobante de compra de cupones</p>
            </div>
            <div class="factura__numero">
                <p>Factura</p>
                <strong><?= e($compra['numero_factura']) ?></strong>
                <p><?= fecha_y_hora($compra['creado_en']) ?></p>
            </div>
        </header>

        <div class="factura__cuerpo">
            <div class="factura__partes">
                <section class="factura__parte">
                    <h2>Cliente</h2>
                    <p><strong><?= e($compra['nombre_completo'] . ' ' . $compra['apellidos']) ?></strong></p>
                    <p>DUI <?= e($compra['dui']) ?></p>
                    <p><?= e($compra['correo']) ?></p>
                </section>
                <section class="factura__parte">
                    <h2>Comercio</h2>
                    <p><strong><?= e($compra['empresa']) ?></strong></p>
                    <p>NIT <?= e($compra['empresa_nit']) ?></p>
                    <p><?= e($compra['empresa_direccion']) ?>. Tel. <?= e($compra['empresa_telefono']) ?></p>
                </section>
            </div>

            <div class="tabla-contenedor tabla-contenedor--plano">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Descripción</th>
                            <th scope="col" class="numero">Cantidad</th>
                            <th scope="col" class="numero">Precio unitario</th>
                            <th scope="col" class="numero">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="tabla__titulo"><?= e($compra['titulo']) ?></span>
                                <span class="tabla__sub">Precio regular <?= dinero($compra['precio_regular']) ?> por cupón</span>
                            </td>
                            <td class="numero"><?= (int) $compra['cantidad'] ?></td>
                            <td class="numero"><?= dinero($compra['precio_unitario']) ?></td>
                            <td class="numero"><?= dinero($compra['total']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="factura__total">
                <span>Total pagado</span>
                <strong><?= dinero($compra['total']) ?></strong>
            </div>
            <?php if ($ahorro > 0): ?>
                <p class="factura__ahorro">Te ahorraste <?= dinero($ahorro) ?> con esta compra.</p>
            <?php endif; ?>

            <section class="factura__codigos">
                <h2><?= count($compra['cupones']) === 1 ? 'Tu código de cupón' : 'Tus códigos de cupón' ?></h2>
                <p>
                    Cada código vale por un cupón y se canjea una sola vez.
                    <?= $vencido
                        ? 'El plazo para canjear terminó el ' . fecha_larga($compra['fecha_limite_canje']) . '.'
                        : 'Canjéalos hasta el ' . fecha_larga($compra['fecha_limite_canje']) . '.' ?>
                </p>
                <ul class="codigos">
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
            </section>
        </div>

        <footer class="factura__pie">
            Pagado con tarjeta terminada en <?= e($compra['tarjeta_ultimos4']) ?>.
            Pago simulado para el proyecto de cátedra: no se hizo ningún cargo real.
        </footer>
    </article>
</div>

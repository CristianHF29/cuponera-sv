<?php
$descuento = porcentaje_descuento($oferta['precio_regular'], $oferta['precio_oferta']);

// cuántos cupones puede llevar todavía: máximo 5 por oferta y sin pasar lo que queda
$maximo = MAX_CUPONES_POR_OFERTA - $yaCompradas;
if ($restantes !== null) {
    $maximo = min($maximo, $restantes);
}
?>
<div class="contenedor">
    <nav class="migas" aria-label="Ruta">
        <a href="<?= url('/') ?>">Ofertas</a>
        <span aria-hidden="true">/</span>
        <span><?= e($oferta['empresa']) ?></span>
    </nav>

    <div class="detalle">
        <article>
            <p class="detalle__empresa"><?= e($oferta['empresa']) ?></p>
            <h1 class="detalle__titulo"><?= e($oferta['titulo']) ?></h1>
            <p class="detalle__descripcion"><?= nl2br(e($oferta['descripcion'])) ?></p>

            <dl class="datos">
                <div>
                    <dt>Puedes comprarlo</dt>
                    <dd>del <?= fecha_corta($oferta['fecha_inicio']) ?> al <?= fecha_corta($oferta['fecha_fin']) ?></dd>
                </div>
                <div>
                    <dt>Canjéalo antes del</dt>
                    <dd><?= fecha_larga($oferta['fecha_limite_canje']) ?></dd>
                </div>
                <div>
                    <dt>Cupones</dt>
                    <dd><?= $restantes === null ? 'Sin límite' : 'Quedan ' . $restantes . ' de ' . (int) $oferta['cantidad_cupones'] ?></dd>
                </div>
                <div>
                    <dt>Teléfono</dt>
                    <dd><?= e($oferta['empresa_telefono']) ?></dd>
                </div>
                <div class="datos__ancho">
                    <dt>Dónde se canjea</dt>
                    <dd><?= e($oferta['empresa_direccion']) ?></dd>
                </div>
            </dl>
        </article>

        <aside class="compra tarjeta" aria-label="Comprar cupón">
            <div class="compra__precio">
                <strong><?= dinero($oferta['precio_oferta']) ?></strong>
                <s><span class="sr-only">Precio regular </span><?= dinero($oferta['precio_regular']) ?></s>
                <span class="sello">−<?= $descuento ?>%</span>
            </div>

            <?php if ($situacion === 'vencida'): ?>
                <p class="compra__aviso">Esta oferta terminó el <?= fecha_larga($oferta['fecha_fin']) ?>. Mira las que siguen disponibles.</p>
                <a class="boton boton--suave boton--bloque" href="<?= url('/') ?>">Ver ofertas disponibles</a>

            <?php elseif ($situacion === 'agotada'): ?>
                <p class="compra__aviso">Se vendieron todos los cupones de esta oferta.</p>
                <a class="boton boton--suave boton--bloque" href="<?= url('/') ?>">Ver ofertas disponibles</a>

            <?php elseif (!Auth::usuario()): ?>
                <p>Para comprar necesitas una cuenta de cliente. Es gratis y toma un minuto.</p>
                <div class="grupo-botones">
                    <a class="boton boton--primario boton--bloque" href="<?= url('/login?tipo=cliente') ?>">Inicia sesión para comprar</a>
                    <a class="boton boton--borde boton--bloque" href="<?= url('/registro/cliente') ?>">Crear mi cuenta</a>
                </div>

            <?php elseif (!Auth::es('cliente')): ?>
                <p class="compra__aviso">Las compras se hacen con una cuenta de cliente. Ahora estás con una cuenta de <?= e(mb_strtolower(Auth::TIPOS[Auth::tipo()])) ?>.</p>

            <?php elseif ($maximo <= 0): ?>
                <p class="compra__aviso">Ya tienes <?= $yaCompradas ?> cupones de esta oferta, el máximo que se permite por cliente.</p>
                <a class="boton boton--suave boton--bloque" href="<?= url('/mis-cupones') ?>">Ver mis cupones</a>

            <?php else: ?>
                <form method="post" action="<?= url('/oferta/' . (int) $oferta['id'] . '/comprar') ?>" class="formulario" novalidate>
                    <?= campo_csrf() ?>

                    <div class="campo<?= clase_error('cantidad') ?>">
                        <label for="cantidad">¿Cuántos cupones quieres?</label>
                        <select id="cantidad" name="cantidad" data-cantidad data-precio="<?= e($oferta['precio_oferta']) ?>"<?= aria_error('cantidad') ?>>
                            <?php for ($i = 1; $i <= $maximo; $i++): ?>
                                <option value="<?= $i ?>"<?= anterior('cantidad', '1') === (string) $i ? ' selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <?php if (!error_de('cantidad')): ?>
                            <p class="campo__ayuda">
                                <?= $yaCompradas > 0
                                    ? 'Ya tienes ' . $yaCompradas . ' de esta oferta. El máximo por cliente es ' . MAX_CUPONES_POR_OFERTA . '.'
                                    : 'Puedes llevar hasta ' . MAX_CUPONES_POR_OFERTA . ' cupones de esta oferta.' ?>
                            </p>
                        <?php endif; ?>
                        <?= mensaje_error('cantidad') ?>
                    </div>

                    <div class="campo<?= clase_error('numero_tarjeta') ?>">
                        <label for="numero_tarjeta">Número de tarjeta de crédito o débito</label>
                        <input type="text" id="numero_tarjeta" name="numero_tarjeta" inputmode="numeric" autocomplete="cc-number"
                               placeholder="0000 0000 0000 0000" maxlength="23" data-tarjeta<?= aria_error('numero_tarjeta') ?>>
                        <?= mensaje_error('numero_tarjeta') ?>
                    </div>

                    <div class="fila-campos">
                        <div class="campo<?= clase_error('vencimiento') ?>">
                            <label for="vencimiento">Vence</label>
                            <input type="text" id="vencimiento" name="vencimiento" inputmode="numeric" autocomplete="cc-exp"
                                   placeholder="MM/AA" maxlength="5" data-vencimiento<?= aria_error('vencimiento') ?>>
                            <?= mensaje_error('vencimiento') ?>
                        </div>
                        <div class="campo<?= clase_error('cvv') ?>">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" name="cvv" inputmode="numeric" autocomplete="cc-csc"
                                   placeholder="123" maxlength="4"<?= aria_error('cvv') ?>>
                            <?= mensaje_error('cvv') ?>
                        </div>
                    </div>

                    <div class="compra__total">
                        <span>Total a pagar</span>
                        <strong data-total><?= dinero($oferta['precio_oferta']) ?></strong>
                    </div>

                    <button class="boton boton--primario boton--bloque" type="submit">Comprar cupón</button>
                    <p class="compra__nota">Pago simulado para el proyecto: no se hace ningún cargo. De tu tarjeta solo guardamos los últimos 4 dígitos.</p>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</div>

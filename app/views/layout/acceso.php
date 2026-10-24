<!DOCTYPE html>
<html lang="es">
<head>
    <?php require RAIZ . '/app/views/partials/cabeza.php'; ?>
</head>
<body class="pagina-acceso">
<a class="saltar" href="#contenido">Saltar al formulario</a>

<?php require RAIZ . '/app/views/partials/nav.php'; ?>

<main id="contenido" class="acceso">
    <aside class="acceso__lado">
        <div class="acceso__mensaje">
            <p class="acceso__frase"><?= e($frase ?? 'Los descuentos de aquí, en un solo lugar.') ?></p>
            <div class="boleto boleto--mango" aria-hidden="true">
                <div class="boleto__forma">
                    <div class="boleto__cuerpo">
                        <span>Tu próximo cupón</span>
                        <strong>2x1</strong>
                    </div>
                    <div class="boleto__talon"><span>cupón</span></div>
                </div>
            </div>
        </div>
    </aside>

    <section class="acceso__formulario">
        <div class="acceso__caja">
            <?php require RAIZ . '/app/views/partials/alertas.php'; ?>
            <?= $contenido ?>
        </div>
    </section>
</main>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>

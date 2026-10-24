<!DOCTYPE html>
<html lang="es">
<head>
    <?php require RAIZ . '/app/views/partials/cabeza.php'; ?>
</head>
<body class="<?= e($claseCuerpo ?? '') ?>">
<a class="saltar" href="#contenido">Saltar al contenido</a>

<?php require RAIZ . '/app/views/partials/nav.php'; ?>

<main id="contenido" class="contenido">
    <div class="contenedor">
        <?php require RAIZ . '/app/views/partials/alertas.php'; ?>
    </div>
    <?= $contenido ?>
</main>

<?php require RAIZ . '/app/views/partials/pie.php'; ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>

<?php
$layout = 'acceso';
$frase = 'Ya casi. Revisa tu bandeja de entrada.';
?>
<h1 class="acceso__titulo">Revisa tu correo</h1>
<p class="acceso__intro">
    Enviamos un enlace a <strong><?= e($correo) ?></strong>. Ábrelo dentro de los próximos
    <?= MINUTOS_ENLACE_RECUPERACION ?> minutos para crear tu contraseña nueva. Si no lo ves, busca en la carpeta de spam.
</p>

<?php if ($enlacePrueba): ?>
    <div class="caja-prueba">
        <p><strong>Modo de pruebas.</strong> No hay un servidor de correo configurado, así que el mensaje se guardó en
            <code>storage/correos/</code>. Puedes seguir desde aquí:</p>
        <a class="boton boton--anil" href="<?= e($enlacePrueba) ?>">Crear mi contraseña nueva</a>
    </div>
<?php endif; ?>

<p class="acceso__alterno">¿No te llegó? <a href="<?= url('/recuperar') ?>">Pide otro enlace</a>.</p>

<?php
$layout = 'acceso';
$frase = 'No pasa nada, a todos se nos olvida alguna vez.';
?>
<a class="volver" href="<?= url('/login') ?>">Volver a iniciar sesión</a>
<h1 class="acceso__titulo">Recupera tu contraseña</h1>
<p class="acceso__intro">Escribe el correo con el que te registraste y te enviaremos un enlace para crear una contraseña nueva.</p>

<form method="post" action="<?= url('/recuperar') ?>" class="formulario" novalidate>
    <?= campo_csrf() ?>

    <?php require RAIZ . '/app/views/auth/_selector_tipo.php'; ?>

    <div class="campo<?= clase_error('correo') ?>">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="<?= e(anterior('correo')) ?>"
               autocomplete="email" placeholder="nombre@correo.com" required<?= aria_error('correo') ?>>
        <?= mensaje_error('correo') ?>
    </div>

    <button class="boton boton--primario boton--bloque" type="submit">Enviarme el enlace</button>
</form>

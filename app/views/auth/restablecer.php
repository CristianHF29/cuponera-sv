<?php
$layout = 'acceso';
$frase = 'Una contraseña nueva y a seguir ahorrando.';
?>
<h1 class="acceso__titulo">Crea tu contraseña nueva</h1>
<p class="acceso__intro">Usa al menos 8 caracteres, con letras y números. Mejor si no es la misma que usas en otros sitios.</p>

<form method="post" action="<?= url('/restablecer') ?>" class="formulario" novalidate>
    <?= campo_csrf() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="campo<?= clase_error('clave') ?>">
        <label for="clave">Contraseña nueva</label>
        <div class="con-boton">
            <input type="password" id="clave" name="clave" autocomplete="new-password" required<?= aria_error('clave') ?>>
            <button type="button" class="ver-clave" data-ver-clave="clave" aria-pressed="false">Mostrar</button>
        </div>
        <?= mensaje_error('clave') ?>
    </div>

    <div class="campo<?= clase_error('clave_confirmacion') ?>">
        <label for="clave_confirmacion">Repite la contraseña</label>
        <input type="password" id="clave_confirmacion" name="clave_confirmacion" autocomplete="new-password" required<?= aria_error('clave_confirmacion') ?>>
        <?= mensaje_error('clave_confirmacion') ?>
    </div>

    <button class="boton boton--primario boton--bloque" type="submit">Guardar contraseña</button>
</form>

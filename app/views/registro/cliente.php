<?php
$layout = 'acceso';
$frase = 'Crea tu cuenta gratis y empieza a ahorrar en negocios de aquí.';
?>
<h1 class="acceso__titulo">Crea tu cuenta</h1>
<p class="acceso__intro">Con tu cuenta compras cupones, guardas tus facturas y tienes tus códigos a mano cuando los necesites.</p>

<form method="post" action="<?= url('/registro/cliente') ?>" class="formulario" novalidate>
    <?= campo_csrf() ?>

    <div class="fila-campos">
        <div class="campo<?= clase_error('nombre_completo') ?>">
            <label for="nombre_completo">Nombre completo</label>
            <input type="text" id="nombre_completo" name="nombre_completo" value="<?= e(anterior('nombre_completo')) ?>"
                   placeholder="Ej. María José" autocomplete="given-name" maxlength="100" required<?= aria_error('nombre_completo') ?>>
            <?= mensaje_error('nombre_completo') ?>
        </div>
        <div class="campo<?= clase_error('apellidos') ?>">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" value="<?= e(anterior('apellidos')) ?>"
                   placeholder="Ej. López Hernández" autocomplete="family-name" maxlength="100" required<?= aria_error('apellidos') ?>>
            <?= mensaje_error('apellidos') ?>
        </div>
    </div>

    <div class="fila-campos">
        <div class="campo<?= clase_error('dui') ?>">
            <label for="dui">DUI</label>
            <input type="text" id="dui" name="dui" value="<?= e(anterior('dui')) ?>" inputmode="numeric"
                   placeholder="00000000-0" maxlength="10" data-mascara="dui" required<?= aria_error('dui') ?>>
            <?= mensaje_error('dui') ?>
        </div>
        <div class="campo<?= clase_error('fecha_nacimiento') ?>">
            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= e(anterior('fecha_nacimiento')) ?>"
                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>" autocomplete="bday" required<?= aria_error('fecha_nacimiento') ?>>
            <?php if (!error_de('fecha_nacimiento')): ?>
                <p class="campo__ayuda">Necesitas tener 18 años o más.</p>
            <?php endif; ?>
            <?= mensaje_error('fecha_nacimiento') ?>
        </div>
    </div>

    <div class="campo<?= clase_error('usuario') ?>">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" value="<?= e(anterior('usuario')) ?>" maxlength="30"
               autocomplete="username" autocapitalize="off" spellcheck="false" placeholder="Ej. maria.lopez" required<?= aria_error('usuario') ?>>
        <?php if (!error_de('usuario')): ?>
            <p class="campo__ayuda">De 4 a 30 caracteres: letras, números, punto o guion bajo.</p>
        <?php endif; ?>
        <?= mensaje_error('usuario') ?>
    </div>

    <div class="campo<?= clase_error('correo') ?>">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="<?= e(anterior('correo')) ?>" maxlength="120"
               autocomplete="email" placeholder="nombre@correo.com" required<?= aria_error('correo') ?>>
        <?= mensaje_error('correo') ?>
    </div>

    <div class="fila-campos">
        <div class="campo<?= clase_error('clave') ?>">
            <label for="clave">Contraseña</label>
            <div class="con-boton">
                <input type="password" id="clave" name="clave" autocomplete="new-password" required<?= aria_error('clave') ?>>
                <button type="button" class="ver-clave" data-ver-clave="clave" aria-pressed="false">Mostrar</button>
            </div>
            <?php if (!error_de('clave')): ?>
                <p class="campo__ayuda">Mínimo 8 caracteres, con letras y números.</p>
            <?php endif; ?>
            <?= mensaje_error('clave') ?>
        </div>
        <div class="campo<?= clase_error('clave_confirmacion') ?>">
            <label for="clave_confirmacion">Repite la contraseña</label>
            <input type="password" id="clave_confirmacion" name="clave_confirmacion" autocomplete="new-password" required<?= aria_error('clave_confirmacion') ?>>
            <?= mensaje_error('clave_confirmacion') ?>
        </div>
    </div>

    <button class="boton boton--primario boton--bloque" type="submit">Crear mi cuenta</button>
</form>

<p class="acceso__alterno">
    ¿Ya tienes cuenta? <a href="<?= url('/login?tipo=cliente') ?>">Inicia sesión</a>.
    ¿Tienes un negocio? <a href="<?= url('/registro/empresa') ?>">Regístralo aquí</a>.
</p>

<?php
$layout = 'acceso';
$frase = 'Lleva más clientes a tu negocio con cupones que la gente sí usa.';
?>
<h1 class="acceso__titulo">Registra tu empresa</h1>
<p class="acceso__intro">Cuéntanos de tu negocio. Un administrador revisa la solicitud y, cuando la apruebe, podrás publicar tus ofertas.</p>

<form method="post" action="<?= url('/registro/empresa') ?>" class="formulario" novalidate>
    <?= campo_csrf() ?>

    <div class="campo<?= clase_error('nombre') ?>">
        <label for="nombre">Nombre de la empresa</label>
        <input type="text" id="nombre" name="nombre" value="<?= e(anterior('nombre')) ?>" maxlength="150"
               autocomplete="organization" placeholder="Ej. Pupusería La Ceiba" required<?= aria_error('nombre') ?>>
        <?= mensaje_error('nombre') ?>
    </div>

    <div class="fila-campos">
        <div class="campo<?= clase_error('nit') ?>">
            <label for="nit">NIT</label>
            <input type="text" id="nit" name="nit" value="<?= e(anterior('nit')) ?>" inputmode="numeric"
                   placeholder="0000-000000-000-0" maxlength="17" data-mascara="nit" required<?= aria_error('nit') ?>>
            <?= mensaje_error('nit') ?>
        </div>
        <div class="campo<?= clase_error('telefono') ?>">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" value="<?= e(anterior('telefono')) ?>" inputmode="numeric"
                   placeholder="2222-0000" maxlength="9" data-mascara="telefono" autocomplete="tel-national" required<?= aria_error('telefono') ?>>
            <?= mensaje_error('telefono') ?>
        </div>
    </div>

    <div class="campo<?= clase_error('direccion') ?>">
        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" value="<?= e(anterior('direccion')) ?>" maxlength="255"
               autocomplete="street-address" placeholder="Calle, número, colonia y municipio" required<?= aria_error('direccion') ?>>
        <?= mensaje_error('direccion') ?>
    </div>

    <div class="campo<?= clase_error('correo') ?>">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="<?= e(anterior('correo')) ?>" maxlength="120"
               autocomplete="email" placeholder="contacto@tunegocio.com" required<?= aria_error('correo') ?>>
        <?= mensaje_error('correo') ?>
    </div>

    <div class="campo<?= clase_error('usuario') ?>">
        <label for="usuario">Usuario para entrar al panel</label>
        <input type="text" id="usuario" name="usuario" value="<?= e(anterior('usuario')) ?>" maxlength="30"
               autocomplete="username" autocapitalize="off" spellcheck="false" required<?= aria_error('usuario') ?>>
        <?php if (!error_de('usuario')): ?>
            <p class="campo__ayuda">De 4 a 30 caracteres: letras, números, punto o guion bajo.</p>
        <?php endif; ?>
        <?= mensaje_error('usuario') ?>
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

    <button class="boton boton--primario boton--bloque" type="submit">Enviar solicitud</button>
</form>

<p class="acceso__alterno">¿Tu empresa ya está registrada? <a href="<?= url('/login?tipo=empresa') ?>">Inicia sesión</a>.</p>

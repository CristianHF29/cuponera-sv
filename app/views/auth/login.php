<?php
$layout = 'acceso';
$frase = 'Qué bueno verte de nuevo. Tus descuentos te esperan.';
?>
<h1 class="acceso__titulo">Inicia sesión</h1>
<p class="acceso__intro">Elige tu tipo de cuenta y entra con tu usuario o tu correo.</p>

<form method="post" action="<?= url('/login') ?>" class="formulario" novalidate>
    <?= campo_csrf() ?>

    <?php require RAIZ . '/app/views/auth/_selector_tipo.php'; ?>

    <div class="campo<?= clase_error('usuario') ?>">
        <label for="usuario">Usuario o correo</label>
        <input type="text" id="usuario" name="usuario" value="<?= e(anterior('usuario')) ?>"
               autocomplete="username" autocapitalize="off" spellcheck="false" required<?= aria_error('usuario') ?>>
        <?= mensaje_error('usuario') ?>
    </div>

    <div class="campo<?= clase_error('clave') ?>">
        <div class="campo__fila">
            <label for="clave">Contraseña</label>
            <a class="campo__enlace" href="<?= url('/recuperar?tipo=' . $tipoElegido) ?>" data-enlace-recuperar>¿Olvidaste tu contraseña?</a>
        </div>
        <div class="con-boton">
            <input type="password" id="clave" name="clave" autocomplete="current-password" required<?= aria_error('clave') ?>>
            <button type="button" class="ver-clave" data-ver-clave="clave" aria-pressed="false">Mostrar</button>
        </div>
        <?= mensaje_error('clave') ?>
    </div>

    <button class="boton boton--primario boton--bloque" type="submit">Entrar</button>
</form>

<p class="acceso__alterno">
    ¿Todavía no tienes cuenta? <a href="<?= url('/registro/cliente') ?>">Créala gratis</a>
    o <a href="<?= url('/registro/empresa') ?>">registra tu negocio</a>.
</p>

<?php if (APP_ENTORNO === 'desarrollo'): ?>
    <details class="datos-prueba">
        <summary>Usuarios de prueba</summary>
        <p>Todos usan la contraseña <strong>Cuponera2026</strong>.</p>
        <table>
            <tr><th scope="row">Cliente</th><td>maria.lopez, carlos.rivas, ana.martinez</td></tr>
            <tr><th scope="row">Empresa aprobada</th><td>laceiba, cafeataco, olasdeltunco, flordeizote</td></tr>
            <tr><th scope="row">Empresa pendiente</th><td>donchepe, miradaclara, volcanfit</td></tr>
            <tr><th scope="row">Empresa rechazada</th><td>sanrafael</td></tr>
            <tr><th scope="row">Administrador</th><td>admin, kmendez</td></tr>
        </table>
    </details>
<?php endif; ?>

<?php
// Menú principal: cambia según quién haya iniciado sesión
$cuentaNav = Auth::usuario();
$tipoNav = $cuentaNav['tipo'] ?? null;

$pendientesNav = 0;
if ($tipoNav === 'administrador') {
    try {
        $pendientesNav = (new Empresa())->conteoPorEstado()['pendiente'];
    } catch (Throwable $e) {
        $pendientesNav = 0; // si la base falla, el menú igual se muestra
    }
}
?>
<header class="barra">
    <div class="barra__interior contenedor">
        <a class="marca" href="<?= url('/') ?>">
            <img class="marca__icono" src="<?= asset('img/logo.svg') ?>" alt="" width="40" height="28">
            <span>La Cuponera <span class="marca__pais">SV</span></span>
        </a>

        <button class="barra__boton-menu" type="button" aria-expanded="false" aria-controls="menu-principal" data-menu-boton>
            <span class="barra__hamburguesa" aria-hidden="true"></span>
            <span class="sr-only">Menú</span>
        </button>

        <nav class="menu" id="menu-principal" aria-label="Principal" data-menu>
            <ul class="menu__lista">
                <?php if ($tipoNav === 'administrador'): ?>
                    <li><a href="<?= url('/admin') ?>"<?= enlace_activo('/admin', true) ?>>Resumen</a></li>
                    <li>
                        <a href="<?= url('/admin/solicitudes') ?>"<?= enlace_activo('/admin/solicitudes') ?>>
                            Solicitudes
                            <?php if ($pendientesNav > 0): ?>
                                <span class="contador" aria-label="<?= $pendientesNav ?> pendientes"><?= $pendientesNav ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="<?= url('/admin/reportes') ?>"<?= enlace_activo('/admin/reportes') ?>>Reportes</a></li>
                    <li><a href="<?= url('/admin/administradores') ?>"<?= enlace_activo('/admin/administradores') ?>>Administradores</a></li>
                <?php elseif ($tipoNav === 'empresa'): ?>
                    <li><a href="<?= url('/empresa') ?>"<?= enlace_activo('/empresa', true) ?>>Mis ofertas</a></li>
                    <li><a href="<?= url('/empresa/ofertas/nueva') ?>"<?= enlace_activo('/empresa/ofertas/nueva') ?>>Publicar oferta</a></li>
                    <li><a href="<?= url('/') ?>">Ver el sitio</a></li>
                <?php else: ?>
                    <li><a href="<?= url('/') ?>"<?= enlace_activo('/', true) ?>>Ofertas</a></li>
                    <?php if ($tipoNav === 'cliente'): ?>
                        <li><a href="<?= url('/mis-cupones') ?>"<?= enlace_activo('/mis-cupones') ?>>Mis cupones</a></li>
                    <?php else: ?>
                        <li><a href="<?= url('/registro/empresa') ?>"<?= enlace_activo('/registro/empresa') ?>>Registra tu negocio</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="menu__cuenta">
                <?php if ($cuentaNav): ?>
                    <span class="menu__saludo">
                        <?= e($tipoNav === 'empresa' ? $cuentaNav['nombre'] : 'Hola, ' . primer_nombre($cuentaNav['nombre'])) ?>
                    </span>
                    <form method="post" action="<?= url('/salir') ?>">
                        <?= campo_csrf() ?>
                        <button class="boton boton--borde-claro boton--chico" type="submit">Cerrar sesión</button>
                    </form>
                <?php else: ?>
                    <a class="menu__enlace-cuenta" href="<?= url('/registro') ?>">Crear cuenta</a>
                    <a class="boton boton--mango boton--chico" href="<?= url('/login') ?>">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

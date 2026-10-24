<?php $tipoActual = Auth::tipo(); ?>
<div class="contenedor">
    <section class="pagina-error">
        <p class="pagina-error__codigo" aria-hidden="true">403</p>
        <h1>Esta sección no es para tu tipo de cuenta</h1>
        <p>
            Entraste con una cuenta de <?= e(mb_strtolower(Auth::TIPOS[$tipoActual] ?? 'invitado')) ?>.
            Si necesitas esta página, cierra sesión y entra con la cuenta correcta.
        </p>
        <a class="boton boton--primario" href="<?= url(Auth::panel()) ?>">Ir a mi inicio</a>
    </section>
</div>

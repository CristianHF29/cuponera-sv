<div class="contenedor">
    <section class="pagina-error">
        <p class="pagina-error__codigo" aria-hidden="true">500</p>

        <?php if (!empty($problemaDeBase)): ?>
            <h1>No pudimos conectar con la base de datos</h1>
            <p>Revisa estos tres puntos y vuelve a cargar la página:</p>
            <ol class="lista-revision">
                <li>MySQL está encendido en el panel de control de XAMPP.</li>
                <li>Importaste el archivo <code>database/cuponera_sv.sql</code> en phpMyAdmin.</li>
                <li>El usuario y la contraseña de MySQL coinciden con los de <code>config/config.php</code>.</li>
            </ol>
        <?php else: ?>
            <h1>Algo salió mal de nuestro lado</h1>
            <p>El error quedó registrado. Intenta de nuevo en un momento.</p>
        <?php endif; ?>

        <?php if (!empty($detalle)): ?>
            <p class="detalle-tecnico"><?= e($detalle) ?></p>
        <?php endif; ?>

        <a class="boton boton--primario" href="<?= url('/') ?>">Volver al inicio</a>
    </section>
</div>

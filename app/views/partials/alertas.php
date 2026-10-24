<?php foreach (mensajes_flash() as $mensaje): ?>
    <div class="alerta alerta--<?= e($mensaje['tipo']) ?>" role="<?= $mensaje['tipo'] === 'error' ? 'alert' : 'status' ?>">
        <p><?= e($mensaje['mensaje']) ?></p>
        <button type="button" class="alerta__cerrar" data-cerrar-alerta aria-label="Cerrar aviso">&times;</button>
    </div>
<?php endforeach; ?>

<?php if (total_errores() > 1): ?>
    <div class="alerta alerta--error" role="alert">
        <p>Hay <?= total_errores() ?> datos por corregir. Revisa los campos marcados en rojo.</p>
    </div>
<?php endif; ?>

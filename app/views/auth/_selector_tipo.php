<?php
// Selector del tipo de cuenta (se usa en el login y en la recuperación de contraseña).
// Necesita $tipoElegido.
?>
<fieldset class="selector-tipo<?= clase_error('tipo') ?>">
    <legend>Tipo de cuenta</legend>
    <div class="selector-tipo__opciones">
        <?php foreach (Auth::TIPOS as $valorTipo => $nombreTipo): ?>
            <label class="selector-tipo__opcion">
                <input type="radio" name="tipo" value="<?= $valorTipo ?>"<?= $tipoElegido === $valorTipo ? ' checked' : '' ?> data-tipo-cuenta>
                <span><?= $nombreTipo ?></span>
            </label>
        <?php endforeach; ?>
    </div>
    <?= mensaje_error('tipo') ?>
</fieldset>

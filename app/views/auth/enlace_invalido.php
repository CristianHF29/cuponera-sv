<?php
$layout = 'acceso';
$frase = 'Por seguridad, los enlaces de recuperación duran poco.';
?>
<h1 class="acceso__titulo">Este enlace ya no sirve</h1>
<p class="acceso__intro">
    Puede que haya vencido (dura <?= MINUTOS_ENLACE_RECUPERACION ?> minutos), que ya lo hayas usado o que pidieras uno
    más nuevo después. Pide otro y ábrelo enseguida.
</p>
<a class="boton boton--primario" href="<?= url('/recuperar') ?>">Pedir un enlace nuevo</a>

<?php
$editando = $oferta !== null;
$accion = $editando ? url('/empresa/ofertas/' . (int) $oferta['id']) : url('/empresa/ofertas');

// valor del campo: lo que escribió antes de un error, lo guardado en la base o un valor inicial
$valor = function (string $campo, string $inicial = '') use ($editando, $oferta) {
    return anterior($campo, $editando ? ($oferta[$campo] ?? '') : $inicial);
};
$estadoElegido = $valor('estado', 'disponible');
?>
<div class="contenedor">
    <nav class="migas" aria-label="Ruta">
        <a href="<?= url('/empresa') ?>">Mis ofertas</a>
        <span aria-hidden="true">/</span>
        <span><?= $editando ? 'Editar oferta' : 'Nueva oferta' ?></span>
    </nav>

    <header class="encabezado-pagina">
        <div>
            <h1><?= $editando ? 'Editar oferta' : 'Publica una oferta' ?></h1>
            <p><?= $editando
                ? 'Los cambios se ven en el sitio en cuanto los guardes.'
                : 'Llena los datos y mira en la vista previa cómo la verán tus clientes.' ?></p>
        </div>
    </header>

    <div class="editor-oferta">
        <form method="post" action="<?= $accion ?>" class="tarjeta formulario" novalidate>
            <?= campo_csrf() ?>

            <div class="campo<?= clase_error('titulo') ?>">
                <label for="titulo">Título de la oferta</label>
                <input type="text" id="titulo" name="titulo" value="<?= e($valor('titulo')) ?>" maxlength="150"
                       placeholder="Ej. Combo familiar: 12 pupusas y 4 refrescos" required<?= aria_error('titulo') ?>>
                <?= mensaje_error('titulo') ?>
            </div>

            <div class="fila-campos">
                <div class="campo<?= clase_error('precio_regular') ?>">
                    <label for="precio_regular">Precio regular ($)</label>
                    <input type="number" id="precio_regular" name="precio_regular" value="<?= e($valor('precio_regular')) ?>"
                           min="0.01" step="0.01" inputmode="decimal" placeholder="18.00" required<?= aria_error('precio_regular') ?>>
                    <?= mensaje_error('precio_regular') ?>
                </div>
                <div class="campo<?= clase_error('precio_oferta') ?>">
                    <label for="precio_oferta">Precio de oferta ($)</label>
                    <input type="number" id="precio_oferta" name="precio_oferta" value="<?= e($valor('precio_oferta')) ?>"
                           min="0.01" step="0.01" inputmode="decimal" placeholder="12.50" required<?= aria_error('precio_oferta') ?>>
                    <?= mensaje_error('precio_oferta') ?>
                </div>
            </div>

            <div class="fila-campos fila-campos--tres">
                <div class="campo<?= clase_error('fecha_inicio') ?>">
                    <label for="fecha_inicio">Inicia</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e($valor('fecha_inicio', date('Y-m-d'))) ?>" required<?= aria_error('fecha_inicio') ?>>
                    <?= mensaje_error('fecha_inicio') ?>
                </div>
                <div class="campo<?= clase_error('fecha_fin') ?>">
                    <label for="fecha_fin">Termina</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= e($valor('fecha_fin', date('Y-m-d', strtotime('+30 days')))) ?>" required<?= aria_error('fecha_fin') ?>>
                    <?= mensaje_error('fecha_fin') ?>
                </div>
                <div class="campo<?= clase_error('fecha_limite_canje') ?>">
                    <label for="fecha_limite_canje">Canje hasta</label>
                    <input type="date" id="fecha_limite_canje" name="fecha_limite_canje" value="<?= e($valor('fecha_limite_canje', date('Y-m-d', strtotime('+45 days')))) ?>" required<?= aria_error('fecha_limite_canje') ?>>
                    <?= mensaje_error('fecha_limite_canje') ?>
                </div>
            </div>

            <div class="campo<?= clase_error('cantidad_cupones') ?>">
                <label for="cantidad_cupones">Cantidad de cupones <span class="campo__opcional">(opcional)</span></label>
                <input type="number" id="cantidad_cupones" name="cantidad_cupones" value="<?= e($valor('cantidad_cupones')) ?>"
                       min="1" step="1" inputmode="numeric" placeholder="Sin límite"<?= aria_error('cantidad_cupones') ?>>
                <?php if (!error_de('cantidad_cupones')): ?>
                    <p class="campo__ayuda">Déjalo vacío si no quieres ponerle límite.</p>
                <?php endif; ?>
                <?= mensaje_error('cantidad_cupones') ?>
            </div>

            <div class="campo<?= clase_error('descripcion') ?>">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="2000"
                          placeholder="Qué incluye, horarios, si es para comer en el local o para llevar..."<?= aria_error('descripcion') ?>><?= e($valor('descripcion')) ?></textarea>
                <?= mensaje_error('descripcion') ?>
            </div>

            <fieldset class="opciones-radio">
                <legend>Estado</legend>
                <label class="opcion-radio">
                    <input type="radio" name="estado" value="disponible"<?= $estadoElegido === 'disponible' ? ' checked' : '' ?>>
                    <span><strong>Disponible</strong><span>Se muestra en el sitio durante su vigencia.</span></span>
                </label>
                <label class="opcion-radio">
                    <input type="radio" name="estado" value="no disponible"<?= $estadoElegido === 'no disponible' ? ' checked' : '' ?>>
                    <span><strong>No disponible</strong><span>Queda guardada pero nadie la ve hasta que la actives.</span></span>
                </label>
                <?= mensaje_error('estado') ?>
            </fieldset>

            <div class="grupo-botones">
                <button class="boton boton--primario" type="submit"><?= $editando ? 'Guardar cambios' : 'Publicar oferta' ?></button>
                <a class="boton boton--borde" href="<?= url('/empresa') ?>">Cancelar</a>
            </div>
        </form>

        <aside class="vista-previa" aria-label="Vista previa del cupón">
            <p class="vista-previa__titulo">Así se verá en el sitio</p>
            <article class="cupon cupon--pitahaya" data-previa>
                <div class="cupon__enlace">
                    <div class="cupon__cuerpo">
                        <p class="cupon__empresa"><?= e($empresa['nombre']) ?></p>
                        <h3 class="cupon__titulo" data-previa-titulo>El título de tu oferta</h3>
                        <p class="cupon__precios">
                            <span class="cupon__precio" data-previa-precio>$0.00</span>
                            <s class="cupon__antes" data-previa-antes>$0.00</s>
                        </p>
                        <p class="cupon__detalle" data-previa-fin>Hasta el ...</p>
                    </div>
                    <div class="cupon__talon">
                        <span class="cupon__porcentaje" data-previa-porcentaje>−0%</span>
                        <span class="cupon__ahorro" data-previa-ahorro>ahorras $0.00</span>
                    </div>
                </div>
            </article>
            <p class="campo__ayuda">Cada cliente puede comprar hasta <?= MAX_CUPONES_POR_OFERTA ?> cupones de una misma oferta.</p>
            <?php if ($editando && (int) $oferta['vendidos'] > 0): ?>
                <p class="campo__ayuda">Esta oferta ya vendió <?= (int) $oferta['vendidos'] ?> cupones.</p>
            <?php endif; ?>
        </aside>
    </div>
</div>

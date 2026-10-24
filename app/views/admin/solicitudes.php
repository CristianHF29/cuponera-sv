<?php
$pestanas = ['pendiente' => 'Pendientes', 'aprobada' => 'Aprobadas', 'rechazada' => 'Rechazadas'];
?>
<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Solicitudes de empresas</h1>
            <p>Revisa los datos de cada negocio. Al aprobarlo defines la comisión que se cobra por cada cupón vendido.</p>
        </div>
    </header>

    <nav class="pestanas" aria-label="Estado de las solicitudes">
        <?php foreach ($pestanas as $clave => $nombre): ?>
            <a href="<?= url('/admin/solicitudes?estado=' . $clave) ?>"<?= $estado === $clave ? ' aria-current="page"' : '' ?>>
                <?= $nombre ?> <span class="numerito"><?= $conteo[$clave] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (!$empresas): ?>
        <div class="vacio">
            <?php if ($estado === 'pendiente'): ?>
                <h2>Todo al día</h2>
                <p>No hay empresas esperando revisión. Las nuevas solicitudes van a aparecer aquí.</p>
            <?php else: ?>
                <h2>Nada por aquí todavía</h2>
                <p>Aún no hay solicitudes <?= $estado === 'aprobada' ? 'aprobadas' : 'rechazadas' ?>.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($estado === 'pendiente'): ?>
        <div class="solicitudes">
            <?php foreach ($empresas as $empresa): $id = (int) $empresa['id']; ?>
                <article class="solicitud" id="empresa-<?= $id ?>">
                    <div class="solicitud__datos">
                        <h2 class="solicitud__nombre"><?= e($empresa['nombre']) ?></h2>
                        <p class="solicitud__fecha">Solicitud enviada el <?= fecha_y_hora($empresa['creado_en']) ?></p>
                        <dl class="datos">
                            <div><dt>NIT</dt><dd><?= e($empresa['nit']) ?></dd></div>
                            <div><dt>Teléfono</dt><dd><?= e($empresa['telefono']) ?></dd></div>
                            <div><dt>Correo</dt><dd><?= e($empresa['correo']) ?></dd></div>
                            <div><dt>Usuario</dt><dd><?= e($empresa['usuario']) ?></dd></div>
                            <div class="datos__ancho"><dt>Dirección</dt><dd><?= e($empresa['direccion']) ?></dd></div>
                        </dl>
                    </div>

                    <div class="solicitud__decision">
                        <form method="post" action="<?= url('/admin/solicitudes/' . $id . '/aprobar') ?>" class="formulario" novalidate>
                            <?= campo_csrf() ?>
                            <div class="campo<?= clase_error('comision_' . $id) ?>">
                                <label for="comision-<?= $id ?>">Comisión por cupón vendido</label>
                                <div class="con-sufijo">
                                    <input type="number" id="comision-<?= $id ?>" name="comision" min="0.01" max="100" step="0.01"
                                           inputmode="decimal" placeholder="10" value="<?= e(anterior('comision_' . $id)) ?>"<?= aria_error('comision_' . $id) ?>>
                                    <span class="sufijo" aria-hidden="true">%</span>
                                </div>
                                <?= mensaje_error('comision_' . $id) ?>
                            </div>
                            <button class="boton boton--anil boton--bloque" type="submit">Aprobar empresa</button>
                        </form>

                        <details class="rechazo"<?= error_de('motivo_' . $id) ? ' open' : '' ?>>
                            <summary>Rechazar solicitud</summary>
                            <form method="post" action="<?= url('/admin/solicitudes/' . $id . '/rechazar') ?>" class="formulario" novalidate
                                  data-confirmar="¿Seguro que quieres rechazar la solicitud de <?= e($empresa['nombre']) ?>?">
                                <?= campo_csrf() ?>
                                <div class="campo<?= clase_error('motivo_' . $id) ?>">
                                    <label for="motivo-<?= $id ?>">Motivo del rechazo</label>
                                    <textarea id="motivo-<?= $id ?>" name="motivo" rows="3" maxlength="255"
                                              placeholder="Ej. El NIT no coincide con el nombre del negocio."<?= aria_error('motivo_' . $id) ?>><?= e(anterior('motivo_' . $id)) ?></textarea>
                                    <p class="campo__ayuda">La empresa verá este mensaje cuando intente iniciar sesión.</p>
                                    <?= mensaje_error('motivo_' . $id) ?>
                                </div>
                                <button class="boton boton--peligro boton--bloque" type="submit">Rechazar solicitud</button>
                            </form>
                        </details>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Empresa</th>
                        <th scope="col">NIT</th>
                        <?php if ($estado === 'aprobada'): ?>
                            <th scope="col" class="numero">Comisión</th>
                        <?php else: ?>
                            <th scope="col">Motivo</th>
                        <?php endif; ?>
                        <th scope="col">Revisada por</th>
                        <th scope="col">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $empresa): ?>
                        <tr>
                            <td>
                                <span class="tabla__titulo"><?= e($empresa['nombre']) ?></span>
                                <span class="tabla__sub"><?= e($empresa['correo']) ?></span>
                            </td>
                            <td><?= e($empresa['nit']) ?></td>
                            <?php if ($estado === 'aprobada'): ?>
                                <td class="numero"><?= porcentaje($empresa['porcentaje_comision']) ?></td>
                            <?php else: ?>
                                <td class="tabla__motivo"><?= e($empresa['motivo_rechazo']) ?></td>
                            <?php endif; ?>
                            <td><?= e($empresa['revisado_por_nombre'] ?? 'Sin registro') ?></td>
                            <td><?= fecha_corta($empresa['revisado_en']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="contenedor">
    <header class="encabezado-pagina">
        <div>
            <h1>Administradores</h1>
            <p>Las personas que pueden aprobar empresas y ver los reportes.</p>
        </div>
    </header>

    <div class="dos-columnas">
        <section aria-label="Lista de administradores">
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Registrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($administradores as $admin): ?>
                            <tr>
                                <td>
                                    <span class="tabla__titulo"><?= e($admin['nombre']) ?><?= (int) $admin['id'] === Auth::id() ? ' (tú)' : '' ?></span>
                                    <span class="tabla__sub"><?= e($admin['correo']) ?></span>
                                </td>
                                <td><?= e($admin['usuario']) ?></td>
                                <td>
                                    <?= fecha_corta($admin['creado_en']) ?>
                                    <span class="tabla__sub"><?= $admin['creado_por_nombre'] ? 'por ' . e($admin['creado_por_nombre']) : 'cuenta inicial' ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="tarjeta">
            <h2 class="tarjeta__titulo">Registrar un administrador</h2>
            <form method="post" action="<?= url('/admin/administradores') ?>" class="formulario" novalidate>
                <?= campo_csrf() ?>

                <div class="campo<?= clase_error('nombre') ?>">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= e(anterior('nombre')) ?>" maxlength="100"
                           autocomplete="off" placeholder="Ej. Roberto Alvarado"<?= aria_error('nombre') ?>>
                    <?= mensaje_error('nombre') ?>
                </div>

                <div class="campo<?= clase_error('correo') ?>">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" value="<?= e(anterior('correo')) ?>" maxlength="120"
                           autocomplete="off"<?= aria_error('correo') ?>>
                    <?= mensaje_error('correo') ?>
                </div>

                <div class="campo<?= clase_error('usuario') ?>">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" value="<?= e(anterior('usuario')) ?>" maxlength="30"
                           autocomplete="off"<?= aria_error('usuario') ?>>
                    <?= mensaje_error('usuario') ?>
                </div>

                <div class="campo<?= clase_error('clave') ?>">
                    <label for="clave">Contraseña temporal</label>
                    <div class="con-boton">
                        <input type="password" id="clave" name="clave" autocomplete="new-password"<?= aria_error('clave') ?>>
                        <button type="button" class="ver-clave" data-ver-clave="clave" aria-pressed="false">Mostrar</button>
                    </div>
                    <p class="campo__ayuda">Mínimo 8 caracteres, con letras y números. La persona puede cambiarla con «¿Olvidaste tu contraseña?».</p>
                    <?= mensaje_error('clave') ?>
                </div>

                <div class="campo<?= clase_error('clave_confirmacion') ?>">
                    <label for="clave_confirmacion">Repite la contraseña</label>
                    <input type="password" id="clave_confirmacion" name="clave_confirmacion" autocomplete="new-password"<?= aria_error('clave_confirmacion') ?>>
                    <?= mensaje_error('clave_confirmacion') ?>
                </div>

                <button class="boton boton--primario boton--bloque" type="submit">Registrar administrador</button>
            </form>
        </section>
    </div>
</div>

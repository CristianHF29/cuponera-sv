<div class="contenedor contenedor--angosto">
    <section class="tarjeta tarjeta--aire">
        <h1>Recibimos la solicitud de <?= e($nombreEmpresa) ?></h1>
        <p class="texto-grande">Gracias por querer ser parte de La Cuponera. Esto es lo que sigue:</p>

        <ol class="pasos">
            <li>
                <div>
                    <strong>Revisamos tus datos</strong>
                    Un administrador confirma el NIT y la información de contacto de tu negocio.
                </div>
            </li>
            <li>
                <div>
                    <strong>Se fija la comisión</strong>
                    Al aprobar tu empresa se define el porcentaje que se cobra por cada cupón vendido.
                </div>
            </li>
            <li>
                <div>
                    <strong>Publicas tus ofertas</strong>
                    Entras con tu usuario y contraseña y creas tu primera oferta desde el panel.
                </div>
            </li>
        </ol>

        <div class="grupo-botones">
            <a class="boton boton--primario" href="<?= url('/login?tipo=empresa') ?>">Ir a iniciar sesión</a>
            <a class="boton boton--borde" href="<?= url('/') ?>">Ver las ofertas</a>
        </div>
    </section>
</div>

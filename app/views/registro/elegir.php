<div class="contenedor contenedor--angosto">
    <header class="encabezado-pagina encabezado-pagina--centro">
        <div>
            <h1>¿Cómo quieres usar La Cuponera?</h1>
            <p>Elige el tipo de cuenta. Te toma menos de dos minutos.</p>
        </div>
    </header>

    <div class="opciones-registro">
        <a class="opcion opcion--cliente" href="<?= url('/registro/cliente') ?>">
            <span class="opcion__icono" aria-hidden="true">%</span>
            <h2>Quiero comprar cupones</h2>
            <p>Descuentos en comida, bienestar, aventura y más. Pagas en línea y canjeas en el local.</p>
            <span class="opcion__accion">Crear cuenta de cliente</span>
        </a>
        <a class="opcion opcion--empresa" href="<?= url('/registro/empresa') ?>">
            <span class="opcion__icono" aria-hidden="true">+</span>
            <h2>Tengo un negocio</h2>
            <p>Publica ofertas, atrae clientes nuevos y paga solo una comisión por cada cupón vendido.</p>
            <span class="opcion__accion">Registrar mi empresa</span>
        </a>
    </div>

    <p class="texto-centro">¿Ya tienes cuenta? <a href="<?= url('/login') ?>">Inicia sesión</a></p>
</div>

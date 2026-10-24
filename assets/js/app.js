// La Cuponera SV - pequeñas ayudas para que el sitio sea más cómodo de usar.
// Todo funciona igual sin JavaScript; esto solo mejora la experiencia.

document.addEventListener('DOMContentLoaded', () => {

    // Menú en celulares
    const botonMenu = document.querySelector('[data-menu-boton]');
    const menu = document.querySelector('[data-menu]');
    if (botonMenu && menu) {
        botonMenu.addEventListener('click', () => {
            const abierto = menu.classList.toggle('abierto');
            botonMenu.setAttribute('aria-expanded', String(abierto));
        });
    }

    // Mostrar u ocultar la contraseña
    document.querySelectorAll('[data-ver-clave]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.dataset.verClave);
            if (!campo) return;
            const seVe = campo.type === 'text';
            campo.type = seVe ? 'password' : 'text';
            boton.textContent = seVe ? 'Mostrar' : 'Ocultar';
            boton.setAttribute('aria-pressed', String(!seVe));
        });
    });

    // Guiones automáticos para DUI, NIT y teléfono
    const mascaras = {
        dui: '########-#',
        nit: '####-######-###-#',
        telefono: '####-####',
    };
    document.querySelectorAll('[data-mascara]').forEach((campo) => {
        const patron = mascaras[campo.dataset.mascara];
        if (!patron) return;
        campo.addEventListener('input', () => {
            const digitos = campo.value.replace(/\D/g, '');
            let resultado = '';
            let i = 0;
            for (const caracter of patron) {
                if (i >= digitos.length) break;
                resultado += caracter === '#' ? digitos[i++] : caracter;
            }
            campo.value = resultado;
        });
    });

    // Tarjeta en grupos de 4 y vencimiento como MM/AA
    const tarjeta = document.querySelector('[data-tarjeta]');
    if (tarjeta) {
        tarjeta.addEventListener('input', () => {
            const digitos = tarjeta.value.replace(/\D/g, '').slice(0, 19);
            tarjeta.value = digitos.replace(/(\d{4})(?=\d)/g, '$1 ');
        });
    }
    const vencimiento = document.querySelector('[data-vencimiento]');
    if (vencimiento) {
        vencimiento.addEventListener('input', () => {
            let digitos = vencimiento.value.replace(/\D/g, '').slice(0, 4);
            if (digitos.length > 2) digitos = digitos.slice(0, 2) + '/' + digitos.slice(2);
            vencimiento.value = digitos;
        });
    }

    // Total de la compra según la cantidad elegida
    const cantidad = document.querySelector('[data-cantidad]');
    const total = document.querySelector('[data-total]');
    if (cantidad && total) {
        const precio = parseFloat(cantidad.dataset.precio);
        const pintarTotal = () => {
            total.textContent = '$' + (precio * Number(cantidad.value)).toFixed(2);
        };
        cantidad.addEventListener('change', pintarTotal);
        pintarTotal();
    }

    // El enlace "¿Olvidaste tu contraseña?" lleva el tipo de cuenta elegido
    const enlaceRecuperar = document.querySelector('[data-enlace-recuperar]');
    if (enlaceRecuperar) {
        document.querySelectorAll('[data-tipo-cuenta]').forEach((opcion) => {
            opcion.addEventListener('change', () => {
                const destino = new URL(enlaceRecuperar.href, window.location.href);
                destino.searchParams.set('tipo', opcion.value);
                enlaceRecuperar.href = destino.toString();
            });
        });
    }

    // Confirmar antes de acciones que no se pueden deshacer
    document.querySelectorAll('form[data-confirmar]').forEach((formulario) => {
        formulario.addEventListener('submit', (evento) => {
            if (!window.confirm(formulario.dataset.confirmar)) evento.preventDefault();
        });
    });

    // Cerrar avisos
    document.querySelectorAll('[data-cerrar-alerta]').forEach((boton) => {
        boton.addEventListener('click', () => boton.closest('.alerta').remove());
    });

    // Imprimir factura o reportes
    document.querySelectorAll('[data-imprimir]').forEach((boton) => {
        boton.addEventListener('click', () => window.print());
    });

    // Vista previa del cupón mientras la empresa llena el formulario
    const previa = document.querySelector('[data-previa]');
    if (previa) {
        const campo = (nombre) => document.querySelector(`[name="${nombre}"]`);
        const dinero = (valor) => '$' + (Number.isFinite(valor) ? valor : 0).toFixed(2);
        const meses = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sept.', 'oct.', 'nov.', 'dic.'];
        const poner = (selector, texto) => {
            const elemento = previa.querySelector(selector);
            if (elemento) elemento.textContent = texto;
        };

        const pintarPrevia = () => {
            const titulo = campo('titulo').value.trim();
            const regular = parseFloat(campo('precio_regular').value) || 0;
            const oferta = parseFloat(campo('precio_oferta').value) || 0;
            const descuento = regular > 0 && oferta > 0 && oferta < regular ? Math.round((1 - oferta / regular) * 100) : 0;

            poner('[data-previa-titulo]', titulo || 'El título de tu oferta');
            poner('[data-previa-precio]', dinero(oferta));
            poner('[data-previa-antes]', dinero(regular));
            poner('[data-previa-porcentaje]', '−' + descuento + '%');
            poner('[data-previa-ahorro]', 'ahorras ' + dinero(regular > oferta ? regular - oferta : 0));

            const fin = campo('fecha_fin').value;
            if (fin) {
                const [anio, mes, dia] = fin.split('-').map(Number);
                poner('[data-previa-fin]', `Hasta el ${dia} ${meses[mes - 1]} ${anio}`);
            }
        };

        ['titulo', 'precio_regular', 'precio_oferta', 'fecha_fin'].forEach((nombre) => {
            const elemento = campo(nombre);
            if (elemento) elemento.addEventListener('input', pintarPrevia);
        });
        pintarPrevia();
    }
});

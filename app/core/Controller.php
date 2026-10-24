<?php
/*
 * Controlador base. Todos los controladores heredan de aquí.
 */
class Controller
{
    // Revisa que el formulario se haya enviado por POST desde nuestro propio sitio (token CSRF).
    // Si no, regresa a la página indicada con un aviso.
    protected function validarFormulario(string $volverA): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrf_valido()) {
            flash('error', 'Por seguridad el formulario caducó. Vuelve a enviarlo, por favor.');
            redirigir($volverA);
        }
    }
}

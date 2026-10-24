<?php
/*
 * La Cuponera SV
 * Punto de entrada: todas las peticiones llegan aquí (ver .htaccess)
 * y el router decide qué controlador atiende cada ruta.
 */

define('RAIZ', __DIR__);

// Con el servidor integrado de PHP (php -S localhost:8000 index.php) dejamos que
// los archivos de assets/ se sirvan solos, igual que hace Apache.
if (PHP_SAPI === 'cli-server') {
    $pedido = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($pedido, '/assets/') && is_file(RAIZ . $pedido)) {
        return false;
    }
}

require RAIZ . '/config/config.php';
require RAIZ . '/app/core/helpers.php';

// Carga automática de clases: buscamos el archivo con el mismo nombre de la clase
spl_autoload_register(function ($clase) {
    foreach (['core', 'controllers', 'models'] as $carpeta) {
        $archivo = RAIZ . "/app/$carpeta/$clase.php";
        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

date_default_timezone_set(ZONA_HORARIA);
preparar_manejo_de_errores();
iniciar_sesion();

$router = new Router();
foreach (glob(RAIZ . '/routes/*.php') as $archivoDeRutas) {
    require $archivoDeRutas;
}

$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', ruta_actual());

<?php
/*
 * Funciones de apoyo que usa todo el proyecto.
 * Están agrupadas por tema: URLs, vistas, sesión y formularios, seguridad, errores y formatos.
 */

/* ---------------------------------------------------------------
 * URLs
 * ------------------------------------------------------------- */

// Carpeta donde está el proyecto dentro del servidor, por ejemplo "/cuponera-sv"
function ruta_base(): string
{
    $carpeta = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    return rtrim($carpeta, '/');
}

// Ruta que pidió el navegador sin la carpeta del proyecto: "/login", "/oferta/3", etc.
function ruta_actual(): string
{
    if (isset($_GET['r'])) {
        $ruta = (string) $_GET['r'];
    } else {
        $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = ruta_base();
        if ($base !== '' && str_starts_with($ruta, $base)) {
            $ruta = substr($ruta, strlen($base));
        }
    }
    $ruta = '/' . trim(rawurldecode($ruta), '/');
    return $ruta === '/index.php' ? '/' : $ruta;
}

function url(string $ruta = '/'): string
{
    $ruta = '/' . ltrim($ruta, '/');
    if (URL_AMIGABLES) {
        return ruta_base() . $ruta;
    }
    // Sin mod_rewrite la ruta viaja en el parámetro r: index.php?r=login
    [$camino, $consulta] = array_pad(explode('?', $ruta, 2), 2, '');
    $enlace = ruta_base() . '/index.php?r=' . ltrim($camino, '/');
    return $consulta === '' ? $enlace : $enlace . '&' . $consulta;
}

// URL completa (con http://...) para los enlaces que salen en los correos
function url_absoluta(string $ruta): string
{
    $relativa = substr(url($ruta), strlen(ruta_base()));
    if (APP_URL !== '') {
        return rtrim(APP_URL, '/') . $relativa;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ruta_base() . $relativa;
}

// Archivos de la carpeta assets/. El ?v= evita que el navegador use una versión vieja del CSS
function asset(string $archivo): string
{
    $ruta = '/assets/' . ltrim($archivo, '/');
    $version = is_file(RAIZ . $ruta) ? filemtime(RAIZ . $ruta) : 1;
    return ruta_base() . $ruta . '?v=' . $version;
}

function redirigir(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}

// Marca el enlace del menú de la página en la que estamos
function enlace_activo(string $ruta, bool $exacta = false): string
{
    $actual = ruta_actual();
    $activo = $actual === $ruta || (!$exacta && str_starts_with($actual, rtrim($ruta, '/') . '/'));
    return $activo ? ' aria-current="page"' : '';
}

/* ---------------------------------------------------------------
 * Vistas
 * ------------------------------------------------------------- */

// Pinta una vista dentro del layout. Una vista puede pedir otro layout con $layout = 'acceso';
function vista(string $__vista, array $__datos = []): void
{
    extract($__datos, EXTR_SKIP);
    ob_start();
    require RAIZ . '/app/views/' . $__vista . '.php';
    $contenido = ob_get_clean();
    require RAIZ . '/app/views/layout/' . ($layout ?? 'base') . '.php';
}

// Arma una vista y la devuelve como texto, sin layout (la usamos para los correos)
function plantilla(string $__vista, array $__datos = []): string
{
    extract($__datos, EXTR_SKIP);
    ob_start();
    require RAIZ . '/app/views/' . $__vista . '.php';
    return ob_get_clean();
}

function pagina_de_error(int $codigo): void
{
    $titulos = [
        403 => 'No tienes acceso a esta sección',
        404 => 'No encontramos esta página',
    ];
    http_response_code($codigo);
    vista('errores/' . $codigo, ['titulo' => $titulos[$codigo] ?? 'Error']);
    exit;
}

/* ---------------------------------------------------------------
 * Sesión, mensajes y formularios
 * ------------------------------------------------------------- */

function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('cuponera');
    session_set_cookie_params([
        'path' => ruta_base() . '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Los errores y datos del formulario anterior solo duran una petición
    $GLOBALS['formulario_anterior'] = $_SESSION['_formulario'] ?? ['errores' => [], 'datos' => []];
    unset($_SESSION['_formulario']);
}

// Mensaje que se muestra una sola vez en la siguiente página (tipo: exito, error, aviso, info)
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function mensajes_flash(): array
{
    $mensajes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $mensajes;
}

// Regresa al formulario con los errores y con lo que la persona ya había escrito
function volver_con_errores(string $ruta, array $errores, ?array $datos = null): void
{
    $datos = $datos ?? $_POST;
    // Las contraseñas y los datos de la tarjeta nunca se devuelven al formulario
    foreach (['clave', 'clave_confirmacion', 'numero_tarjeta', 'vencimiento', 'cvv', '_token'] as $campo) {
        unset($datos[$campo]);
    }
    $_SESSION['_formulario'] = ['errores' => $errores, 'datos' => $datos];
    redirigir($ruta);
}

// Lo que la persona escribió antes de que el formulario volviera con errores
function anterior(string $campo, $porDefecto = ''): string
{
    $datos = $GLOBALS['formulario_anterior']['datos'] ?? [];
    $valor = array_key_exists($campo, $datos) ? $datos[$campo] : $porDefecto;
    return is_scalar($valor) ? (string) $valor : '';
}

function error_de(string $campo): ?string
{
    return $GLOBALS['formulario_anterior']['errores'][$campo] ?? null;
}

function total_errores(): int
{
    return count($GLOBALS['formulario_anterior']['errores'] ?? []);
}

// Atajos para pintar los campos con error en las vistas
function clase_error(string $campo): string
{
    return error_de($campo) ? ' campo--invalido' : '';
}

function aria_error(string $campo): string
{
    return error_de($campo) ? ' aria-invalid="true" aria-describedby="error-' . e($campo) . '"' : '';
}

function mensaje_error(string $campo): string
{
    $error = error_de($campo);
    return $error ? '<p class="campo__error" id="error-' . e($campo) . '">' . e($error) . '</p>' : '';
}

/* ---------------------------------------------------------------
 * Seguridad
 * ------------------------------------------------------------- */

// Todo lo que viene de la base o del usuario pasa por aquí antes de imprimirse
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function token_csrf(): string
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="_token" value="' . token_csrf() . '">';
}

function csrf_valido(): bool
{
    $enviado = $_POST['_token'] ?? '';
    return is_string($enviado) && isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $enviado);
}

/* ---------------------------------------------------------------
 * Errores
 * ------------------------------------------------------------- */

function registrar_error(string $mensaje): void
{
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
    @file_put_contents(RAIZ . '/storage/logs/errores.log', $linea, FILE_APPEND);
}

// Cualquier excepción que nadie atrapó termina aquí: se registra y se muestra una página amable
function preparar_manejo_de_errores(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', APP_ENTORNO === 'desarrollo' ? '1' : '0');

    set_exception_handler(function (Throwable $e) {
        registrar_error(get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

        // descartamos lo que se alcanzó a armar de la página
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 1045 acceso denegado, 1049 base inexistente, 2002 MySQL apagado, 42S02 falta una tabla
        $problemaDeBase = $e instanceof PDOException
            && in_array((string) $e->getCode(), ['1044', '1045', '1049', '2002', '2006', '42S02'], true);

        if (!headers_sent()) {
            http_response_code(500);
        }
        try {
            vista('errores/500', [
                'titulo' => 'Algo salió mal',
                'problemaDeBase' => $problemaDeBase,
                'detalle' => APP_ENTORNO === 'desarrollo'
                    ? $e->getMessage() . ' (' . basename($e->getFile()) . ', línea ' . $e->getLine() . ')'
                    : null,
            ]);
        } catch (Throwable $otro) {
            echo '<h1>Algo salió mal</h1><p>Intenta de nuevo en unos minutos.</p>';
        }
    });
}

/* ---------------------------------------------------------------
 * Formatos
 * ------------------------------------------------------------- */

function dinero($monto): string
{
    return '$' . number_format((float) $monto, 2);
}

// 10.00 -> "10%", 12.50 -> "12.5%"
function porcentaje($valor): string
{
    $texto = number_format((float) $valor, 2);
    return rtrim(rtrim($texto, '0'), '.') . '%';
}

function porcentaje_descuento($regular, $oferta): int
{
    $regular = (float) $regular;
    return $regular > 0 ? (int) round((1 - (float) $oferta / $regular) * 100) : 0;
}

// "30 de noviembre de 2026"
function fecha_larga(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $momento = strtotime($fecha);
    return date('j', $momento) . ' de ' . $meses[date('n', $momento) - 1] . ' de ' . date('Y', $momento);
}

// "30 nov. 2026"
function fecha_corta(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $meses = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sept.', 'oct.', 'nov.', 'dic.'];
    $momento = strtotime($fecha);
    return date('j', $momento) . ' ' . $meses[date('n', $momento) - 1] . ' ' . date('Y', $momento);
}

// "13 de octubre de 2026, 2:45 p. m."
function fecha_y_hora(?string $fechaHora): string
{
    if (!$fechaHora) {
        return '';
    }
    $momento = strtotime($fechaHora);
    $hora = date('g:i', $momento) . (date('a', $momento) === 'am' ? ' a. m.' : ' p. m.');
    return fecha_larga($fechaHora) . ', ' . $hora;
}

function primer_nombre(string $nombre): string
{
    return explode(' ', trim($nombre))[0];
}

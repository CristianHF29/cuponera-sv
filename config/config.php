<?php
/*
 * Configuración de La Cuponera SV.
 *
 * Los valores de abajo funcionan con un XAMPP recién instalado.
 * Si en tu computadora algo cambia (por ejemplo, MySQL con contraseña),
 * no edites este archivo: crea config/config.local.php y define solo lo distinto.
 *
 *   <?php
 *   define('DB_CLAVE', 'mi_clave');
 *
 * config.local.php no se sube a GitHub (está en .gitignore).
 */

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

function configurar(string $nombre, $valor): void
{
    if (!defined($nombre)) {
        define($nombre, $valor);
    }
}

// General
configurar('APP_NOMBRE', 'La Cuponera SV');
// En 'desarrollo' se ven los detalles de los errores y, si no hay correo configurado,
// el enlace de recuperación aparece en pantalla. Para presentar se puede usar 'produccion'.
configurar('APP_ENTORNO', 'desarrollo');
// Vacío = se calcula solo. Sirve para armar el enlace que va en el correo de recuperación.
configurar('APP_URL', '');
configurar('ZONA_HORARIA', 'America/El_Salvador');

// Base de datos (MySQL o MariaDB de XAMPP)
configurar('DB_HOST', '127.0.0.1');
configurar('DB_PUERTO', 3306);
configurar('DB_NOMBRE', 'cuponera_sv');
configurar('DB_USUARIO', 'root');
configurar('DB_CLAVE', '');

// URLs amigables: /login en lugar de /index.php?r=login
// Necesitan mod_rewrite (viene activo en XAMPP). Si todo da 404, cámbialo a false.
configurar('URL_AMIGABLES', true);

// Correo para recuperar contraseñas
//  'archivo' -> no envía nada: guarda el correo en storage/correos/ (ideal para probar)
//  'smtp'    -> lo envía de verdad con PHPMailer (Gmail, Outlook, Mailtrap...)
configurar('CORREO_MODO', 'archivo');
configurar('SMTP_HOST', 'smtp.gmail.com');
configurar('SMTP_PUERTO', 587);
configurar('SMTP_USUARIO', '');
configurar('SMTP_CLAVE', '');
configurar('CORREO_REMITENTE', '');
configurar('CORREO_REMITENTE_NOMBRE', 'La Cuponera SV');

// Reglas del negocio
configurar('MAX_CUPONES_POR_OFERTA', 5);       // por cliente, según el enunciado
configurar('MINUTOS_ENLACE_RECUPERACION', 30);

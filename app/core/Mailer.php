<?php
/*
 * Envío de correos.
 *  - CORREO_MODO = 'archivo': guarda el correo como HTML en storage/correos/ (para probar sin internet)
 *  - CORREO_MODO = 'smtp':    lo envía con PHPMailer usando los datos SMTP de config.php
 *
 * Devuelve 'enviado', 'archivo' o 'error'.
 */
class Mailer
{
    public static function enviar(string $para, string $nombre, string $asunto, string $html): string
    {
        if (CORREO_MODO === 'smtp') {
            try {
                self::enviarPorSmtp($para, $nombre, $asunto, $html);
                return 'enviado';
            } catch (Throwable $e) {
                registrar_error('No se pudo enviar el correo a ' . $para . ': ' . $e->getMessage());
                return 'error';
            }
        }

        self::guardarEnArchivo($para, $asunto, $html);
        return 'archivo';
    }

    private static function enviarPorSmtp(string $para, string $nombre, string $asunto, string $html): void
    {
        $carpeta = RAIZ . '/app/lib/PHPMailer/';
        if (!is_file($carpeta . 'PHPMailer.php')) {
            throw new RuntimeException('Falta PHPMailer en app/lib/PHPMailer/');
        }
        require_once $carpeta . 'Exception.php';
        require_once $carpeta . 'PHPMailer.php';
        require_once $carpeta . 'SMTP.php';

        $correo = new PHPMailer\PHPMailer\PHPMailer(true);
        $correo->isSMTP();
        $correo->Host = SMTP_HOST;
        $correo->Port = (int) SMTP_PUERTO;
        $correo->SMTPAuth = true;
        $correo->Username = SMTP_USUARIO;
        $correo->Password = SMTP_CLAVE;
        $correo->SMTPSecure = (int) SMTP_PUERTO === 465 ? 'ssl' : 'tls';
        $correo->CharSet = 'UTF-8';

        $correo->setFrom(CORREO_REMITENTE !== '' ? CORREO_REMITENTE : SMTP_USUARIO, CORREO_REMITENTE_NOMBRE);
        $correo->addAddress($para, $nombre);
        $correo->isHTML(true);
        $correo->Subject = $asunto;
        $correo->Body = $html;
        $correo->AltBody = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        $correo->send();
    }

    private static function guardarEnArchivo(string $para, string $asunto, string $html): void
    {
        $nombreArchivo = date('Y-m-d_His') . '_' . preg_replace('/[^a-z0-9]+/i', '-', $para) . '.html';
        $encabezado = '<!-- Para: ' . $para . ' | Asunto: ' . $asunto . ' -->' . PHP_EOL;
        file_put_contents(RAIZ . '/storage/correos/' . $nombreArchivo, $encabezado . $html);
    }
}

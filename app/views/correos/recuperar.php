<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recupera tu contraseña</title>
</head>
<body style="margin:0;padding:0;background:#F5F6FB;font-family:Arial,Helvetica,sans-serif;color:#1C1E33;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F6FB;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="background:#2D3A8C;padding:22px 28px;color:#FFFFFF;font-size:20px;font-weight:bold;">
                        La Cuponera <span style="background:#FFB21C;color:#1C1E33;font-size:12px;padding:2px 6px;border-radius:5px;">SV</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 14px;font-size:18px;font-weight:bold;">Hola, <?= e($nombre) ?></p>
                        <p style="margin:0 0 14px;line-height:1.5;">
                            Recibimos una solicitud para cambiar la contraseña de tu cuenta.
                            Si fuiste tú, presiona el botón y crea una nueva:
                        </p>
                        <p style="margin:24px 0;">
                            <a href="<?= e($enlace) ?>" style="display:inline-block;background:#E0306E;color:#FFFFFF;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:999px;">Crear contraseña nueva</a>
                        </p>
                        <p style="margin:0 0 14px;line-height:1.5;font-size:14px;color:#575B78;">
                            El enlace vence en <?= (int) $minutos ?> minutos y solo funciona una vez.
                            Si no pediste el cambio, ignora este correo: tu contraseña sigue igual.
                        </p>
                        <p style="margin:0;font-size:12px;color:#575B78;word-break:break-all;">
                            Si el botón no abre, copia esta dirección en tu navegador:<br><?= e($enlace) ?>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

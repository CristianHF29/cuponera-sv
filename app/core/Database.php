<?php
/*
 * Conexión a MySQL con PDO.
 * Se abre una sola vez por petición y todos los modelos la comparten.
 */
class Database
{
    private static ?PDO $conexion = null;

    public static function conexion(): PDO
    {
        if (self::$conexion === null) {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PUERTO, DB_NOMBRE);

            self::$conexion = new PDO($dsn, DB_USUARIO, DB_CLAVE, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Que MySQL use la misma hora que PHP (El Salvador está en UTC-6 todo el año)
            self::$conexion->exec("SET time_zone = '-06:00'");
        }
        return self::$conexion;
    }
}

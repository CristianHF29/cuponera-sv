<?php

class Administrador extends ModeloUsuario
{
    protected string $tabla = 'administradores';

    public function listado(): array
    {
        return $this->filas(
            'SELECT a.id, a.nombre, a.correo, a.usuario, a.creado_en, c.nombre AS creado_por_nombre
             FROM administradores a
             LEFT JOIN administradores c ON c.id = a.creado_por
             ORDER BY a.creado_en DESC, a.id DESC'
        );
    }

    public function crear(array $datos, int $creadoPor): int
    {
        $this->ejecutar(
            'INSERT INTO administradores (nombre, correo, usuario, contrasena, creado_por) VALUES (?, ?, ?, ?, ?)',
            [
                $datos['nombre'],
                $datos['correo'],
                $datos['usuario'],
                password_hash($datos['clave'], PASSWORD_DEFAULT),
                $creadoPor,
            ]
        );
        return (int) $this->db->lastInsertId();
    }
}

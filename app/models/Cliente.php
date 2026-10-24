<?php

class Cliente extends ModeloUsuario
{
    protected string $tabla = 'clientes';

    public function registrar(array $datos): int
    {
        $this->ejecutar(
            'INSERT INTO clientes (usuario, correo, contrasena, nombre_completo, apellidos, dui, fecha_nacimiento)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $datos['usuario'],
                $datos['correo'],
                password_hash($datos['clave'], PASSWORD_DEFAULT),
                $datos['nombre_completo'],
                $datos['apellidos'],
                $datos['dui'],
                $datos['fecha_nacimiento'],
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function total(): int
    {
        return (int) $this->valorUnico('SELECT COUNT(*) FROM clientes');
    }
}

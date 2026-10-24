<?php

class Empresa extends ModeloUsuario
{
    protected string $tabla = 'empresas';

    // Toda empresa nueva entra como "pendiente" hasta que un administrador la revise
    public function registrar(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO empresas (nombre, nit, direccion, telefono, correo, usuario, contrasena, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')",
            [
                $datos['nombre'],
                $datos['nit'],
                $datos['direccion'],
                $datos['telefono'],
                $datos['correo'],
                $datos['usuario'],
                password_hash($datos['clave'], PASSWORD_DEFAULT),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function porEstado(string $estado): array
    {
        // las pendientes, de la más antigua a la más nueva (orden de llegada)
        $orden = $estado === 'pendiente' ? 'e.creado_en ASC' : 'e.revisado_en DESC';
        return $this->filas(
            "SELECT e.*, a.nombre AS revisado_por_nombre
             FROM empresas e
             LEFT JOIN administradores a ON a.id = e.revisado_por
             WHERE e.estado = ?
             ORDER BY $orden",
            [$estado]
        );
    }

    public function conteoPorEstado(): array
    {
        $conteo = ['pendiente' => 0, 'aprobada' => 0, 'rechazada' => 0];
        foreach ($this->filas('SELECT estado, COUNT(*) AS total FROM empresas GROUP BY estado') as $fila) {
            $conteo[$fila['estado']] = (int) $fila['total'];
        }
        return $conteo;
    }

    // Solo se aprueban o rechazan solicitudes que siguen pendientes
    public function aprobar(int $id, float $comision, int $adminId): bool
    {
        return $this->ejecutar(
            "UPDATE empresas
             SET estado = 'aprobada', porcentaje_comision = ?, motivo_rechazo = NULL,
                 revisado_por = ?, revisado_en = NOW()
             WHERE id = ? AND estado = 'pendiente'",
            [$comision, $adminId, $id]
        ) > 0;
    }

    public function rechazar(int $id, string $motivo, int $adminId): bool
    {
        return $this->ejecutar(
            "UPDATE empresas
             SET estado = 'rechazada', motivo_rechazo = ?, porcentaje_comision = NULL,
                 revisado_por = ?, revisado_en = NOW()
             WHERE id = ? AND estado = 'pendiente'",
            [$motivo, $adminId, $id]
        ) > 0;
    }
}

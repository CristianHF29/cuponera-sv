<?php
/*
 * Clase base de los modelos. Todos usan la misma conexión PDO
 * y estos atajos para no repetir prepare/execute en cada consulta.
 */
abstract class Modelo
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    protected function fila(string $sql, array $parametros = []): ?array
    {
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        $fila = $consulta->fetch();
        return $fila === false ? null : $fila;
    }

    protected function filas(string $sql, array $parametros = []): array
    {
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    protected function valorUnico(string $sql, array $parametros = [])
    {
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchColumn();
    }

    // Devuelve cuántas filas se modificaron
    protected function ejecutar(string $sql, array $parametros = []): int
    {
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->rowCount();
    }
}

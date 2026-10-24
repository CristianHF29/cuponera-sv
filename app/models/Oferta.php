<?php

class Oferta extends Modelo
{
    // Cupones vendidos de cada oferta (se calcula sumando las compras)
    private const VENDIDOS = '(SELECT COALESCE(SUM(c.cantidad), 0) FROM compras c WHERE c.oferta_id = o.id)';

    // Lo que tiene que cumplir una oferta para verse en el sitio
    private const VISIBLE = "o.estado = 'disponible' AND e.estado = 'aprobada'
                             AND CURDATE() BETWEEN o.fecha_inicio AND o.fecha_fin";

    // Ofertas que ve el público en la página principal (con búsqueda opcional)
    public function disponibles(string $busqueda = ''): array
    {
        $sql = 'SELECT o.*, e.nombre AS empresa, ' . self::VENDIDOS . ' AS vendidos
                FROM ofertas o
                JOIN empresas e ON e.id = o.empresa_id
                WHERE ' . self::VISIBLE;
        $parametros = [];

        if ($busqueda !== '') {
            $sql .= ' AND (o.titulo LIKE ? OR o.descripcion LIKE ? OR e.nombre LIKE ?)';
            $parecido = '%' . addcslashes($busqueda, '%_\\') . '%';
            $parametros = [$parecido, $parecido, $parecido];
        }

        // las agotadas tampoco se muestran; primero las que están por terminar
        $sql = "SELECT * FROM ($sql) AS t
                WHERE t.cantidad_cupones IS NULL OR t.vendidos < t.cantidad_cupones
                ORDER BY t.fecha_fin ASC, t.id DESC";

        return $this->filas($sql, $parametros);
    }

    public function detalle(int $id): ?array
    {
        return $this->fila(
            'SELECT o.*, e.nombre AS empresa, e.estado AS empresa_estado, e.direccion AS empresa_direccion,
                    e.telefono AS empresa_telefono, ' . self::VENDIDOS . ' AS vendidos
             FROM ofertas o
             JOIN empresas e ON e.id = o.empresa_id
             WHERE o.id = ?',
            [$id]
        );
    }

    public function deEmpresa(int $empresaId): array
    {
        return $this->filas(
            'SELECT o.*, ' . self::VENDIDOS . ' AS vendidos,
                    (SELECT COALESCE(SUM(c.total), 0) FROM compras c WHERE c.oferta_id = o.id) AS ingresos
             FROM ofertas o
             WHERE o.empresa_id = ?
             ORDER BY o.creado_en DESC, o.id DESC',
            [$empresaId]
        );
    }

    // Una empresa solo puede ver y editar sus propias ofertas
    public function buscarDeEmpresa(int $id, int $empresaId): ?array
    {
        return $this->fila(
            'SELECT o.*, ' . self::VENDIDOS . ' AS vendidos FROM ofertas o WHERE o.id = ? AND o.empresa_id = ?',
            [$id, $empresaId]
        );
    }

    public function crear(int $empresaId, array $d): int
    {
        $this->ejecutar(
            'INSERT INTO ofertas (empresa_id, titulo, precio_regular, precio_oferta, fecha_inicio, fecha_fin,
                                  fecha_limite_canje, cantidad_cupones, descripcion, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$empresaId, $d['titulo'], $d['precio_regular'], $d['precio_oferta'], $d['fecha_inicio'],
             $d['fecha_fin'], $d['fecha_limite_canje'], $d['cantidad_cupones'], $d['descripcion'], $d['estado']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, int $empresaId, array $d): void
    {
        $this->ejecutar(
            'UPDATE ofertas
             SET titulo = ?, precio_regular = ?, precio_oferta = ?, fecha_inicio = ?, fecha_fin = ?,
                 fecha_limite_canje = ?, cantidad_cupones = ?, descripcion = ?, estado = ?
             WHERE id = ? AND empresa_id = ?',
            [$d['titulo'], $d['precio_regular'], $d['precio_oferta'], $d['fecha_inicio'], $d['fecha_fin'],
             $d['fecha_limite_canje'], $d['cantidad_cupones'], $d['descripcion'], $d['estado'], $id, $empresaId]
        );
    }

    public function cambiarEstado(int $id, int $empresaId, string $estado): void
    {
        $this->ejecutar('UPDATE ofertas SET estado = ? WHERE id = ? AND empresa_id = ?', [$estado, $id, $empresaId]);
    }

    /* ---- ayudas para las vistas ---- */

    // En qué situación está la oferta hoy: disponible, pausada, programada, vencida o agotada
    public static function situacion(array $oferta): string
    {
        $hoy = date('Y-m-d');
        if ($oferta['estado'] !== 'disponible') {
            return 'pausada';
        }
        if ($oferta['fecha_inicio'] > $hoy) {
            return 'programada';
        }
        if ($oferta['fecha_fin'] < $hoy) {
            return 'vencida';
        }
        if ($oferta['cantidad_cupones'] !== null && (int) $oferta['vendidos'] >= (int) $oferta['cantidad_cupones']) {
            return 'agotada';
        }
        return 'disponible';
    }

    // Cupones que quedan, o null si la oferta no tiene límite
    public static function restantes(array $oferta): ?int
    {
        if ($oferta['cantidad_cupones'] === null) {
            return null;
        }
        return max(0, (int) $oferta['cantidad_cupones'] - (int) $oferta['vendidos']);
    }
}

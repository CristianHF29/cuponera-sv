<?php

class Compra extends Modelo
{
    public function cuponesDelCliente(int $clienteId, int $ofertaId): int
    {
        return (int) $this->valorUnico(
            'SELECT COALESCE(SUM(cantidad), 0) FROM compras WHERE cliente_id = ? AND oferta_id = ?',
            [$clienteId, $ofertaId]
        );
    }

    /*
     * Registra la compra y genera un código único por cada cupón.
     * Todo va dentro de una transacción: si algo falla, no queda una compra a medias.
     * Si no se puede vender, lanza DomainException con un mensaje para el cliente.
     */
    public function registrar(int $clienteId, int $ofertaId, int $cantidad, string $ultimos4): int
    {
        $this->db->beginTransaction();
        try {
            // FOR UPDATE bloquea la oferta mientras dura la compra, así dos personas
            // no pueden llevarse el último cupón al mismo tiempo
            $oferta = $this->fila(
                'SELECT o.*, e.estado AS empresa_estado
                 FROM ofertas o JOIN empresas e ON e.id = o.empresa_id
                 WHERE o.id = ? FOR UPDATE',
                [$ofertaId]
            );
            if (!$oferta) {
                throw new DomainException('Esa oferta ya no existe.');
            }

            $oferta['vendidos'] = (int) $this->valorUnico(
                'SELECT COALESCE(SUM(cantidad), 0) FROM compras WHERE oferta_id = ? FOR UPDATE',
                [$ofertaId]
            );

            $situacion = Oferta::situacion($oferta);
            if ($situacion === 'agotada') {
                throw new DomainException('Se acabaron los cupones de esta oferta. ¡Llegaste un poquito tarde!');
            }
            if ($oferta['empresa_estado'] !== 'aprobada' || $situacion !== 'disponible') {
                throw new DomainException('Esta oferta ya no está a la venta.');
            }

            // máximo de cupones por cliente en la misma oferta (5 según el enunciado)
            $yaTiene = (int) $this->valorUnico(
                'SELECT COALESCE(SUM(cantidad), 0) FROM compras WHERE cliente_id = ? AND oferta_id = ? FOR UPDATE',
                [$clienteId, $ofertaId]
            );
            $limite = MAX_CUPONES_POR_OFERTA;
            if ($yaTiene + $cantidad > $limite) {
                $espacio = $limite - $yaTiene;
                throw new DomainException($espacio > 0
                    ? "Puedes tener hasta $limite cupones de esta oferta y ya tienes $yaTiene. Aún puedes comprar $espacio."
                    : "Ya tienes los $limite cupones que se permiten por oferta.");
            }

            $restantes = Oferta::restantes($oferta);
            if ($restantes !== null && $cantidad > $restantes) {
                throw new DomainException("Solo quedan $restantes cupones disponibles. Baja la cantidad e inténtalo otra vez.");
            }

            $precio = (float) $oferta['precio_oferta'];
            $this->ejecutar(
                'INSERT INTO compras (cliente_id, oferta_id, cantidad, precio_unitario, total, tarjeta_ultimos4)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$clienteId, $ofertaId, $cantidad, $precio, round($precio * $cantidad, 2), $ultimos4]
            );
            $compraId = (int) $this->db->lastInsertId();

            $this->ejecutar(
                'UPDATE compras SET numero_factura = ? WHERE id = ?',
                [sprintf('F%s-%06d', date('Y'), $compraId), $compraId]
            );

            for ($i = 0; $i < $cantidad; $i++) {
                $this->crearCupon($compraId, $ofertaId, $clienteId);
            }

            $this->db->commit();
            return $compraId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function crearCupon(int $compraId, int $ofertaId, int $clienteId): void
    {
        // El código es aleatorio; si por casualidad ya existe, probamos con otro
        for ($intento = 0; $intento < 5; $intento++) {
            try {
                $this->ejecutar(
                    'INSERT INTO cupones (codigo, compra_id, oferta_id, cliente_id) VALUES (?, ?, ?, ?)',
                    [self::generarCodigo(), $compraId, $ofertaId, $clienteId]
                );
                return;
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') { // 23000 = valor duplicado
                    throw $e;
                }
            }
        }
        throw new RuntimeException('No se pudo generar un código único para el cupón.');
    }

    // Formato CUP-XXXX-XXXX. Sin 0, O, 1 ni I para que no se confundan al leerlos en el local
    public static function generarCodigo(): string
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $codigo = '';
        for ($i = 0; $i < 8; $i++) {
            $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return 'CUP-' . substr($codigo, 0, 4) . '-' . substr($codigo, 4);
    }

    // La factura solo la puede ver el cliente que hizo la compra
    public function factura(int $compraId, int $clienteId): ?array
    {
        $compra = $this->fila(
            'SELECT c.*, o.titulo, o.fecha_limite_canje, o.precio_regular,
                    e.nombre AS empresa, e.nit AS empresa_nit, e.direccion AS empresa_direccion,
                    e.telefono AS empresa_telefono,
                    cl.nombre_completo, cl.apellidos, cl.dui, cl.correo
             FROM compras c
             JOIN ofertas o ON o.id = c.oferta_id
             JOIN empresas e ON e.id = o.empresa_id
             JOIN clientes cl ON cl.id = c.cliente_id
             WHERE c.id = ? AND c.cliente_id = ?',
            [$compraId, $clienteId]
        );
        if (!$compra) {
            return null;
        }
        $compra['cupones'] = $this->filas('SELECT codigo, estado FROM cupones WHERE compra_id = ? ORDER BY id', [$compraId]);
        return $compra;
    }

    public function historial(int $clienteId): array
    {
        $compras = $this->filas(
            'SELECT c.*, o.titulo, o.fecha_limite_canje, e.nombre AS empresa
             FROM compras c
             JOIN ofertas o ON o.id = c.oferta_id
             JOIN empresas e ON e.id = o.empresa_id
             WHERE c.cliente_id = ?
             ORDER BY c.creado_en DESC, c.id DESC',
            [$clienteId]
        );
        if (!$compras) {
            return [];
        }

        // traemos todos los cupones de una sola vez y los repartimos por compra
        $ids = array_column($compras, 'id');
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $porCompra = [];
        foreach ($this->filas("SELECT compra_id, codigo, estado FROM cupones WHERE compra_id IN ($marcas) ORDER BY id", $ids) as $cupon) {
            $porCompra[$cupon['compra_id']][] = $cupon;
        }
        foreach ($compras as &$compra) {
            $compra['cupones'] = $porCompra[$compra['id']] ?? [];
        }
        unset($compra);

        return $compras;
    }

    public function totalCupones(): int
    {
        return (int) $this->valorUnico('SELECT COALESCE(SUM(cantidad), 0) FROM compras');
    }
}

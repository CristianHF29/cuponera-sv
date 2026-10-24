<?php
/*
 * Consultas para los reportes del administrador.
 * Ganancias = total vendido por la empresa x porcentaje de comisión fijado al aprobarla.
 */
class Reporte extends Modelo
{
    public function porEmpresa(): array
    {
        return $this->filas(
            "SELECT e.id, e.nombre, e.porcentaje_comision,
                    COALESCE(SUM(c.cantidad), 0) AS cupones_vendidos,
                    COALESCE(SUM(c.total), 0) AS total_ventas,
                    ROUND(COALESCE(SUM(c.total), 0) * e.porcentaje_comision / 100, 2) AS ganancias
             FROM empresas e
             LEFT JOIN ofertas o ON o.empresa_id = e.id
             LEFT JOIN compras c ON c.oferta_id = o.id
             WHERE e.estado = 'aprobada'
             GROUP BY e.id, e.nombre, e.porcentaje_comision
             ORDER BY total_ventas DESC, e.nombre"
        );
    }
}

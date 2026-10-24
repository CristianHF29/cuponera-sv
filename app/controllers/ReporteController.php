<?php
/*
 * Reportes básicos del administrador:
 * cupones vendidos, ventas y ganancias por empresa, y los totales generales.
 */
class ReporteController extends Controller
{
    public function __construct()
    {
        Auth::exigir('administrador');
    }

    public function index(): void
    {
        $empresas = (new Reporte())->porEmpresa();

        $totales = ['cupones' => 0, 'ventas' => 0.0, 'ganancias' => 0.0];
        foreach ($empresas as $fila) {
            $totales['cupones'] += (int) $fila['cupones_vendidos'];
            $totales['ventas'] += (float) $fila['total_ventas'];
            $totales['ganancias'] += (float) $fila['ganancias'];
        }

        vista('admin/reportes', [
            'titulo' => 'Reportes',
            'empresas' => $empresas,
            'totales' => $totales,
        ]);
    }
}

<?php
declare(strict_types=1);

/**ReporteDeudas*/
class ReporteDeudas implements ReporteDatos
{
    private Deuda $deudaModel;

    public function __construct(Deuda $deudaModel)
    {
        $this->deudaModel = $deudaModel;
    }

    public function getTitulo(): string
    {
        return 'Reporte de Deudas ' . date('Y');
    }

    public function getColumnas(): array
    {
        return ['Nombres y Apellidos', 'Categoría', 'Fecha Límite de Pago', 'Estado', 'Totalidad'];
    }

    public function getFilas(): array
    {
        $deudas = $this->deudaModel->obtenerPendientes();
        $filas = [];

        foreach ($deudas as $deuda) {
            $filas[] = [
                $deuda['nombres'] . ' ' . $deuda['apellidos'],
                $deuda['categoria_nombre'],
                $deuda['fecha_limite_pago'],
                $deuda['pago'],
                $deuda['totalidad']
            ];
        }
        return $filas;
    }
}

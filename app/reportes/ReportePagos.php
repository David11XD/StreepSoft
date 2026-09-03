<?php
declare(strict_types=1);

/**ReportePagos
 * Implementa ReporteDatos para el "Reporte de Pagos".*/
class ReportePagos implements ReporteDatos
{
    private Deuda $deudaModel;

    public function __construct(Deuda $deudaModel)
    {
        $this->deudaModel = $deudaModel;
    }

    public function getTitulo(): string
    {
        return 'Reporte de Pagos ' . date('Y');
    }

    public function getColumnas(): array
    {
        return ['Jugador', 'Categoría', 'Mes', 'Valor total', 'Estado', 'Fecha límite', 'Estado de pago'];
    }

    public function getFilas(): array
    {
        $deudas = $this->deudaModel->obtenerTodos();

        $filas = [];

        foreach ($deudas as $deuda) {
            // El orden de estos valores DEBE coincidir con el orden
            // definido en getColumnas() de arriba.
            $filas[] = [
                $deuda['nombres'] . ' ' . $deuda['apellidos'],
                $deuda['categoria_nombre'],
                $deuda['mes'],
                $deuda['totalidad'],
                ucfirst($deuda['pago']), // 'pagado' / 'pendiente' / 'mora'
                $deuda['fecha_limite_pago'],
                $deuda['fecha_pago'] ?? 'Sin pagar',
            ];
        }

        return $filas;
    }
}
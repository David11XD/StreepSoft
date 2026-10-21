<?php
declare(strict_types=1);

class ReporteDeudas implements ReporteDatos
{
    private Deuda $deudaModel;

    public function __construct(Deuda $deudaModel)
    {
        $this->deudaModel = $deudaModel;
    }

    public function getTitulo(): string
    {
        return 'Reporte de Deudas Pendientes';
    }

    public function getColumnas(): array
    {
        return [
            'Jugador',
            'Categoría',
            'Mes',
            'Año',
            'Concepto',
            'Valor de la deuda',
            'Valor pagado',
            'Saldo pendiente',
            'Fecha límite de pago',
            'Estado'
        ];
    }

    public function getFilas(): array
    {
        $deudas = $this->deudaModel->obtenerTodasConEstado();
        $filas = [];

        foreach ($deudas as $deuda) {
            $estado = strtolower((string) ($deuda['pago'] ?? 'pendiente'));

            // El reporte muestra solamente deudas no pagadas.
            if ($estado === 'pagado') {
                continue;
            }

            $total = (float) ($deuda['totalidad'] ?? 0);
            $pagado = (float) ($deuda['valor_pagado'] ?? 0);
            $saldo = max(0, $total - $pagado);

            $filas[] = [
                trim(
                    ($deuda['nombres'] ?? '') . ' ' .
                    ($deuda['apellidos'] ?? '')
                ),
                $deuda['categoria'] ?? 'Sin categoría',
                $deuda['mes'] ?? '',
                $deuda['anio'] ?? '',
                $deuda['concepto'] ?? 'Mensualidad',
                $total,
                $pagado,
                $saldo,
                $deuda['fecha_limite_pago'] ?? '',
                ucfirst($estado)
            ];
        }

        return $filas;
    }
}

<?php
declare(strict_types=1);
/**
 * Reporte de pagos realizados desde el historial de deudas.
 */
class ReportePagos implements ReporteDatos
{
    private HistorialDeuda $historialModel;

    public function __construct(HistorialDeuda $historialModel)
    {
        $this->historialModel = $historialModel;
    }

    public function getTitulo(): string
    {
        return 'Reporte de Pagos ' . date('Y');
    }

    public function getColumnas(): array
    {
        return [
            'Jugador',
            'Categoría',
            'Mes',
            'Año',
            'Concepto',
            'Valor total',
            'Valor pagado',
            'Fecha límite de pago',
            'Fecha de pago',
            'Método de pago'
        ];
    }

    public function getFilas(): array
    {
        $pagos = $this->historialModel->obtenerTodos();
        $filas = [];

        foreach ($pagos as $pago) {
            $filas[] = [
                trim(
                    ($pago['nombres'] ?? '') . ' ' .
                    ($pago['apellidos'] ?? '')
                ),
                $pago['categoria_nombre'] ?? 'Sin categoría',
                $pago['mes'] ?? '',
                $pago['anio'] ?? '',
                $pago['concepto'] ?? 'Mensualidad',
                $pago['totalidad'] ?? 0,
                $pago['valor_pagado'] ?? 0,
                $pago['fecha_limite_pago'] ?? '',
                $pago['fecha_pago'] ?? '',
                $pago['metodo_pago'] ?? 'No especificado'
            ];
        }

        return $filas;
    }
}
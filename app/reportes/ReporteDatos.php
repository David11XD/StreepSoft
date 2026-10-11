<?php
declare(strict_types=1);

/**ReporteDatos */
interface ReporteDatos
{
    /** Título del reporte, ej: "Reporte de Pagos 2026" */
    public function getTitulo(): string;

    /** Nombres de columnas en orden, ej: ['Jugador', 'Valor', 'Fecha'] */
    public function getColumnas(): array;

    /**Filas de datos. Cada fila es un array con los valores EN EL MISMO ORDEN que getColumnas()     */
    public function getFilas(): array;
}
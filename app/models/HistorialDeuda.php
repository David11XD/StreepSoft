<?php
declare(strict_types=1);

/**
 * HistorialDeuda - Modelo para la tabla `historial_deuda`
 *
 * Cada fila es un pago YA REALIZADO. A diferencia de `deudas` (que
 * solo debe tener el estado actual/activo de cada jugador), esta
 * tabla sí está pensada para ir creciendo con el tiempo - es tu
 * historial de cobros.
 */
class HistorialDeuda extends Model
{
    /**
     * Archivar una deuda: copiarla aquí con sus datos de pago.
     * Se llama justo antes de borrarla de `deudas`.
     */
    public function archivar(array $deuda): bool
    {
        $sql = "
            INSERT INTO historial_deuda (
                id_jugadores, id_deuda_original, mes, anio, matricula,
                totalidad, fecha_limite_pago, fecha_pago, id_metodo_pago,
                id_tipo_becas, concepto, descuento_porcentaje, valor_pagado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        return $this->execute($sql, [
            $deuda['id_jugadores'],
            $deuda['id_deudas'],
            $deuda['mes'],
            $deuda['anio'],
            $deuda['matricula'] ?? 0,
            $deuda['totalidad'],
            $deuda['fecha_limite_pago'],
            $deuda['fecha_pago'],
            $deuda['id_metodo_pago'] ?? null,
            $deuda['id_tipo_becas'],
            $deuda['concepto'] ?? null,
            $deuda['descuento_porcentaje'] ?? 0,
            $deuda['valor_pagado'] ?? $deuda['totalidad'],
        ]);
    }

    /** Historial completo de un jugador, más reciente primero */
    public function obtenerPorJugador(int $idJugador): array
    {
        $sql = "
            SELECT * FROM historial_deuda
            WHERE id_jugadores = ?
            ORDER BY fecha_pago DESC
        ";
        return $this->query($sql, [$idJugador]);
    }

    /** ¿Ya existe un pago archivado de este jugador para ese mes/año? */
    public function existeParaMes(int $idJugador, string $mes, int $anio): bool
    {
        $sql = "
            SELECT 1 FROM historial_deuda
            WHERE id_jugadores = ? AND mes = ? AND anio = ?
            LIMIT 1
        ";
        return (bool) $this->queryOne($sql, [$idJugador, $mes, $anio]);
    }

    /** Total recaudado por la academia (histórico completo, no solo el ciclo actual) */
    public function totalRecaudado(): float
    {
        $sql = "SELECT COALESCE(SUM(valor_pagado), 0) AS total FROM historial_deuda";
        $fila = $this->queryOne($sql);
        return (float) ($fila['total'] ?? 0);
    }
}

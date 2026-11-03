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

    public function obtenerTodos(): array
    {
        $sql = "
            SELECT
                h.id_historial,
                j.nombres,
                j.apellidos,
                c.nombre AS categoria_nombre,
                h.mes,
                h.anio,
                h.concepto,
                h.totalidad,
                h.valor_pagado,
                h.fecha_limite_pago,
                h.fecha_pago,
                h.descuento_porcentaje,
                mp.tipo_metodo_pago AS metodo_pago
            FROM historial_deuda h
            INNER JOIN jugadores j
                ON j.id_jugadores = h.id_jugadores
            LEFT JOIN categorias c
                ON c.id_categorias = j.id_categorias
            LEFT JOIN metodo_pago mp
                ON mp.id_metodo_pago = h.id_metodo_pago
            ORDER BY h.fecha_pago DESC, h.id_historial DESC
        ";

        return $this->query($sql);
    }
    
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
            SELECT
                h.id_historial,
                h.id_jugadores,
                h.mes,
                h.anio,
                h.matricula,
                h.totalidad,
                h.fecha_limite_pago,
                h.fecha_pago,
                h.concepto,
                h.descuento_porcentaje,
                h.valor_pagado,
                mp.tipo_metodo_pago AS metodo_pago
            FROM historial_deuda h
            LEFT JOIN metodo_pago mp
                ON mp.id_metodo_pago = h.id_metodo_pago
            WHERE h.id_jugadores = ?
            ORDER BY h.fecha_pago DESC, h.id_historial DESC
        ";

        return $this->query($sql, [$idJugador]);
    }


    /** Obtiene un pago histórico por su identificador para editarlo. */
    public function obtenerPorId(int $idHistorial): ?array
    {
        $sql = "
            SELECT
                h.*,
                j.nombres,
                j.apellidos,
                j.foto,
                c.nombre AS categoria,
                mp.tipo_metodo_pago AS metodo_pago
            FROM historial_deuda h
            INNER JOIN jugadores j
                ON j.id_jugadores = h.id_jugadores
            LEFT JOIN categorias c
                ON c.id_categorias = j.id_categorias
            LEFT JOIN metodo_pago mp
                ON mp.id_metodo_pago = h.id_metodo_pago
            WHERE h.id_historial = ?
            LIMIT 1
        ";

        return $this->queryOne($sql, [$idHistorial]);
    }

    /** Actualiza los datos editables de un pago ya archivado. */
    public function actualizar(int $idHistorial, array $datos): bool
    {
        $sql = "
            UPDATE historial_deuda
            SET
                matricula = ?,
                totalidad = ?,
                fecha_limite_pago = ?,
                fecha_pago = ?,
                id_metodo_pago = ?,
                concepto = ?,
                descuento_porcentaje = ?,
                valor_pagado = ?
            WHERE id_historial = ?
            LIMIT 1
        ";

        return $this->execute($sql, [
            $datos['matricula'],
            $datos['totalidad'],
            $datos['fecha_limite_pago'],
            $datos['fecha_pago'],
            $datos['id_metodo_pago'],
            $datos['concepto'],
            $datos['descuento_porcentaje'],
            $datos['valor_pagado'],
            $idHistorial,
        ]);
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

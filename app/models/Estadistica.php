<?php

declare(strict_types=1);

/**
 * Estadísticas reales utilizadas por el dashboard.
 * Los pagos se consultan desde historial_deuda porque el proyecto no tiene
 * una tabla `pagos`; las mensualidades efectivamente pagadas se archivan allí.
 */
class Estadistica extends Model
{
    public function recaudacionPorMes(?int $anio = null): array
    {
        $anio = $anio ?? (int) date('Y');
        $sql = "
            SELECT MONTH(fecha_pago) AS mes_numero,
                   SUM(valor_pagado) AS total
            FROM historial_deuda
            WHERE YEAR(fecha_pago) = ?
            GROUP BY MONTH(fecha_pago)
            ORDER BY MONTH(fecha_pago)
        ";
        return $this->query($sql, [$anio]);
    }

    public function recaudacionTotal(?int $anio = null): float
    {
        $anio = $anio ?? (int) date('Y');
        $sql = "
            SELECT COALESCE(SUM(valor_pagado), 0) AS total
            FROM historial_deuda
            WHERE YEAR(fecha_pago) = ?
        ";
        $resultado = $this->queryOne($sql, [$anio]);
        return (float) ($resultado['total'] ?? 0);
    }

    public function recaudacionMesActual(): float
    {
        $sql = "
            SELECT COALESCE(SUM(valor_pagado), 0) AS total
            FROM historial_deuda
            WHERE YEAR(fecha_pago) = YEAR(CURDATE())
              AND MONTH(fecha_pago) = MONTH(CURDATE())
        ";
        $resultado = $this->queryOne($sql);
        return (float) ($resultado['total'] ?? 0);
    }

    public function recaudacionMesAnterior(): float
    {
        $sql = "
            SELECT COALESCE(SUM(valor_pagado), 0) AS total
            FROM historial_deuda
            WHERE fecha_pago >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
              AND fecha_pago < DATE_FORMAT(CURDATE(), '%Y-%m-01')
        ";
        $resultado = $this->queryOne($sql);
        return (float) ($resultado['total'] ?? 0);
    }

    /** Total histórico de fichas, incluyendo las que ahora están inactivas o retiradas. */
    public function totalJugadores(): int
    {
        $resultado = $this->queryOne("SELECT COUNT(*) AS total FROM jugadores");
        return (int) ($resultado['total'] ?? 0);
    }

    public function jugadoresNuevosMes(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM jugadores
            WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND created_at < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }

    public function jugadoresConDeuda(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT d.id_jugadores) AS total
            FROM deudas d
            INNER JOIN jugadores j ON j.id_jugadores = d.id_jugadores
            WHERE j.estado = 'activo'
              AND d.pago IN ('pendiente', 'mora')
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }

    public function jugadoresEnMora(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT d.id_jugadores) AS total
            FROM deudas d
            INNER JOIN jugadores j ON j.id_jugadores = d.id_jugadores
            WHERE j.estado = 'activo'
              AND d.pago = 'mora'
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }

    public function deudaTotal(): float
    {
        $sql = "
            SELECT COALESCE(SUM(d.totalidad - COALESCE(d.valor_pagado, 0)), 0) AS total
            FROM deudas d
            INNER JOIN jugadores j ON j.id_jugadores = d.id_jugadores
            WHERE j.estado = 'activo'
              AND d.pago IN ('pendiente', 'mora')
        ";
        $resultado = $this->queryOne($sql);
        return (float) ($resultado['total'] ?? 0);
    }

    public function totalInstructoresActivos(): int
    {
        $resultado = $this->queryOne("SELECT COUNT(*) AS total FROM instructor WHERE estado = 'activo'");
        return (int) ($resultado['total'] ?? 0);
    }

    /** Compatibilidad con PerfilAdminController y otras pantallas existentes. */
    public function totalInstructores(): int
    {
        $resultado = $this->queryOne("SELECT COUNT(*) AS total FROM instructor");
        return (int) ($resultado['total'] ?? 0);
    }

    /** Número de pagos archivados; los pagos cerrados viven en historial_deuda. */
    public function pagosRegistrados(): int
    {
        $resultado = $this->queryOne("SELECT COUNT(*) AS total FROM historial_deuda");
        return (int) ($resultado['total'] ?? 0);
    }

    /**
     * Distribución de instructores por la categoría que tienen asignada.
     * Incluye instructores activos e inactivos y agrupa los que no tienen
     * categoría asignada bajo "Sin categoría".
     */
    public function instructoresPorCategoria(): array
    {
        $sql = "
            SELECT
                COALESCE(c.nombre, 'Sin categoría') AS categoria,
                COUNT(DISTINCT i.id_instructor) AS total
            FROM instructor i
            LEFT JOIN categorias c
                ON c.id_instructor = i.id_instructor
            GROUP BY c.id_categorias, c.nombre
            ORDER BY c.nombre IS NULL, c.nombre
        ";
        return $this->query($sql);
    }

    /**
     * Tarjeta "Instructores" del dashboard: UNA fila por categoría, de la menor a la mayor
     * (Sub-6, Sub-10, ... Sub-20), con la cantidad de jugadores activos de esa categoría y
     * el instructor que los tiene a cargo.
     *
     *  - Se listan todas las categorías, aunque no tengan jugadores (total_jugadores = 0).
     *  - instructor: el/los instructores de los jugadores activos de la categoría (lo normal
     *    es uno solo; si hubiera más de uno se muestran todos separados por " / ").
     *    Si la categoría no tiene jugadores activos, se usa el instructor al que está asignada
     *    la categoría (categorias.id_instructor) siempre que esté activo; si no hay, 'Sin instructor'.
     *  - porcentaje: parte de los jugadores activos con categoría que tiene cada una.
     */
    public function jugadoresPorInstructorCategoria(): array
    {
        $sql = "
            SELECT
                c.id_categorias,
                c.nombre AS categoria,
                COALESCE(
                    (
                        SELECT GROUP_CONCAT(
                            DISTINCT TRIM(CONCAT(COALESCE(ij.nombres, '')))
                            ORDER BY ij.nombres
                            SEPARATOR ' / '
                        )
                        FROM jugadores jc
                        INNER JOIN instructor ij
                            ON ij.id_instructor = jc.id_instructor
                        WHERE jc.id_categorias = c.id_categorias
                          AND jc.estado = 'activo'
                    ),
                    (
                        SELECT TRIM(CONCAT(COALESCE(ic.nombres, '')))
                        FROM instructor ic
                        WHERE ic.id_instructor = c.id_instructor
                          AND ic.estado = 'activo'
                    ),
                    'Sin instructor'
                ) AS instructor,
                COUNT(j.id_jugadores) AS total_jugadores,
                ROUND(
                    COUNT(j.id_jugadores) * 100.0 /
                    NULLIF((
                        SELECT COUNT(*)
                        FROM jugadores
                        WHERE estado = 'activo'
                          AND id_categorias IS NOT NULL
                    ), 0),
                    1
                ) AS porcentaje
            FROM categorias c
            LEFT JOIN jugadores j
                ON j.id_categorias = c.id_categorias
               AND j.estado = 'activo'
            GROUP BY c.id_categorias, c.nombre, c.id_instructor
            ORDER BY CAST(SUBSTRING_INDEX(c.nombre, '-', -1) AS UNSIGNED), c.nombre
        ";
        return $this->query($sql);
    }

    /**
     * Obtiene las cinco deudas más recientes registradas en la tabla activa `deudas`.
     * Esta consulta alimenta exclusivamente la tarjeta del dashboard; no consulta
     * historial_deuda ni presenta pagos ya archivados.
     */
    public function deudasRecientes(int $limite = 4): array
    {
        $limite = max(1, min($limite, 20));
        $sql = "
            SELECT d.id_deudas,
                   d.id_jugadores,
                   d.mes,
                   d.anio,
                   d.totalidad,
                   d.fecha_limite_pago,
                   d.pago,
                   j.nombres,
                   j.apellidos,
                   j.foto,
                   c.nombre AS categoria
            FROM deudas d
            INNER JOIN jugadores j ON j.id_jugadores = d.id_jugadores
            LEFT JOIN categorias c ON c.id_categorias = j.id_categorias
            ORDER BY d.id_deudas DESC
            LIMIT {$limite}
        ";
        return $this->query($sql);
    }

    /**
     * Cuenta jugadores activos a quienes les falta cada documento requerido.
     * Un documento rechazado también cuenta como faltante porque debe reemplazarse.
     */
    public function faltantesPorTipoDocumento(): array
    {
        $tipos = ['eps', 'consentimiento', 'idrd'];
        $resultado = ['eps' => 0, 'consentimiento' => 0, 'idrd' => 0];

        $sql = "
            SELECT COUNT(*) AS total
            FROM jugadores j
            WHERE j.estado = 'activo'
              AND NOT EXISTS (
                  SELECT 1
                  FROM documentos_gestion dg
                  WHERE dg.id_jugadores = j.id_jugadores
                    AND dg.clave = ?
                    AND dg.estado <> 'rechazado'
              )
        ";

        foreach ($tipos as $tipo) {
            $fila = $this->queryOne($sql, [$tipo]);
            $resultado[$tipo] = (int) ($fila['total'] ?? 0);
        }

        return $resultado;
    }

    public function jugadoresSinDocumentoIdentidad(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM jugadores j
            WHERE j.estado = 'activo'
              AND NOT EXISTS (
                  SELECT 1 FROM documentos d
                  WHERE d.id_jugadores = j.id_jugadores
                    AND d.documento IS NOT NULL
                    AND TRIM(d.documento) <> ''
              )
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }

    public function jugadoresSinFoto(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM jugadores
            WHERE estado = 'activo'
              AND (foto IS NULL OR TRIM(foto) = '')
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }

    public function jugadoresSinResponsable(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM jugadores
            WHERE estado = 'activo'
              AND id_responsable IS NULL
        ";
        $resultado = $this->queryOne($sql);
        return (int) ($resultado['total'] ?? 0);
    }
}
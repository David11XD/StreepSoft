<?php
declare(strict_types=1);

/**
 * Modelo para cambios de estado masivos y su historial.
 * Utiliza las tablas existentes cambios_masivos_estado y cambios_masivos_jugadores.
 */
class CambioEstadoJugador extends Model
{
    /**
     * Devuelve TODOS los jugadores, sin filtrar por estado ni por deuda.
     * Las deudas y los meses de mora se incluyen solo como información adicional
     * para que la vista pueda filtrar opcionalmente por ese criterio.
     */
    public function obtenerTodosLosJugadores(): array
    {
        $sql = "
            SELECT
                j.id_jugadores AS id,
                j.nombres,
                j.apellidos,
                COALESCE(NULLIF(TRIM(j.iniciales), ''),
                    CONCAT(UPPER(LEFT(j.nombres, 1)), UPPER(LEFT(j.apellidos, 1)))) AS iniciales,
                TRIM(CONCAT(
                    COALESCE(td.nombre, ''),
                    CASE WHEN td.nombre IS NOT NULL AND doc.documento IS NOT NULL AND doc.documento <> '' THEN ' ' ELSE '' END,
                    COALESCE(doc.documento, '')
                )) AS documento,
                COALESCE(c.nombre, 'Sin categoría') AS categoria,
                LOWER(j.estado) AS estado,
                COALESCE(d.pago, 'sin_deuda') AS pago,
                CASE
                    WHEN d.pago IN ('pendiente', 'mora') AND d.fecha_limite_pago <= CURDATE()
                    THEN GREATEST(TIMESTAMPDIFF(MONTH, d.fecha_limite_pago, CURDATE()), 0) + 1
                    ELSE 0
                END AS meses_sin_pago,
                CASE
                    WHEN d.pago IN ('pendiente', 'mora') THEN COALESCE(d.totalidad, 0)
                    ELSE 0
                END AS deuda
            FROM jugadores j
            LEFT JOIN categorias c ON c.id_categorias = j.id_categorias
            LEFT JOIN (
                SELECT id_jugadores, MAX(id_deudas) AS id_deudas
                FROM (
                    SELECT id_jugadores, id_deudas
                    FROM deudas
                    WHERE pago IN ('pendiente', 'mora')
                    UNION ALL
                    SELECT id_jugadores, id_deudas
                    FROM deuda_inactivos
                    WHERE pago IN ('pendiente', 'mora')
                ) deudas_conservadas
                GROUP BY id_jugadores
            ) ultima_deuda ON ultima_deuda.id_jugadores = j.id_jugadores
            LEFT JOIN (
                SELECT * FROM deudas
                UNION ALL
                SELECT * FROM deuda_inactivos
            ) d ON d.id_deudas = ultima_deuda.id_deudas
            LEFT JOIN documentos doc ON doc.id_documento = (
                SELECT MAX(doc2.id_documento)
                FROM documentos doc2
                WHERE doc2.id_jugadores = j.id_jugadores
            )
            LEFT JOIN tipo_documento td ON td.id_tipo_documento = doc.id_tipo_documento
            ORDER BY FIELD(j.estado, 'activo', 'inactivo', 'retirado'), j.apellidos ASC, j.nombres ASC
        ";

        return $this->query($sql);
    }

    /** Alias de compatibilidad con el nombre anterior. */
    public function obtenerJugadoresPendientes(): array
    {
        return $this->obtenerTodosLosJugadores();
    }

    /** Historial de cambios masivos, del más reciente al más antiguo. */
    public function obtenerHistorialCambios(): array
    {
        $sql = "
            SELECT
                c.id_cambio AS id,
                DATE_FORMAT(c.creado_en, '%d/%m/%Y') AS fecha,
                COALESCE(NULLIF(u.nombre_completo, ''), u.usuario, 'Usuario') AS realizado_por,
                c.jugadores_afectados,
                c.estado_aplicado,
                c.motivo,
                c.descripcion
            FROM cambios_masivos_estado c
            LEFT JOIN usuarios u ON u.id = c.id_usuario
            ORDER BY c.creado_en DESC, c.id_cambio DESC
        ";
        return $this->query($sql);
    }

    public function obtenerCambio(int $idCambio): ?array
    {
        $sql = "
            SELECT
                c.id_cambio AS id,
                DATE_FORMAT(c.creado_en, '%d/%m/%Y %H:%i') AS fecha,
                COALESCE(NULLIF(u.nombre_completo, ''), u.usuario, 'Usuario') AS realizado_por,
                c.jugadores_afectados,
                c.estado_aplicado,
                c.motivo,
                c.descripcion
            FROM cambios_masivos_estado c
            LEFT JOIN usuarios u ON u.id = c.id_usuario
            WHERE c.id_cambio = ?
            LIMIT 1
        ";
        return $this->queryOne($sql, [$idCambio]);
    }

    public function obtenerJugadoresCambio(int $idCambio): array
    {
        $sql = "
            SELECT
                j.id_jugadores AS id,
                j.nombres,
                j.apellidos,
                COALESCE(j.iniciales, CONCAT(UPPER(LEFT(j.nombres, 1)), UPPER(LEFT(j.apellidos, 1)))) AS iniciales,
                TRIM(CONCAT(COALESCE(td.nombre, ''), ' ', COALESCE(doc.documento, ''))) AS documento,
                c.nombre AS categoria,
                j.estado AS estado_actual,
                cj.estado_anterior
            FROM cambios_masivos_jugadores cj
            INNER JOIN jugadores j ON j.id_jugadores = cj.id_jugadores
            LEFT JOIN categorias c ON c.id_categorias = j.id_categorias
            LEFT JOIN documentos doc ON doc.id_documento = (
                SELECT MAX(doc2.id_documento)
                FROM documentos doc2
                WHERE doc2.id_jugadores = j.id_jugadores
            )
            LEFT JOIN tipo_documento td ON td.id_tipo_documento = doc.id_tipo_documento
            WHERE cj.id_cambio = ?
            ORDER BY j.apellidos, j.nombres
        ";
        return $this->query($sql, [$idCambio]);
    }

    /**
     * Registra el cambio y guarda el estado anterior de cada jugador en una transacción.
     * @return array{id_cambio:int,cantidad:int,estado:string}
     */
    public function aplicarCambioMasivo(
        array $idsJugadores,
        int $idUsuario,
        string $estado,
        string $motivo,
        ?string $descripcion
    ): array {
        $estado = strtolower(trim($estado));
        $motivo = trim($motivo);
        $descripcion = $descripcion !== null ? trim($descripcion) : null;
        $idsJugadores = array_values(array_unique(array_filter(
            array_map('intval', $idsJugadores),
            static fn(int $id): bool => $id > 0
        )));

        if ($idUsuario <= 0) {
            throw new InvalidArgumentException('No se pudo identificar al usuario que realiza el cambio.');
        }
        // La opción Retirado se conserva visualmente, pero esta funcionalidad no la persiste.
        if ($estado === 'retirado') {
            throw new InvalidArgumentException('La opción Retirado se mantiene sin guardar cambios.');
        }
        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            throw new InvalidArgumentException('El estado seleccionado no es válido.');
        }
        if ($motivo === '') {
            throw new InvalidArgumentException('Selecciona el motivo del cambio.');
        }
        if ($idsJugadores === []) {
            throw new InvalidArgumentException('Selecciona al menos un jugador.');
        }
        preg_match_all('/./us', $motivo, $caracteresMotivo);
        if (count($caracteresMotivo[0] ?? []) > 150) {
            throw new InvalidArgumentException('El motivo no puede superar los 150 caracteres.');
        }

        $placeholders = implode(',', array_fill(0, count($idsJugadores), '?'));
        $this->pdo->beginTransaction();
        try {
            // Se bloquea la ficha canónica. No se elimina de jugadores porque deudas,
            // documentos e historiales mantienen claves foráneas hacia esta tabla.
            $stmt = $this->pdo->prepare(
                "SELECT j.id_jugadores, j.foto, j.nombres, j.apellidos, j.fecha_nacimiento,
                        j.iniciales, j.id_responsable, j.id_categorias, j.created_at,
                        j.id_eps, j.id_instructor, LOWER(j.estado) AS estado
                 FROM jugadores j
                 WHERE j.id_jugadores IN ($placeholders)
                 FOR UPDATE"
            );
            $stmt->execute($idsJugadores);
            $jugadoresEncontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($jugadoresEncontrados) !== count($idsJugadores)) {
                throw new RuntimeException('Uno o más jugadores no existen. Actualiza la página e inténtalo de nuevo.');
            }

            // Solo se permiten los cambios activos -> inactivos e inactivos -> activos.
            // Los retirados nunca se modifican desde este flujo.
            $jugadores = array_values(array_filter(
                $jugadoresEncontrados,
                static function (array $jugador) use ($estado): bool {
                    $actual = strtolower((string) ($jugador['estado'] ?? ''));
                    return $estado === 'inactivo'
                        ? $actual === 'activo'
                        : $actual === 'inactivo';
                }
            ));
            if ($jugadores === []) {
                throw new InvalidArgumentException(
                    $estado === 'inactivo'
                        ? 'No hay jugadores activos seleccionados para pasar a inactivos. Los ya inactivos o retirados no se modifican.'
                        : 'No hay jugadores inactivos seleccionados para activar. Los retirados no se modifican.'
                );
            }

            $stmtCambio = $this->pdo->prepare(
                "INSERT INTO cambios_masivos_estado
                    (id_usuario, estado_aplicado, motivo, descripcion, jugadores_afectados)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmtCambio->execute([
                $idUsuario,
                $estado,
                $motivo,
                ($descripcion === '' ? null : $descripcion),
                count($jugadores)
            ]);
            $idCambio = (int) $this->pdo->lastInsertId();

            $stmtDetalle = $this->pdo->prepare(
                "INSERT INTO cambios_masivos_jugadores (id_cambio, id_jugadores, estado_anterior) VALUES (?, ?, ?)"
            );
            $stmtArchivar = $this->pdo->prepare(
                "INSERT INTO jugadores_inactivos
                    (id_jugadores, foto, nombres, apellidos, fecha_nacimiento, iniciales,
                     id_responsable, id_categorias, created_at, id_eps, id_instructor,
                     fecha_inactivacion, motivo_inactivacion, descripcion_inactivacion, id_usuario_inactivacion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    foto = VALUES(foto), nombres = VALUES(nombres), apellidos = VALUES(apellidos),
                    fecha_nacimiento = VALUES(fecha_nacimiento), iniciales = VALUES(iniciales),
                    id_responsable = VALUES(id_responsable), id_categorias = VALUES(id_categorias),
                    created_at = VALUES(created_at), id_eps = VALUES(id_eps), id_instructor = VALUES(id_instructor),
                    fecha_inactivacion = CURRENT_TIMESTAMP, motivo_inactivacion = VALUES(motivo_inactivacion),
                    descripcion_inactivacion = VALUES(descripcion_inactivacion),
                    id_usuario_inactivacion = VALUES(id_usuario_inactivacion)"
            );
            $stmtRestaurar = $this->pdo->prepare(
                "UPDATE jugadores
                 SET foto = ?, nombres = ?, apellidos = ?, fecha_nacimiento = ?, iniciales = ?,
                     id_responsable = ?, id_categorias = ?, created_at = ?, id_eps = ?, id_instructor = ?, estado = 'activo'
                 WHERE id_jugadores = ?"
            );
            $stmtEstado = $this->pdo->prepare("UPDATE jugadores SET estado = ? WHERE id_jugadores = ?");
            $stmtEliminarArchivo = $this->pdo->prepare("DELETE FROM jugadores_inactivos WHERE id_jugadores = ?");

            foreach ($jugadores as $jugador) {
                $estadoAnterior = strtolower((string) $jugador['estado']);
                $idJugador = (int) $jugador['id_jugadores'];
                $stmtDetalle->execute([$idCambio, $idJugador, $estadoAnterior]);

                if ($estado === 'inactivo') {
                    // Guardar una copia completa en la tabla de inactivos y ocultarlo
                    // del listado principal. La fila canónica se conserva para las FKs.
                    $stmtArchivar->execute([
                        $idJugador,
                        $jugador['foto'],
                        $jugador['nombres'],
                        $jugador['apellidos'],
                        $jugador['fecha_nacimiento'],
                        $jugador['iniciales'],
                        $jugador['id_responsable'],
                        $jugador['id_categorias'],
                        $jugador['created_at'],
                        $jugador['id_eps'],
                        $jugador['id_instructor'],
                        $motivo,
                        $descripcion === '' ? null : $descripcion,
                        $idUsuario,
                    ]);

                    // Trasladar todas las deudas (pendientes, en mora o cualquier
                    // registro que aún permanezca en deudas) al archivo de inactivos.
                    // Se realiza dentro de esta misma transacción.
                    $this->archivarDeudasDelJugador($idJugador);

                    $stmtEstado->execute(['inactivo', $idJugador]);
                } else {
                    // Restaurar desde la copia guardada, y después retirar esa copia.
                    $inactivo = $this->queryOne(
                        'SELECT * FROM jugadores_inactivos WHERE id_jugadores = ? FOR UPDATE',
                        [$idJugador]
                    );
                    if ($inactivo !== null) {
                        $stmtRestaurar->execute([
                            $inactivo['foto'],
                            $inactivo['nombres'],
                            $inactivo['apellidos'],
                            $inactivo['fecha_nacimiento'],
                            $inactivo['iniciales'],
                            $inactivo['id_responsable'],
                            $inactivo['id_categorias'],
                            $inactivo['created_at'],
                            $inactivo['id_eps'],
                            $inactivo['id_instructor'],
                            $idJugador,
                        ]);
                    } else {
                        // Compatibilidad con jugadores que ya estaban inactivos antes de crear la tabla.
                        $stmtEstado->execute(['activo', $idJugador]);
                    }

                    // Restaurar las deudas con sus IDs originales antes de limpiar
                    // el archivo. Si alguna restauración falla, se revierte todo.
                    $this->restaurarDeudasDelJugador($idJugador);
                    $stmtEliminarArchivo->execute([$idJugador]);
                }
            }

            $this->pdo->commit();
            return [
                'id_cambio' => $idCambio,
                'cantidad' => count($jugadores),
                'estado' => ucfirst($estado),
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Columnas de deuda que se conservan sin modificar el identificador original. */
    private const COLUMNAS_DEUDA = [
        'id_deudas',
        'id_jugadores',
        'matricula',
        'mes',
        'anio',
        'totalidad',
        'fecha_limite_pago',
        'fecha_pago',
        'id_metodo_pago',
        'concepto',
        'descuento_porcentaje',
        'valor_pagado',
        'pago',
        'id_tipo_becas',
    ];

    /**
     * Mueve las deudas del jugador desde deudas hacia deuda_inactivos.
     * Mantiene el ID y cada dato financiero para poder restaurarlos exactamente.
     */
    private function archivarDeudasDelJugador(int $idJugador): int
    {
        $columnas = implode(', ', self::COLUMNAS_DEUDA);
        $stmtConsultar = $this->pdo->prepare(
            "SELECT {$columnas} FROM deudas WHERE id_jugadores = ? ORDER BY id_deudas FOR UPDATE"
        );
        $stmtConsultar->execute([$idJugador]);
        $deudas = $stmtConsultar->fetchAll(PDO::FETCH_ASSOC);

        if ($deudas === []) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count(self::COLUMNAS_DEUDA), '?'));
        $stmtArchivar = $this->pdo->prepare(
            "INSERT INTO deuda_inactivos ({$columnas}) VALUES ({$placeholders})"
        );

        foreach ($deudas as $deuda) {
            $valores = [];
            foreach (self::COLUMNAS_DEUDA as $columna) {
                $valores[] = $deuda[$columna] ?? null;
            }
            // Un ID duplicado en el archivo es una inconsistencia: no se sobrescribe.
            $stmtArchivar->execute($valores);
        }

        $stmtEliminar = $this->pdo->prepare('DELETE FROM deudas WHERE id_jugadores = ?');
        $stmtEliminar->execute([$idJugador]);

        if ($stmtEliminar->rowCount() !== count($deudas)) {
            throw new RuntimeException('No se pudieron trasladar todas las deudas del jugador. No se aplicó la inactivación.');
        }

        return count($deudas);
    }

    /**
     * Devuelve las deudas archivadas a deudas, conservando sus IDs originales.
     */
    private function restaurarDeudasDelJugador(int $idJugador): int
    {
        $columnas = implode(', ', self::COLUMNAS_DEUDA);
        $stmtConsultar = $this->pdo->prepare(
            "SELECT {$columnas} FROM deuda_inactivos WHERE id_jugadores = ? ORDER BY id_deudas FOR UPDATE"
        );
        $stmtConsultar->execute([$idJugador]);
        $deudas = $stmtConsultar->fetchAll(PDO::FETCH_ASSOC);

        if ($deudas === []) {
            return 0;
        }

        // Evita restaurar un periodo duplicado si se creó una deuda manualmente
        // mientras el jugador estaba inactivo.
        $stmtDuplicado = $this->pdo->prepare(
            "SELECT d.id_deudas
             FROM deudas d
             INNER JOIN deuda_inactivos di
                ON di.id_jugadores = d.id_jugadores
               AND di.mes = d.mes
               AND di.anio = d.anio
             WHERE di.id_jugadores = ?
             LIMIT 1
             FOR UPDATE"
        );
        $stmtDuplicado->execute([$idJugador]);
        if ($stmtDuplicado->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'No se pueden restaurar las deudas: existe un periodo de pago duplicado en la tabla de deudas. Revisa los periodos del jugador antes de reactivarlo.'
            );
        }

        // Verifica también colisiones de la clave primaria, incluso si pertenecen
        // a otro jugador por una intervención manual en la base de datos.
        $idsDeuda = array_map(static fn(array $deuda): int => (int) $deuda['id_deudas'], $deudas);
        $idsPlaceholders = implode(', ', array_fill(0, count($idsDeuda), '?'));
        $stmtIds = $this->pdo->prepare(
            "SELECT id_deudas FROM deudas WHERE id_deudas IN ({$idsPlaceholders}) LIMIT 1 FOR UPDATE"
        );
        $stmtIds->execute($idsDeuda);
        if ($stmtIds->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'No se pueden restaurar las deudas porque uno de sus identificadores ya existe en la tabla de deudas.'
            );
        }

        $placeholders = implode(', ', array_fill(0, count(self::COLUMNAS_DEUDA), '?'));
        $stmtRestaurar = $this->pdo->prepare(
            "INSERT INTO deudas ({$columnas}) VALUES ({$placeholders})"
        );
        foreach ($deudas as $deuda) {
            $valores = [];
            foreach (self::COLUMNAS_DEUDA as $columna) {
                $valores[] = $deuda[$columna] ?? null;
            }
            $stmtRestaurar->execute($valores);
        }

        $stmtEliminar = $this->pdo->prepare('DELETE FROM deuda_inactivos WHERE id_jugadores = ?');
        $stmtEliminar->execute([$idJugador]);

        if ($stmtEliminar->rowCount() !== count($deudas)) {
            throw new RuntimeException('No se pudieron restaurar todas las deudas del jugador. No se aplicó la reactivación.');
        }

        return count($deudas);
    }

}

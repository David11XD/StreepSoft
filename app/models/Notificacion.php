<?php
declare(strict_types=1);

/**
 * Notificacion - avisos para el administrador.
 *
 * Tipos de aviso y prioridad (alta / media / baja = "no importante"):
 *
 *   mora                 -> alta
 *   proximo_vencer       -> alta (vence hoy/mañana o ya venció y está en gracia),
 *                           media (2 a 3 días), baja (más de 3 días)
 *   documento_rechazado  -> media
 *   documento_pendiente  -> media (jugador con más de 7 días de inscrito), baja (recién inscrito)
 *   inactivo             -> baja
 *   pago_registrado      -> baja
 *
 * Los avisos son "idempotentes": cada uno tiene una clave única, así que
 * ejecutar la generación muchas veces no duplica nada.
 */
class Notificacion extends Model
{
    public const TIPOS = [
        'mora',
        'inactivo',
        'proximo_vencer',
        'documento_pendiente',
        'documento_rechazado',
        'pago_registrado',
    ];

    public const PRIORIDADES = ['alta', 'media', 'baja'];
    public const ESTADOS = ['nueva', 'revisada', 'resuelta'];

    /** Documentos que el jugador debe entregar (los que pediste vigilar). */
    private const DOCUMENTOS_REQUERIDOS = [
        'eps'            => 'Certificado EPS',
        'consentimiento' => 'Consentimiento informado',
        'idrd'           => 'Ficha IDRD',
    ];

    /** Nombres legibles para los avisos de documentos rechazados. */
    private const NOMBRES_DOCUMENTO = self::DOCUMENTOS_REQUERIDOS + [
        'cedula' => 'Fotocopia de cédula',
    ];

    private const INTERRUPTORES = [
        'activar_mora',
        'activar_inactivos',
        'activar_vencimientos',
        'activar_documentos_pendientes',
        'activar_documentos_rechazados',
        'activar_pagos',
    ];

    /** clave => [mínimo, máximo] */
    private const NUMERICAS = [
        'frecuencia_mora'       => [1, 60],
        'frecuencia_inactivos'  => [1, 60],
        'dias_anticipacion'     => [1, 60],
        'frecuencia_documentos' => [1, 60],
        'monto_pago_importante' => [0, 100000000],
    ];

    private const DEFECTOS = [
        'activar_mora'                  => '1',
        'frecuencia_mora'               => '7',
        'activar_inactivos'             => '1',
        'frecuencia_inactivos'          => '15',
        'activar_vencimientos'          => '1',
        'dias_anticipacion'             => '3',
        'activar_documentos_pendientes' => '1',
        'frecuencia_documentos'         => '7',
        'activar_documentos_rechazados' => '1',
        'activar_pagos'                 => '1',
        'monto_pago_importante'         => '0',
    ];

    /** Mientras no hayan pasado estos minutos no se vuelve a generar al abrir la campana. */
    private const INTERVALO_MINUTOS = 5;

    /** Un jugador con más días que estos sin documentos es "media"; antes, "baja". */
    private const DIAS_JUGADOR_NUEVO = 7;

    /** Los pagos registrados se muestran durante estos días. */
    private const DIAS_PAGOS_RECIENTES = 7;

    /** Los avisos resueltos se borran pasado este tiempo. */
    private const DIAS_CONSERVAR_RESUELTOS = 180;

    private ?array $config = null;

    /** El esquema se revisa una sola vez por petición. */
    private static bool $esquemaRevisado = false;

    /* ------------------------------------------------------------------
     * Lectura
     * ------------------------------------------------------------------ */

    /**
     * Contadores de avisos no resueltos.
     * "seguimiento" = media + baja (es la segunda caja del panel).
     */
    public function resumen(): array
    {
        $r = ['alta' => 0, 'media' => 0, 'baja' => 0];

        $filas = $this->query(
            "SELECT prioridad, COUNT(*) AS cantidad
               FROM notificaciones
              WHERE estado <> 'resuelta'
              GROUP BY prioridad"
        );
        foreach ($filas as $f) {
            if (isset($r[$f['prioridad']])) {
                $r[$f['prioridad']] = (int) $f['cantidad'];
            }
        }

        $r['seguimiento'] = $r['media'] + $r['baja'];
        $r['total'] = $r['alta'] + $r['seguimiento'];

        return $r;
    }

    /**
     * Filtros admitidos: tipo, prioridad, estado y pendientes (true = sin resolver).
     */
    public function listar(array $filtros = [], int $limite = 300): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['tipo']) && in_array($filtros['tipo'], self::TIPOS, true)) {
            $where[] = 'n.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['prioridad']) && in_array($filtros['prioridad'], self::PRIORIDADES, true)) {
            $where[] = 'n.prioridad = ?';
            $params[] = $filtros['prioridad'];
        }
        if (!empty($filtros['estado']) && in_array($filtros['estado'], self::ESTADOS, true)) {
            $where[] = 'n.estado = ?';
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['pendientes'])) {
            $where[] = "n.estado <> 'resuelta'";
        }

        $limite = max(1, min(500, $limite));

        return $this->query(
            "SELECT n.*,
                    TRIM(CONCAT(COALESCE(j.nombres, ji.nombres, ''), ' ', COALESCE(j.apellidos, ji.apellidos, ''))) AS jugador
               FROM notificaciones n
               LEFT JOIN jugadores j ON j.id_jugadores = n.id_jugadores
               LEFT JOIN jugadores_inactivos ji ON ji.id_jugadores = n.id_jugadores
              WHERE " . implode(' AND ', $where) . "
              ORDER BY (n.estado = 'resuelta'),
                       FIELD(n.prioridad, 'alta', 'media', 'baja'),
                       FIELD(n.estado, 'nueva', 'revisada', 'resuelta'),
                       n.fecha_evento DESC, n.id DESC
              LIMIT {$limite}",
            $params
        );
    }

    /** Avisos sin resolver para el panel lateral (campana). */
    public function listarPendientes(int $limite = 60): array
    {
        return $this->listar(['pendientes' => true], $limite);
    }

    /* ------------------------------------------------------------------
     * Configuración
     * ------------------------------------------------------------------ */

    /** Configuración completa: los valores guardados pisan a los de defecto. */
    public function obtenerConfiguracion(): array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $cfg = self::DEFECTOS;
        foreach ($this->query('SELECT clave, valor FROM notificaciones_configuracion') as $f) {
            if (array_key_exists($f['clave'], $cfg)) {
                $cfg[$f['clave']] = (string) $f['valor'];
            }
        }

        return $this->config = $cfg;
    }

    public function guardarConfiguracion(array $datos): void
    {
        $valores = [];

        foreach (self::INTERRUPTORES as $clave) {
            if (array_key_exists($clave, $datos)) {
                $valores[$clave] = ((string) $datos[$clave] === '1') ? '1' : '0';
            }
        }

        foreach (self::NUMERICAS as $clave => [$min, $max]) {
            if (!array_key_exists($clave, $datos)) {
                continue;
            }
            // Acepta "100.000" o "100000": se dejan solo los dígitos.
            $digitos = preg_replace('/\D/', '', (string) $datos[$clave]);
            $n = $digitos === '' ? (int) self::DEFECTOS[$clave] : (int) $digitos;
            $valores[$clave] = (string) max($min, min($max, $n));
        }

        if ($valores === []) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($valores as $clave => $valor) {
                $this->execute(
                    'INSERT INTO notificaciones_configuracion (clave, valor) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE valor = VALUES(valor)',
                    [$clave, $valor]
                );
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $this->config = null;
    }

    /* ------------------------------------------------------------------
     * Cambios de estado hechos por el administrador
     * ------------------------------------------------------------------ */

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['revisada', 'resuelta'], true)) {
            return false;
        }

        $this->asegurarEsquema();

        if ($estado === 'resuelta') {
            // Solo se elimina si el origen confirma que el problema ya no existe.
            $this->generarAutomaticas();

            $pendiente = $this->queryOne(
                "SELECT id FROM notificaciones WHERE id = ? AND estado <> 'resuelta'",
                [$id]
            );

            if ($pendiente !== null) {
                return false;
            }

            // Compatibilidad con registros antiguos que el sincronizador haya marcado resueltos.
            $this->execute('DELETE FROM notificaciones WHERE id = ?', [$id]);
            return true;
        }

        if ($this->queryOne('SELECT id FROM notificaciones WHERE id = ?', [$id]) === null) {
            return false;
        }

        return $this->execute(
            "UPDATE notificaciones SET estado = 'revisada', resolucion = NULL, actualizada_en = NOW() WHERE id = ?",
            [$id]
        );
    }

    /* ------------------------------------------------------------------
     * Esquema de la base de datos
     * ------------------------------------------------------------------ */

    /**
     * Pone al día una tabla `notificaciones` creada con la versión anterior del
     * módulo (prioridad "alta/seguimiento", sin la columna `resolucion` y con
     * URLs que incluían "/streepsoft/"). Si ya está al día no hace nada.
     * Equivale a database/migracion_notificaciones.sql, pero sin tener que
     * ejecutarlo a mano.
     */
    private function asegurarEsquema(): void
    {
        if (self::$esquemaRevisado) {
            return;
        }
        self::$esquemaRevisado = true;

        try {
            $columna = fn(string $nombre) => $this->queryOne(
                "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notificaciones' AND COLUMN_NAME = ?",
                [$nombre]
            );

            $prioridad = $columna('prioridad');
            if ($prioridad === null) {
                return; // la tabla no existe todavía: lo avisará la consulta que falle
            }

            $prioridadAntigua = str_contains((string) $prioridad['COLUMN_TYPE'], 'seguimiento');
            $sinResolucion = $columna('resolucion') === null;

            if ($sinResolucion) {
                $this->execute("ALTER TABLE notificaciones ADD COLUMN resolucion ENUM('manual','auto') DEFAULT NULL AFTER estado");
            }

            if ($prioridadAntigua) {
                $this->execute("ALTER TABLE notificaciones MODIFY prioridad ENUM('alta','media','baja','seguimiento') NOT NULL DEFAULT 'media'");
                $this->execute("UPDATE notificaciones SET prioridad = 'media' WHERE prioridad = 'seguimiento'");
                $this->execute("ALTER TABLE notificaciones MODIFY prioridad ENUM('alta','media','baja') NOT NULL DEFAULT 'media'");
            }

            if ($sinResolucion || $prioridadAntigua) {
                // Las URLs antiguas llevaban la carpeta del proyecto ("/streepsoft/jugadores/deudas").
                $this->execute("UPDATE notificaciones SET url = TRIM(LEADING '/' FROM url) WHERE url LIKE '/%'");
                $this->execute("UPDATE notificaciones SET url = SUBSTRING(url, CHAR_LENGTH('streepsoft/') + 1) WHERE url LIKE 'streepsoft/%'");
                // Lo que el administrador ya había resuelto se respeta como resolución manual.
                $this->execute("UPDATE notificaciones SET resolucion = 'manual' WHERE estado = 'resuelta' AND resolucion IS NULL");
            }
        } catch (Throwable $e) {
            error_log('Notificaciones esquema: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------
     * Generación de avisos
     * ------------------------------------------------------------------ */

    /**
     * Genera avisos solo si pasaron INTERVALO_MINUTOS desde la última vez.
     * Es lo que usan las pantallas; el cron usa generarAutomaticas() directamente.
     */
    public function generarSiCorresponde(): ?array
    {
        $this->asegurarEsquema();

        $fila = $this->queryOne(
            "SELECT TIMESTAMPDIFF(MINUTE, valor, NOW()) AS minutos
               FROM notificaciones_configuracion
              WHERE clave = 'ultima_generacion'"
        );
        $minutos = $fila['minutos'] ?? null;

        if ($minutos !== null && (int) $minutos >= 0 && (int) $minutos < self::INTERVALO_MINUTOS) {
            return null;
        }

        return $this->generarAutomaticas();
    }

    /**
     * Crea, actualiza, reabre y cierra avisos según el estado real del sistema.
     *
     * @return array{procesadas:int,nuevas:int,reabiertas:int,resueltas:int}
     */
    public function generarAutomaticas(): array
    {
        $this->asegurarEsquema();

        $cfg = $this->obtenerConfiguracion();
        $resultado = ['procesadas' => 0, 'nuevas' => 0, 'reabiertas' => 0, 'resueltas' => 0];

        $existentes = $this->cargarExistentes();
        $activas = array_fill_keys(self::TIPOS, []);

        $fuentes = [
            'mora'                => ['activar_mora',                  fn() => $this->avisosMora()],
            'proximo_vencer'      => ['activar_vencimientos',          fn() => $this->avisosPorVencer($cfg)],
            'documento_pendiente' => ['activar_documentos_pendientes', fn() => $this->avisosDocumentosPendientes()],
            'documento_rechazado' => ['activar_documentos_rechazados', fn() => $this->avisosDocumentosRechazados()],
            'pago_registrado'     => ['activar_pagos',                 fn() => $this->avisosPagos($cfg)],
            'inactivo'            => ['activar_inactivos',             fn() => $this->avisosInactivos()],
        ];

        foreach ($fuentes as $tipo => [$interruptor, $buscar]) {
            // Tipo desactivado: no se generan avisos y los abiertos se cierran más abajo.
            if (($cfg[$interruptor] ?? '1') !== '1') {
                continue;
            }

            // Si un tipo falla (por ejemplo, falta una tabla) los demás siguen funcionando,
            // y sus avisos no se cierran por error: se deja todo como estaba.
            try {
                foreach ($buscar() as $aviso) {
                    $activas[$tipo][$aviso['clave']] = true;
                    $resultado['procesadas']++;
                    $this->sincronizar($aviso, $existentes, $cfg, $resultado);
                }
            } catch (Throwable $e) {
                error_log("Notificaciones ({$tipo}): " . $e->getMessage());
                unset($activas[$tipo]);
            }
        }

        $resultado['resueltas'] = $this->cerrarAvisosObsoletos($existentes, $activas);

        $this->execute(
            "INSERT INTO notificaciones_configuracion (clave, valor)
             VALUES ('ultima_generacion', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'))
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );

        return $resultado;
    }

    /** Todos los avisos existentes, indexados por clave única (una sola consulta). */
    private function cargarExistentes(): array
    {
        $mapa = [];
        $filas = $this->query(
            'SELECT id, clave_unica, tipo, prioridad, titulo, mensaje, url, estado, resolucion,
                    TIMESTAMPDIFF(DAY, actualizada_en, NOW()) AS dias_desde_cambio
               FROM notificaciones'
        );
        foreach ($filas as $f) {
            $mapa[$f['clave_unica']] = $f;
        }
        return $mapa;
    }

    /**
     * Inserta el aviso si es nuevo; si ya existe lo mantiene al día y decide si
     * debe volver a aparecer como "nueva":
     *  - cerrado por el sistema y el problema regresó  -> reaparece ya;
     *  - revisado/resuelto a mano en avisos recurrentes -> reaparece al cumplirse
     *    la frecuencia configurada ("repetir cada N días").
     */
    private function sincronizar(array $a, array &$existentes, array $cfg, array &$res): void
    {
        $ex = $existentes[$a['clave']] ?? null;

        if ($ex === null) {
            $this->execute(
                "INSERT INTO notificaciones
                    (clave_unica, tipo, prioridad, id_jugadores, titulo, mensaje, url, fecha_evento, estado, creada_en, actualizada_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, COALESCE(?, NOW()), 'nueva', NOW(), NOW())",
                [$a['clave'], $a['tipo'], $a['prioridad'], $a['jugador'], $a['titulo'], $a['mensaje'], $a['url'], $a['fecha']]
            );
            $existentes[$a['clave']] = [
                'id' => $this->lastInsertId(), 'tipo' => $a['tipo'], 'prioridad' => $a['prioridad'],
                'titulo' => $a['titulo'], 'mensaje' => $a['mensaje'], 'url' => $a['url'],
                'estado' => 'nueva', 'resolucion' => null, 'dias_desde_cambio' => 0,
            ];
            $res['nuevas']++;
            return;
        }

        $frecuencia = $this->frecuencia($a['tipo'], $cfg);
        $venceFrecuencia = $frecuencia > 0 && (int) $ex['dias_desde_cambio'] >= $frecuencia;

        $reabrir = match ($ex['estado']) {
            // Un registro antiguo marcado manualmente como resuelto no puede
            // ocultar un problema que todavía aparece en la fuente.
            'resuelta' => true,
            'revisada' => $venceFrecuencia,
            default    => false,
        };

        if ($reabrir) {
            $this->execute(
                "UPDATE notificaciones
                    SET prioridad = ?, titulo = ?, mensaje = ?, url = ?, fecha_evento = COALESCE(?, NOW()),
                        estado = 'nueva', resolucion = NULL, actualizada_en = NOW()
                  WHERE id = ?",
                [$a['prioridad'], $a['titulo'], $a['mensaje'], $a['url'], $a['fecha'], (int) $ex['id']]
            );
            $res['reabiertas']++;
            return;
        }

        $cambio = $ex['prioridad'] !== $a['prioridad']
            || $ex['titulo'] !== $a['titulo']
            || $ex['mensaje'] !== $a['mensaje']
            || ($ex['url'] ?? null) !== $a['url'];

        if ($cambio) {
            // actualizada_en = actualizada_en evita que MySQL la mueva sola: esa
            // fecha solo debe cambiar cuando cambia el ESTADO del aviso.
            $this->execute(
                'UPDATE notificaciones
                    SET prioridad = ?, titulo = ?, mensaje = ?, url = ?, actualizada_en = actualizada_en
                  WHERE id = ?',
                [$a['prioridad'], $a['titulo'], $a['mensaje'], $a['url'], (int) $ex['id']]
            );
        }
    }

    /** Cierra (resolucion = auto) los avisos abiertos cuyo problema ya no existe. */
    private function cerrarAvisosObsoletos(array $existentes, array $activas): int
    {
        $ids = [];
        foreach ($existentes as $clave => $f) {
            if (!isset($activas[$f['tipo']])) {
                continue;
            }
            if (!isset($activas[$f['tipo']][$clave])) {
                $ids[] = (int) $f['id'];
            }
        }

        foreach (array_chunk($ids, 200) as $grupo) {
            $marcas = implode(',', array_fill(0, count($grupo), '?'));
            $this->execute(
                "DELETE FROM notificaciones WHERE id IN ($marcas)",
                $grupo
            );
        }

        return count($ids);
    }

    private function frecuencia(string $tipo, array $cfg): int
    {
        return match ($tipo) {
            'mora'                => max(1, (int) $cfg['frecuencia_mora']),
            'inactivo'            => max(1, (int) $cfg['frecuencia_inactivos']),
            'documento_pendiente' => max(1, (int) $cfg['frecuencia_documentos']),
            default               => 0,
        };
    }

    /* ------------------------------------------------------------------
     * Buscadores: cada uno devuelve la lista de avisos que HOY deben existir
     * ------------------------------------------------------------------ */

    /**
     * Deudas pendientes o en mora. Se consultan tanto las deudas activas como
     * las trasladadas a deuda_inactivos para no dejar jugadores fuera del aviso.
     * Cualquier deuda con pago pendiente/mora se considera prioridad alta.
     */
    private function avisosMora(): array
    {
        $filas = $this->query(
            "SELECT d.id_deudas, d.id_jugadores, d.mes, d.anio, d.totalidad, d.pago,
                    j.nombres, j.apellidos, d.fecha_limite_pago,
                    DATEDIFF(CURDATE(), d.fecha_limite_pago) AS dias_vencida
               FROM deudas d
               JOIN jugadores j ON j.id_jugadores = d.id_jugadores
              WHERE d.pago IN ('pendiente', 'mora')

              UNION ALL

             SELECT di.id_deudas, di.id_jugadores, di.mes, di.anio, di.totalidad, di.pago,
                    ji.nombres, ji.apellidos, di.fecha_limite_pago,
                    DATEDIFF(CURDATE(), di.fecha_limite_pago) AS dias_vencida
               FROM deuda_inactivos di
               JOIN jugadores_inactivos ji ON ji.id_jugadores = di.id_jugadores
              WHERE di.pago IN ('pendiente', 'mora')"
        );

        $avisos = [];
        foreach ($filas as $r) {
            $estadoDeuda = $r['pago'] === 'mora' ? 'En mora' : 'Deuda pendiente';
            $dias = (int) $r['dias_vencida'];
            $detalleDias = $dias > 0
                ? ' · ' . $this->dias($dias) . ' desde el vencimiento'
                : '';

            $avisos[] = $this->aviso(
                'mora:' . $r['id_deudas'], 'mora', 'alta', (int) $r['id_jugadores'],
                $estadoDeuda,
                $this->nombre($r) . " · {$r['mes']} {$r['anio']} · "
                    . $this->moneda($r['totalidad']) . $detalleDias,
                'jugadores/deudas'
            );
        }

        return $avisos;
    }

    /**
     * Mensualidades pendientes que todavía NO están en mora y que vencen dentro
     * de "días de anticipación" (incluye las ya vencidas que siguen en gracia).
     */
    private function avisosPorVencer(array $cfg): array
    {
        $gracia = (new Configuracion($this->pdo))->obtenerDiasGracia();
        $anticipacion = max(1, min(60, (int) $cfg['dias_anticipacion']));

        $filas = $this->query(
            "SELECT d.id_deudas, d.id_jugadores, d.mes, d.anio, d.fecha_limite_pago, j.nombres, j.apellidos,
                    DATEDIFF(CURDATE(), d.fecha_limite_pago) AS dias_vencida
               FROM deudas d
               JOIN jugadores j ON j.id_jugadores = d.id_jugadores
              WHERE d.pago = 'pendiente'
                AND j.estado = 'activo'
                AND DATEDIFF(CURDATE(), d.fecha_limite_pago) <= ?
                AND d.fecha_limite_pago <= DATE_ADD(CURDATE(), INTERVAL ? DAY)",
            [$gracia, $anticipacion]
        );

        $avisos = [];
        foreach ($filas as $r) {
            $vencida = (int) $r['dias_vencida'];   // > 0: ya venció; <= 0: faltan -N días
            $limite = $this->fecha($r['fecha_limite_pago']);

            if ($vencida > 0) {
                $prioridad = 'alta';
                $entraMora = $gracia + 1 - $vencida;
                $detalle = "venció el {$limite} · entra en mora en " . $this->dias($entraMora);
            } else {
                $faltan = -$vencida;
                $prioridad = $faltan <= 1 ? 'alta' : ($faltan <= 3 ? 'media' : 'baja');
                $detalle = match ($faltan) {
                    0       => 'vence hoy',
                    1       => 'vence mañana',
                    default => "vence el {$limite} (en {$faltan} días)",
                };
            }

            $avisos[] = $this->aviso(
                'vence:' . $r['id_deudas'], 'proximo_vencer', $prioridad, (int) $r['id_jugadores'],
                'Pago próximo a vencer',
                $this->nombre($r) . " · {$r['mes']} {$r['anio']} · {$detalle}",
                'jugadores/deudas'
            );
        }
        return $avisos;
    }

    /** Jugadores activos a los que no se les ha subido EPS, consentimiento o ficha IDRD. */
    private function avisosDocumentosPendientes(): array
    {
        $subidos = [];
        $claves = "'" . implode("','", array_keys(self::DOCUMENTOS_REQUERIDOS)) . "'";
        foreach ($this->query("SELECT id_jugadores, clave FROM documentos_gestion WHERE clave IN ({$claves})") as $d) {
            $subidos[(int) $d['id_jugadores']][$d['clave']] = true;
        }

        $jugadores = $this->query(
            "SELECT id_jugadores, nombres, apellidos, DATEDIFF(CURDATE(), DATE(created_at)) AS dias_inscrito
               FROM jugadores
              WHERE estado = 'activo'"
        );

        $avisos = [];
        foreach ($jugadores as $r) {
            $id = (int) $r['id_jugadores'];
            $faltan = [];
            foreach (self::DOCUMENTOS_REQUERIDOS as $clave => $nombre) {
                if (empty($subidos[$id][$clave])) {
                    $faltan[] = $nombre;
                }
            }
            if ($faltan === []) {
                continue;
            }

            $prioridad = (int) $r['dias_inscrito'] > self::DIAS_JUGADOR_NUEVO ? 'media' : 'baja';
            $avisos[] = $this->aviso(
                'docs_pendientes:' . $id, 'documento_pendiente', $prioridad, $id,
                'Documentos pendientes',
                $this->nombre($r) . ' · Falta: ' . implode(', ', $faltan),
                'documentos/gestionar/' . $id
            );
        }
        return $avisos;
    }

    /** Documentos rechazados que todavía no se han vuelto a subir. */
    private function avisosDocumentosRechazados(): array
    {
        $filas = $this->query(
            "SELECT dg.id, dg.id_jugadores, dg.clave, dg.motivo_rechazo,
                    UNIX_TIMESTAMP(dg.fecha_subida) AS subida, j.nombres, j.apellidos
               FROM documentos_gestion dg
               JOIN jugadores j ON j.id_jugadores = dg.id_jugadores
              WHERE dg.estado = 'rechazado'
                AND j.estado = 'activo'"
        );

        $avisos = [];
        foreach ($filas as $r) {
            $documento = self::NOMBRES_DOCUMENTO[$r['clave']] ?? strtoupper((string) $r['clave']);
            $motivo = trim((string) $r['motivo_rechazo']) !== '' ? $r['motivo_rechazo'] : 'Debe volver a subir el documento';

            // La fecha de subida va en la clave: si lo suben de nuevo y lo vuelven
            // a rechazar, es un aviso nuevo y no queda tapado por el anterior.
            $avisos[] = $this->aviso(
                'doc_rechazado:' . $r['id'] . ':' . (int) $r['subida'], 'documento_rechazado', 'media',
                (int) $r['id_jugadores'],
                'Documento rechazado',
                $this->nombre($r) . " · {$documento} · {$motivo}",
                'documentos/gestionar/' . (int) $r['id_jugadores']
            );
        }
        return $avisos;
    }

    /** Pagos recientes cuyo valor alcanza el monto "importante" configurado. */
    private function avisosPagos(array $cfg): array
    {
        $minimo = max(0, (int) $cfg['monto_pago_importante']);

        $filas = $this->query(
            "SELECT h.id_historial, h.id_jugadores, h.valor_pagado, h.fecha_pago, h.mes, h.anio, j.nombres, j.apellidos
               FROM historial_deuda h
               JOIN jugadores j ON j.id_jugadores = h.id_jugadores
              WHERE h.fecha_pago >= DATE_SUB(CURDATE(), INTERVAL " . self::DIAS_PAGOS_RECIENTES . " DAY)
                AND h.valor_pagado >= ?",
            [$minimo]
        );

        $avisos = [];
        foreach ($filas as $r) {
            $avisos[] = $this->aviso(
                'pago:' . $r['id_historial'], 'pago_registrado', 'baja', (int) $r['id_jugadores'],
                'Pago registrado',
                $this->nombre($r) . " · {$r['mes']} {$r['anio']} · $" . $this->moneda($r['valor_pagado'])
                    . ' · ' . $this->fecha($r['fecha_pago']),
                'jugadores/deudas',
                $r['fecha_pago']
            );
        }
        return $avisos;
    }

    /** Jugadores desactivados. */
    private function avisosInactivos(): array
    {
        $filas = $this->query(
            "SELECT j.id_jugadores, j.nombres, j.apellidos, ji.fecha_inactivacion, ji.motivo_inactivacion
               FROM jugadores j
               LEFT JOIN jugadores_inactivos ji ON ji.id_jugadores = j.id_jugadores
              WHERE j.estado = 'inactivo'"
        );

        $avisos = [];
        foreach ($filas as $r) {
            $desde = $r['fecha_inactivacion'] ? strtotime((string) $r['fecha_inactivacion']) : 0;
            $mensaje = $this->nombre($r);
            if ($desde) {
                $mensaje .= ' · Desactivado el ' . date('d/m/Y', $desde);
            }
            if (trim((string) $r['motivo_inactivacion']) !== '') {
                $mensaje .= ' · ' . $r['motivo_inactivacion'];
            }

            // La fecha va en la clave: si lo reactivan y lo desactivan otra vez, vuelve a avisar.
            $avisos[] = $this->aviso(
                'inactivo:' . $r['id_jugadores'] . ':' . $desde, 'inactivo', 'baja', (int) $r['id_jugadores'],
                'Jugador inactivo', $mensaje, 'jugadores/desactivacion'
            );
        }
        return $avisos;
    }

    /* ------------------------------------------------------------------
     * Utilidades de formato
     * ------------------------------------------------------------------ */

    private function aviso(string $clave, string $tipo, string $prioridad, ?int $jugador, string $titulo, string $mensaje, ?string $url, ?string $fecha = null): array
    {
        return [
            'clave'     => $clave,
            'tipo'      => $tipo,
            'prioridad' => $prioridad,
            'jugador'   => $jugador,
            'titulo'    => $this->recortar($titulo, 150),
            'mensaje'   => $this->recortar($mensaje, 500),
            'url'       => $url,   // ruta relativa: el controlador le antepone la base del proyecto
            'fecha'     => $fecha,
        ];
    }

    /** Recorta a N caracteres (UTF-8) sin depender de la extensión mbstring. */
    private function recortar(string $texto, int $max): string
    {
        return preg_match('/^.{0,' . $max . '}/us', $texto, $m) === 1 ? $m[0] : $texto;
    }

    private function nombre(array $fila): string
    {
        return trim(($fila['nombres'] ?? '') . ' ' . ($fila['apellidos'] ?? ''));
    }

    private function moneda(mixed $valor): string
    {
        return number_format((float) $valor, 0, ',', '.');
    }

    private function fecha(mixed $valor): string
    {
        return date('d/m/Y', strtotime((string) $valor));
    }

    private function dias(int $n): string
    {
        return $n . ' ' . ($n === 1 ? 'día' : 'días');
    }
}

<?php
declare(strict_types=1);

class Deuda extends Model
{
    // Días después de pagar en los que se sigue mostrando "Pago"
    private const DIAS_PARA_VIGENTE = 2;

    public function obtenerTodasConEstado(): array
    {
        $sql = "
            SELECT DISTINCT
                d.id_deudas,
                d.id_jugadores,
                d.matricula,
                d.mes,
                d.anio,
                d.totalidad,
                d.fecha_limite_pago,
                d.fecha_pago,
                d.pago,
                d.concepto,
                d.descuento_porcentaje,
                d.valor_pagado,
                d.id_tipo_becas,
                mp.tipo_metodo_pago AS metodo_pago,
                j.nombres,
                j.apellidos,
                j.foto,
                c.nombre   AS categoria,
                tb.nombre  AS tipo_beca
            FROM deudas d
            INNER JOIN jugadores j  ON j.id_jugadores  = d.id_jugadores
            LEFT  JOIN categorias c ON c.id_categorias = j.id_categorias
            LEFT  JOIN tipos_beca tb ON tb.id_tipo_beca = d.id_tipo_becas
            LEFT  JOIN metodo_pago mp ON mp.id_metodo_pago = d.id_metodo_pago
            ORDER BY d.fecha_limite_pago ASC
        ";

        $filas = $this->query($sql);

        // IMPORTANTE: el array completo (plural, $filas) y la variable
        // de cada elemento dentro del foreach (singular, $fila) deben
        // tener nombres DISTINTOS, y el foreach debe ser "as &$fila"
        // (con el "&") para que el array_merge de abajo sí quede
        // guardado en el array que se devuelve al final.
        $configuracionPagos = (new Configuracion($this->pdo))->obtenerConfiguracionPagos();

        foreach ($filas as &$fila) {
            $fila = array_merge($fila, $this->calcularEstadoVisual($fila, $configuracionPagos));
        }
        unset($fila);

        return $filas;
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT
                d.*,
                j.nombres,
                j.apellidos,
                tb.nombre AS tipo_beca
            FROM deudas d
            INNER JOIN jugadores j ON j.id_jugadores = d.id_jugadores
            LEFT JOIN tipos_beca tb ON tb.id_tipo_beca = d.id_tipo_becas
            WHERE d.id_deudas = ?
            LIMIT 1
        ";

        return $this->queryOne($sql, [$id]);
    }

    public function obtenerResumen(): array
    {
        $sql = "
            SELECT
                COUNT(*)                                              AS total_alumnos,
                COALESCE(SUM(totalidad), 0)                           AS total_general,
                COALESCE(SUM(CASE WHEN pago = 'pendiente' THEN totalidad END), 0) AS total_pendiente,
                COALESCE(SUM(CASE WHEN pago = 'mora'      THEN totalidad END), 0) AS total_mora,
                COALESCE(SUM(CASE WHEN pago = 'pagado'    THEN COALESCE(valor_pagado, totalidad) END), 0) AS total_recaudo
            FROM deudas
        ";

        $resumen = $this->queryOne($sql) ?? [
            'total_alumnos'   => 0,
            'total_general'   => 0,
            'total_pendiente' => 0,
            'total_mora'      => 0,
            'total_recaudo'   => 0,
        ];

        $total = (float) $resumen['total_general'];
        $resumen['porcentaje_pendiente'] = $total > 0 ? round(($resumen['total_pendiente'] / $total) * 100) : 0;
        $resumen['porcentaje_mora']      = $total > 0 ? round(($resumen['total_mora'] / $total) * 100) : 0;
        $resumen['porcentaje_recaudo']   = $total > 0 ? round(($resumen['total_recaudo'] / $total) * 100) : 0;

        return $resumen;
    }

    public function crear(array $datos): int
    {
        $fechaLimite = $datos['fecha_limite_pago'] ?? null;

        if (empty($fechaLimite)) {
            $mes = $this->numeroMes($datos['mes'] ?? null);
            $anio = (int) ($datos['anio'] ?? 0);

            if ($mes === null || $anio < 2000) {
                throw new InvalidArgumentException('No se puede calcular la fecha límite de la deuda.');
            }

            $fechaLimite = (new Configuracion($this->pdo))->calcularFechaLimite($anio, $mes);
        }

        $sql = "
            INSERT INTO deudas (
                id_jugadores, matricula, mes, anio, totalidad,
                fecha_limite_pago, id_tipo_becas, pago
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')
        ";

        $exito = $this->execute($sql, [
            $datos['id_jugadores'],
            $datos['matricula'] ?? 0,
            $datos['mes'],
            $datos['anio'],
            $datos['totalidad'],
            $fechaLimite,
            $datos['id_tipo_becas'],
        ]);

        return $exito ? $this->lastInsertId() : 0;
    }

    /* Primer ciclo de pago de jugador */
    public function crearInicial(array $datos): int
    {
        $fechaPago = trim((string) ($datos['fecha_pago'] ?? ''));
        if ($fechaPago === '') {
            throw new InvalidArgumentException('La fecha de pago es obligatoria.');
        }

        $fechaPagoObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaPago);
        $erroresFecha = DateTimeImmutable::getLastErrors();

        if (
            !$fechaPagoObj
            || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))
            || $fechaPagoObj->format('Y-m-d') !== $fechaPago
        ) {
            throw new InvalidArgumentException('La fecha de pago no es válida.');
        }

        $matricula = max(0, (float) ($datos['matricula'] ?? 0));
        $mensualidad = max(0, (float) ($datos['totalidad'] ?? 0));
        $totalInicial = round($matricula + $mensualidad, 2);

        if ($totalInicial <= 0) {
            throw new InvalidArgumentException('El valor inicial debe ser mayor que cero.');
        }

        // La fecha de pago y la fecha límite representan cosas diferentes:
        // - fecha_pago: día real en que se recibió el dinero.
        // - fecha_limite_pago: día configurado por el administrador para ese ciclo.
        $fechaLimite = (new Configuracion($this->pdo))->calcularFechaLimite(
            (int) $fechaPagoObj->format('Y'),
            (int) $fechaPagoObj->format('n')
        );

        $sql = "
            INSERT INTO deudas (
                id_jugadores,
                matricula,
                mes,
                anio,
                totalidad,
                fecha_limite_pago,
                fecha_pago,
                id_metodo_pago,
                id_tipo_becas,
                concepto,
                valor_pagado,
                pago
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pagado')
        ";

        $exito = $this->execute($sql, [
            $datos['id_jugadores'],
            $matricula,
            $datos['mes'],
            $datos['anio'],
            $totalInicial,
            $fechaLimite,
            $fechaPago,
            $datos['id_metodo_pago'],
            $datos['id_tipo_becas'],
            $datos['concepto'] ?? 'Matrícula y mensualidad de inscripción',
            $datos['valor_pagado'] ?? $totalInicial,
        ]);

        if (!$exito) {
            return 0;
        }

        $idDeuda = $this->lastInsertId();

        // La inscripción ya está pagada, así que no debe quedar en la tabla
        // de deudas activas. Se conserva en historial y se genera el siguiente
        // ciclo mensual como deuda pendiente.
        $deudaInicial = $this->queryOne(
            'SELECT * FROM deudas WHERE id_deudas = ? LIMIT 1',
            [$idDeuda]
        );

        if ($deudaInicial === null) {
            throw new RuntimeException('No se pudo recuperar la deuda inicial creada.');
        }

        $historialModel = new HistorialDeuda($this->pdo);

        if (!$historialModel->archivar($deudaInicial)) {
            throw new RuntimeException('No se pudo archivar el pago inicial en el historial.');
        }

        if (!$this->execute('DELETE FROM deudas WHERE id_deudas = ?', [$idDeuda])) {
            throw new RuntimeException('No se pudo retirar el pago inicial de las deudas activas.');
        }

        $this->crearSiguienteDeuda($deudaInicial);

        return $idDeuda;
    }

    /**
     * Registra el pago y completa el ciclo:
     * 1) mueve la deuda pagada a historial_deuda;
     * 2) elimina la deuda de la tabla activa;
     * 3) crea automáticamente la deuda del siguiente mes.
     *
     * La operación es transaccional: si cualquiera de los pasos falla,
     * todo se revierte.
     */
    public function registrarPago(int $idDeuda, array $datos): bool
    {
        if ($idDeuda <= 0) {
            throw new InvalidArgumentException('ID de deuda inválido');
        }

        $this->pdo->beginTransaction();

        try {
            $deuda = $this->queryOne(
                'SELECT * FROM deudas WHERE id_deudas = ? FOR UPDATE',
                [$idDeuda]
            );

            if ($deuda === null) {
                throw new RuntimeException('La deuda no existe o ya fue procesada');
            }

            if (($deuda['pago'] ?? '') === 'pagado') {
                throw new RuntimeException('La deuda ya está marcada como pagada');
            }

            // Preparar los datos finales que quedarán en historial.
            $deuda['fecha_pago'] = $datos['fecha_pago'];
            $deuda['id_metodo_pago'] = $datos['id_metodo_pago'];
            $deuda['concepto'] = $datos['concepto'] ?? ($deuda['concepto'] ?? null);
            $deuda['descuento_porcentaje'] = $datos['descuento_porcentaje'] ?? 0;
            $deuda['valor_pagado'] = $datos['valor_pagado'];

            $historialModel = new HistorialDeuda($this->pdo);

            if (!$historialModel->archivar($deuda)) {
                throw new RuntimeException('No se pudo archivar la deuda en el historial');
            }

            // La deuda pagada deja de pertenecer a la tabla de deudas activas.
            if (!$this->execute(
                'DELETE FROM deudas WHERE id_deudas = ?',
                [$idDeuda]
            )) {
                throw new RuntimeException('No se pudo retirar la deuda pagada de la lista activa');
            }

            // Crear el siguiente ciclo mensual.
            $this->crearSiguienteDeuda($deuda);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('Deuda::registrarPago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea la deuda mensual inmediatamente posterior al ciclo pagado.
     * No crea duplicados y no genera mensualidad para jugadores inactivos.
     */
    private function crearSiguienteDeuda(array $deuda): int
    {
        $idJugador = (int) $deuda['id_jugadores'];

        $jugador = $this->queryOne(
            'SELECT estado FROM jugadores WHERE id_jugadores = ? LIMIT 1',
            [$idJugador]
        );

        if ($jugador === null) {
            throw new RuntimeException('El jugador asociado a la deuda no existe');
        }

        if (($jugador['estado'] ?? 'activo') !== 'activo') {
            // Jugador retirado/inactivo: no se genera el siguiente ciclo.
            return 0;
        }

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $mesAnterior = array_search($deuda['mes'], $meses, true);

        if ($mesAnterior === false) {
            throw new RuntimeException('Mes inválido en la deuda pagada');
        }

        $mesSiguiente = $mesAnterior + 1;
        $anioSiguiente = (int) $deuda['anio'];

        if ($mesSiguiente > 12) {
            $mesSiguiente = 1;
            $anioSiguiente++;
        }

        $nombreMesSiguiente = $meses[$mesSiguiente];

        // Seguridad contra duplicados.
        $yaExiste = $this->queryOne(
            'SELECT id_deudas
             FROM deudas
             WHERE id_jugadores = ? AND mes = ? AND anio = ?
             LIMIT 1',
            [$idJugador, $nombreMesSiguiente, $anioSiguiente]
        );

        if ($yaExiste !== null) {
            return (int) $yaExiste['id_deudas'];
        }

        // El siguiente mes es solamente mensualidad: la matrícula no se repite.
        $mensualidad = max(
            0,
            (float) $deuda['totalidad'] - (float) ($deuda['matricula'] ?? 0)
        );

        // Día de cobro configurable desde la tabla configuracion.
        $configuracion = new Configuracion($this->pdo);
        $diaCobro = $configuracion->obtenerDiaCobro();
        $diaCobro = max(1, min(31, $diaCobro));

        $diasMes = cal_days_in_month(
            CAL_GREGORIAN,
            $mesSiguiente,
            $anioSiguiente
        );

        $diaReal = min($diaCobro, $diasMes);
        $fechaLimite = sprintf(
            '%04d-%02d-%02d',
            $anioSiguiente,
            $mesSiguiente,
            $diaReal
        );

        $sql = "
            INSERT INTO deudas (
                id_jugadores,
                matricula,
                mes,
                anio,
                totalidad,
                fecha_limite_pago,
                id_tipo_becas,
                pago,
                concepto
            ) VALUES (?, 0, ?, ?, ?, ?, ?, 'pendiente', ?)
        ";

        $exito = $this->execute($sql, [
            $idJugador,
            $nombreMesSiguiente,
            $anioSiguiente,
            $mensualidad,
            $fechaLimite,
            (int) $deuda['id_tipo_becas'],
            'Mensualidad ' . $nombreMesSiguiente . ' ' . $anioSiguiente,
        ]);

        if (!$exito) {
            throw new RuntimeException('No se pudo crear la deuda del siguiente mes');
        }

        return $this->lastInsertId();
    }

    public function marcarVencidaComoMora(): int
    {
        $diasGracia = (new Configuracion($this->pdo))->obtenerDiasGracia();

        // Se conserva la regla del sistema: la mora inicia después de que
        // hayan transcurrido todos los días de gracia.
        $sql = "
            UPDATE deudas
            SET pago = 'mora'
            WHERE pago = 'pendiente'
              AND DATEDIFF(CURDATE(), fecha_limite_pago) > :dias_gracia
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':dias_gracia' => $diasGracia]);
        return $stmt->rowCount();
    }

    /**
     * Calcula el estado VISUAL sin depender de valores quemados en código.
     *
     * La función acepta la configuración como segundo argumento para que el
     * controlador/modelo pueda reutilizar la misma lectura de BD para muchas
     * filas. También soporta que en el futuro una fila tenga una columna
     * dias_gracia propia y, si falta fecha_limite_pago, puede reconstruirla
     * usando mes/año + el día configurado.
     */
    public function calcularEstadoVisual(array $fila, ?array $configuracionPagos = null): array
    {
        $config = $configuracionPagos ?? (new Configuracion($this->pdo))->obtenerConfiguracionPagos();
        $diasGracia = $this->resolverDiasGracia($fila, $config);
        $fechaLimite = $this->resolverFechaLimite($fila, $config);
        $hoy = new DateTimeImmutable('today');

        $resultado = [
            'estado_visual' => 'pendiente',
            'vigencia_texto' => '',
            'advertencia_texto' => '',
            'dias_gracia' => $diasGracia,
        ];

        if ($fechaLimite === null) {
            $resultado['estado_visual'] = 'pendiente';
            $resultado['vigencia_texto'] = 'fecha límite no disponible';
            $resultado['advertencia_texto'] = 'Revisa la fecha de esta deuda.';
            return $resultado;
        }

        $estadoBD = $this->normalizarEstado($fila['pago'] ?? 'pendiente');

        // Pago reciente. Se mantiene la representación existente para
        // compatibilidad con la vista, aunque el ciclo normal archiva el pago.
        if ($estadoBD === 'pagado') {
            $diasDesdePago = null;

            if (!empty($fila['fecha_pago'])) {
                try {
                    $fechaPago = new DateTimeImmutable((string) $fila['fecha_pago']);
                    $diasDesdePago = $fechaPago <= $hoy
                        ? $fechaPago->diff($hoy)->days
                        : -$hoy->diff($fechaPago)->days;
                } catch (Throwable) {
                    $diasDesdePago = null;
                }
            }

            if ($diasDesdePago !== null && $diasDesdePago >= 0 && $diasDesdePago < self::DIAS_PARA_VIGENTE) {
                $resultado['estado_visual'] = 'pago';
                $resultado['vigencia_texto'] = $diasDesdePago === 0
                    ? 'hoy'
                    : 'hace ' . $diasDesdePago . ' día' . ($diasDesdePago === 1 ? '' : 's');
                return $resultado;
            }

            $resultado['estado_visual'] = 'vigente';
            $resultado['vigencia_texto'] = $fechaLimite >= $hoy
                ? 'vence en ' . $this->pluralDias($hoy->diff($fechaLimite)->days)
                : 'al día';
            return $resultado;
        }

        $fechaInicioMora = $fechaLimite->modify('+' . ($diasGracia + 1) . ' days');

        // Si BD ya marca mora, se respeta; también se calcula mora por fecha
        // para evitar depender de un cron para la presentación visual.
        if ($estadoBD === 'mora' || $hoy >= $fechaInicioMora) {
            $diasMora = $fechaLimite < $hoy ? $fechaLimite->diff($hoy)->days : 0;
            $resultado['estado_visual'] = 'mora';
            $resultado['vigencia_texto'] = 'vencido hace ' . $this->pluralDias($diasMora);
            $resultado['advertencia_texto'] = '¡' . $this->pluralDias($diasMora) . ' de mora! Más de 30 días se considera inactivo.';
            return $resultado;
        }

        // Día límite: todavía no es mora.
        if ($hoy == $fechaLimite) {
            $resultado['estado_visual'] = 'pendiente';
            $resultado['vigencia_texto'] = 'vence hoy';
            $resultado['advertencia_texto'] = $diasGracia > 0
                ? 'Tiene ' . $this->pluralDias($diasGracia) . ' de gracia después de la fecha límite.'
                : 'El periodo de gracia es de 0 días.';
            return $resultado;
        }

        // Periodo posterior a la fecha límite pero todavía dentro de gracia.
        if ($hoy > $fechaLimite) {
            $diasTranscurridos = $fechaLimite->diff($hoy)->days;
            $diasRestantes = max(0, $diasGracia - $diasTranscurridos + 1);

            $resultado['estado_visual'] = 'pendiente';
            $resultado['vigencia_texto'] = 'en periodo de gracia';
            $resultado['advertencia_texto'] = $diasRestantes > 0
                ? 'Quedan ' . $this->pluralDias($diasRestantes) . ' de gracia antes de la mora.'
                : 'La mora será aplicada en la próxima actualización.';
            return $resultado;
        }

        // Antes de la fecha límite.
        $diasParaVencer = $hoy->diff($fechaLimite)->days;
        $resultado['estado_visual'] = 'vigente';
        $resultado['vigencia_texto'] = 'vence en ' . $this->pluralDias($diasParaVencer);
        return $resultado;
    }

    private function resolverDiasGracia(array $fila, array $config): int
    {
        if (isset($fila['dias_gracia']) && is_numeric($fila['dias_gracia'])) {
            return max(0, min(30, (int) $fila['dias_gracia']));
        }

        return max(0, min(30, (int) ($config['dias_gracia'] ?? 5)));
    }

    private function resolverFechaLimite(array $fila, array $config): ?DateTimeImmutable
    {
        if (!empty($fila['fecha_limite_pago'])) {
            try {
                return new DateTimeImmutable((string) $fila['fecha_limite_pago']);
            } catch (Throwable) {
                // Continúa con la reconstrucción por mes/año.
            }
        }

        $anio = isset($fila['anio']) ? (int) $fila['anio'] : 0;
        $mes = $this->numeroMes($fila['mes'] ?? null);

        if ($anio < 2000 || $mes === null) {
            return null;
        }

        $dia = max(1, min(31, (int) ($config['dia_cobro'] ?? 5)));
        $diasDelMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $diaReal = min($dia, $diasDelMes);

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $anio, $mes, $diaReal));
    }

    private function numeroMes(mixed $mes): ?int
    {
        if (is_numeric($mes)) {
            $valor = (int) $mes;
            return $valor >= 1 && $valor <= 12 ? $valor : null;
        }

        $meses = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
            'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
            'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
        ];

        $clave = strtolower(trim((string) $mes));
        return $meses[$clave] ?? null;
    }

    private function normalizarEstado(mixed $estado): string
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'pagado', 'paid', 'payed', 'cancelado', 'cancelled' => 'pagado',
            'mora', 'moroso', 'vencido', 'overdue', 'atrasado' => 'mora',
            default => 'pendiente',
        };
    }

    private function pluralDias(int $dias): string
    {
        return $dias . ' día' . ($dias === 1 ? '' : 's');
    }
}
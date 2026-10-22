<?php

declare(strict_types=1);

class Configuracion extends Model
{
    private const DEFAULT_DIA_COBRO = 5;
    private const DEFAULT_DIAS_GRACIA = 5;
    private const MAX_DIA_COBRO = 31;
    private const MAX_DIAS_GRACIA = 30;

    /**
     * Obtener todas las configuraciones.
     */
    public function obtenerTodas(): array
    {
        $sql = "SELECT clave, valor FROM configuracion ORDER BY clave";
        return $this->query($sql);
    }

    /**
     * Obtener una configuración por clave.
     */
    public function obtenerPorClave(string $clave): ?string
    {
        $sql = "SELECT valor FROM configuracion WHERE clave = :clave LIMIT 1";
        $fila = $this->queryOne($sql, [':clave' => $clave]);
        return $fila['valor'] ?? null;
    }

    /**
     * Devuelve siempre una configuración de pagos válida y normalizada.
     *
     * dia_cobro es la clave oficial actual. dia_pago se conserva como
     * compatibilidad para instalaciones/código anterior.
     */
    public function obtenerConfiguracionPagos(): array
    {
        $diaCobro = $this->obtenerPorClave('dia_cobro');

        if ($diaCobro === null || $diaCobro === '') {
            $diaCobro = $this->obtenerPorClave('dia_pago');
        }

        $diasGracia = $this->obtenerPorClave('dias_gracia');

        $dia = filter_var($diaCobro, FILTER_VALIDATE_INT);
        $gracia = filter_var($diasGracia, FILTER_VALIDATE_INT);

        $dia = $dia === false
            ? self::DEFAULT_DIA_COBRO
            : max(1, min(self::MAX_DIA_COBRO, $dia));

        $gracia = $gracia === false
            ? self::DEFAULT_DIAS_GRACIA
            : max(0, min(self::MAX_DIAS_GRACIA, $gracia));

        return [
            'dia_cobro' => $dia,
            'dias_gracia' => $gracia,
        ];
    }

    public function obtenerDiaCobro(): int
    {
        return $this->obtenerConfiguracionPagos()['dia_cobro'];
    }

    public function obtenerDiasGracia(): int
    {
        return $this->obtenerConfiguracionPagos()['dias_gracia'];
    }

    /**
     * Calcula la fecha límite de un mes usando el día configurado.
     * Si se elige 31 en un mes corto, usa automáticamente el último día
     * disponible del mes.
     */
    public function calcularFechaLimite(int $anio, int $mes, ?int $diaCobro = null): string
    {
        if ($mes < 1 || $mes > 12) {
            throw new InvalidArgumentException('Mes inválido.');
        }

        if ($anio < 2000 || $anio > 2200) {
            throw new InvalidArgumentException('Año inválido.');
        }

        $dia = $diaCobro ?? $this->obtenerDiaCobro();
        $dia = max(1, min(self::MAX_DIA_COBRO, (int) $dia));

        $diasDelMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $diaReal = min($dia, $diasDelMes);

        return sprintf('%04d-%02d-%02d', $anio, $mes, $diaReal);
    }

    /**
     * Primer día en que la deuda debe considerarse mora, siguiendo la regla
     * actual: fecha límite + días de gracia + 1.
     */
    public function calcularFechaInicioMora(string $fechaLimite, ?int $diasGracia = null): string
    {
        $fecha = new DateTimeImmutable($fechaLimite);
        $gracia = $diasGracia ?? $this->obtenerDiasGracia();
        $gracia = max(0, min(self::MAX_DIAS_GRACIA, (int) $gracia));

        return $fecha->modify('+' . ($gracia + 1) . ' days')->format('Y-m-d');
    }

    /**
     * Actualizar una configuración existente. Si la clave no existe, se crea.
     */
    public function actualizar(string $clave, string $valor): bool
    {
        return $this->guardar($clave, $valor);
    }

    /**
     * Guardar múltiples configuraciones de forma atómica.
     */
    public function actualizarMultiples(array $datos): bool
    {
        $this->pdo->beginTransaction();

        try {
            foreach ($datos as $clave => $valor) {
                if (!$this->guardar($clave, (string) $valor)) {
                    throw new RuntimeException("No se pudo guardar la configuración {$clave}");
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * INSERT/UPDATE de una clave. Compatible con el esquema actual de BD,
     * cuya tabla no necesita un campo id para operar estas claves.
     */
    public function guardar(string $clave, string $valor): bool
    {
        $existente = $this->obtenerPorClave($clave);

        if ($existente !== null) {
            $sql = "UPDATE configuracion SET valor = :valor WHERE clave = :clave";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':valor' => $valor,
                ':clave' => $clave,
            ]);
        }

        $sql = "INSERT INTO configuracion (clave, valor) VALUES (:clave, :valor)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':clave' => $clave,
            ':valor' => $valor,
        ]);
    }
}

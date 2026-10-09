<?php
declare(strict_types=1);

/**
 * GeneradorDeudas
 *
 * Este generador funciona como respaldo del ciclo automático.
 * La operación normal ocurre en Deuda::registrarPago():
 * deuda activa -> historial -> nueva deuda.
 *
 * Si por alguna razón no existe la nueva deuda (por ejemplo, una
 * ejecución interrumpida fuera de la transacción), este servicio puede
 * reconstruir el siguiente ciclo a partir del último registro histórico.
 */
class GeneradorDeudas
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function generarDeudasProximoMes(): array
    {
        $resultado = [
            'generadas' => 0,
            'errores' => [],
            'mensaje' => '',
        ];

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

        try {
            $jugadores = $this->pdo->query(
                "SELECT id_jugadores
                 FROM jugadores
                 WHERE estado = 'activo'
                 ORDER BY id_jugadores"
            )->fetchAll(PDO::FETCH_ASSOC);

            $diaCobro = $this->obtenerDiaCobro();

            foreach ($jugadores as $jugador) {
                $idJugador = (int) $jugador['id_jugadores'];

                // Mientras exista una deuda activa, no se crea otra.
                $activa = $this->pdo->prepare(
                    "SELECT id_deudas
                     FROM deudas
                     WHERE id_jugadores = ?
                     LIMIT 1"
                );
                $activa->execute([$idJugador]);

                if ($activa->fetch(PDO::FETCH_ASSOC)) {
                    continue;
                }

                // Buscar el último ciclo ya pagado/archivado.
                $stmt = $this->pdo->prepare(
                    "SELECT *
                     FROM historial_deuda
                     WHERE id_jugadores = ?
                     ORDER BY anio DESC,
                              FIELD(mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre') DESC,
                              id_historial DESC
                     LIMIT 1"
                );
                $stmt->execute([$idJugador]);
                $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$ultimo) {
                    // Sin historial no hay una base segura para inventar un ciclo.
                    continue;
                }

                $mesAnterior = array_search($ultimo['mes'], $meses, true);
                if ($mesAnterior === false) {
                    $resultado['errores'][] =
                        "Mes inválido en historial para jugador {$idJugador}";
                    continue;
                }

                $mesSiguiente = $mesAnterior + 1;
                $anioSiguiente = (int) $ultimo['anio'];

                if ($mesSiguiente > 12) {
                    $mesSiguiente = 1;
                    $anioSiguiente++;
                }

                $nombreMes = $meses[$mesSiguiente];

                // Evitar duplicados incluso si el cron se ejecuta varias veces.
                $existeHistorial = $this->pdo->prepare(
                    "SELECT id_historial
                     FROM historial_deuda
                     WHERE id_jugadores = ? AND mes = ? AND anio = ?
                     LIMIT 1"
                );
                $existeHistorial->execute([$idJugador, $nombreMes, $anioSiguiente]);

                if ($existeHistorial->fetch(PDO::FETCH_ASSOC)) {
                    continue;
                }

                $totalAnterior = (float) ($ultimo['totalidad'] ?? 0);
                $matriculaAnterior = (float) ($ultimo['matricula'] ?? 0);
                $mensualidad = max(0, $totalAnterior - $matriculaAnterior);

                $fechaLimite = $this->generarFechaLimite(
                    $mesSiguiente,
                    $anioSiguiente,
                    $diaCobro
                );

                $insert = $this->pdo->prepare(
                    "INSERT INTO deudas (
                        id_jugadores,
                        matricula,
                        mes,
                        anio,
                        totalidad,
                        fecha_limite_pago,
                        id_tipo_becas,
                        pago,
                        concepto
                    ) VALUES (?, 0, ?, ?, ?, ?, ?, 'pendiente', ?)"
                );

                $insert->execute([
                    $idJugador,
                    $nombreMes,
                    $anioSiguiente,
                    $mensualidad,
                    $fechaLimite,
                    (int) $ultimo['id_tipo_becas'],
                    "Mensualidad {$nombreMes} {$anioSiguiente}",
                ]);

                $resultado['generadas']++;
            }
        } catch (Throwable $e) {
            error_log('GeneradorDeudas::generarDeudasProximoMes - ' . $e->getMessage());
            $resultado['errores'][] = $e->getMessage();
        }

        $resultado['mensaje'] =
            "Se generaron {$resultado['generadas']} deudas de respaldo.";

        return $resultado;
    }

    private function obtenerDiaCobro(): int
    {
        return (new Configuracion($this->pdo))->obtenerDiaCobro();
    }

    private function generarFechaLimite(
        int $mes,
        int $anio,
        int $dia
    ): string {
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $diaReal = min($dia, $diasEnMes);

        return sprintf('%04d-%02d-%02d', $anio, $mes, $diaReal);
    }

    /**
     * Cambia a mora únicamente después del día de gracia configurado.
     */
    public function actualizarMoras(): array
    {
        $resultado = [
            'actualizadas' => 0,
            'errores' => [],
        ];

        try {
            $diasGracia = (new Configuracion($this->pdo))->obtenerDiasGracia();

            $sql = "
                UPDATE deudas
                SET pago = 'mora'
                WHERE pago = 'pendiente'
                  AND DATEDIFF(CURDATE(), fecha_limite_pago) > :dias_gracia
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':dias_gracia' => $diasGracia]);
            $resultado['actualizadas'] = $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('GeneradorDeudas::actualizarMoras - ' . $e->getMessage());
            $resultado['errores'][] = $e->getMessage();
        }

        return $resultado;
    }
}
?>

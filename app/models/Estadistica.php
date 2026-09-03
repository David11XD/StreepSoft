<?php

declare(strict_types=1);

/**
 * Estadistica - Modelo para obtener datos estadísticos
 * 
 * ¿Qué hace?
 * - Obtiene recaudación por mes
 * - Obtiene totales
 * - Obtiene datos para gráficos
 */
class Estadistica extends Model
{
    /**
     * Obtener recaudación por mes del año actual
     * 
     * ¿Qué retorna?
     * Array con los meses y montos recaudados
     * 
     * Ejemplo:
     * [
     *     ['mes' => 'Enero', 'mes_numero' => 1, 'total' => 150000],
     *     ['mes' => 'Febrero', 'mes_numero' => 2, 'total' => 200000],
     * ]
     * 
     * @return array - Recaudación por mes
     */
    public function recaudacionPorMes(): array
    {
        // SQL para obtener suma de pagos por mes
        $sql = "
            SELECT 
                MONTH(fecha_pago) as mes_numero,
                MONTHNAME(fecha_pago) as mes,
                SUM(monto) as total
            FROM pagos
            WHERE YEAR(fecha_pago) = YEAR(NOW())
            GROUP BY MONTH(fecha_pago)
            ORDER BY MONTH(fecha_pago)
        ";

        return $this->query($sql);
    }

    /**
     * Obtener recaudación total del año
     * 
     * @return float - Total recaudado
     */
    public function recaudacionTotal(): float
    {
        $sql = "
            SELECT SUM(monto) as total
            FROM pagos
            WHERE YEAR(fecha_pago) = YEAR(NOW())
        ";

        $result = $this->queryOne($sql);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Obtener cantidad de jugadores
     * 
     * @return int - Total de jugadores
     */
    public function totalJugadores(): int
    {
        $sql = "SELECT COUNT(*) as total FROM jugadores";
        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtener jugadores con deuda
     * 
     * @return int - Total con deuda
     */
    public function jugadoresConDeuda(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT id_jugadores) as total 
            FROM deudas 
            WHERE pago IN ('pendiente', 'mora')";

        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtener deuda total pendiente
     * 
     * @return float - Monto total en deuda
     */
    public function deudaTotal(): float
    {
        $sql = "
            SELECT SUM(monto) as total
            FROM deudas
            WHERE estado = 'pendiente'
        ";

        $result = $this->queryOne($sql);
        return (float)($result['total'] ?? 0);
    }

    /*"Tener deuda" = el jugador debe dinero y "Estar en mora" = específicamente que ya se venció el plazo*/
    public function jugadoresEnMora(): int
    {
        $sql = "SELECT COUNT(DISTINCT id_jugadores) as total 
            FROM deudas 
            WHERE pago = 'mora'";

        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    public function pagosRegistrados(): int
    {
        $sql = "SELECT COUNT(*) as total 
            FROM deudas 
            WHERE pago = 'pagado'";

        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    public function totalInstructores(): int
    {
        $sql = "SELECT COUNT(*) as total FROM instructor";
        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }
}

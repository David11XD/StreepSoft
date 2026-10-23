<?php

class Mensualidad extends Model
{
    protected static $table = 'pagos_mensualidades';
    protected static $primaryKey = 'id';

    
     // Registrar pago de mensualidad
     
    public function registrar(int $jugadorId, string $mes, float $valor, string $metodoPago, string $fechaPago): bool
    {
        $sql = "INSERT INTO pagos_mensualidades (jugador_id, anio, mes, valor, metodo_pago, fecha_pago) 
                VALUES (:jugador_id, :anio, :mes, :valor, :metodo_pago, :fecha_pago)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':anio' => date('Y'),
            ':mes' => $mes,
            ':valor' => $valor,
            ':metodo_pago' => $metodoPago,
            ':fecha_pago' => $fechaPago
        ]);
    }

    
    //Verificar si un jugador ya pagó un mes específico
     
    public function mesPagado(int $jugadorId, string $mes): bool
    {
        $sql = "SELECT id FROM pagos_mensualidades 
                WHERE jugador_id = :jugador_id AND anio = :anio AND mes = :mes";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':anio' => date('Y'),
            ':mes' => $mes
        ]);
        
        return $stmt->fetch() !== false;
    }

    
    //Obtener todos los pagos de mensualidades del año actual
    
    public function obtenerTodosDelAnio(): array
    {
        $sql = "SELECT m.*, 
                       j.nombre, j.apellido, j.documento,
                       c.nombre AS categoria_nombre
                FROM pagos_mensualidades m
                INNER JOIN jugadores j ON m.jugador_id = j.id
                LEFT JOIN categorias c ON j.categoria_id = c.id
                WHERE m.anio = :anio
                ORDER BY m.fecha_pago DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':anio' => date('Y')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     //Obtener pagos de un jugador en un año específico
     
    public function obtenerPorJugadorYAnio(int $jugadorId, int $anio): array
    {
        $sql = "SELECT * FROM pagos_mensualidades 
                WHERE jugador_id = :jugador_id AND anio = :anio
                ORDER BY FIELD(mes, 'ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':anio' => $anio
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     //Obtener matriz de pagos por jugador (12 meses)
     
    public function obtenerMatrizPagos(int $jugadorId): array
    {
        $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $pagos = $this->obtenerPorJugadorYAnio($jugadorId, date('Y'));
        
        $matriz = [];
        foreach ($meses as $mes) {
            $pagado = false;
            $valor = 0;
            foreach ($pagos as $p) {
                if ($p['mes'] === $mes) {
                    $pagado = true;
                    $valor = (float)$p['valor'];
                    break;
                }
            }
            $matriz[$mes] = [
                'pagado' => $pagado,
                'valor' => $valor
            ];
        }
        
        return $matriz;
    }

    
     //Obtener jugadores que deben un mes específico
    
    public function obtenerMorososPorMes(string $mes): array
    {
        $sql = "SELECT j.id, j.nombre, j.apellido, j.documento,
                       c.nombre AS categoria_nombre
                FROM jugadores j
                LEFT JOIN categorias c ON j.categoria_id = c.id
                LEFT JOIN pagos_mensualidades m ON j.id = m.jugador_id 
                    AND m.anio = :anio AND m.mes = :mes
                WHERE j.estado = 'activo' AND m.id IS NULL
                ORDER BY j.apellido ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':anio' => date('Y'),
            ':mes' => $mes
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     //Obtener el valor de la mensualidad desde configuración
     
    public function getValorMensualidad(): float
    {
        $sql = "SELECT valor FROM configuracion WHERE clave = 'valor_mensualidad'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['valor'] ?? 0);
    }

    
      //Obtener nombres de meses
     
    public function getMeses(): array
    {
        return [
            'ene' => 'Enero',
            'feb' => 'Febrero',
            'mar' => 'Marzo',
            'abr' => 'Abril',
            'may' => 'Mayo',
            'jun' => 'Junio',
            'jul' => 'Julio',
            'ago' => 'Agosto',
            'sep' => 'Septiembre',
            'oct' => 'Octubre',
            'nov' => 'Noviembre',
            'dic' => 'Diciembre'
        ];
    }

    
    //Obtener meses disponibles 
     
    public function getMesesCodigos(): array
    {
        return ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    }
}
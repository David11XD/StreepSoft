<?php

class Uniforme extends Model
{
    protected static $table = 'uniformes';
    protected static $primaryKey = 'id';

    
    //Crear registro de uniforme para un jugador
     
    public function crear(int $jugadorId, string $talla, string $color, float $valorTotal): bool
    {
        $sql = "INSERT INTO uniformes (jugador_id, talla, color, valor_total, saldo_pendiente, estado) 
                VALUES (:jugador_id, :talla, :color, :valor_total, :saldo_pendiente, 'pendiente')";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':talla' => $talla,
            ':color' => $color,
            ':valor_total' => $valorTotal,
            ':saldo_pendiente' => $valorTotal
        ]);
    }

    
     //Obtener uniforme de un jugador
     
    public function obtenerPorJugador(int $jugadorId): ?array
    {
        $sql = "SELECT u.*, 
                       (SELECT SUM(valor) FROM abonos_uniformes WHERE uniforme_id = u.id) AS total_abonado
                FROM uniformes u
                WHERE u.jugador_id = :jugador_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':jugador_id' => $jugadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['total_abonado'] = (float)($result['total_abonado'] ?? 0);
            $result['saldo_pendiente'] = (float)$result['saldo_pendiente'];
        }
        
        return $result ?: null;
    }

    
        //Registrar abono al uniforme
     
    public function registrarAbono(int $uniformeId, float $valor, string $metodoPago, string $fechaPago): bool
    {
        $this->pdo->beginTransaction();

        try {
            $sql = "INSERT INTO abonos_uniformes (uniforme_id, valor, metodo_pago, fecha_pago) 
                    VALUES (:uniforme_id, :valor, :metodo_pago, :fecha_pago)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':uniforme_id' => $uniformeId,
                ':valor' => $valor,
                ':metodo_pago' => $metodoPago,
                ':fecha_pago' => $fechaPago
            ]);

            $sql = "UPDATE uniformes 
                    SET saldo_pendiente = saldo_pendiente - :valor,
                        estado = CASE WHEN saldo_pendiente - :valor <= 0 THEN 'pagado' ELSE 'pendiente' END
                    WHERE id = :uniforme_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':uniforme_id' => $uniformeId,
                ':valor' => $valor
            ]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    
     //Obtener historial de abonos de un uniforme
     
    public function obtenerAbonos(int $uniformeId): array
    {
        $sql = "SELECT * FROM abonos_uniformes 
                WHERE uniforme_id = :uniforme_id 
                ORDER BY fecha_pago DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uniforme_id' => $uniformeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     //Verificar si un jugador ya tiene uniforme
     
    public function jugadorTieneUniforme(int $jugadorId): bool
    {
        $sql = "SELECT id FROM uniformes WHERE jugador_id = :jugador_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':jugador_id' => $jugadorId]);
        return $stmt->fetch() !== false;
    }
}
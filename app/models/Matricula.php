<?php

declare(strict_types=1);

class Matricula extends Model
{
    protected static $table = 'matriculas';
    protected static $primaryKey = 'id';

    
     //Registrar una nueva matrícula
     
    public function registrar(int $jugadorId, float $valor, string $metodoPago, string $fechaPago): bool
    {
        $sql = "INSERT INTO matriculas (jugador_id, anio, valor, metodo_pago, fecha_pago)   
                VALUES (:jugador_id, :anio, :valor, :metodo_pago, :fecha_pago)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':anio' => date('Y'),
            ':valor' => $valor,
            ':metodo_pago' => $metodoPago,
            ':fecha_pago' => $fechaPago
        ]);
    }

    
     //Verificar si un jugador ya pagó la matrícula del año actual
     
    public function pagoRealizado(int $jugadorId): bool 
    {
        $sql = "SELECT id FROM matriculas 
                WHERE jugador_id = :jugador_id AND anio = :anio";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':jugador_id' => $jugadorId,
            ':anio' => date('Y')
        ]);
        
        return $stmt->fetch() !== false;
    }

    
     //Obtener todas las matrículas del año actual con datos del jugador
     
    public function obtenerTodasDelAnio(): array
    {
        $sql = "SELECT m.*, 
                       j.nombre, j.apellido, j.documento,
                       c.nombre AS categoria_nombre
                FROM matriculas m
                INNER JOIN jugadores j ON m.jugador_id = j.id
                LEFT JOIN categorias c ON j.categoria_id = c.id
                WHERE m.anio = :anio
                ORDER BY m.fecha_pago DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':anio' => date('Y')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    //Obtener jugadores que no han pagado la matrícula del año actual
     
    public function obtenerNoPagosMatricula(): array
    {
        $sql = "SELECT j.id, j.nombre, j.apellido, j.documento,
                       c.nombre AS categoria_nombre
                FROM jugadores j
                LEFT JOIN categorias c ON j.categoria_id = c.id
                LEFT JOIN matriculas m ON j.id = m.jugador_id AND m.anio = :anio
                WHERE j.estado = 'activo' AND m.id IS NULL
                ORDER BY j.apellido ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':anio' => date('Y')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    //Obtener jugadores que no han pagado matrícula 
     
    public function obtenerMorososMatricula(): array
    {
        return $this->obtenerNoPagosMatricula();
    }

    
    //Obtener el valor de la matrícula desde configuración
     
    public function getValorMatricula(): float
    {
        $sql = "SELECT valor FROM configuracion WHERE clave = 'valor_matricula'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['valor'] ?? 0);
    }

    
     //Actualizar el valor de la matrícula
     
    public function setValorMatricula(float $valor): bool
    {
        $sql = "UPDATE configuracion SET valor = :valor WHERE clave = 'valor_matricula'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':valor' => $valor]);
    }
}
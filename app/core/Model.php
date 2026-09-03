<?php
declare(strict_types=1);

/* Model - clase base para todos los modelos */

abstract class Model
{
    /* La conexion a la base de datos */
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* Ejecutar una consulta de select */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Ejucutar una consulta que retorna Una fila */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /* Ejecutar un INSERT, UPTADE o DELETE */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    
    protected function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

}
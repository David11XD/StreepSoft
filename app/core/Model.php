<?php
declare(strict_types=1);

/* Model - clase base para todos los modelos */

abstract class Model
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* Ejecutar una consulta SELECT */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);

        // No pasar un array vacío a execute(). Algunos drivers/configuraciones
        // de PDO pueden producir HY093 cuando la consulta no tiene placeholders.
        if ($params !== []) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Ejecutar una consulta que retorna una fila */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);

        if ($params !== []) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /* Ejecutar INSERT, UPDATE o DELETE */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);

        if ($params !== []) {
            return $stmt->execute($params);
        }

        return $stmt->execute();
    }

    protected function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}

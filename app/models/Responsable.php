<?php 
declare(strict_types=1);

class Responsable extends Model
{
        /**
     * Obtener todos los responsables de un jugador
     */
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM responsables ORDER BY nombres, apellidos";
        return $this->query($sql);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM responsables WHERE id_responsable = ? LIMIT 1";
        return $this->queryOne($sql, [$id]);
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO responsables (
                nombres, apellidos, id_tipo_documento, identificacion, numero_celular
            ) VALUES (?, ?, ?, ?, ?)
        ";

        $exito = $this->execute($sql, [
            $datos['nombres'],
            $datos['apellidos'],
            $datos['id_tipo_documento'],
            $datos['identificacion'],
            $datos['numero_celular'],
        ]);

        return $exito ? $this->lastInsertId() : 0;
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE responsables
            SET nombres = ?,
                apellidos = ?,
                id_tipo_documento = ?,
                identificacion = ?,
                numero_celular = ?
            WHERE id_responsable = ?
        ";

        return $this->execute($sql, [
            $datos['nombres'],
            $datos['apellidos'],
            $datos['id_tipo_documento'],
            $datos['identificacion'],
            $datos['numero_celular'],
            $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM responsables WHERE id_responsable = ?";
        return $this->execute($sql, [$id]);
    }
}

?>
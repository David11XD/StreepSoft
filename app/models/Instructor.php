<?php
declare(strict_types=1);

class Instructor extends Model
{
    public function obtenerTodos(): array
    {
        $sql = "
            SELECT
                i.id_instructor,
                i.foto,
                i.nombres,
                i.apellidos,
                i.edad,
                i.numero_celular,
                i.id_categorias,
                i.descripcion,
                i.estado,
                c.nombre AS categoria
            FROM instructor i
            LEFT JOIN categorias c ON c.id_categorias = i.id_categorias
            ORDER BY i.nombres, i.apellidos
        ";

        return $this->query($sql);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT
                i.*,
                c.nombre AS categoria
            FROM instructor i
            LEFT JOIN categorias c ON c.id_categorias = i.id_categorias
            WHERE i.id_instructor = ?
            LIMIT 1
        ";

        return $this->queryOne($sql, [$id]);
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO instructor (
                nombres, apellidos, edad, numero_celular, id_categorias, descripcion, foto, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $exito = $this->execute($sql, [
            $datos['nombres'],
            $datos['apellidos'],
            $datos['edad'] ?? null,
            $datos['numero_celular'] ?? null,
            $datos['id_categorias'],
            $datos['descripcion'] ?? null,
            $datos['foto'] ?? null,
            $datos['estado'] ?? 'activo',
        ]);

        return $exito ? $this->lastInsertId() : 0;
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE instructor
            SET nombres = ?,
                apellidos = ?,
                edad = ?,
                numero_celular = ?,
                id_categorias = ?,
                descripcion = ?,
                foto = COALESCE(?, foto),
                estado = ? 
            WHERE id_instructor = ?
        ";

        return $this->execute($sql, [
            $datos['nombres'],
            $datos['apellidos'],
            $datos['edad'] ?? null,
            $datos['numero_celular'] ?? null,
            $datos['id_categorias'],
            $datos['descripcion'] ?? null,
            $datos['foto'] ?? null,
            $datos['estado'] ?? 'activo', 
            $id,
        ]);
    }

    public function retirar(int $id): bool
    {
        return $this->execute(
            "UPDATE instructor SET estado = 'inactivo' WHERE id_instructor = ?",
            [$id]
        );
    }

    public function reactivar(int $id): bool
    {
        return $this->execute(
            "UPDATE instructor SET estado = 'activo' WHERE id_instructor = ?",
            [$id]
        );
    }

    public function actualizarFoto(int $id, ?string $foto): bool
    {
        return $this->execute(
            "UPDATE instructor SET foto = ? WHERE id_instructor = ?",
            [$foto, $id]
        );
    }
}

?>

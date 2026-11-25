<?php
declare(strict_types=1);

class Categoria extends Model
{
    public function obtenerTodas(): array
    {
        $sql = "SELECT id_categorias, nombre FROM categorias ORDER BY nombre";
        return $this->query($sql);
    }

    /**
     * Categorías junto con el instructor al que pertenecen (NULL si no tienen).
     * Una categoría pertenece a un solo instructor; un instructor puede tener varias.
     */
    public function obtenerConInstructor(): array
    {
        $sql = "
            SELECT
                c.id_categorias,
                c.nombre,
                c.id_instructor,
                CONCAT_WS(' ', i.nombres, i.apellidos) AS instructor_nombre,
                i.estado AS instructor_estado
            FROM categorias c
            LEFT JOIN instructor i ON i.id_instructor = c.id_instructor
            ORDER BY c.nombre
        ";
        return $this->query($sql);
    }

    /**
     * Id del instructor ACTIVO que tiene asignada la categoría, o null si no tiene
     * instructor o si el que tiene está inactivo (no se asignan jugadores a un instructor retirado).
     */
    public function obtenerInstructorActivo(int $idCategoria): ?int
    {
        if ($idCategoria <= 0) {
            return null;
        }

        $fila = $this->queryOne(
            "SELECT c.id_instructor
             FROM categorias c
             INNER JOIN instructor i ON i.id_instructor = c.id_instructor
             WHERE c.id_categorias = ? AND i.estado = 'activo'",
            [$idCategoria]
        );

        return $fila ? (int) $fila['id_instructor'] : null;
    }
}

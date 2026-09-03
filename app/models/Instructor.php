<?php
declare(strict_types=1);

class Instructor extends Model
{
    public function obtenerTodos(): array
    {
        $sql = "
            SELECT id_instructor, nombres, apellidos, edad, numero_instructor
            FROM instructor
            ORDER BY nombres
        ";

        return $this->query($sql);
    }
}

?>

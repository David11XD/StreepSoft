<?php
declare(strict_types=1);

class Actividad extends Model
{
    public function registrar(int $idUsuario, string $descripcion): bool
    {
        $sql = "INSERT INTO actividad (id_usuario, descripcion) VALUES (:id_usuario, :descripcion)";

        return $this->execute($sql, [
            ':id_usuario' => $idUsuario,
            ':descripcion' => $descripcion
        ]);
    }

    public function obtenerRecientes(int $idUsuario, int $limite = 4): array
    {
        $sql = "SELECT descripcion, creado_en 
                FROM actividad 
                WHERE id_usuario = :id_usuario 
                ORDER BY creado_en DESC 
                LIMIT :limite";

        return $this->query($sql, [
            ':id_usuario' => $idUsuario,
            ':limite' => $limite
        ]);
    }
}

?>

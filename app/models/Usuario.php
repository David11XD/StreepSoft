<?php

declare(strict_types=1);

class Usuario extends Model
{
    public function obtenerporusuario(string $usuario): array|null
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
        return $this->queryOne($sql, [':usuario' => $usuario]);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        return $this->queryOne($sql, [':id' => $id]);
    }

    // El método que va a guardar los cambios cuando alguien edite su información
    public function actualizarPerfil(int $id, string $nombreCompleto, string $telefono, string $documentoIdentidad): bool
    {
        $sql = "UPDATE usuarios 
            SET nombre_completo = :nombre_completo, 
                telefono = :telefono,
                documento_identidad = :documento_identidad
            WHERE id = :id";

        return $this->execute($sql, [
            ':nombre_completo' => $nombreCompleto,
            ':telefono' => $telefono,
            ':documento_identidad' => $documentoIdentidad,
            ':id' => $id
        ]);
    }

    public function actualizarFoto(int $id, string $nombreFoto): bool
    {
        $sql = "UPDATE usuarios SET foto = :foto WHERE id = :id";

        return $this->execute($sql, [
            ':foto' => $nombreFoto,
            ':id' => $id
        ]);
    }
}

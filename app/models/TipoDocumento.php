<?php
declare(strict_types=1);

class TipoDocumento extends Model
{
    public function obtenerTodos(): array
    {
        $sql = "SELECT id_tipo_documento, nombre FROM tipo_documento ORDER BY id_tipo_documento";
        return $this->query($sql);
    }
}

?>
<?php
declare(strict_types=1);

class TipoBeca extends Model
{
    public function obtenerTodas(): array
    {
        return $this->query(
            "SELECT id_tipo_beca, nombre FROM tipos_beca ORDER BY id_tipo_beca"
        );
    }
}


?>
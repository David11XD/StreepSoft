<?php
declare(strict_types=1);

class Categoria extends Model
{
    public function obtenerTodas(): array
    {
        $sql = "SELECT id_categorias, nombre FROM categorias ORDER BY nombre";
        return $this->query($sql);
    }
}

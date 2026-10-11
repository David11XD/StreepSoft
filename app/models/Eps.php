<?php
declare(strict_types=1);

class Eps extends Model
{
    public function obtenerTodas(): array
    {
        $sql = "SELECT id_eps, nombre FROM eps ORDER BY nombre";
        return $this->query($sql);
    }
}

?>

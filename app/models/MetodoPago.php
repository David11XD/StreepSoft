<?php
declare(strict_types=1);

class MetodoPago extends Model
{
    public function obtenerTodos(): array
    {
        return $this->query(
            "SELECT id_metodo_pago, tipo_metodo_pago FROM metodo_pago ORDER BY tipo_metodo_pago"
        );
    }
}


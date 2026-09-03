<?php
declare(strict_types=1);

class Documento extends Model
{
    /*** Guardar el documento de un jugador */

    public function crear(int $idJugador, ?string $numeroDocumento, ?int $idTipoDocumento): bool
    {
        if (empty($numeroDocumento) || empty($idTipoDocumento)) {
            return true;
        }

        $sql = "
            INSERT INTO documentos (id_jugadores, documento, id_tipo_documento)
            VALUES (?, ?, ?)
        ";

        return $this->execute($sql, [$idJugador, $numeroDocumento, $idTipoDocumento]);
    }
}

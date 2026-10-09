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

    public function guardarOActualizar(int $idJugador, ?string $numeroDocumento, ?int $idTipoDocumento): bool
    {
        if (empty($numeroDocumento) || empty($idTipoDocumento)){
            return true;
        }

        $existente = $this->queryOne(
            "SELECT id_documento FROM documentos WHERE id_jugadores = ? LIMIT 1",
            [$idJugador]
        );

        if ($existente) {
            return $this->execute(
                "UPDATE documentos SET documento = ?, id_tipo_documento = ? WHERE id_jugadores = ?",
                [$numeroDocumento, $idTipoDocumento, $idJugador]
            );
        }

        
        return $this->crear($idJugador, $numeroDocumento, $idTipoDocumento);
    }
}

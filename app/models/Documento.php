<?php
declare(strict_types=1);

class Documento extends Model
{
    private const TIPOS = [
        'cedula' => ['nombre' => 'Fotocopia de cédula', 'descripcion' => 'Documento de identidad del jugador.'],
        'consentimiento' => ['nombre' => 'Consentimiento informado', 'descripcion' => 'Formato firmado por el acudiente o jugador mayor de edad.'],
        'idrd' => ['nombre' => 'Ficha del IDRD', 'descripcion' => 'Ficha de inscripción del IDRD diligenciada.'],
        'eps' => ['nombre' => 'Certificado de EPS', 'descripcion' => 'Certificado vigente de afiliación a salud.'],
    ];

    public static function tipos(): array { return self::TIPOS; }

    // Compatibilidad con el registro de identificación que utiliza JugadorController.
    public function crear(int $idJugador, ?string $numeroDocumento, ?int $idTipoDocumento): bool
    {
        if (empty($numeroDocumento) || empty($idTipoDocumento)) return true;
        return $this->execute(
            "INSERT INTO documentos (id_jugadores, documento, id_tipo_documento) VALUES (?, ?, ?)",
            [$idJugador, $numeroDocumento, $idTipoDocumento]
        );
    }

    public function guardarOActualizar(int $idJugador, ?string $numeroDocumento, ?int $idTipoDocumento): bool
    {
        if (empty($numeroDocumento) || empty($idTipoDocumento)) return true;
        $existente = $this->queryOne("SELECT id_documento FROM documentos WHERE id_jugadores=? LIMIT 1", [$idJugador]);
        if ($existente) {
            return $this->execute("UPDATE documentos SET documento=?, id_tipo_documento=? WHERE id_jugadores=?", [$numeroDocumento,$idTipoDocumento,$idJugador]);
        }
        return $this->crear($idJugador,$numeroDocumento,$idTipoDocumento);
    }


    public function listarJugadoresDocumentos(): array
    {
        $sql = "SELECT j.id_jugadores AS id, j.nombres, j.apellidos, j.iniciales, j.foto,
                       CONCAT(COALESCE(td.nombre, ''), ' ', COALESCE(d.documento, '')) AS documento,
                       c.nombre AS categoria,
                       COUNT(dg.id) AS entregados,
                       SUM(CASE WHEN dg.estado = 'verificar' THEN 1 ELSE 0 END) AS por_verificar,
                       SUM(CASE WHEN dg.estado = 'verificado' THEN 1 ELSE 0 END) AS verificados,
                       SUM(CASE WHEN dg.estado = 'rechazado' THEN 1 ELSE 0 END) AS rechazados
                FROM jugadores j
                LEFT JOIN categorias c ON c.id_categorias = j.id_categorias
                LEFT JOIN documentos d ON d.id_jugadores = j.id_jugadores
                LEFT JOIN tipo_documento td ON td.id_tipo_documento = d.id_tipo_documento
                LEFT JOIN documentos_gestion dg ON dg.id_jugadores = j.id_jugadores
                WHERE j.estado = 'activo'
                GROUP BY j.id_jugadores, j.nombres, j.apellidos, j.iniciales, j.foto, td.nombre, d.documento, c.nombre
                ORDER BY j.apellidos, j.nombres";
        $rows = $this->query($sql);
        foreach ($rows as &$row) {
            $row['entregados'] = (int)$row['entregados'];
            $row['por_verificar'] = (int)$row['por_verificar'];
            $row['verificados'] = (int)$row['verificados'];
            $row['rechazados'] = (int)($row['rechazados'] ?? 0);
            $row['estado'] = ($row['entregados'] < 4 || $row['rechazados'] > 0) ? 'incompleto'
                : ($row['verificados'] === 4 ? 'completo' : 'verificar');
            $row['nombre'] = trim($row['nombres'].' '.$row['apellidos']);
            $row['documento'] = trim((string)$row['documento']);
        }
        return $rows;
    }

    public function obtenerJugador(int $id): ?array
    {
        return $this->queryOne(
            "SELECT j.id_jugadores AS id, j.nombres, j.apellidos, j.iniciales, j.foto, j.estado,
                    CONCAT(COALESCE(td.nombre, ''), ' ', COALESCE(d.documento, '')) AS documento,
                    c.nombre AS categoria
             FROM jugadores j
             LEFT JOIN categorias c ON c.id_categorias=j.id_categorias
             LEFT JOIN documentos d ON d.id_jugadores=j.id_jugadores
             LEFT JOIN tipo_documento td ON td.id_tipo_documento=d.id_tipo_documento
             WHERE j.id_jugadores=? LIMIT 1", [$id]
        );
    }

    public function obtenerPorJugador(int $idJugador): array
    {
        $rows = $this->query("SELECT * FROM documentos_gestion WHERE id_jugadores=?", [$idJugador]);
        $map = [];
        foreach ($rows as $row) $map[$row['clave']] = $row;
        $resultado = [];
        foreach (self::TIPOS as $clave => $info) {
            $r = $map[$clave] ?? [];
            $resultado[] = [
                'id' => (int)($r['id'] ?? 0), 'clave' => $clave,
                'nombre' => $info['nombre'], 'nombreCompleto' => $info['nombre'],
                'descripcion' => $info['descripcion'],
                'estado' => $r['estado'] ?? 'pendiente',
                'archivo' => $r['nombre_original'] ?? '', 'tamanoBytes' => (int)($r['tamano_bytes'] ?? 0),
                'tamano' => $this->formatearTamano((int)($r['tamano_bytes'] ?? 0)),
                'fechaSubida' => $r['fecha_subida'] ?? '', 'subidoPor' => $r['subido_por'] ?? '',
                'fechaVerificacion' => $r['fecha_verificacion'] ?? '', 'verificadoPor' => $r['verificado_por'] ?? '',
                'motivoRechazo' => $r['motivo_rechazo'] ?? null,
                'url' => !empty($r['id']) ? '/streepsoft/documentos/archivo/'.(int)$r['id'].'?modo=preview' : '',
            ];
        }
        return $resultado;
    }

    public function obtenerPorJugadorYClave(int $jugadorId, string $clave): ?array
    {
        return $this->queryOne("SELECT * FROM documentos_gestion WHERE id_jugadores=? AND clave=? LIMIT 1", [$jugadorId,$clave]);
    }

    public function guardarArchivo(int $jugadorId, string $clave, string $nombre, string $ruta, int $bytes, int $usuarioId, string $usuarioNombre): bool
    {
        $sql = "INSERT INTO documentos_gestion
                (id_jugadores, clave, nombre_original, ruta_archivo, tamano_bytes, estado, id_usuario_subio, subido_por, fecha_subida)
                VALUES (?, ?, ?, ?, ?, 'verificar', ?, ?, NOW())
                ON DUPLICATE KEY UPDATE nombre_original=VALUES(nombre_original), ruta_archivo=VALUES(ruta_archivo),
                tamano_bytes=VALUES(tamano_bytes), estado='verificar', id_usuario_subio=VALUES(id_usuario_subio),
                subido_por=VALUES(subido_por), fecha_subida=NOW(), fecha_verificacion=NULL,
                id_usuario_verifico=NULL, verificado_por=NULL, motivo_rechazo=NULL";
        return $this->execute($sql, [$jugadorId,$clave,$nombre,$ruta,$bytes,$usuarioId,$usuarioNombre]);
    }

    public function obtenerRegistro(int $id): ?array
    {
        return $this->queryOne("SELECT * FROM documentos_gestion WHERE id=?", [$id]);
    }

    public function cambiarEstado(int $id, string $estado, ?string $motivo, ?int $usuarioId, ?string $usuarioNombre): bool
    {
        if ($estado === 'verificado') {
            return $this->execute("UPDATE documentos_gestion SET estado='verificado', fecha_verificacion=NOW(), id_usuario_verifico=?, verificado_por=?, motivo_rechazo=NULL WHERE id=?", [$usuarioId,$usuarioNombre,$id]);
        }
        if ($estado === 'verificar') {
            return $this->execute("UPDATE documentos_gestion SET estado='verificar', fecha_verificacion=NULL, id_usuario_verifico=NULL, verificado_por=NULL, motivo_rechazo=NULL WHERE id=?", [$id]);
        }
        if ($estado === 'rechazado') {
            return $this->execute("UPDATE documentos_gestion SET estado='rechazado', fecha_verificacion=NULL, id_usuario_verifico=NULL, verificado_por=NULL, motivo_rechazo=? WHERE id=?", [$motivo,$id]);
        }
        return false;
    }

    public function eliminarRegistro(int $id): bool
    {
        return $this->execute("DELETE FROM documentos_gestion WHERE id=?", [$id]);
    }

    public function resumen(): array
    {
        return $this->queryOne("SELECT COUNT(*) total, SUM(estado='verificado') verificados, SUM(estado='verificar') verificar FROM documentos_gestion") ?? ['total'=>0,'verificados'=>0,'verificar'=>0];
    }

    private function formatearTamano(int $bytes): string
    {
        if (!$bytes) return '';
        return $bytes < 1048576 ? max(1, round($bytes/1024)).' KB' : number_format($bytes/1048576,1).' MB';
    }
}

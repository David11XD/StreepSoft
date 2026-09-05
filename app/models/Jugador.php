<?php
declare(strict_types=1);

class Jugador extends Model
{
    // ─── OBTENER TODOS ───
    public function obtenerTodos(): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            ORDER BY apellidos, nombres
        ";
        return $this->query($sql);
    }

    // ─── OBTENER POR ID ───
    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT * 
            FROM jugadores
            WHERE id_jugadores = ?
            LIMIT 1
        ";
        return $this->queryOne($sql, [$id]);
    }

    // ─── OBTENER POR CATEGORÍA ───
    public function obtenerPorCategoria(string $categoria): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            WHERE categoria = ?
            ORDER BY apellidos, nombres
        ";
        return $this->query($sql, [$categoria]);
    }

    // ─── OBTENER CON DEUDA ───
    public function obtenerConDeuda(): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            WHERE pago IS NOT NULL
            ORDER BY apellidos, nombres
        ";
        return $this->query($sql);
    }

    // ─── OBTENER ACTIVOS ───
    public function obtenerActivos(): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            WHERE estado = 'Activo'
            ORDER BY apellidos, nombres
        ";
        return $this->query($sql);
    }

    // ─── CREAR ───
    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO jugadores (
                foto,
                primer_apellido,
                segundo_apellido,
                primer_nombre,
                segundo_nombre,
                tipo_de_documento,
                identificacion,
                iniciales,
                fecha_nacimiento,
                edad,
                sexo,
                eps,
                instructor,
                categoria,
                talla_camiseta,
                numero_camiseta,
                talla_pantaloneta,
                talla_media,
                acudiente,
                tipo,
                identificacion_acudiente,
                numero_acudiente
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?
            )
        ";

        $exito = $this->execute($sql, [
            $datos['foto']                     ?? null,
            $datos['primer_apellido'],
            $datos['segundo_apellido']         ?? null,
            $datos['primer_nombre'],
            $datos['segundo_nombre']           ?? null,
            $datos['tipo_de_documento']        ?? null,
            $datos['identificacion']           ?? null,
            $datos['iniciales']                ?? null,
            $datos['fecha_nacimiento'],
            $datos['edad']                     ?? null,
            $datos['sexo']                     ?? null,
            $datos['eps']                      ?? null,
            $datos['instructor']               ?? null,
            $datos['categoria']                ?? null,
            $datos['talla_camiseta']           ?? null,
            $datos['numero_camiseta']          ?? null,
            $datos['talla_pantaloneta']        ?? null,
            $datos['talla_media']              ?? null,
            $datos['acudiente'],
            $datos['tipo']                     ?? null,
            $datos['identificacion_acudiente'] ?? null,
            $datos['numero_acudiente']         ?? null,
        ]);

        if (!$exito) {
            return 0;
        }

        return $this->lastInsertId();
    }

    // ─── ACTUALIZAR ───
    public function actualizar(int $id, array $datos): bool
    {
        $valores = [
            $datos ['foto']                    ??null, 
            $datos['primer_apellido'],
            $datos['segundo_apellido']         ?? null,
            $datos['primer_nombre'],
            $datos['segundo_nombre']           ?? null,
            $datos['tipo_de_documento']        ?? null,
            $datos['identificacion']           ?? null,
            $datos['iniciales']                ?? null,
            $datos['fecha_nacimiento'],
            $datos['edad']                     ?? null,
            $datos['sexo']                     ?? null,
            $datos['eps']                      ?? null,
            $datos['instructor']               ?? null,
            $datos['categoria']                ?? null,
            $datos['talla_camiseta']           ?? null,
            $datos['numero_camiseta']          ?? null,
            $datos['talla_pantaloneta']        ?? null,
            $datos['talla_media']              ?? null,
            $datos['acudiente'],
            $datos['tipo']                     ?? null,
            $datos['identificacion_acudiente'] ?? null,
            $datos['numero_acudiente']         ?? null,
        ];

        $sql = "
            UPDATE jugadores SET
                primer_apellido          = ?,
                segundo_apellido         = ?,
                primer_nombre            = ?,
                segundo_nombre           = ?,
                tipo_de_documento        = ?,
                identificacion           = ?,
                iniciales                = ?,
                fecha_nacimiento         = ?,
                edad                     = ?,
                sexo                     = ?,
                eps                      = ?,
                instructor               = ?,
                categoria                = ?,
                talla_camiseta           = ?,
                numero_camiseta          = ?,
                talla_pantaloneta        = ?,
                talla_media              = ?,
                acudiente                = ?,
                tipo                     = ?,
                identificacion_acudiente = ?,
                numero_acudiente         = ?
        ";

        if (!empty($datos['foto'])) {
            $sql     .= ", foto = ?";
            $valores[] = $datos['foto'];
        }

        $sql     .= " WHERE id_jugadores = ?";
        $valores[] = $id;

        return $this->execute($sql, $valores);
    }

    // ─── DESACTIVAR ───
    public function desactivar(int $id): bool
    {
        $sql = "UPDATE deudas SET pago = 'mora' WHERE id_jugadores = ?";
        return $this->execute($sql, [$id]);
    }

    // ─── ELIMINAR (desactiva) ───
    public function eliminar(int $id): bool
    {
        return $this->desactivar($id);
    }

    // ─── CONTAR ───
    public function contar(): int
    {
        $sql    = "SELECT COUNT(*) as total FROM jugadores";
        $result = $this->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    // ─── CALCULAR EDAD ───
    public function calcularEdad(string $fechaNacimiento): int
    {
        $nacimiento = new DateTime($fechaNacimiento);
        $hoy        = new DateTime();
        return (int)$hoy->diff($nacimiento)->y;
    }
}


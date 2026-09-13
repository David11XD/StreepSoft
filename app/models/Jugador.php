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
                nombres,
                apellidos,
                fecha_nacimiento,
                id_responsable,
                id_categorias,
                id_eps,
                id_instructor,
                foto,
                iniciales
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $datos['id_responsables'] ?? null,
            $datos['id_categorias'],
            $datos['id_eps'],
            $datos['id_instructor'],
            $datos['foto'] ?? null,
            $datos['iniciales'] ?? null,
        ]);

        if (!$exito) {
            return 0;
        }

        return $this->lastInsertId();
    }

    public function obtenerParaEditar(int $id): ?array
    {
        $sql = "
            SELECT
                j.*,
                doc.documento,
                doc.id_tipo_documento,
                r.nombres AS responsable_nombres,
                r.apellidos AS responsable_apellidos,
                r.id_tipo_documento AS responsable_id_tipo_documento,
                r.identificacion AS responsable_identificacion,
                r.numero_celular AS responsable_numero_celular
            FROM jugadores j
            LEFT JOIN documentos doc ON doc.id_jugadores = j.id_jugadores
            LEFT JOIN responsables r ON r.id_responsable = j.id_responsable
            WHERE j.id_jugadores = ?
            LIMIT 1
        ";

        return $this->queryOne($sql, [$id]);
    }



    /**
     * Actualizar un jugador
     */
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
            UPDATE jugadores
            SET nombres = ?,
                apellidos = ?,
                fecha_nacimiento = ?,
                id_responsable = ?,
                id_categorias = ?,
                id_eps = ?,
                id_instructor = ?,
                iniciales = ?,
                foto = COALESCE(?, foto)
            WHERE id_jugadores = ?
        ";

        return $this->execute($sql, [
            $datos['nombres'] ?? null,
            $datos['apellidos'] ?? null,
            $datos['fecha_nacimiento'] ?? null,
            $datos['id_responsable'] ?? null,
            $datos['id_categorias'] ?? null,
            $datos['id_eps'] ?? null,
            $datos['id_instructor'] ?? null,
            $datos['iniciales'] ?? null,
            $datos['foto'] ?? null,
            $id
        ]);
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


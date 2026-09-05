<?php
declare(strict_types=1);

/**
 * Jugador - Modelo de jugadores
 * 
 */
class Jugador extends Model
{
    /**
     * Obtener todos los jugadores
     * 
     */

    public function obtenerTodos(): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            ORDER BY apellidos, nombres
        ";
        
        return $this->query($sql);
    }

    /**
     * Obtener un jugador por ID
     */

    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            WHERE id_jugadores = ?
            LIMIT 1
        ";
        
        return $this->queryOne($sql, [$id]);
    }

    /**
     * Obtener jugadores por categoría
     * 
     */

    public function obtenerPorCategoria(int $categoriaId): array
    {
        $sql = "
            SELECT * 
            FROM vista_jugadores
            WHERE id_categoria = ?
            ORDER BY apellidos, nombres
        ";
        
        return $this->query($sql, [$categoriaId]);
    }

    /**
     * Obtener jugadores con deuda
     * 
     * @return array - Jugadores que tienen deuda
     */
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

    /**
     * Crear un nuevo jugador
     *
     */
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
            $datos['nombres'],
            $datos['apellidos'],
            $datos['fecha_nacimiento'],
            $datos['id_responsables'] ?? null,
            $datos['id_categorias'],
            $datos['id_eps'],
            $datos['id_instructor'],
            $datos['foto'] ?? null,
            $datos['iniciales'] ?? null,
        ]);

        // Si el INSERT no funcionó, no hay id que devolver
        if (!$exito) {
            return 0;
        }

        // lastInsertId() viene de la clase base Model (ver Model.php)
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
        // NOTA: se corrigió igual que crear() -> mismas columnas reales
        // de la tabla jugadores. Aún no está conectada a ninguna ruta,
        // pero la dejamos coherente con el resto del modelo.
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

    /**
     * Eliminar un jugador
     */
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM jugadores WHERE id_jugadores = ?";
        return $this->execute($sql, [$id]);
    }

    /**
     * Contar jugadores
     */
    public function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM vista_jugadores";
        $result = $this->queryOne($sql);
        return $result['total'] ?? 0;
    }
}
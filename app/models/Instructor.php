<?php
declare(strict_types=1);

class Instructor extends Model
{
    /**
     * Jugadores que pasaron a este instructor en la última llamada a crear()/actualizar()
     * al reasignar sus categorías (solo informativo).
     */
    public int $jugadoresReasignados = 0;

    // Columnas del instructor + sus categorías (una categoría pertenece a un solo instructor,
    // un instructor puede tener varias). `categoria` es el texto "Sub-10, Sub-20" para mostrar
    // y `categorias_ids` los ids separados por coma.
    private const SELECT_CON_CATEGORIAS = "
        SELECT
            i.id_instructor,
            i.foto,
            i.nombres,
            i.apellidos,
            i.edad,
            i.numero_celular,
            i.descripcion,
            i.estado,
            GROUP_CONCAT(c.nombre ORDER BY c.nombre SEPARATOR ', ') AS categoria,
            GROUP_CONCAT(c.id_categorias ORDER BY c.nombre SEPARATOR ',') AS categorias_ids
        FROM instructor i
        LEFT JOIN categorias c ON c.id_instructor = i.id_instructor
    ";

    private const GROUP_POR_INSTRUCTOR = "
        GROUP BY i.id_instructor, i.foto, i.nombres, i.apellidos, i.edad,
                 i.numero_celular, i.descripcion, i.estado
    ";

    public function obtenerTodos(): array
    {
        $sql = self::SELECT_CON_CATEGORIAS . self::GROUP_POR_INSTRUCTOR . "
            ORDER BY i.nombres, i.apellidos
        ";

        return $this->query($sql);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = self::SELECT_CON_CATEGORIAS . "
            WHERE i.id_instructor = ?
        " . self::GROUP_POR_INSTRUCTOR . "
            LIMIT 1
        ";

        return $this->queryOne($sql, [$id]);
    }

    /**
     * Ids de las categorías que tiene asignadas el instructor.
     *
     * @return int[]
     */
    public function obtenerIdsCategorias(int $id): array
    {
        $filas = $this->query(
            "SELECT id_categorias FROM categorias WHERE id_instructor = ? ORDER BY nombre",
            [$id]
        );

        return array_map(fn (array $f): int => (int) $f['id_categorias'], $filas);
    }

    /**
     * Crea el instructor. Si $datos['categorias'] (array de ids) viene definido, le asigna
     * esas categorías en la MISMA transacción: si algo falla no queda nada a medias.
     */
    public function crear(array $datos): int
    {
        $this->jugadoresReasignados = 0;

        return $this->enTransaccion(function () use ($datos): int {
            $sql = "
                INSERT INTO instructor (
                    nombres, apellidos, edad, numero_celular, descripcion, foto, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $exito = $this->execute($sql, [
                $datos['nombres'],
                $datos['apellidos'],
                $datos['edad'] ?? null,
                $datos['numero_celular'] ?? null,
                $datos['descripcion'] ?? null,
                $datos['foto'] ?? null,
                $datos['estado'] ?? 'activo',
            ]);

            $id = $exito ? $this->lastInsertId() : 0;

            if ($id > 0 && array_key_exists('categorias', $datos)) {
                $this->sincronizarCategorias(
                    $id,
                    (array) $datos['categorias'],
                    ($datos['estado'] ?? 'activo') === 'activo'
                );
            }

            return $id;
        });
    }

    /**
     * Actualiza el instructor. Si $datos['categorias'] (array de ids) viene definido, sus
     * categorías quedan EXACTAMENTE esas: las que ya no marca se liberan, las nuevas se le
     * asignan (aunque pertenecieran a otro instructor) y los jugadores de sus categorías
     * pasan a ser suyos. Si la clave no viene, las categorías no se tocan.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $this->jugadoresReasignados = 0;

        return $this->enTransaccion(function () use ($id, $datos): bool {
            $sql = "
                UPDATE instructor
                SET nombres = ?,
                    apellidos = ?,
                    edad = ?,
                    numero_celular = ?,
                    descripcion = ?,
                    foto = COALESCE(?, foto),
                    estado = ?
                WHERE id_instructor = ?
            ";

            $exito = $this->execute($sql, [
                $datos['nombres'],
                $datos['apellidos'],
                $datos['edad'] ?? null,
                $datos['numero_celular'] ?? null,
                $datos['descripcion'] ?? null,
                $datos['foto'] ?? null,
                $datos['estado'] ?? 'activo',
                $id,
            ]);

            if ($exito && array_key_exists('categorias', $datos)) {
                $this->sincronizarCategorias(
                    $id,
                    (array) $datos['categorias'],
                    ($datos['estado'] ?? 'activo') === 'activo'
                );
            }

            return $exito;
        });
    }

    public function retirar(int $id): bool
    {
        return $this->execute(
            "UPDATE instructor SET estado = 'inactivo' WHERE id_instructor = ?",
            [$id]
        );
    }

    public function reactivar(int $id): bool
    {
        return $this->execute(
            "UPDATE instructor SET estado = 'activo' WHERE id_instructor = ?",
            [$id]
        );
    }

    public function actualizarFoto(int $id, ?string $foto): bool
    {
        return $this->execute(
            "UPDATE instructor SET foto = ? WHERE id_instructor = ?",
            [$foto, $id]
        );
    }

    /**
     * Deja las categorías del instructor exactamente en $idsCategorias.
     *  1) Libera las que tenía y ya no marcó (sus jugadores no se tocan).
     *  2) Toma las marcadas; si eran de otro instructor se le quitan a ese.
     *  3) Si el instructor está activo, los jugadores de esas categorías pasan a él
     *     (tabla jugadores y también jugadores_inactivos, para que al reactivar un
     *     jugador no vuelva con el instructor anterior).
     * Debe llamarse dentro de una transacción.
     *
     * @param int[] $idsCategorias
     */
    private function sincronizarCategorias(int $idInstructor, array $idsCategorias, bool $reasignarJugadores): void
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idsCategorias),
            fn (int $v): bool => $v > 0
        )));

        // Se ignoran ids que no existen en categorias
        if ($ids !== []) {
            $marcas = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare("SELECT id_categorias FROM categorias WHERE id_categorias IN ($marcas)");
            $stmt->execute($ids);
            $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        if ($ids === []) {
            $this->execute('UPDATE categorias SET id_instructor = NULL WHERE id_instructor = ?', [$idInstructor]);
            return;
        }

        $marcas = implode(',', array_fill(0, count($ids), '?'));

        $this->execute(
            "UPDATE categorias SET id_instructor = NULL WHERE id_instructor = ? AND id_categorias NOT IN ($marcas)",
            array_merge([$idInstructor], $ids)
        );

        $this->execute(
            "UPDATE categorias SET id_instructor = ? WHERE id_categorias IN ($marcas)",
            array_merge([$idInstructor], $ids)
        );

        if (!$reasignarJugadores) {
            return;
        }

        $parametros = array_merge([$idInstructor], $ids, [$idInstructor]);

        $stmt = $this->pdo->prepare(
            "UPDATE jugadores SET id_instructor = ? WHERE id_categorias IN ($marcas) AND id_instructor <> ?"
        );
        $stmt->execute($parametros);
        $this->jugadoresReasignados += $stmt->rowCount();

        $stmt = $this->pdo->prepare(
            "UPDATE jugadores_inactivos SET id_instructor = ? WHERE id_categorias IN ($marcas) AND id_instructor <> ?"
        );
        $stmt->execute($parametros);
    }

    /**
     * Ejecuta $accion dentro de una transacción (si ya hay una abierta, se une a ella).
     *
     * @template T
     * @param callable():T $accion
     * @return T
     */
    private function enTransaccion(callable $accion)
    {
        if ($this->pdo->inTransaction()) {
            return $accion();
        }

        $this->pdo->beginTransaction();
        try {
            $resultado = $accion();
            $this->pdo->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}

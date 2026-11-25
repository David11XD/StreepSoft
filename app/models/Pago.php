<?php

declare(strict_types=1);

class Pago extends Model
{
    /* Trae todos los pagos con datos del alumno, para la tabla principal */
    public function obtenerTodos(): array
    {
        $sql = "SELECT p.*,
                       j.nombres, j.apellidos, j.iniciales,
                       c.nombre AS categoria_nombre
                FROM pagos p
                INNER JOIN jugadores j ON p.id_jugadores = j.id_jugadores
                LEFT JOIN categorias c ON j.id_categorias = c.id_categorias
                ORDER BY p.fecha DESC";

        return $this->query($sql);
    }

    /* Trae un pago específico por su id */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT p.*,
                       j.nombres, j.apellidos
                FROM pagos p
                INNER JOIN jugadores j ON p.id_jugadores = j.id_jugadores
                WHERE p.id = :id";

        return $this->queryOne($sql, [':id' => $id]);
    }

    /* Trae el historial de correcciones de un pago específico */
    public function obtenerHistorial(int $idPago): array
    {
        $sql = "SELECT h.*, u.nombre_completo AS editor_nombre
                FROM pagos_historial h
                INNER JOIN usuarios u ON h.id_usuario = u.id
                WHERE h.id_pago = :id_pago
                ORDER BY h.editado_en DESC";

        return $this->query($sql, [':id_pago' => $idPago]);
    }
}

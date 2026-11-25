<?php
/**
 * Elimina del historial los movimientos de reactivación con más de 24 horas.
 * No elimina jugadores, deudas, documentos ni otros movimientos históricos.
 * Ejecutar desde CLI: php scripts/limpiar_historial_reactivaciones.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado.\n");
}

date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/CambioEstadoJugador.php';

try {
    $eliminados = (new CambioEstadoJugador($pdo))->limpiarReactivacionesVencidas();
    echo sprintf(
        "[%s] Limpieza completada: %d movimiento(s) de reactivación eliminado(s).\n",
        date('Y-m-d H:i:s'),
        $eliminados
    );
} catch (Throwable $e) {
    error_log('Limpieza del historial de reactivaciones: ' . $e->getMessage());
    fwrite(STDERR, 'Error al limpiar el historial de reactivaciones: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

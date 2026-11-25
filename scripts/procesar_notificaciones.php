<?php
/**
 * Tarea programada de notificaciones.
 * Ejecutar desde CLI:  php scripts/procesar_notificaciones.php
 *
 * Conviene programarla una vez al día (después de actualizar las moras);
 * cron/generar-deudas-diarias.php ya la llama al final.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado.\n");
}

date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Configuracion.php';
require_once __DIR__ . '/../app/models/Notificacion.php';

try {
    $r = (new Notificacion($pdo))->generarAutomaticas();

    echo sprintf(
        "[%s] Notificaciones: %d avisos vigentes, %d nuevos, %d reabiertos, %d cerrados.\n",
        date('Y-m-d H:i:s'),
        $r['procesadas'],
        $r['nuevas'],
        $r['reabiertas'],
        $r['resueltas']
    );
} catch (Throwable $e) {
    error_log('Tarea automática de notificaciones: ' . $e->getMessage());
    fwrite(STDERR, 'Error al procesar notificaciones: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

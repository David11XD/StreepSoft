<?php
define('APP_PATH', __DIR__ . '/../app');
define('PROJECT_ROOT', __DIR__ . '/..');

require_once PROJECT_ROOT . '/config/database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/models/Configuracion.php';
require_once APP_PATH . '/utils/GeneradorDeudas.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log("CRON - Error BD: " . $e->getMessage());
    exit(1);
}

$generador = new GeneradorDeudas($pdo);

echo "=== CRON: Respaldo de ciclos de deudas ===\n";
echo "Hora: " . date('Y-m-d H:i:s') . "\n\n";

echo "1. Comprobando ciclos faltantes...\n";
$resultado1 = $generador->generarDeudasProximoMes();
echo "   Generadas: " . $resultado1['generadas'] . "\n";
echo "   " . $resultado1['mensaje'] . "\n";

if (!empty($resultado1['errores'])) {
    echo "   Errores:\n";
    foreach ($resultado1['errores'] as $error) {
        echo "   - " . $error . "\n";
    }
}

echo "\n2. Actualizando moras...\n";
$resultado2 = $generador->actualizarMoras();
echo "   Actualizadas: " . $resultado2['actualizadas'] . "\n";

if (!empty($resultado2['errores'])) {
    echo "   Errores:\n";
    foreach ($resultado2['errores'] as $error) {
        echo "   - " . $error . "\n";
    }
}

$log = sprintf(
    "\n[%s] Generadas: %d, Moras: %d",
    date('Y-m-d H:i:s'),
    $resultado1['generadas'],
    $resultado2['actualizadas']
);

@file_put_contents(
    PROJECT_ROOT . '/logs/cron-deudas.log',
    $log,
    FILE_APPEND
);

echo "\n=== CRON Completado ===\n";
exit(0);
?>

<?php
/**
 * Limpieza de fotos huérfanas: ADMINISTRADORES, JUGADORES e INSTRUCTORES.
 *
 * Antes de corregir los controladores, cada cambio de foto dejaba la anterior en disco
 * (administradores y jugadores), eliminar un jugador dejaba su foto ahí, y un guardado
 * fallido podía dejar la foto nueva sin usar (también en instructores). Este script borra
 * de las tres carpetas las fotos que ya no usa ningún registro en la BD.
 *
 * Qué se considera "en uso":
 *   - Administradores: usuarios.foto                          (public/Image/admins)
 *   - Jugadores:       jugadores.foto  Y  jugadores_inactivos.foto   (public/Image/jugadores)
 *                      (al inactivar un jugador su foto se copia a jugadores_inactivos,
 *                       por eso ambas tablas cuentan)
 *   - Instructores:    instructor.foto                        (public/Image/instructores)
 *
 * Ejecutar desde CLI (por defecto SOLO MUESTRA lo que borraría, no borra nada):
 *   php scripts/limpiar_fotos_huerfanas.php
 *
 * Para borrar de verdad (el borrado es definitivo, no pasa por la papelera):
 *   php scripts/limpiar_fotos_huerfanas.php --borrar
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado.\n");
}
 
date_default_timezone_set('America/Bogota');
 
require_once __DIR__ . '/../config/database.php';
 
$borrar = in_array('--borrar', $argv ?? [], true);
 
// Una foto subida hace menos de estos minutos NO se toca: puede estar justo en medio
// de un guardado (el archivo ya está en disco pero la BD aún no la registra).
const MINUTOS_DE_GRACIA = 10;
 
$grupos = [
    'Administradores' => [
        'carpeta'    => __DIR__ . '/../public/Image/admins',
        'total_sql'  => 'SELECT COUNT(*) FROM usuarios',
        'en_uso_sql' => "SELECT foto FROM usuarios WHERE foto IS NOT NULL AND foto <> ''",
        'protegidas' => [],
    ],
    'Jugadores' => [
        'carpeta'    => __DIR__ . '/../public/Image/jugadores',
        'total_sql'  => 'SELECT COUNT(*) FROM jugadores',
        'en_uso_sql' => "SELECT foto FROM jugadores WHERE foto IS NOT NULL AND foto <> ''
                         UNION
                         SELECT foto FROM jugadores_inactivos WHERE foto IS NOT NULL AND foto <> ''",
        'protegidas' => [],
    ],
    'Instructores' => [
        'carpeta'    => __DIR__ . '/../public/Image/instructores',
        'total_sql'  => 'SELECT COUNT(*) FROM instructor',
        'en_uso_sql' => "SELECT foto FROM instructor WHERE foto IS NOT NULL AND foto <> ''",
        'protegidas' => ['default.jpg'],   // imagen por defecto: nunca se borra
    ],
];
 
$totalHuerfanas = 0;
$totalEliminadas = 0;
$totalBytes = 0;
 
foreach ($grupos as $nombre => $g) {
    echo "=== $nombre ===\n";
 
    if (!is_dir($g['carpeta'])) {
        echo "  No existe la carpeta: {$g['carpeta']}\n\n";
        continue;
    }
 
    try {
        $enUso = $pdo->query($g['en_uso_sql'])->fetchAll(PDO::FETCH_COLUMN);
        $totalRegistros = (int) $pdo->query($g['total_sql'])->fetchColumn();
    } catch (Throwable $e) {
        echo "  Error consultando la BD (se omite este grupo): " . $e->getMessage() . "\n\n";
        continue;
    }
 
    // Seguridad: si la tabla viene vacía algo anda mal; no se borra nada de este grupo
    if ($totalRegistros === 0) {
        echo "  La tabla está vacía; se omite para no borrar fotos por error.\n\n";
        continue;
    }
 
    $enUso = array_map('basename', $enUso);
    $huerfanas = [];
    $recientes = 0;
    $bytes = 0;
    $limite = time() - (MINUTOS_DE_GRACIA * 60);
 
    foreach (scandir($g['carpeta']) as $archivo) {
        $ruta = $g['carpeta'] . '/' . $archivo;
 
        // Se ignoran ".", "..", ".gitkeep", archivos ocultos, subcarpetas y fotos protegidas
        if ($archivo[0] === '.' || !is_file($ruta) || in_array($archivo, $g['protegidas'], true)) {
            continue;
        }
 
        if (in_array($archivo, $enUso, true)) {
            continue;
        }
 
        if (filemtime($ruta) > $limite) {
            $recientes++;
            continue;
        }
 
        $huerfanas[] = $archivo;
        $bytes += filesize($ruta);
    }
 
    echo sprintf("  Fotos en uso en la BD: %d\n", count($enUso));
    echo sprintf("  Fotos huérfanas: %d (%.2f MB)\n", count($huerfanas), $bytes / 1048576);
    if ($recientes > 0) {
        echo sprintf("  Se omitieron %d foto(s) subidas hace menos de %d min (pueden estar guardándose).\n", $recientes, MINUTOS_DE_GRACIA);
    }
 
    $totalHuerfanas += count($huerfanas);
    $totalBytes += $bytes;
 
    if (!$huerfanas) {
        echo "  Nada que limpiar.\n\n";
        continue;
    }
 
    foreach ($huerfanas as $f) {
        if (!$borrar) {
            echo "    [se borraría] $f\n";
        } elseif (unlink($g['carpeta'] . '/' . $f)) {
            $totalEliminadas++;
        } else {
            echo "    [error] No se pudo borrar $f\n";
        }
    }
    echo "\n";
}
 
if (!$borrar) {
    echo sprintf("Modo simulación: %d foto(s) huérfana(s) (%.2f MB) en total. No se borró nada.\n", $totalHuerfanas, $totalBytes / 1048576);
    echo "Si la lista es correcta, ejecuta de nuevo con --borrar para eliminarlas.\n";
} else {
    echo sprintf("[%s] Eliminadas %d de %d fotos huérfanas.\n", date('Y-m-d H:i:s'), $totalEliminadas, $totalHuerfanas);
}
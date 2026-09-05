<?php
declare(strict_types=1);

ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', '600');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/streepsoft/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base = dirname($_SERVER['SCRIPT_NAME']);
$base = str_replace('\\', '/', $base);

// IMPORTANTE: Si la ruta termina en /public, quitar /public
// Esto sucede cuando el archivo está en /public/index.php
if (basename($base) === 'public') {
    $base = dirname($base);
}

// ============================================================================
// 1. CONFIGURACIÓN INICIAL
// ============================================================================

// Definir la URL base de la aplicación
define('BASE_URL', rtrim($protocol. '://' . $host . $base, '/') . '/');
define('APP_PATH', __DIR__ . '/../app');
define('CONFIG_PATH', __DIR__ . '/../config');

// Zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', '0');  // No mostrar errores en pantalla (seguridad)
ini_set('log_errors', '1');      // Loguear errores en archivo
ini_set('error_log', __DIR__ . '/../logs/error.log');

// ============================================================================
// 2. CARGAR ARCHIVOS NECESARIOS
// ============================================================================

// Cargar configuración de base de datos
require_once CONFIG_PATH . '/database.php';

// Cargar clases base (Core)
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/SessionTimeout.php';
require_once APP_PATH . '/helpers/url.php';

// ========================================================================
// CARGAR MODELOS
// =========================================================================

require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Jugador.php';
require_once APP_PATH . '/models/Estadistica.php';
require_once APP_PATH . '/models/Recuperacion.php';
require_once APP_PATH . '/models/Categoria.php';
require_once APP_PATH . '/models/Instructor.php';
require_once APP_PATH . '/models/Eps.php';
require_once APP_PATH . '/models/TipoDocumento.php';
require_once APP_PATH . '/models/Responsable.php';
require_once APP_PATH . '/models/Documento.php';
require_once APP_PATH . '/models/Deuda.php';
require_once APP_PATH . '/models/MetodoPago.php';
require_once APP_PATH . '/models/TipoBeca.php';
require_once APP_PATH . '/models/Actividad.php';

// Verificar si la sesion expiro por timeout
SessionTimeout::check();

// ANTES DE RENDERIZAR VISTAS PROTEGIDAS
if (Auth::check()) {
    // Verificar que la sesión no expiró
    if (time() - ($_SESSION['last_activity'] ?? time()) > 600) {
        // 10 minutos de inactividad
        session_destroy();
        header('Location: /streepsoft/?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Hacer disponible la conexión PDO globalmente
$GLOBALS['pdo'] = $pdo ??  null;

// ============================================================================
// 3. DEFINIR RUTAS
// ============================================================================

// Definir las rutas de la aplicación
// Formato: method(ruta, 'ControllerName@methodName')

// RUTAS PÚBLICAS (sin autenticación requerida)
$rutas = [
    // Página de inicio
    'GET' => [
        '/' => ['controller' => 'PublicController', 'method' => 'home'],
        '/home' => ['controller' => 'PublicController', 'method' => 'home'],
        
        // Login
        '/login' => ['controller' => 'AuthController', 'method' => 'showLogin'],
        
        // Recuperar contraseña
        '/recuperar-contrasena' => ['controller' => 'RecuperacionController', 'method' => 'showRecover'],
    ],
    
    'POST' => [
        // Login (procesar)
        '/login' => ['controller' => 'AuthController', 'method' => 'login'],
        
        // Logout
        '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
        '/quick-login' => ['controller' => 'QuickLoginController', 'method' => 'quickLogin'],
    
        // Recuperacion de contraseña
        '/recuperacion-enviar-pin' => ['action' => 'recuperation'],
    ]
];

// RUTAS PROTEGIDAS (requieren autenticación)
if (Auth::check()) {
    $rutasProtegidas = [
        'GET' => [
            '/dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
            '/nav-menu' => ['controller' => 'NavController', 'method' => 'render'],
            '/jugadores/gestion' => ['controller' => 'JugadorController', 'method' => 'gestion'],
            '/jugadores/deudas' => ['controller' => 'DeudaController', 'method' => 'listar'],
            '/deudas/:id/pago' => ['controller' => 'DeudaController', 'method' => 'mostrarPago'],
            '/jugadores/crear' => ['controller' => 'JugadorController', 'method' => 'crear'],
            '/jugadores/editar/:id' => ['controller' => 'JugadorController', 'method' => 'editar'],
            '/perfil-jugador' => ['controller' => 'JugadorController', 'method' => 'perfil'],
            '/pagos/historial' => ['controller' => 'PagosController', 'method' => 'matriz'],
            '/perfil/administrador' => ['controller' => 'PerfilAdminController', 'method' => 'perfil'],
            '/reportes/generar' => ['controller' => 'ReporteController', 'method' => 'generar'],
        ],
        
        'POST' => [
            '/jugadores/guardar' => ['controller' => 'JugadorController', 'method' => 'guardar'],
            '/jugadores/actualizar' => ['controller' => 'JugadorController', 'method' => 'actualizar'],
            '/jugadores/eliminar/:id' => ['controller' => 'JugadorController', 'method' => 'eliminar'],
            '/deudas/registrar-pago' => ['controller' => 'DeudaController', 'method' => 'registrarPago'],
            '/perfil/actualizar' => ['controller' => 'PerfilAdminController', 'method' => 'actualizarPerfil'],
            '/perfil/cambiar-foto' => ['controller' => 'PerfilAdminController', 'method' => 'cambiarFoto'],
        ]
    ];
    
    // Combinar rutas
    foreach ($rutasProtegidas as $metodo => $rutasMetodo) {
        if (!isset($rutas[$metodo])) {
            $rutas[$metodo] = [];
        }
        $rutas[$metodo] = array_merge($rutas[$metodo], $rutasMetodo);
    }
}
 
// ============================================================================
// 4. PROCESAR LA PETICIÓN
// ============================================================================

try {
    // Obtener el método HTTP (GET, POST, etc)
    $metodo = $_SERVER['REQUEST_METHOD'];
    
    $uri = $_GET['url'] ?? '/';

    // Si viene en REQUEST_URI (cuando .htaccess funciona o entras directo a /public/)
    if (empty($_GET['url'])) {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Extraer la carpeta del proyecto de BASE_URL
        $projectFolder = parse_url(BASE_URL, PHP_URL_PATH);
        $projectFolder = rtrim($projectFolder, '/');

        if (!empty($projectFolder) && 
                $projectFolder !== '/'
                && strpos($uri, $projectFolder) === 0){
            $uri = substr($uri, strlen($projectFolder));
        }

        if (strpos($uri, '/public') === 0){
            $uri = substr($uri, strlen('/public'));
        }

        $uri = trim($uri, '/');

        if ($uri === ''){
            $uri = '/';
        } else {
            $uri = '/' . $uri;
        }
    }
    
    $uri = '/' . trim($uri, '/');

    // Buscar la ruta en nuestras rutas definidas
    $rutaEncontrada = null;
    $parametros = [];
    
    if (isset($rutas[$metodo])) {
        foreach ($rutas[$metodo] as $ruta => $detalles) {
            // Convertir :id a un patrón numérico y construir la regex
            $patron = '#^' . str_replace(':id', '(\d+)', $ruta) . '$#';
            
            if (preg_match($patron, $uri, $matches)) {
                $rutaEncontrada = $detalles;
                array_shift($matches); // Remover el match completo
                $parametros = $matches;
                break;
            }
        }
    }

    if (!empty($rutas[$_SERVER['REQUEST_METHOD']][$uri])) {
        $ruta = $rutas[$_SERVER['REQUEST_METHOD']][$uri];
        
        if (isset($ruta['action']) && $ruta['action'] === 'recuperation') {
            // Ejecutar RecuperacionController.php directamente (mantiene PHPMailer intacto)
            require_once APP_PATH . '/controllers/RecuperacionController.php';
            exit;
        }
    }

    // Si no encontramos la ruta, mostrar 404
    if (!$rutaEncontrada) {

        http_response_code(404);

        echo "<h2>404 - Ruta no encontrada</h2>";

        echo "<hr>";

        echo "<strong>REQUEST_URI:</strong><br>";
        echo htmlspecialchars($_SERVER['REQUEST_URI']);

        echo "<br><br>";

        echo "<strong>SCRIPT_NAME:</strong><br>";
        echo htmlspecialchars($_SERVER['SCRIPT_NAME']);

        echo "<br><br>";

        echo "<strong>BASE_URL:</strong><br>";
        echo htmlspecialchars(BASE_URL);

        echo "<br><br>";

        echo "<strong>Ruta calculada (\$uri):</strong><br>";
        echo htmlspecialchars($uri);

        echo "<br><br>";

        echo "<strong>Método HTTP:</strong><br>";
        echo htmlspecialchars($metodo);

        echo "<br><br>";

        echo "<strong>Rutas disponibles:</strong>";

        echo "<pre>";
        print_r(array_keys($rutas[$metodo] ?? []));
        echo "</pre>";

        exit;
    }
    
    // ========================================================================
    // 5. EJECUTAR EL CONTROLADOR
    // ========================================================================
    
    // Obtener nombre del controlador y método
    $controllerName = $rutaEncontrada['controller'];
    $methodName = $rutaEncontrada['method'];
    
    // Cargar el archivo del controlador
    $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
    
    if (!file_exists($controllerFile)) {
        throw new Exception("Controlador no encontrado: $controllerName");
    }
    
    require_once $controllerFile;
    
    // Crear una instancia del controlador
    if (!class_exists($controllerName)) {
        throw new Exception("Clase controlador no encontrada: $controllerName");
    }
    
    $controller = new $controllerName($GLOBALS['pdo']);
    
    // Verificar que el método existe
    if (!method_exists($controller, $methodName)) {
        throw new Exception("Método no encontrado: $controllerName@$methodName");
    }
    
    // Ejecutar el método
    if (count($parametros) > 0) {
        $parametrosConvertidos = [];
        foreach ($parametros as $param) {
            $parametrosConvertidos[] = is_numeric($param) ? (int) $param : $param;
        }
        call_user_func_array([$controller, $methodName], $parametrosConvertidos);
    } else {
        $controller->$methodName();
    }

} catch (Exception $e) {
    // Si hay un error, registrarlo
    error_log("Error: " . $e->getMessage());
    
    // Mostrar el error (solo en desarrollo)
    http_response_code(500);
    echo "Error en la aplicación";
    
    if (ini_get('display_errors')) {
        echo "<pre>";
        echo htmlspecialchars($e->getMessage());
        echo "</pre>";
    }
}


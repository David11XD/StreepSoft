<?php
declare(strict_types=1);

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin(): void
    {
        // Si ya está logueado, redirigir a dashboard
        if (Auth::check()) {
            $this->redirect('/streepsoft/dashboard');
        }

        // Renderizar vista de login
        $this->view('auth/login', [
            'titulo' => 'Iniciar Sesión'
        ]);
    }

    // Procesar login (POST)
    public function login(): void
    {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/login');
        }

        // Obtener credenciales
        $usuario = trim($_POST['usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        // Validar que no estén vacíos
        if (empty($usuario) || empty($contrasena)) {
            $this->redirect('/streepsoft/login?error=campos_vacios');
        }

        // Crear modelo de usuario
        $usuarioModel = new Usuario($this->pdo);

        // Obtener usuario de la BD
        $usuarioData = $usuarioModel->obtenerporusuario($usuario);

        // Verificar que existe
        if (!$usuarioData) {
            error_log("Intento de login fallido: usuario no encontrado: $usuario");
            $this->redirect('/streepsoft/login?error=credenciales_invalidas');
        }

        // Verificar contraseña
        if (!password_verify($contrasena, $usuarioData['contrasena'])) {
            error_log("Intento de login fallido: contraseña incorrecta: $usuario");
            $this->redirect('/streepsoft/login?error=credenciales_invalidas');
        }


        // Registrar en sesión
        Auth::login($usuarioData);

        // Inicializar timestamp de última actividad
        $_SESSION['last_activity'] = time();

        // Log de éxito
        error_log("Login exitoso: {$usuarioData['usuario']}");

        // Limpiar quick login si existe
        if (isset($_SESSION['quick_login'])) {
            unset($_SESSION['quick_login']);
        }

        // Redirigir al dashboard
        $this->redirect('/streepsoft/dashboard');
    }

    /**
     * Cerrar sesión (logout)
     */
    public function logout(): void
    {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/');
        }

        // Verificar CSRF token
        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/?error=csrf');
        }

        // Verificar que está autenticado
        if (!Auth::check()) {
            $this->redirect('/streepsoft/');
        }

        // Obtener nombre antes de cerrar
        $nombreUsuario = Auth::nombre();

        // Cerrar sesión completamente
        Auth::logout();

        // Log
        error_log("Logout: $nombreUsuario");

        // Redirigir a home
        $this->redirect('/streepsoft/');
    }
}
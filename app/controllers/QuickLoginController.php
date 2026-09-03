<?php
declare(strict_types=1);

/**
 * QuickLoginController - Inicio rápido (sin credenciales)
 */
class QuickLoginController extends Controller
{
    /**
     * Realizar quick login
     */
    public function quickLogin(): void
    {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/');
        }

        // Verificar CSRF
        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/?error=csrf');
        }

        // Verificar que quick login está disponible
        if (!SessionTimeout::isQuickLoginAvailable()) {
            $this->redirect('/streepsoft/?error=quickLoginExpired');
        }

        // Obtener datos del quick login
        $quickLoginData = SessionTimeout::getQuickLoginData();

        if (!$quickLoginData) {
            $this->redirect('/streepsoft/?error=noQuickLoginData');
        }

        // Obtener usuario de la BD
        $usuarioModel = new Usuario($this->pdo);
        $usuario = $usuarioModel->obtenerporusuario($quickLoginData['usuario_nombre']);

        if (!$usuario) {
            $this->redirect('/streepsoft/?error=usuarioNoEncontrado');
        }

        // Registrar en sesión
        Auth::login($usuario);
        $_SESSION['last_activity'] = time();

        // Limpiar quick login
        SessionTimeout::clearQuickLogin();

        // Log
        error_log("Quick login exitoso: {$usuario['usuario']}");

        // Redirigir al dashboard
        $this->redirect('/streepsoft/dashboard');
    }
}
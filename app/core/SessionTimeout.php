<?php
declare(strict_types=1);

// SessionTimeout - Gestiona timeout de sesión

class SessionTimeout 
{
    // 10 minutos en segundos
    const TIMEOUT = 600;

    // 2 minutos para quick login
    const QUICK_LOGIN_TIMEOUT = 120;

    // VERIFICAR EL TIMEOUT
    public static function check(): void
    {
        // ✅ Limpiar cookie de logout manual si usuario está logueado nuevamente
        if (Auth::check() && isset($_COOKIE['logout_manual'])){
            setcookie('logout_manual', '', time() - 3600, '/streepsoft/');
            error_log("Cookie logout_manual limpiada (usuario logueado nuevamente)");
        }
    
        if(!Auth::check()){
            return;
        }

        // Obtener timestamp de última actividad
        $lastActivity = $_SESSION['last_activity'] ?? time();
        $currentTime = time();
        $difference = $currentTime - $lastActivity;

        // si pasaron más de 10 minutos → TIMEOUT AUTOMÁTICO
        if ($difference > self::TIMEOUT) {
            $nombreUsuario = Auth::nombre();
            
            // ✅ GUARDAR quick_login COMO COOKIE (antes de destruir sesión)
            self::saveQuitckLoginDataAsCookie();

            // Cerrar sesión
            $_SESSION = [];
            session_destroy();

            error_log("Session expirada por timeout: $nombreUsuario");

            // Redirigir a home (NO con parámetro, la cookie contiene todo)
            header('Location: /streepsoft/');
            exit;
        }

        $_SESSION['last_activity'] = $currentTime;
    }

    // ✅ GUARDAR QUICK LOGIN COMO COOKIE (no como sesión)
    public static function saveQuitckLoginDataAsCookie(): void
    {
        // NO guardar si fue logout manual (verificar cookie)
        if (isset($_COOKIE['logout_manual']) && $_COOKIE['logout_manual'] === '1') {
            error_log("Logout manual detectado: no guardar quick_login");
            return;
        }

        $usuario = Auth::user();

        if ($usuario) {
            // ✅ Crear un array con datos de quick login
            $quickLoginData = [
                'usuario_id' => $usuario['id'],
                'usuario_nombre' => $usuario['usuario'],
                'usuario_email' => $usuario['email'] ?? null,
                'timestamp' => time(),
                'expires_at' => time() + self::QUICK_LOGIN_TIMEOUT
            ];

            // ✅ Guardar como JSON en cookie
            setcookie(
                'quick_login_data',
                json_encode($quickLoginData),
                time() + self::QUICK_LOGIN_TIMEOUT,
                '/streepsoft/',
                '',
                false,
                true  // httponly
            );

            error_log("Quick login cookie establecida para: " . $usuario['usuario']);
        }
    }

    // Verificar si quick login está disponible (desde COOKIE)
    public static function isQuickLoginAvailable(): bool
    {
        // ✅ Verificar cookie de quick_login (no sesión)
        if (!isset($_COOKIE['quick_login_data'])) {
            return false;
        }

        // ✅ Verificar que NO fue logout manual
        if (isset($_COOKIE['logout_manual']) && $_COOKIE['logout_manual'] === '1') {
            return false;
        }

        try {
            $quickLoginData = json_decode($_COOKIE['quick_login_data'], true);
            if (!is_array($quickLoginData)) {
                return false;
            }

            $currentTime = time();
            $expiresAt = $quickLoginData['expires_at'] ?? 0;

            // Si expiró (pasaron más de 2 minutos)
            if ($currentTime > $expiresAt) {
                // Limpiar cookie expirada
                setcookie('quick_login_data', '', time() - 3600, '/streepsoft/');
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error verificando quick_login cookie: " . $e->getMessage());
            return false;
        }
    }

    // Obtener datos de quick login desde cookie
    public static function getQuickLoginData(): ?array
    {
        if (!self::isQuickLoginAvailable()) {
            return null;
        }

        try {
            return json_decode($_COOKIE['quick_login_data'], true);
        } catch (Exception $e) {
            error_log("Error decodificando quick_login: " . $e->getMessage());
            return null;
        }
    }


    // Limpiar la cookie de quick login
    public static function clearQuickLogin(): void
    {
        setcookie('quick_login_data', '', time() - 3600, '/streepsoft/', '', false, true);
        error_log("Quick login cookie limpiada tras uso exitoso");
    }
}
?>
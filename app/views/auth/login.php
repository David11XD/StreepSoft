<?php
// Inicializar contador de retrocesos en sesión
if (!isset($_SESSION['backClickCount'])) {
    $_SESSION['backClickCount'] = 0;
}

$mensaje = '';
$tipo = '';

if (isset($_GET['error'])) {

    switch ($_GET['error']) {

        case 'campos_vacios':
            $mensaje = 'Debes completar todos los campos.';
            $tipo = 'danger';
            break;

        case 'credenciales_invalidas':
            $mensaje = 'Usuario o contraseña incorrectos.';
            $tipo = 'danger';
            break;

        case 'csrf':
            $mensaje = 'La sesión expiró. Intenta nuevamente.';
            $tipo = 'warning';
            break;

        default:
            $mensaje = 'Ha ocurrido un error.';
            $tipo = 'danger';
    }
}

if (isset($_GET['success'])) {

    switch ($_GET['success']) {

        case 'logout':
            $mensaje = 'Has cerrado sesión correctamente.';
            $tipo = 'success';
            break;
    }

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Font Awesome - librería de íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/streepsoft/public/css/login/login.css">
    <link rel="stylesheet" href="/streepsoft/public/css/login/login-alert.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
</head>
<body class="background-login">
    <div class="page-wrapper">
        <div class="card">
            <div class="card-img">
                <div class="imagen-logo">
                    <img src="/streepsoft/public/Image/CopColombiaInternacional.png" alt="logo" width="110px" height="70px">
                </div>
            </div>
        </div>

        <div class="login-card">

            <h1 class="h1-login">Iniciar Sesión</h1>
            <p class="p1-login">Porfavor Ingresa tus credenciales para ingresar</p>

            <form action="/streepsoft/login" method="POST">

                <div class="input-group">
                    <label for="email">Correo</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="email" id="email" name="usuario" placeholder="Usuario" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="contrasena" placeholder="Contraseña" required>
                    </div>
                </div>

                <?php if(!empty($mensaje)): ?> 
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    
                    <script>
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: <?= json_encode($mensaje) ?>,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,

                        background: '#232323',
                        color: '#ffffff',
                        iconColor: '#f5c400'
                    });
                    </script>
                <?php endif; ?> 

                <?php if (isset($_GET['password'])): ?> 
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                    <script>
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Contraseña actualizada correctamente',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,

                            background: '#232323',
                            color: '#ffffff',
                            iconColor: '#f5c400'
                        })
                    </script>

                <?php endif; ?> 


                <button class="buttonLogin" type="submit">Ingresar</button>

                <div class="link">
                    <div class="link-password">
                        <a class="alogin" href="/streepsoft/app/views/auth/logincorreo.php">¿Olvidaste tu <span>contraseña?</span> </a>
                    </div>

                </div>
                
            </form>
        </div>
    </div>

    <script src="/streepsoft/public/js/login/login.js"></script>
    <script src="/streepsoft/public/js/login/recover.js"></script>

    <script>
        // Contador de veces que el usuario intenta retroceder
        // let backClickCount = <?php echo $_SESSION['backClickCount'] ?? 0; ?>;
        const MAX_BACK_CLICKS = 2;

        // Prevenir retroceso (back button)
        window.history.pushState(null, null, window.location.href);
                    
        window.addEventListener('popstate', function(event) {
            // Incrementar contador
            backClickCount++;
                    
            // Si ya intentó 2 veces, bloquearlo
            if (backClickCount >= MAX_BACK_CLICKS) {
                alert('No puedes retroceder más de 2 veces. Por favor, inicia sesión o usa el botón de flecha.');
                // Volver a empujar el estado para mantener en login
                window.history.pushState(null, null, window.location.href);
            } else {
                // Permitir retroceso
                window.history.back();
            }
        });

        // Guardar el contador en el servidor (opcional, para persistencia)
        window.addEventListener('beforeunload', function() {
            fetch('/streepsoft/api/update-back-count', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ count: backClickCount })
            }).catch(e => {}); // Ignorar errores
        });
    </script> 
</body>
</html>
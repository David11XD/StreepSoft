<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/streepsoft/public/css/login/log.css">
</head>
<body>

    <div class="background-login">
        <div class="page-wrapper">

            <div class="card">
                <div class="card-img">
                    <div class="imagen-logo">
                        <img src="/streepsoft/public/Image/CopColombiaInternacional.png" alt="logo" width="90px" height="50px">
                    </div>
                </div>
            </div>

            <div class="login-card">
                <form action="/streepsoft/app/controllers/RecuperacionController.php" method="POST">
    
                    <h1 class="h1-recover">Recuperar <span>Contraseña</span></h1>
                    
                    <p class="message-code">Para hacer la recuerparacion de tu cuenta ingresa el correo electronico.</p>

                    <div class="input-group">
                        <label for="usuario">Email</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" 
                                id="usuario"
                                name="usuario" 
                                placeholder="correo@ejemplo.com"
                                required>
                        </div>
                    </div>

                    <button class="buttonRecover" type="submit" name="enviar_correo">Enviar</button>
                    
                    <div class="link">
                        <a  href="/streepsoft/app/views/auth/login.php">cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['pinEX'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'PIN EXPIRADO',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,

            background: '#232323',
            color: '#ffffff',
            iconColor: '#f5c400'
            })
        </script>

    <?php endif; ?>

    <?php if (isset($_GET['user'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'usuario no encontrado',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,

            background: '#232323',
            color: '#ffffff',
            iconColor: '#f5c400'
            })
        </script>

    <?php endif; ?>
</body>

    <script src="/streepsoft/public/js/login/login.js"></script>
    <script src="/streepsoft/public/js/login/recover.js"></script>
</html>
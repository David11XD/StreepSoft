<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Codigo</title>
    <link rel="stylesheet" href="/streepsoft/public/css/login/log.css">
</head>
<body class="background-login">
    <div class="page-wrapper">
        
        <div class="card">
            <div class="card-img">
                <div class="imagen-logo">
                        <img src="../Image/CopColombiaInternacional.png" alt="logo" width="90px" height="50px">
                </div>
            </div>
        </div>

        <div class="login-card">

            <h1 class="h1-verify">Pin de <span>Seguridad</span></h1>
            <?php 
                $emailOculto = $_GET['email'] ?? 'tu correo';
                $usuario = $_GET['usuario'] ?? '';
            ?> 
            <p class="messege">Se acaba de enviar un correo electrónico con un código de verificación a <!-- <?php echo htmlspecialchars($emailOculto); ?> --></p>

            <form action="/streepsoft/app/controllers/RecuperacionController.php" method="POST" id="pinForm">
                
                <div class="pin-container">
                    <input class="pin-input" type="text" maxlength="1" name="pin[]" inputmode="numeric" pattern="[0-9]*" required>
                    <input class="pin-input" type="text" maxlength="1" name="pin[]" inputmode="numeric" pattern="[0-9]*" required>
                    <input class="pin-input" type="text" maxlength="1" name="pin[]" inputmode="numeric" pattern="[0-9]*" required>
                    <input class="pin-input" type="text" maxlength="1" name="pin[]" inputmode="numeric" pattern="[0-9]*" required>
                    <input class="pin-input" type="text" maxlength="1" name="pin[]" inputmode="numeric" pattern="[0-9]*" required>
                </div>

                <input type="hidden" name="usuario" value="<?=  $_GET['usuario'] ?? ''  ?>">

                <button class="buttonRecover" type="submit" name="verificar_pin">Verificar</button>
            </form>
            
            <div class="link">
                <a href="/streepsoft/app/views/auth/logincorreo.php">cancelar</a>
            </div>
        
        </div>
    </div>

     <?php if (isset($_GET['pinNo'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'PIN NO ENCONTRADO',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,

            background: '#232323',
            color: '#ffffff',
            iconColor: '#f5c400'
            })
        </script>

    <?php endif; ?>

    <?php if (isset($_GET['pinIN'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'PIN INCORRECTO',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,

            background: '#232323',
            color: '#ffffff',
            iconColor: '#f5c400'
            })
        </script>

    <?php endif; ?>

    <script src="/streepsoft/public/js/login/verify.js"></script>
</body>
</html>
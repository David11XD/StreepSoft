<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar contraseña</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/streepsoft/public/css/login/log.css">
</head>
<body class="background-login">
    <div class="page-wrapper">
        
    <div class="card">
        <div class="card-img">
            <div class="imagen-logo">
                <img src="/streepsoft/public/Image/CopColombiaInternacional.png" alt="logo" width="90px" height="50px">
            </div>
        </div>
    </div>   

    <div class="login-card">

        <h1 class="h1-Password-recovered">Contraseña <span>recuperada</span></h1>

        <p class="messege-recoverd">Porfavor digite su nueva contraseña </p>

        <form action="/streepsoft/app/controllers/RecuperacionController.php" method="POST" id="formPassword">

            <input type="hidden" name="usuario" value="<?= $_SESSION['usuario_recuperacion'] ?? '' ?>">
            
            <div class="input-group">
                <label>Nueva contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input
                        type="password"
                        id="nuevaPassword"
                        name="password"
                        placeholder="Nueva contraseña"
                        required
                    >
                </div>

                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>

                    <span class="strength-text" id="strengthText"></span>
                </div>

                <p class="req-msg" id="reqMsg"></p>
            </div>

            <div class="input-group">
                <label>Confirmar contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="confirmarPassword"
                        name="confirmar_password"
                        placeholder="Confirmar contraseña"
                        required
                    >
                </div>
                <p class="match-msg" id="matchMsg"></p>
            </div>
            <button class="buttonLogin" type="submit" name="cambiar_password" id="btnActualizar">Actualizar</button>
        </form>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/streepsoft/public/js/login/newpassword.js"></script>
</body>
</html>
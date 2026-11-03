<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        width: 100%;
        height: 100vh;
        height: 100dvh;
        overflow: hidden;
        background: #141414;
    }

    img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

     /* Codigo responsivo para pantallas con una relación de aspecto menor a 3:2 */
    @media (max-aspect-ratio: 3/2) {
        img {
            object-fit: contain;
        }
    }
</style>

<body>
    <img src="<?php echo BASE_URL; ?>public/Image/errores/404.jpg"
        alt="404 - Página no encontrada">

</body>

</html>
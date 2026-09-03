<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Alumno | StreepSoft</title>
    <link rel="stylesheet" href="/streepsoft/public/css/dashboard/dashboard.css">
    <link rel="stylesheet" href="/streepsoft/public/css/perfil.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
    
    <div id="nav-card"></div>

    <div class="main-content">
            <section class="perfil-page">

        <div class="perfil-header">
            <h1 class="perfil-titulo">Perfil de Alumno</h1>
            <div class="perfil-controles">
                <div class="buscador-wrapper">
                    <i class="bi bi-search buscador-icono"></i>
                    <input type="search" class="buscador-alumno" placeholder="Buscar alumno">
                </div>
                <div class="tabs">
                    <button class="tab-btn tab-activo">Alumno</button>
                    <button class="tab-btn">Pagos alumno</button>
                </div>
            </div>
        </div>

        <div class="tarjeta-estudiante">
            <div class="tarjeta-izquierda">
                <img src="/streepsoft/public/Image/perfilAlumno.png" alt="foto alumno" class="foto-alumno">
                <div class="tarjeta-info">
                    <div class="nombre-fila">
                        <span class="estudiante-label">Estudiante</span>
                        <span class="badge-activo">Activo</span>
                    </div>
                    <p class="nombre-completo">Juan Mora | JLM - Instructor . Julian</p>
                </div>
            </div>
            <div class="tarjeta-derecha">
                <p class="id-label">Identificacion</p>
                <div class="id-fila">
                    <span class="id-tipo">TI</span>
                    <span class="id-numero">102277387</span>
                </div>
                <button class="btn-editar">
                    <i class="bi bi-pencil-fill"></i> Editar
                </button>
            </div>
        </div>

        <div class="seccion">
            <h2 class="seccion-titulo"><span>DATOS PERSONALES</span></h2>
            <div class="campos-grid-5">
                <div class="campo">
                    <span class="campo-label">Segundo nombre</span>
                    <span class="campo-valor">Luis</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Primer Nombre</span>
                    <span class="campo-valor">Juan</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Segundo apellido</span>
                    <span class="campo-valor">Castillo</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Primer apellido</span>
                    <span class="campo-valor">Diaz</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Identificacion</span>
                    <span class="campo-valor">TI 102277387</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Instructor</span>
                    <span class="campo-valor">Julian</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Iniciales</span>
                    <span class="campo-valor">JLM</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Fecha Nacimiento</span>
                    <span class="campo-valor">12/06/2015</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Edad</span>
                    <span class="campo-valor">11</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Eps</span>
                    <span class="campo-valor">Nueva Eps</span>
                </div>
            </div>
        </div>

        <div class="seccion">
            <h2 class="seccion-titulo"><span>TALLA Y UNIFORMES</span></h2>
            <div class="campos-grid">
                <div class="campo">
                    <span class="campo-label">N de camisa</span>
                    <span class="campo-valor">10</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Talla Camisa</span>
                    <span class="campo-valor">L</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Talla Pantaloneta</span>
                    <span class="campo-valor">L</span>
                </div>
                <div class="campo">
                    <span class="campo-label">Talla Media</span>
                    <span class="campo-valor">L</span>
                </div>
            </div>
        </div>

        <div class="seccion">
            <h2 class="seccion-titulo"><span>ACUDIENTE</span></h2>
            <div class="acudiente-card">
                <div class="campos-grid">
                    <div class="campo">
                        <span class="campo-label">Acudiente</span>
                        <span class="campo-valor">Pablo Diaz</span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Identificacion</span>
                        <span class="campo-valor">cc 10082863</span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Numero Acudiente</span>
                        <span class="campo-valor">+57 300001128</span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Matricula</span>
                        <span class="campo-valor">2024/02/12</span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Deuda</span>
                        <span class="badge-deuda deuda-ok">Al dia</span>
                    </div>
                </div>
            </div>
        </div>

    </section>
    </div>


    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
</body>

</html>
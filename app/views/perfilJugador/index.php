<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Alumno | StreepSoft</title>
    <link rel="stylesheet" href="/streepsoft/public/css/dashboard/dashboard.css">
    <link rel="stylesheet" href="/streepsoft/public/css/perfil/perfil.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
    
    <div id="nav-card"></div>

    <div class="main-content">
        <section class="perfil-page">

            <div class="perfil-header">
                <h1 class="perfil-titulo">Perfil de Alumno</h1>
            </div>

            <div class="tarjeta-estudiante">
                <div class="tarjeta-izquierda">
                    <?php if (!empty($jugador['foto'])): ?>
                        <img src="/streepsoft/public/Image/jugadores/<?= htmlspecialchars($jugador['foto']) ?>" alt="foto alumno" class="foto-alumno">
                    <?php else: ?>
                        <img src="/streepsoft/public/Image/perfilAlumno.png" alt="foto alumno" class="foto-alumno">
                    <?php endif; ?>
                    <div class="tarjeta-info">
                        <div class="nombre-fila">
                            <span class="estudiante-label">Estudiante</span>
                            <span class="badge-activo <?= ($jugador['estado'] === 'Activo') ? 'activo' : 'inactivo' ?>">
                                <?= htmlspecialchars($jugador['estado']) ?>
                            </span>
                        </div>
                        <p class="nombre-completo">
                            <?= htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']) ?> | 
                            <?= htmlspecialchars($jugador['iniciales'] ?? 'N/A') ?> - 
                            Instructor . <?= htmlspecialchars($jugador['instructor'] ?? 'No asignado') ?>
                        </p>
                    </div>
                </div>
                <div class="tarjeta-derecha">
                    <p class="id-label">Identificacion</p>
                    <div class="id-fila">
                        <span class="id-tipo"><?= htmlspecialchars($jugador['tipo_documento'] ?? 'TI') ?></span>
                        <span class="id-numero"><?= htmlspecialchars($jugador['documentos'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <div class="seccion">
                <h2 class="seccion-titulo"><span>DATOS PERSONALES</span></h2>
                <div class="campos-grid-5">
                    <div class="campo">
                        <span class="campo-label">Nombres</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['nombres'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Apellidos</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['apellidos'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Identificacion</span>
                        <span class="campo-valor"><?= htmlspecialchars(($jugador['tipo_documento'] ?? 'TI') . ' ' . ($jugador['documentos'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Instructor</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['instructor'] ?? 'No asignado') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Iniciales</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['iniciales'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Fecha Nacimiento</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['fecha_nacimiento'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Edad</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['edad'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">Categoria</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['categoria'] ?? 'N/A') ?></span>
                    </div>
                    <div class="campo">
                        <span class="campo-label">EPS</span>
                        <span class="campo-valor"><?= htmlspecialchars($jugador['eps'] ?? 'N/A') ?></span>
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
                <h2 class="seccion-titulo"><span>ACUDIENTE / RESPONSABLE</span></h2>
                <div class="acudiente-card">
                    <?php if (!empty($responsable)): ?>
                        <div class="campos-grid">
                            <div class="campo">
                                <span class="campo-label">Nombre Responsable</span>
                                <span class="campo-valor"><?= htmlspecialchars($responsable['nombres'] . ' ' . $responsable['apellidos']) ?></span>
                            </div>
                            <div class="campo">
                                <span class="campo-label">Tipo Documento</span>
                                <span class="campo-valor"><?= htmlspecialchars($responsable['tipo_documento'] ?? 'N/A') ?></span>
                            </div>
                            <div class="campo">
                                <span class="campo-label">Identificacion</span>
                                <span class="campo-valor"><?= htmlspecialchars($responsable['identificacion'] ?? 'N/A') ?></span>
                            </div>
                            <div class="campo">
                                <span class="campo-label">Numero Celular</span>
                                <span class="campo-valor"><?= htmlspecialchars($responsable['numero_celular'] ?? 'N/A') ?></span>
                            </div>
                            <div class="campo">
                                <span class="campo-label">Estado Deuda</span>
                                <span class="badge-deuda <?= ($jugador['pago'] === 'pagado') ? 'deuda-ok' : 'deuda-pendiente' ?>">
                                    <?= htmlspecialchars(ucfirst($jugador['pago'] ?? 'Sin información')) ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="sin-datos">No tiene responsable asignado</p>
                    <?php endif; ?>
                </div>
            </div>

        </section>
    </div>


    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
</body>

</html>
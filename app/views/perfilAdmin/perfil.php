<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Administrador | Streepsoft</title>
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/perfilAdmin/perfilAdmin.css">
</head>

<body>
    <div id="nav-card"></div>

    <div class="main-content">

        <?php if (($_GET['success'] ?? '') === 'actualizado'): ?>
            <div id="notificacion-exito" class="toast-exito">
                <i class="fi fi-rr-check-circle"></i>
                Información actualizada correctamente.
            </div>
        <?php endif; ?>

        <div class="perfil-admin-container">
            <div class="perfil-fila-superior">
                <div class="tarjeta-datos">
                    <button class="boton-editar-info">
                        <i class="fi fi-rr-pencil"></i> Editar información
                    </button>

                    <div class="datos-personales">
                        <div class="foto-wrapper">
                            <?php if (!empty($admin['foto'])): ?>
                                <img src="/streepsoft/public/Image/admins/<?php echo htmlspecialchars($admin['foto']); ?>"
                                    alt="Foto de perfil" class="foto-admin-real">
                            <?php else: ?>
                                <div class="foto-placeholder">
                                    <i class="fi fi-rr-user"></i>
                                </div>
                            <?php endif; ?>

                            <form action="/streepsoft/perfil/cambiar-foto" method="POST" enctype="multipart/form-data" id="formCambiarFoto">
                                <input type="file" name="foto" id="inputFoto" accept="image/png, image/jpeg" hidden>
                                <button type="button" class="boton-cambiar-foto" id="botonCambiarFoto">Cambiar foto</button>
                            </form>
                        </div>

                        <div class="info-admin">
                            <h2><?php echo isset($admin['nombre_completo']) ? $admin['nombre_completo'] : 'No disponible'; ?></h2>
                            <p class="rol-admin">Administrador</p>

                            <div class="dato-linea">
                                <i class="fi fi-rr-envelope"></i>
                                <div>
                                    <span class="dato-label">Correo electrónico</span>
                                    <strong><?php echo isset($admin['usuario']) ? $admin['usuario'] : 'No disponible'; ?></strong>
                                </div>
                            </div>

                            <div class="dato-linea">
                                <i class="fi fi-rr-user"></i>
                                <div>
                                    <span class="dato-label">Documento de identidad</span>
                                    <strong><?php echo isset($admin['documento_identidad']) ? $admin['documento_identidad'] : 'No disponible'; ?></strong>
                                </div>
                            </div>

                            <div class="dato-linea">
                                <i class="fi fi-rr-phone-call"></i>
                                <div>
                                    <span class="dato-label">Teléfono</span>
                                    <strong><?php echo isset($admin['telefono']) ? $admin['telefono'] : 'No disponible'; ?></strong>
                                </div>
                            </div>

                            <div class="dato-linea">
                                <i class="fi fi-rr-calendar"></i>
                                <div>
                                    <span class="dato-label">Fecha de registro</span>
                                    <strong><?php echo isset($admin['creado_en']) ? date('d/m/Y', strtotime($admin['creado_en'])) : 'No disponible'; ?></strong>
                                </div> <!--empty-->
                            </div>
                        </div>
                    </div>
                    <div class="linea-divisora"></div>
                </div>

                <!--Actividad reciente-->
                <div class="tarjeta-actividad">
                    <h3><i class="fi fi-rr-pending"></i> Actividad Reciente</h3>

                    <div class="lista-actividad">
                        <?php if (empty($actividad)): ?>
                            <p style="color:#888; font-size:13px;"> Todavia no hay actividad registrada.</p>
                        <?php else: ?>
                            <?php foreach ($actividad as $item): ?>
                                <div class="item-actividad">
                                    <span class="punto-actividad"></span>
                                    <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                                    <span class="fecha-actividad">
                                        <?php echo date('d M, h:i A', strtotime($item['creado_en'])) ?>
                                    </span>
                                </div>
                            <?php endforeach ?>
                        <?php endif ?>
                    </div>

                    <!-- El botón y la línea quedan encapsulados al final -->
                    <div class="actividad-footer">
                        <button class="boton-ver-actividad">Ver toda la actividad</button>
                        <div class="linea-divisora"></div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del perfil -->
            <div class="stats-perfil">
                <div class="stat-perfil">
                    <div class="stat-icono">
                        <i class="fi fi-rr-users"></i>
                    </div>
                    <div>
                        <h2><?php /** @var array $stats */ echo $stats['jugadores']; ?></h2> <!-- @var array Esto le indica a Intelephense que la variable $stats sí existe y es un array-->
                        <p>Jugadores Registrados</p>
                    </div>
                </div>

                <div class="stat-perfil">
                    <div class="stat-icono">
                        <i class="fi fi-rr-triangle-warning"></i>
                    </div>
                    <div>
                        <h2><?php echo $stats['mora']; ?></h2>
                        <p>Jugadores en Mora</p>
                    </div>
                </div>

                <div class="stat-perfil">
                    <div class="stat-icono">
                        <i class="fi fi-rr-document"></i>
                    </div>
                    <div>
                        <h2><?php echo $stats['pagos']; ?></h2>
                        <p>Pagos Registrados</p>
                    </div>
                </div>

                <div class="stat-perfil">
                    <div class="stat-icono">
                        <i class="fi fi-rr-user"></i>
                    </div>
                    <div>
                        <h2><?php echo $stats['instructores']; ?></h2>
                        <p>Entrenadores Activos</p>
                    </div>
                </div>
            </div>

            <!-- Documentos y Reportes -->
            <div class="panel-documentos">
                <div class="documentos-header">
                    <i class="fi fi-rr-document"></i>
                    <div>
                        <h3>Documentos y reportes</h3>
                        <p>Descarga información general del sistema en diferentes formatos</p>
                    </div>
                </div>

                <div class="tabla-documentos-wrapper">
                    <table class="tabla-documentos">
                        <thead>
                            <tr>
                                <th>Documentos</th>
                                <th>Descripción</th>
                                <th>Formato</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-documento">
                                    <i class="fi fi-rr-users"></i>
                                    Reporte General de Jugadores
                                </td>
                                <td>Lista completa de todos los jugadores registrados</td>
                                <td>
                                    <select class="select-formato">
                                        <option value="pdf" selected>PDF</option>
                                        <option value="word">WORD</option>
                                        <option value="excel">EXCEL</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="boton-descargar" data-tipo="jugadores">
                                        <i class="fi fi-rr-download"></i> Descargar
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td class="col-documento">
                                    <i class="fi fi-rr-money"></i>
                                    Reporte de Pagos
                                </td>
                                <td>Historial de todos los pagos realizados</td>
                                <td>
                                    <select class="select-formato">
                                        <option value="pdf" selected>PDF</option>
                                        <option value="word">WORD</option>
                                        <option value="excel">EXCEL</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="boton-descargar" data-tipo="pagos">
                                        <i class="fi fi-rr-download"></i> Descargar
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td class="col-documento">
                                    <i class="fi fi-rr-triangle-warning"></i>
                                    Reporte de Deudas
                                </td>
                                <td>Estado actual de deudas de los jugadores</td>
                                <td>
                                    <select class="select-formato">
                                        <option value="pdf" selected>PDF</option>
                                        <option value="word">WORD</option>
                                        <option value="excel">EXCEL</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="boton-descargar" data-tipo="deudas">
                                        <i class="fi fi-rr-download"></i> Descargar
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td class="col-documento">
                                    <i class="fi fi-rr-trophy"></i>
                                    Reporte de Torneos
                                </td>
                                <td>Historial y resultados de torneos</td>
                                <td>
                                    <select class="select-formato">
                                        <option value="pdf" selected>PDF</option>
                                        <option value="word">WORD</option>
                                        <option value="excel">EXCEL</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="boton-descargar" data-tipo="torneos">
                                        <i class="fi fi-rr-download"></i> Descargar
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <button class="boton-ver-reportes">
                    <i class="fi fi-rr-folder"></i> Ver todos los reportes
                </button>
                <div class="linea-divisora"></div>
            </div>

            <!-- Modal: Editar información -->
            <div class="modal-overlay" id="modalEditarInfo">
                <div class="modal-caja">
                    <div class="modal-header">
                        <h3>Editar información</h3>
                        <button type="button" class="modal-cerrar" id="cerrarModalEditarInfo">✕</button>
                    </div>

                    <?php if (($_GET['error'] ?? '') === 'campos_vacios'): ?>
                        <p class="modal-mensaje modal-mensaje-error">Todos los campos son obligatorios.</p>
                    <?php endif; ?>

                    <?php if (($_GET['error'] ?? '') === 'nombre_invalido'): ?>
                        <p class="modal-mensaje modal-mensaje-error">El nombre solo puede contener letras y espacios.</p>
                    <?php endif; ?>

                    <?php if (($_GET['error'] ?? '') === 'telefono_invalido'): ?>
                        <p class="modal-mensaje modal-mensaje-error">El teléfono solo puede contener números.</p>
                    <?php endif; ?>

                    <?php if (($_GET['error'] ?? '') === 'documento_invalido'): ?>
                        <p class="modal-mensaje modal-mensaje-error">El documento solo puede contener números.</p>
                    <?php endif; ?>

                    <!-- Envía la información al controlador mediante una petición oculta (POST) -->
                    <form action="/streepsoft/perfil/actualizar" method="POST" id="formEditarInfo">

                        <label for="input-nombre-completo">Nombre completo</label>
                        <input type="text" id="input-nombre-completo" name="nombre_completo"
                            value="<?php echo isset($admin['nombre_completo']) ? htmlspecialchars($admin['nombre_completo']) : ''; ?>"
                            pattern="[A-Za-zÀ-ÿñÑ\s]+"
                            title="Solo se permiten letras y espacios, sin números ni caracteres especiales"
                            required>

                        <label for="input-telefono">Teléfono</label>
                        <input type="text" id="input-telefono" name="telefono"
                            value="<?php echo isset($admin['telefono']) ? htmlspecialchars($admin['telefono']) : ''; ?>"
                            pattern="[0-9]+"
                            maxlength="10"
                            title="Solo se permiten números"
                            required>

                        <label for="input-documento">Documento de identidad</label>
                        <input type="text" id="input-documento" name="documento_identidad"
                            value="<?php echo isset($admin['documento_identidad']) ? htmlspecialchars($admin['documento_identidad']) : ''; ?>"
                            pattern="[0-9]+"
                            maxlength="10"
                            title="Solo se permiten números"
                            required>

                        <div class="modal-acciones">
                            <button type="button" class="boton-cancelar" id="cancelarModalEditarInfo">Cancelar</button>
                            <button type="submit" class="boton-guardar">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="/streepsoft/public/js/navbar/script.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/perfilAdmin/perfilAdmin.js"></script>
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.7/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514195634-UH0HGKBC.js" defer></script>
</body>

</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Administrador | Streepsoft</title>
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/perfilAdmin/perfilAdmin.css">
        <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
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

            <!-- Configuracion de pagos--->
            <section class="configuracion">
                <div class="card-configuracion">
                    <span class="icon-park-outline--setting-config"></span>
                    <div class="configuracion-texto">
                        <h2>Configuración de pagos</h2>
                        <p>Configure las fechas limites para los pagos de los alumnos</p>
                    </div>
                </div>
                
                <?php if (($_GET['success'] ?? '') === 'configuracion_guardada'): ?>
                    <p class="modal-mensaje" style="color: #2ecc72">Configuración guardada</p>
                <?php endif; ?>
                <?php if (($_GET['error'] ?? '') === 'dia_invalido'): ?>
                    <p class="modal-mesaje modal-mensaje-error">Elige und ia de cobro válido.</p>
                <?php endif; ?>
                <?php if (($_GET['error'] ?? '') === 'gracia_invalida'): ?>
                    <p class="modal-mensaje modal-mensaje-error">Elige un perido de gracia válido.</p>
                <?php endif; ?>
                <?php if (($_GET['error'] ?? '') === 'configuracion_guardado'): ?>
                    <p class="modal-mensaje modal-mensaje-error">No se pudo guardar la configuración de pagos.</p>
                <?php endif; ?>
                <?php if (($_GET['error'] ?? '') === 'gracia_invalida'): ?>
                    <p class="modal-mensaje modal-mensaje-error">Elige un período de gracia válido.</p>
                <?php endif; ?>

                <form action="/streepsoft/perfil/configuracion-pagos" method="POST" id="formConfiguracionPagos">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="grid">
                        <div class="card-1">
                            <div class="limite-pago">
                                <span class="lets-icons--date-fill"></span>
                                <div class="text-limite">
                                    <h2>Fecha de pago</h2>
                                    <p>Selecciona el día máximo para realizar el pago mensual.</p>
                                </div>
                            </div>

                            <div class="input-limite">
                                <label>Dia de Pago</label>
                                <select name="dia_cobro" id="selectDiaCobro">
                                    <?php for ($dia = 1; $dia <= 31; $dia++): ?>
                                        <option value="<?= $dia ?>" <?= $dia === $diaCobro  ? 'selected'  : '' ?>>
                                            Dia <?= $dia ?> del mes
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card-2">
                            <div class="periodo-gracia">
                                <span class="ant-design--history-outlined"></span>
                                <div class="text-periodo">
                                    <h2>Periodo de Gracia</h2>
                                    <p>Días adicionales antes de marcar el pago como mora.</p>
                                </div>
                            </div>

                            <div class="input-periodo">
                                <label>Dias de Gracia</label>
                                <select name="dias_gracia" id="selectDiasGracia">
                                    <?php for ($gracia = 0; $gracia <= 30; $gracia++): ?>
                                        <option value="<?= $gracia ?>" <?= $gracia === $diasGracia ? 'selected' : '' ?>>
                                            <?= $gracia === 0 ? 'Sin dias de gracia' : $gracia . ' ' . ($gracia === 1 ? 'dia' : 'dias') ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card-3">
                            <div class="resumen">
                                <span class="mynaui--danger-square-solid"></span>
                                <div class="texto-resumen">
                                    <h2>Resumen</h2>
                                    <p>Vista previa de la configuraciòn</p>
                                </div>
                            </div>

                            <div class="card-resumen">
                                <div class="fecha">
                                    <div class="fecha-pago-texto">
                                        <h3>Fecha limite</h3>
                                        <p id="resumenDiaCobro">Dia <?= $diaCobro ?> de cada mes</p>
                                    </div>
                                    <div class="fecha-gracia-texto">
                                        <h3>Periodo de Gracia</h3>
                                        <p id="resumentDiasGracia"><?= $diasGracia > 0 ? $diasGracia . ' dias de gracia' : 'sin dias de gracia' ?></p>
                                    </div>
                                </div>

                                <div class="advertencia">
                                    <div class="card-advertencia">
                                        <span class="jam--triangle-danger-f"></span>
                                        <div class="text-advertencia">
                                            <h3>Inicio de mora</h3>
                                            <p id="resumenInicioMora">A partir del día <?= $diaCobro + $diasGracia + 1 ?> de cada mes*</p>
                                        </div>
                                    </div>

                                    <div class="card-recordar">
                                        <span class="hugeicons--idea-01"></span>
                                        <div class="text-recordar">
                                            <?php if ($diasGracia > 0): ?>
                                                <p>El pago vence el día <?= $diaCobro ?>. Después tendrá <?= $diasGracia ?> días de gracia; la mora inicia al terminar ese periodo.</p>
                                            <?php else: ?>
                                                <p>El pago vence el día <?= $diaCobro ?> y la mora inicia al día siguiente si continúa pendiente.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-configuracion">
                        <button type="submit" class="btn-guardar">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

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
                        <input type="text" maxlength="15" id="input-nombre-completo" name="nombre_completo"
                            value="<?php echo isset($admin['nombre_completo']) ? htmlspecialchars($admin['nombre_completo']) : ''; ?>"
                            pattern="[A-Za-zÀ-ÿñÑ\s]+"
                            maxlength="50"
                            minlength="10"
                            title="Solo se permiten letras y espacios, sin números ni caracteres especiales"
                            required>

                        <label for="input-telefono">Teléfono</label>
                        <input type="text"  inputmode="numeric" maxlength="10" id="input-telefono" name="telefono"
                            value="<?php echo isset($admin['telefono']) ? htmlspecialchars($admin['telefono']) : ''; ?>"
                            pattern="[0-9]+"
                            maxlength="10"
                            title="Solo se permiten números"
                            required>

                        <label for="input-documento">Documento de identidad</label>
                        <input type="text" inputmode="numeric" maxlength="10" id="input-documento" name="documento_identidad"
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
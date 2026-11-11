<?php
$esc = static fn($valor) => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$faltanSubir = $faltanSubir ?? [];
$faltanVerificar = $faltanVerificar ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/documentos/gestionar.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <title>Documentos del alumno</title>
</head>
<body>
    <div id="nav-card"></div>

    <div class="main-content">

        <div class="ges-navegacion">
            <a href="/streepsoft/documentos" class="ges-btn-volver">
                <i class="fi fi-rr-arrow-left"></i>
                Volver al listado
            </a>

            <nav class="ges-migas" aria-label="Ruta de navegacion">
                <a href="/streepsoft/documentos">Documentos</a>
                <span class="ges-migas-separador">&gt;</span>
                <span class="ges-migas-actual"><?= htmlspecialchars($alumno['nombre']) ?></span>
            </nav>
        </div>

        <h1 class="ges-titulo">Documentos del alumnos</h1>

        <!--  TARJETA DEL ALUMNO  -->
        <section class="ges-perfil">

            <div class="ges-perfil-fila">
                <span class="ges-avatar">
                    <?php if (!empty($alumno['foto'])): ?>
                        <img src="/streepsoft/public/Image/jugadores/<?= rawurlencode(basename((string)$alumno['foto'])) ?>"
                             alt="Foto de <?= $esc($alumno['nombre']) ?>"
                             loading="lazy"
                             onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                    <?php endif; ?>
                    <span class="ges-avatar-iniciales" <?= !empty($alumno['foto']) ? 'hidden' : '' ?>><?= $esc(mb_substr($alumno['iniciales'] ?? '', 0, 3)) ?></span>
                </span>

                <div class="ges-perfil-info">
                    <h2 class="ges-perfil-nombre"><?= htmlspecialchars($alumno['nombre']) ?></h2>
                    <span class="ges-perfil-doc"><?= htmlspecialchars($alumno['documento']) ?></span>
                    <span class="ges-etiqueta-activo"><?= $alumno['activo'] ? 'Activo' : 'Inactivo' ?></span>
                </div>
            </div>

            <div class="ges-perfil-progreso">

                <!-- Fila de arriba: conteo a la izquierda, etiqueta a la derecha -->
                <div class="ges-progreso-cabecera">
                    <span class="ges-progreso-conteo">
                        <?= $verificados ?> de <?= $totalDocumentos ?> verificados · <?= $entregados ?> entregados
                    </span>
                    <span class="ges-etiqueta-progreso progreso-<?= $estadoGeneral['clase'] ?>">
                        <?= $estadoGeneral['texto'] ?>
                    </span>
                </div>

                <!-- Barra dividida: un segmento por cada documento -->
                <div class="ges-segmentos">
                    <?php foreach ($documentos as $documento): ?>
                        <span class="ges-segmento segmento-<?= $documento['estado'] ?>" title="<?= htmlspecialchars($documento['nombre']) ?>"></span>
                    <?php endforeach; ?>
                </div>

                <!-- Mensaje de lo que falta -->
                <p class="ges-progreso-falta">
                    <?php if (count($faltanSubir) > 0): ?>
                        Falta subir: <?= htmlspecialchars(implode(', ', $faltanSubir)) ?>
                    <?php elseif (count($faltanVerificar) > 0): ?>
                        Falta verificar: <?= htmlspecialchars(implode(', ', $faltanVerificar)) ?>
                    <?php else: ?>
                        Todos los documentos están verificados
                    <?php endif; ?>
                </p>

            </div>

        </section>

        <!--  DOCUMENTOS OBLIGATORIOS  -->
        <section class="tdoc" aria-label="Documentos obligatorios de inscripción">

            <div class="tdoc-cabecera">
                <h2 class="tdoc-titulo">Documentos obligatorios de inscripción</h2>
                <p class="tdoc-subtitulo">Selecciona un documento para revisarlo, subirlo o verificarlo. Solo archivos PDF · máximo 10 MB.</p>
            </div>

            <div class="tdoc-cuerpo">

                <!-- Columna izquierda: las 4 filas de documentos -->
                <div class="tdoc-lista">
                    <div id="tdocFilas">
                        <?php foreach ($documentos as $documento): ?>
                            <button type="button" class="tdoc-fila" data-clave="<?= $documento['clave'] ?>">

                                <span class="tdoc-circulo">
                                    <i class="fi fi-rr-badge-check icono-verificado"></i>
                                    <i class="fi fi-rr-file-upload icono-verificar"></i>
                                    <i class="fi fi-rr-file-exclamation icono-pendiente"></i>
                                </span>

                                <span class="tdoc-fila-texto">
                                    <span class="tdoc-fila-nombre"><?= htmlspecialchars($documento['nombreCompleto']) ?></span>
                                    <span class="tdoc-fila-meta"></span>
                                </span>

                                <span class="tdoc-chip"></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="tdoc-lista-pie">
                        <button type="button" class="tdoc-btn" id="tdocSiguiente">
                            <i class="fi fi-rr-arrow-right"></i>
                            Ir al siguiente por atender
                        </button>
                        <span class="tdoc-nota">También puedes arrastrar un archivo sobre cualquier documento para subirlo.</span>
                    </div>
                </div>

                <!-- Columna derecha: detalle del documento seleccionado -->
                <div class="tdoc-detalle" id="tdocDetalle">

                    <!-- 1. Nombre, descripcion y pastilla de estado -->
                    <div class="tdoc-detalle-cabecera">
                        <div class="tdoc-detalle-info">
                            <span class="tdoc-detalle-etiqueta">Documento seleccionado</span>
                            <span class="tdoc-detalle-nombre" id="tdocNombre"></span>
                            <span class="tdoc-detalle-desc" id="tdocDescripcion"></span>
                        </div>
                        <span class="tdoc-chip" id="tdocChip"></span>
                    </div>

                    <!-- 2. Aviso rojo cuando el documento fue rechazado -->
                    <div class="tdoc-alerta" id="tdocAlerta" role="alert" hidden>
                        <i class="fi fi-rr-triangle-warning"></i>
                        <span id="tdocAlertaTexto"></span>
                    </div>

                    <!-- 3. Vista previa: zona para soltar, imagen o "hoja" de ejemplo -->
                    <div class="tdoc-vista">
                        <label class="tdoc-zona" data-estados="pendiente">
                            <i class="fi fi-rr-upload"></i>
                            <span class="tdoc-zona-titulo">Arrastra el archivo aquí o <span class="tdoc-zona-enlace">selecciónalo</span></span>
                            <span class="tdoc-zona-nota">Solo PDF · máximo 10 MB</span>
                            <input type="file" class="tdoc-input-archivo" accept="application/pdf,.pdf">
                        </label>

                        <img class="tdoc-imagen" id="tdocImagen" alt="Vista previa del documento cargado" hidden>
                        <iframe id="tdocPdfPreview" class="tdoc-pdf-preview" title="Vista previa PDF" hidden></iframe>

                        <!-- Hoja de ejemplo: se ve cuando el documento está pendiente o cuando es un PDF -->
                        <div class="tdoc-hoja" id="tdocHoja" hidden>
                            <div class="tdoc-hoja-titulo"></div>
                            <div class="tdoc-hoja-linea" style="width: 100%"></div>
                            <div class="tdoc-hoja-linea" style="width: 92%"></div>
                            <div class="tdoc-hoja-linea" style="width: 96%"></div>
                            <div class="tdoc-hoja-linea" style="width: 70%"></div>
                            <div class="tdoc-hoja-linea" style="width: 100%"></div>
                            <div class="tdoc-hoja-linea" style="width: 84%"></div>
                            <div class="tdoc-hoja-pie">
                                <div class="tdoc-hoja-firma"></div>
                                <span class="tdoc-hoja-ext" id="tdocExtension"></span>
                            </div>
                        </div>
                        <span class="tdoc-vista-nota" id="tdocNotaVista" hidden></span>
                    </div>

                    <!-- 4. Datos del archivo -->
                    <div class="tdoc-datos" data-estados="verificar verificado rechazado">
                        <div class="tdoc-dato">
                            <span class="tdoc-dato-etiqueta">Archivo</span>
                            <span class="tdoc-dato-valor recortar" id="tdocArchivo"></span>
                        </div>
                        <div class="tdoc-dato">
                            <span class="tdoc-dato-etiqueta">Tamaño</span>
                            <span class="tdoc-dato-valor" id="tdocTamano"></span>
                        </div>
                        <div class="tdoc-dato">
                            <span class="tdoc-dato-etiqueta">Subido</span>
                            <span class="tdoc-dato-valor" id="tdocSubido"></span>
                        </div>
                        <div class="tdoc-dato">
                            <span class="tdoc-dato-etiqueta">Verificación</span>
                            <span class="tdoc-dato-valor" id="tdocVerificacion"></span>
                        </div>
                    </div>
                    <span class="tdoc-ruta" id="tdocRuta" data-estados="verificar verificado rechazado"></span>

                    <!-- 5. Confirmar rechazo -->
                    <div class="tdoc-confirmar" id="tdocConfirmarRechazo" hidden>
                        <label>
                            Motivo del rechazo
                            <select id="tdocMotivo">
                                <option value="Ilegible">Ilegible o borroso</option>
                                <option value="Incompleto">Incompleto (falta una cara o una página)</option>
                                <option value="No corresponde">No corresponde al alumno</option>
                                <option value="Vencido">Documento vencido</option>
                            </select>
                        </label>
                        <div class="tdoc-botones">
                            <button type="button" class="tdoc-btn btn-peligro-relleno" data-accion="confirmar-rechazo">Confirmar rechazo</button>
                            <button type="button" class="tdoc-btn" data-accion="cancelar">Cancelar</button>
                        </div>
                    </div>

                    <!-- 6. Confirmar quitar -->
                    <div class="tdoc-confirmar" id="tdocConfirmarQuitar" hidden>
                        <span>¿Quitar este documento? El alumno volverá a quedar con el documento pendiente.</span>
                        <div class="tdoc-botones">
                            <button type="button" class="tdoc-btn btn-peligro-relleno" data-accion="confirmar-quitar">Sí, quitar</button>
                            <button type="button" class="tdoc-btn" data-accion="cancelar">Cancelar</button>
                        </div>
                    </div>

                    <!-- 7. Botones de accion (cada uno se ve solo en sus data-estados) -->
                    <div class="tdoc-botones">
                        <label class="tdoc-btn btn-dorado" data-estados="pendiente">
                            <i class="fi fi-rr-upload"></i>
                            Subir documento
                            <input type="file" class="tdoc-input-archivo" accept="application/pdf,.pdf">
                        </label>

                        <button type="button" class="tdoc-btn btn-dorado" data-accion="verificar" data-estados="verificar">
                            <i class="fi fi-rr-check"></i>
                            Verificar
                        </button>

                        <button type="button" class="tdoc-btn btn-peligro" data-accion="abrir-rechazo" data-estados="verificar">
                            <i class="fi fi-rr-cross"></i>
                            Rechazar
                        </button>

                        <button type="button" class="tdoc-btn btn-ok" data-accion="revertir" data-estados="verificado" title="Revertir la verificación">
                            <i class="fi fi-rr-check"></i>
                            Verificado · Revertir
                        </button>

                        <button type="button" class="tdoc-btn" data-accion="descargar" data-estados="verificar verificado rechazado">
                            <i class="fi fi-rr-download"></i>
                            Descargar
                        </button>

                        <button type="button" class="tdoc-btn" data-accion="abrir" data-estados="verificar verificado rechazado">
                            <i class="fi fi-rr-eye"></i>
                            Abrir
                        </button>

                        <label class="tdoc-btn" data-estados="verificar verificado rechazado">
                            <i class="fi fi-rr-upload"></i>
                            Reemplazar
                            <input type="file" class="tdoc-input-archivo" accept="application/pdf,.pdf">
                        </label>

                        <button type="button" class="tdoc-btn btn-peligro btn-icono" data-accion="preguntar-quitar"
                            data-estados="verificar verificado rechazado" title="Eliminar documento" aria-label="Quitar documento">
                            <i class="fi fi-rr-trash"></i>
                        </button>
                    </div>

                </div>

            </div>
        </section>

    </div>

    <!-- Mensaje flotante (aviso) que aparece unos segundos despues de cada accion -->
    <div class="tdoc-aviso" id="tdocAviso" role="status" hidden></div>

    <!-- Los datos de PHP pasan a JavaScript en formato JSON -->
    <script>
        const DATOS_TARJETA = <?= json_encode($datosTarjeta, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/documentos/gestionar.js"></script>
</body>

</html>

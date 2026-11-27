<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumno</title>
    <link rel="stylesheet" href="/streepsoft/public/css/nuevo/formularioJugador.css?v=<?= @filemtime(dirname(__DIR__, 4) . '/public/css/nuevo/formularioJugador.css') ?: 1 ?>">
</head>
<body>
    <div class="contenedor">
        <div class="contenedor-pagina-1">

            <div class="encabezado">
                <div class="titulo">
                    <h1>Nuevo Jugador</h1>
                    <i class="mingcute--user-add-fill"></i>
                </div>
                
                <p>Complete los campos para agregar un nuevo alumno</p>      
            </div>

            <div class="contenedor-pasos">

                <div class="pasos">

                    <div class="paso activo">
                        <div class="circulo"></div>
                        <span>Datos</span>
                    </div>

                    <div class="paso">
                        <div class="circulo"></div>
                        <span>Academia</span>
                    </div>

                    <div class="paso">
                        <div class="circulo"></div>
                        <span>uniforme</span>
                    </div>

                    
                    <div class="paso">
                        <div class="circulo"></div>
                        <span>Acudiente</span>
                    </div>

                    <div class="paso">
                        <div class="circulo"></div>
                        <span>pago</span>
                    </div>
                </div>


            </div>

        </div>


        <form action="/streepsoft/jugadores/guardar" method="POST" enctype="multipart/form-data" id="formjugador" target="_top" novalidate>
            
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            
            <section class="paso-formulario activo">
                <div class="contenido-datos">

                    <div class="contenedor-foto">
                        <label class="zona-foto" id="zonaFoto">
                            
                            <i class="fluent--camera-add-48-filled"></i>

                            <span class="texto-foto">
                                Subir foto<br>
                                del alumno
                            </span>
                            <img class="foto-miniatura" id="fotoMiniatura" src="#" alt="Foto del alumno" />
                        </label>
                        
                        <input type="file"
                               id="inputFoto"
                               name="foto"
                               accept="image/png, image/jpeg"
                               hidden>

                        <p class="foto-info">
                            JPG · PNG · MAX: 2 MB
                        </p>
                        <input type="hidden" id="fotoBase64" name="foto_base64" value="" />
                    </div>

                    <div>
                        <div class="titulo-seccion">
                            <div class="basil--document-solid"></div>
                            <span>Datos alumno</span>
                        </div>

                        <div class="grid-2">
                            <div class="grupo">
                                <label for="">segundo apellido</label>
                                <input type="text"
                                    name="apellido2"
                                    data-filtro="letras" maxlength="40"
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">segundo nombre</label>
                                <input type="text"
                                    name="nombre2"
                                    data-filtro="letras" maxlength="40"
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">primer apellido</label>
                                <input type="text"
                                    name="apellido1"
                                    data-filtro="letras" maxlength="40"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label for="">primer Nombre</label>
                                <input type="text"
                                    name="nombre1"
                                    data-filtro="letras" maxlength="40"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label>Tipo de documento</label>

                                <select name="id_tipo_documento" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach (($tiposDocumento ?? []) as $tipo): ?>
                                        <option value="<?= (int)$tipo['id_tipo_documento'] ?>">
                                            <?= htmlspecialchars($tipo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grupo">
                                <label for="">Identificacion</label>
                                <input type="text"
                                    inputmode="numeric"
                                    name="documento"
                                    data-filtro="numeros"
                                    pattern="[0-9]*"
                                    maxlength="10"
                                    placeholder="Escribe tu Documento" required>
                            </div>

                            <div class="grupo">
                                <label for="">Iniciales</label>
                                <input type="text"
                                    name="iniciales"
                                    maxlength="3"
                                    placeholder="Opcinal">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="paso-formulario">
                <div class="titulo-seccion">
                    <div class="solar--calendar-bold-duotone"></div>
                    <span>Informacion de academia</span>
                </div>

                <div class="grid-2">

                    <div class="grupo">
                        <label>Fecha de nacimiento</label>

                        <input type="date"
                            name="fecha_nacimiento"
                            required>
                    </div>

                    <div class="grupo">
                        <label>Edad</label>

                        <input type="text"
                            inputmode="numeric"
                            name="edad"
                                    data-filtro="numeros"
                            pattern="[0-9]*"
                            maxlength="2"
                            required>
                    </div>

                    <div class="grupo">
                        <label>Sexo</label>

                        <select name="sexo" required>
                            <option value="">Seleccione</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Masculino">Masculino</option>
                        </select>
                    </div>

                    <div class="grupo">
                        <label>EPS</label>

                        <select name="id_eps" required>
                            <option value="">Seleccione</option>
                            <?php foreach (($epsList ?? []) as $eps): ?>
                                <option value="<?= (int)$eps['id_eps'] ?>">
                                    <?= htmlspecialchars($eps['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label>Instructor</label>

                        <select name="id_instructor" required>
                            <option value="">Seleccione</option>
                            <?php foreach (($instructores ?? []) as $instructor): ?>
                                <option value="<?= (int)$instructor['id_instructor'] ?>">
                                    <?= htmlspecialchars($instructor['nombres'] . ' ' . $instructor['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    
                    <div class="grupo">
                        <label>Categoria</label>

                        <div class="campo">
                            <select name="id_categorias" required>
                                <option value="">Seleccione</option>
                                <?php foreach (($categorias ?? []) as $categoria): ?>
                                    <option value="<?= (int)$categoria['id_categorias'] ?>" data-instructor="<?= (($categoria['instructor_estado'] ?? '') === 'activo') ? (int) $categoria['id_instructor'] : '' ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section class="paso-formulario">
                <div class="titulo-seccion">
                    <div class="fluent--shirt-20-filled"></div>
                    <span>Información del uniforme (opcional)</span>
                </div>

                <div class="grid-3">
                    <div class="grupo">
                        <label for="numero_camisa">Número de camiseta</label>
                        <input
                            type="text"
                            id="numero_camisa"
                            name="numero_camisa"
                                    data-filtro="numeros"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="3"
                            placeholder="Ej: 10"
                            value=""
                        >
                    </div>

                  <div class="grupo">
                        <label for="talla_camisa">Talla de uniforme</label>
                        <input
                            type="text"
                            id="talla_camisa"
                            name="talla_camisa"
                            maxlength="10"
                            placeholder="Ej: XL"
                            value="<?= htmlspecialchars($jugador['talla_camisa'] ?? '') ?>"
                        >
                    </div>


                    <div class="grupo">
                        <label for="talla_media">Talla de medias</label>
                        <select id="talla_media" name="talla_media">
                            <option value="">Seleccione</option>
                            <option value="Niño" <?= (($jugador['talla_media'] ?? '') === 'Niño') ? 'selected' : '' ?>>Niño</option>
                            <option value="Adolescente" <?= (($jugador['talla_media'] ?? '') === 'Adolescente') ? 'selected' : '' ?>>Adolescente</option>
                            <option value="Adulto" <?= (($jugador['talla_media'] ?? '') === 'Adulto') ? 'selected' : '' ?>>Adulto</option>
                        </select>
                    </div>
                </div>
            </section> 

            <section class="paso-formulario">
                <div class="titulo-seccion">
                    <div class="mage--users-fill"></div>
                    <span>Informacion del Acudiente</span>
                </div>

                <div class="grid-3">

                    <div class="grupo">
                        <label for="">Nombres del Acudiente</label>
                        <input type="text"
                            name="responsable_nombres"
                                    data-filtro="letras" maxlength="60"
                            placeholder="Nombres" required>
                    </div>

                    <div class="grupo">
                        <label for="">Apellidos del acudiente</label>
                        <input type="text"
                            name="responsable_apellidos"
                                    data-filtro="letras" maxlength="60"
                            placeholder="Apellidos" required>
                    </div>

                    <div class="grupo">
                        <label>Tipo de documento</label>

                        <select name="responsable_id_tipo_documento" required>
                            <option value="">Seleccione</option>
                            <?php foreach (($tiposDocumento ?? []) as $tipo): ?>
                                <option value="<?= (int)$tipo['id_tipo_documento'] ?>">
                                    <?= htmlspecialchars($tipo['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label for="">Identificacion</label>
                        <input type="text"
                            inputmode="numeric"
                            name="responsable_identificacion"
                                    data-filtro="numeros"
                            placeholder="Escribe tu Documento"
                            pattern="[0-9]*"
                            maxlength="10" required>
                    </div>

                    <div class="grupo">
                        <label for="">Numero</label>
                        <input type="text"
                            inputmode="numeric"
                            name="responsable_numero_celular"
                                    data-filtro="numeros"
                            placeholder="Telefono de contacto"
                            pattern="[0-9]*"
                            maxlength="10" required>
                    </div>
                </div>
            </section>

                       
            <section class="paso-formulario">
                <div class="titulo-seccion">
                    <div class="fluent--money-24-filled"></div>
                    <span>Pago de Alumno</span>
                </div>

                <div class="grid-2">

                    <div class="grupo">
                        <label for="matriculaJugador">Matrícula (COP)</label>
                        <input type="text"
                            id="matriculaJugador"
                            inputmode="numeric"
                            autocomplete="off"
                            name="Matricula"
                            maxlength="15"
                            placeholder="$80.000" required>
                    </div>

                    <div class="grupo">
                        <label for="mensualidadJugador">Mensualidad (COP)</label>
                        <input type="text"
                            id="mensualidadJugador"
                            inputmode="numeric"
                            autocomplete="off"
                            name="Mensualidad"
                            maxlength="15"
                            placeholder="$90.000" required>
                    </div>

                    <div class="grupo">
                        <label for="">Fecha de pago</label>
                        <input type="date"
                            name="fecha_pago"
                            required>
                    </div>

                    <div class="grupo">
                        <label>Metodo de pago</label>

                        <select name="id_metodo_pago" id="metodoPago" required>
                            <option value="">Seleccione</option>

                            <?php foreach (($metodoPago ?? []) as $metodo): ?>
                                <option value="<?= (int)$metodo['id_metodo_pago'] ?>">
                                    <?= htmlspecialchars($metodo['tipo_metodo_pago']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="grupo">
                        <label>Tipo de beca</label>

                           <select name="id_tipo_becas" required>
                            <option value="">Seleccione</option>
                            <?php foreach (($tiposBeca ?? []) as $beca): ?>
                                <option value="<?= (int) $beca['id_tipo_beca'] ?>">
                                    <?= htmlspecialchars($beca['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo" id="grupoComprobante" style="display: none; margin-bottom: 16px;">
                        <label for="comprobante">Número de comprobante</label>
                        <input type="text" id="comprobante" name="comprobante" maxlength="10" 
                                placeholder="Ingrese el número de comprobante" required>
                    </div>

                    <div class="grupo resumen-pago-inicial" style="grid-column: 1 / -1; padding: 14px 16px; border: 1px solid #d6a000; border-radius: 10px; background: rgba(214,160,0,.07); margin-top: 12px;">
                        <p style="margin: 0 0 8px; font-weight: 600;">Resumen de inscripción</p>
                        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                            <span>Matrícula: <strong id="resumenMatricula">$0</strong></span>
                            <span aria-hidden="true">+</span>
                            <span>Mensualidad: <strong id="resumenMensualidad">$0</strong></span>
                            <span aria-hidden="true">=</span>
                            <span>Total a pagar: <strong id="resumenTotalInicial" style="font-size:1.15rem; color:#d6a000;">$0</strong></span>
                        </div>
                        <small style="display:block; margin-top:6px; opacity:.75;">Total = matrícula + mensualidad. Los importes se guardan por separado.</small>
                    </div>
                </div>
            </section>


            <footer class="acciones" style="margin-top: 12px;">
                <button type="button"
                        class="btn btn-cancelar"
                        id="cerrarRegistro">   
                    cancelar
                </button>

                <button type="button"
                        class="btn btn-anterior"
                        id="btnAnterior">

                    Anterior
                </button>

                <button type="button"
                        class="btn btn-siguiente"
                        id="btnSiguiente">

                    Siguiente
                </button>

                <button type="submit"
                        class="btn btn-guardar"
                        id="btnGuardar">
                    Guardar
                </button>
            </footer>

        </form>

    </div>

    <div class="modal-recorte" id="modalRecorte">
        <div class="modal-recorte-contenido">
            <div class="modal-recorte-header">
                <h3>Ajustar foto</h3>
                <button class="modal-recorte-cerrar" id="cerrarRecorte">&times;</button>
            </div>
            <div class="modal-recorte-body">
                <div class="recorte-contenedor">
                    <img id="imagenRecorte" src="#" alt="Previsualización" />
                </div>
            </div>
            <div class="modal-recorte-footer">
                <button class="btn btn-cancelar" id="cancelarRecorte">Cancelar</button>
                <button class="btn btn-guardar" id="aceptarRecorte">Aceptar</button>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Aviso que dejó el servidor al rechazar el formulario (campos faltantes, error de foto, etc.)
        window.AVISO_FORMULARIO = <?= json_encode($avisoCrear ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script src="/streepsoft/public/js/nuevo/avisosFormulario.js?v=<?= @filemtime(dirname(__DIR__, 4) . '/public/js/nuevo/avisosFormulario.js') ?: 1 ?>"></script>
    <script src="/streepsoft/public/js/nuevo/filtrosInputs.js?v=<?= @filemtime(dirname(__DIR__, 4) . '/public/js/nuevo/filtrosInputs.js') ?: 1 ?>"></script>
    <script src="/streepsoft/public/js/nuevo/formularioJugador.js?v=<?= @filemtime(dirname(__DIR__, 4) . '/public/js/nuevo/formularioJugador.js') ?: 1 ?>"></script>
    <script src="/streepsoft/public/js/jugadores/asignacionInstructor.js?v=<?= @filemtime(dirname(__DIR__, 4) . '/public/js/jugadores/asignacionInstructor.js') ?: 1 ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

</body>
</html>
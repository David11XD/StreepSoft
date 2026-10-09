<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumno</title>
    <link rel="stylesheet" href="/streepsoft/public/css/nuevo/formularioJugador.css">
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

        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e74c3c; font-weight:bold;">
                <?php
                    $errores = [
                        'csrf' => 'Token de seguridad inválido, intenta de nuevo.',
                        'campos_vacios' => 'Faltan campos obligatorios por completar.',
                        'fecha_invalida' => 'La fecha de nacimiento no es válida.',
                        'creacion_fallida' => 'No se pudo guardar el alumno, intenta de nuevo.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error.');
                ?>
            </p>
        <?php endif; ?>

        <form action="/streepsoft/jugadores/guardar" method="POST" enctype="multipart/form-data" id="formjugador" target="_top">
            
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
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">segundo nombre</label>
                                <input type="text"
                                    name="nombre2"
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">primer apellido</label>
                                <input type="text"
                                    name="apellido1"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label for="">primer Nombre</label>
                                <input type="text"
                                    name="nombre1"
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
                                    pattern="[0-9]*"
                                    maxlength="10"
                                    placeholder="Escribe tu Documento" required>
                            </div>

                            <div class="grupo">
                                <label for="">Iniciales</label>
                                <input type="text"
                                    name="iniciales"
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
                                    <option value="<?= (int)$categoria['id_categorias'] ?>">
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
                    <span>Informacion del Uniforme</span>
                </div>

                <div class="grid-3">

                    <div class="grupo">
                        <label>Talla de camiseta</label>
                        <input type="text" name="numero_camisa" inputmode="numeric" placeholder="Ej: 10" pattern="[0-9]*" maxlength="3">
                    </div>

                    <div class="grupo">
                        <label>Talla de camiseta</label>
                        <input type="text" name="talla_camisa" placeholder="Ej: XL" maxlength="3">
                    </div>


                    <div class="grupo">
                        <label>Talla de pantaloneta</label>
                        <input type="text" name="talla_pantalon" placeholder="Ej: L" maxlength="3">
                    </div>


                    <div class="grupo">
                        <label>Talla de media</label>
                        <input type="number" name="talla_media" placeholder="Ej: 35" maxlength="2">
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
                            placeholder="Nombres">
                    </div>

                    <div class="grupo">
                        <label for="">Apellidos del acudiente</label>
                        <input type="text"
                            name="responsable_apellidos"
                            placeholder="Apellidos">
                    </div>

                    <div class="grupo">
                        <label>Tipo de documento</label>

                        <select name="responsable_id_tipo_documento">
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
                            placeholder="Escribe tu Documento"
                            pattern="[0-9]*"
                            maxlength="10">
                    </div>

                    <div class="grupo">
                        <label for="">Numero</label>
                        <input type="text"
                            inputmode="numeric"
                            name="responsable_numero_celular"
                            placeholder="Telefono de contacto"
                            pattern="[0-9]*"
                            maxlength="10">
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
                        <label for="">Matricula</label>
                        <input type="text"
                            inputmode="numeric"
                            name="Matricula"
                            maxlength="10"
                            placeholder="$90.000" required>
                    </div>

                    <div class="grupo">
                        <label for="">Mensualidad</label>
                        <input type="text"
                            inputmode="numeric"
                            name="Mensualidad"
                            maxlength="10"
                            placeholder="$80.000" required>
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

                    <div class="grupo" id="grupoComprobante" style="display: none;">
                        <label for="comprobante">Número de comprobante</label>
                        <input type="text" id="comprobante" name="comprobante" maxlength="10" 
                                placeholder="Ingrese el número de comprobante" required>
                    </div>
                </div>
            </section>


            <footer class="acciones">
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


    <script src="/streepsoft/public/js/nuevo/formularioJugador.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

</body>
</html>
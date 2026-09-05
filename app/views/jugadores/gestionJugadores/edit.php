<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/Editar/EditarJugador.css">
    <title>Jugador | Editar</title>
    <?php if (isset($_GET['error'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: <?= json_encode([
                    'csrf' => 'Token de seguridad inválido, intenta de nuevo.',
                    'campos_vacios' => 'Faltan campos obligatorios por completar.',
                    'fecha_invalida' => 'La fecha ingresada no es válida.',
                    'actualizacion_fallida' => 'No se pudo actualizar el jugador.',
                ][$_GET['error']] ?? 'Ocurrió un error.') ?>,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#f5c400',
                background: '#232323',
                color: '#ffffff'
            }).then(() => {
                // Limpia el ?error=... de la URL para que no vuelva a
                // salir el mensaje si recargas la página
                window.history.replaceState({}, '', window.location.pathname);
            });
        </script>
    <?php endif; ?>
    <div class="contenedor">
        <div class="contenedor-pagina-1">

            <div class="encabezado">
                <div class="titulo">
                    <h1 id="tituloFormulario">Editar Jugador</h1>
                    <i class="mingcute--user-add-fill"></i>
                </div>
                
                <p id="subtituloFormulario">Edite los campos que necesarios para el registro</p>      
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
                </div>
            </div>
        </div>
        <?php 
            $partesNombres = explode(' ', trim($jugador['nombres'] ?? ''), 2);
            $partesApellidos = explode(' ', trim($jugador['apellidos'] ?? ''), 2);
        ?>
        <form action="/streepsoft/jugadores/actualizar" id="formjugador"  method="POST" enctype="multipart/form-data" target="_top">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="id_jugadores" value="<?=  (int) $jugador['id_jugadores'] ?>">
            <input type="hidden" name="id_responsable" value="<?= (int) ($jugador['id_responsable'] ?? 0) ?>">

            <section class="paso-formulario activo">
                <div class="contenido-datos">

                    <div class="contenedor-foto">
                        <div class="zona-foto-wrapper">
                            <label class="zona-foto" id="zonaFoto">

                                <i class="fluent--camera-add-48-filled"></i>
                                <span class="texto-foto">
                                    Subir foto<br>
                                    del alumno
                                </span>
                                <img class="foto-miniatura" id="fotoMiniatura" src="<?= $jugador['foto'] ? '/streepsoft/public/Image/jugadores/' . htmlspecialchars($jugador['foto']) : '#' ?>" alt="Foto del alumno" />
                            </label>
                            <div class="foto-controles" id="fotoControles">
                                <button type="button" class="btn-modificar-foto" id="btnModificarFoto">Modificar</button>
                                <button type="button" class="btn-eliminar-foto" id="btnEliminarFoto" aria-label="Quitar foto">&times;</button>
                            </div>
                        </div>
                        
                        <input type="file"
                               id="inputFoto"
                               accept="image/png, image/jpeg">

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
                                    value="<?= htmlspecialchars($partesApellidos[1] ?? '') ?>"
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">segundo nombre</label>
                                <input type="text"
                                    name="nombre2"
                                    value="<?= htmlspecialchars($partesNombres[1] ?? '') ?>"
                                    placeholder="Opcional">
                            </div>

                            <div class="grupo">
                                <label for="">primer apellido</label>
                                <input type="text"
                                    name="apellido1"
                                    value="<?= htmlspecialchars($partesApellidos[0] ?? '') ?>"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label for="">primer Nombre</label>
                                <input type="text"
                                    name="nombre1"
                                    value="<?= htmlspecialchars($partesNombres[0] ?? '') ?>"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label>Tipo de documento</label>

                                <select name="id_tipo_documento" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($tipoDocumento as $td): ?>
                                        <option value="<?= (int) $td['id_tipo_documento'] ?>" <?= ((int) ($jugador['id_tipo_documento'] ?? 0) === (int) $td['id_tipo_documento']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($td['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grupo">
                                <label for="">Identificacion</label>
                                <input type="text"
                                    name="documentos"
                                    value="<?= htmlspecialchars_decode($jugador['documentos'] ?? '') ?>"
                                    placeholder="Escribe tu Documento" required>
                            </div>

                            <div class="grupo">
                                <label for="">Iniciales</label>
                                <input type="text"
                                    name="iniciales"
                                    value="<?= htmlspecialchars($jugador['iniciales'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($jugador['fecha_nacimiento'] ?? '') ?>"
                            required>
                    </div>

                    <div class="grupo">
                        <label>Edad</label>
                        <input type="text"
                            name="edad">
                    </div>

                    <div class="grupo">
                        <label>Sexo</label>

                        <select name="tipo_documento">
                            <option value="">Seleccione</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Masculino">Masculino</option>
                        </select>
                    </div>

                    <div class="grupo">
                        <label>EPS</label>

                        <select name="id_eps" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($epsList as $eps): ?>
                                <option value="<?= (int) $eps['id_eps'] ?>" <?= ((int) $jugador['id_eps'] === (int) $eps['id_eps']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($eps['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label>Instructor</label>

                        <select name="id_instructor" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($instructores as $instructor): ?>
                                <option value="<?= (int) $instructor['id_instructor'] ?>" <?= ((int) $jugador['id_instructor'] === (int) $instructor['id_instructor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($instructor['nombres'] . ' ' . $instructor['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    
                    <div class="grupo">
                        <label>Categoria</label>

                        <select name="id_categorias" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= (int) $categoria['id_categorias'] ?>" <?= ((int) $jugador['id_categorias'] === (int) $categoria['id_categorias']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <label for="">Nombres del acudiente</label>
                        <input type="text"
                            name="responsable_nombres"
                            value="<?= htmlspecialchars($jugador['responsable_nombres'] ?? '') ?>"
                            placeholder="Nombres">
                    </div>

                    <div class="grupo">
                        <label for="">Apellidos del acudiente</label>
                        <input type="text"
                            name="responsable_apellidos"
                            value="<?= htmlspecialchars($jugador['responsable_apellidos'] ?? '') ?>"
                            placeholder="Apellidos">
                    </div>

                    <div class="grupo">
                        <label>Tipo de documento</label>

                        <select name="responsable_id_tipo_documento">
                            <option value="">Seleccione</option>
                            <?php foreach ($tipoDocumento as $td): ?>
                                <option value="<?= (int) $td['id_tipo_documento'] ?>" <?= ((int) ($jugador['responsable_id_tipo_documento'] ?? 0) === (int) $td['id_tipo_documento']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($td['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label for="">Identificacion</label>
                        <input type="text"
                            name="responsable_identificacion"
                            value="<?= htmlspecialchars($jugador['responsable_identificacion'] ?? '') ?>"
                            placeholder="Escribe tu Documento"
                            pattern="[0-9]*"
                            maxlength="10">
                    </div>

                    <div class="grupo">
                        <label for="">Numero</label>
                        <input type="text"
                            name="responsable_numero_celular"
                            value="<?= htmlspecialchars($jugador['responsable_numero_celular'] ?? '') ?>"
                            placeholder="Telefono de contacto"
                            pattern="[0-9]*"
                            maxlength="10">
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
                    Editar Jugador
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


    <script src="/streepsoft/public/js/Editar/Editar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        // Avisar al documento padre cuando se guarden cambios.
        document.getElementById('formjugador').addEventListener('submit', (e) => {
            if (modoFormulario !== 'editar') return;
            e.preventDefault();

            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    tipo: 'jugadorEditado',
                    id: params.get('id'),
                    foto: fotoBase64.value,
                    fotoEliminada: fotoEliminada
                }, '*');
            }
        });
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/nuevo/nuevoInstructor.css">
    <title>Alumno</title>
</head>
<body>
    <div class="contenedor">
        <div class="contenedor-pagina-1">

            <div class="encabezado">
                <div class="titulo">
                    <h1 id="tituloFormulario">Nuevo Instructor</h1>
                    <i class="mingcute--user-add-fill"></i>
                </div>
                
                <p id="subtituloFormulario">Complete los campos para agregar un nuevo instructor</p>      
            </div>

            <div class="contenedor-pasos">

                <div class="pasos">
                    <div class="paso activo">
                        <div class="circulo"></div>
                        <span>Datos Instructor</span>
                    </div>
                </div>
            </div>

        </div>

        <form action="/streepsoft/instructores/guardar" method="POST" enctype="multipart/form-data" id="formjugador" target="_top">
            <?php if (isset($_SESSION['csrf_token'])): ?>
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <?php endif; ?>

            <section class="paso-formulario activo">
                <div class="contenido-datos">

                    <div class="contenedor-foto">
                        <div class="zona-foto-wrapper">
                            <label class="zona-foto" id="zonaFoto">

                                <i class="fluent--camera-add-48-filled"></i>
                                <span class="texto-foto">
                                    Subir foto<br>
                                    del Instructor
                                </span>
                                <img class="foto-miniatura" id="fotoMiniatura" src="#" alt="Foto del alumno" />
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
                            <span>Datos Instructor</span>
                        </div>

                        <div class="grid-2">
                            <div class="grupo">
                                <label for="">Apellidos</label>
                                <input type="text"
                                    name="apellidos"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label for="">Nombres</label>
                                <input type="text"
                                    name="nombres"
                                    placeholder="Obligatorio" required>
                            </div>

                            <div class="grupo">
                                <label>Edad</label>

                                <input type="text"
                                    inputmode="numeric"
                                    name="edad"
                                    maxlength="2"
                                    required>
                            </div>

                            <div class="grupo">
                                <label for="">Numero celular</label>
                                <input type="text"
                                    name="numero_celular"
                                    placeholder="Telefono de contacto" required>
                            </div>

                            <div class="grupo">
                                <label>Categoria</label>

                                <select name="id_categorias" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= (int) $cat['id_categorias'] ?>">
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grupo">
                                <label>Estado</label>

                                <select name="estado" id="estado"  required>
                                    <option value="">Seleccione</option>
                                    <option value="Activo" <?= ($instructor['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="Inactivo" <?= ($instructor['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>

                            <div class="grupo">
                                <label>Descripción</label>
                                <textarea rows="2" cols="15" name="descripcion" placeholder="Escribe una descripción"></textarea>
                            </div>

                        </div>
                    </div>
                </div>
            </section>


            <footer class="acciones">
                <button type="button"
                        class="btn btn-cancelar"
                        id="cerrarRegistro">   
                    cancelar
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


    <script src="/streepsoft/public/js/nuevo/instructor.js"></script>
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
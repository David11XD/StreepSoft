<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/Instructor/instructor.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <title>Instructor</title>
</head>
<body>

    <div id="nav-card"></div>

    <div class="main-content">
        <div class="card-body">
            <div class="text-card">
                <h1>Bienvenido a Instructores </h1>
            </div>

            <div class="card-ets">
                <div class="card-instructores">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="fa6-solid--user-tie"></div>
                        </div>
                    </div>
                    <div class="text-instructor">
                        <h2>Total de Instructores</h2>
                        <h3><?= $totalInstructores ?></h3>
                        <p>Registrados</p>
                    </div>
                </div>

                <div class="card-instructores">
                    <div class="icon-circle">
                        <div class="circle-1">
                            <div class="bi--check-lg"></div>
                        </div>
                    </div>
                    <div class="text-instructor">
                        <h2>Activos</h2>
                        <h3><?= $totalActivos ?></h3>
                        <p>Actualmente</p>
                    </div>
                </div>

                <div class="card-instructores">
                    <div class="icon-circle">
                        <div class="circle-2">
                            <div class="dashicons--no-alt"></div>
                        </div>
                    </div>
                    <div class="text-instructor">
                        <h2>Inactivos</h2>
                        <h3><?= $totalInactivos ?></h3>
                        <p>No disponible</p>
                    </div>
                </div>
                
                <div class="card-instructores">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="ph--users-four-fill "></div>
                        </div>
                    </div>
                    <div class="text-instructor">
                        <h2>Jugadores</h2>
                        <h3><?= $totalJugadoresAsignados ?></h3>
                        <p>Asignados</p>
                    </div>
                </div>
            </div>

            <div class="card-otp">
                <div class="card-options">
                    <div class="card-one">
                        <div class="card-buscar">
                            <div class="buscar">
                                <button>
                                    <div class="basil--search-solid"></div>
                                </button>
                                
                                <input type="text" 
                                id="buscarJugador"
                                placeholder="Buscar alumno"
                                autocomplete="off">
                            </div>    
                        </div>

                        <div class="card-select">
                            <div class="select-tipo">
                                <span class="material-symbols--brightness-1"></span>
                                <select class="custom-select" id="filtroEstado">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Estado</option>
                                    <option value="">Inactivo</option>
                                    <option value="">Activo</option>
                                </select>
                            </div>

                        </div>

                        <div class="card-filter">
                            <button class="btn-filtro" id="btnFiltro">
                                <i class="tabler--filter-filled"></i>
                            </button>

                            <div class="menu-filtro" id="menuFiltro">

                                <!-- ORDEN -->
                                <div class="filtro-seccion">

                                    <span class="filtro-titulo">
                                        Ordenar
                                    </span>

                                    <button class="opcion-filtro" data-orden="az">
                                        A → Z
                                    </button>

                                    <button class="opcion-filtro" data-orden="za">
                                        Z → A
                                    </button>

                                </div>


                                <!-- COLUMNAS -->
                                <div class="filtro-seccion">

                                    <span class="filtro-titulo">
                                        Columnas
                                    </span>

                                    <label>
                                        <input type="checkbox" data-columna="0" checked>
                                        Foto
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="1" checked>
                                        Apellidos
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="2" checked>
                                        Nombres
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="3" checked>
                                        Edad
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="4" checked>
                                        Numero Celular
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="5" checked>
                                        Categorias
                                    </label>
                                    
                                    <label>
                                        <input type="checkbox" data-columna="6" checked>
                                        Descripcion
                                    </label>

                                    <label>
                                        <input type="checkbox" data-columna="7" checked>
                                        Estado
                                    </label>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="card-two">
                        <div class="card-registrar">
                            <a href="#" class="registrar" id="btnNuevoJugador">
                                <span class="ic--round-plus"></span>
                                <h2>Nuevo Instructor</h2>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-tb">
                <div class="card-tables">
                    <div class="table-responsive">
                        <table id="tablaInstrutores">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Apellidos</th>
                                    <th>Nombres</th>
                                    <th>Edad</th>
                                    <th>Numero celular</th>
                                    <th>Categorias</th>
                                    <th>Sede</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                                <tbody>
                                    <?php if (empty($instructores)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding: 24px;">
                                                No hay deudas registradas todavia.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php  foreach ($instructores as $instructor): ?>
                                            <?php
                                                $estado = strtolower($instructor['estado'] ?? '');
                                                $claseContenedor = $estado === 'activo' ? 'estado' : 'estado-i';
                                                $claseCirculo    = $estado === 'activo' ? 'ci' : 'ci-i';
                                            ?>
                                    <tr>
                                        <td>
                                            <div class="foto">
                                                <?php if(!empty($instructor['foto'])): ?>
                                                    <img src="/streepsoft/public/Image/instructores/<?= htmlspecialchars($instructor['foto']) ?>" alt="Foto">
                                                <?php else: ?>
                                                    <?php
                                                        // A partir del primer nombre y primer apellido.
                                                        $iniciales = trim((string)($instructor['iniciales'] ?? ''));
                                                        if($iniciales === ''){
                                                            $iniciales = mb_strtoupper(
                                                                mb_substr((string)($instructor['nombres'] ?? ''), 0, 1) .
                                                                mb_substr((string)($instructor['apellidos'] ?? ''), 0, 1)
                                                            );
                                                        }
                                                        echo htmlspecialchars($iniciales);
                                                    ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">
                                                <h3><?= htmlspecialchars($instructor['apellidos']) ?></h3>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">
                                                <h2><?= htmlspecialchars($instructor['nombres']) ?></h2>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">
                                                <h3><?= htmlspecialchars($instructor['edad']) ?></h3>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">   
                                                <h2><?= htmlspecialchars($instructor['numero_celular']) ?></h2>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">
                                                <h2><?= htmlspecialchars($instructor['categoria'] ?? '') ?></h2>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-text">
                                                <h3><?= htmlspecialchars($instructor['descripcion'] ?? '-') ?></h3>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="<?= $claseContenedor ?>">
                                                <div class="<?= $claseCirculo ?>"></div>
                                                <p><?= htmlspecialchars($instructor['estado'] ?? '') ?></p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="table-accion">
                                                <button class="btn-menu-accion" type="button">
                                                    <span class="uil--ellipsis-v"></span>
                                                </button>
                                                
                                                <div class="menu-acciones">
                                                    <button class="btn-editar" type="button" data-id-instructor="<?= (int) $instructor['id_instructor'] ?>">Editar</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>
                        </table>
                    </div>


                        <div class="card-paginacion">

                        <div class="info-registros" id="infoRegistros">
                            <P>Mostrando 1 - 5 de 12 Jugadores</P>
                        </div>

                        <div class="paginacion" id="paginacion">
                            <div class="Pagina">
                                <button class="pagina-btn anterior" id="btnAnterior">
                                    Anterior
                                </button>

                                <div id="numerosPaginas"></div>

                                <button class="pagina-btn siguiente" id="btnSiguiente">
                                    siguiente
                                </button>
                            </div>
                        </div>

                        <div class="cantidad-registros">
                            <div class="registros">
                                <label for="cantidadRegistros">
                                    Mostrar
                                </label>

                                <select id="cantidadRegistros">
                                    <option value="5" selected>5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                </select>

                                <span>registros</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal-registro" id="modalRegistro">
        <div class="modal-registro-contenido">
        <button class="cerrar-registro" id="cerrarRegistro">&times;</button>
        <iframe src="#" class="iframe-registro" id="iframeRegistro"></iframe>
    </div>

   <script src="/streepsoft/public/js/instructor/tablainstructores.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
</div>
</body>
</html>
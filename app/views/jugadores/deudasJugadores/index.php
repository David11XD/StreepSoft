<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Alumnos</title>
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/tablePagos.css" />
        <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
</head>
<body>
    <div id="nav-card"></div>

    <div class="main-content">
        <div class="card-body">
            <div class="text-card">
                <h1>Bienvenido a Pago de jugadores | Alumnos</h1>
            </div>

            <div class="card-ets">
                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="grommet-icons--money"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2>$<?= number_format((float) $resumen['total_general'], 0, ',', '.') ?></h2>
                        <h3>Total de Pagos</h3>
                        <p><?= (int) $resumen['total_alumnos'] ?> alumnos</p>
                    </div>
                </div>

                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle-1">
                            <div class="mdi--cash-check"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2>$<?= number_format((float) $resumen['total_pendiente'], 0, ',', '.') ?> cop</h2>
                        <h3>Pendientes</h3>
                        <p><?= (int) $resumen['porcentaje_pendiente'] ?> % del total</p>
                    </div>
                </div>

                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle-2">
                            <div class="mdi--cash-clock"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2>$<?= number_format((float) $resumen['total_mora'], 0, ',', '.') ?> cop</h2>
                        <h3>Mora</h3>
                        <p><?= (int) $resumen['porcentaje_mora'] ?> % del total</p>
                    </div>
                </div>
                
                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="mdi--cash-register"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2>$<?= number_format((float) $resumen['total_recaudo'], 0, ',', '.') ?> cop</h2>
                        <h3>Recaudo</h3>
                        <p><?= (int) $resumen['porcentaje_recaudo'] ?> % del total</p>
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
                                <label class="la--users"></label>
                                <select class="custom-select">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Todos</option>
                                    <option value="">Beca</option>
                                    <option value="">Media-beca</option>
                                    <option value="">Normal</option>
                                </select>
                            </div>

                            <div class="select-tipo">
                                <span class="material-symbols--brightness-1"></span>
                                <select class="custom-select">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Estado</option>
                                    <option value="">Inactivo</option>
                                    <option value="">Activo</option>
                                </select>
                            </div>

                            <div class="select-tipo">
                                <span class="tdesign--money-filled"></span>
                                <select class="custom-select">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Pago</option>
                                    <option value="">pagado</option>
                                    <option value="">Mora</option>
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
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card-tb">
                <div class="card-tables">
                    <div class="table-responsive">
                        <table id="tablaJugadores">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombres</th>
                                    <th>Monto del ciclo</th>
                                    <th>Fecha limite</th>
                                    <th>Estado de pago</th>
                                    <th>Fecha pago</th>
                                    <th>Metodo </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($deudas)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 24px;">
                                        No hay deudas registradas todavia.
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php foreach ($deudas as $deuda): ?>
                                    <?php  
                                    $estado = $deuda['estado_visual'] ?? 'pendiente';
                                        
                                        $config = [
                                            'vigente'   => ['foto' => 'ci',   'estado' =>   'estados',    'vig' => 'vigencia', 'label' => 'vigente', 'pagado' => false],
                                            'pago'      => ['foto' => 'ci',   'estado' =>   'estados-p',  'vig' => 'vigencia', 'label' => 'Pago',    'pagado' => true],
                                            'pendiente' => ['foto' => 'ci-p', 'estado' =>   'estados-pe', 'vig' => 'vigencia-pe', 'label' => 'pendiente', 'pagado' => false],
                                            'mora'      => ['foto' => 'ci-m', 'estado' =>   'estados-mo', 'vig' => 'vigencia-m',  'label' => 'mora',      'pagado' => false],

                                        ][$estado] ?? [
                                            'foto' => 'ci-p',
                                            'estado' => 'estados-pe',
                                            'vig' => 'vigencia-pe',
                                            'label' => 'desconocido',
                                            'pagado' => false
                                        ];

                                        $iniciales = strtoupper(mb_substr($deuda['nombres'] ?? '', 0, 1) . mb_substr($deuda['apellidos'] ?? '', 0, 1));
                                            $nombreCompleto = trim(($deuda['nombres'] ?? '') . ' ' . ($deuda['apellidos'] ?? ''));
                                            $subtitulo = trim(($deuda['categoria'] ?? '') . ' . ' . ($deuda['tipo_beca'] ?? ''), ' .');

                                        $nombreCompleto = trim($deuda['nombres'] . ' ' . $deuda['apellidos']);
                                        $subtitulo = trim(($deuda['categoria'] ?? '') . ' . ' . ($deuda['tipo_beca'] ?? ''), ' .');

                                        $fechaLimiteObj = date_create($deuda['fecha_limite_pago']);
                                        if (!$fechaLimiteObj) {
                                            $fechaLimiteFmt = 'Fecha no definida';
                                        } else {
                                            $fechaLimiteFmt = $fechaLimiteObj->format('d M \d\e Y');
                                        }
                                        $fechaPagoFmt = ' - ';
                                            if ($deuda['fecha_pago']) {
                                                $fechaPago = date_create($deuda['fecha_pago']);
                                                if ($fechaPago) {
                                                    $fechaPagoFmt = $fechaPago->format('d M \d\e Y');
                                                }
                                            }
                                        $advertenciaClase = $estado === 'mora' ? 'advertencia-m' : 'advertencia';
                                    ?>
                                <tr>
                                    <td>
                                        <div class="foto">
                                            <p><?=  htmlspecialchars($iniciales) ?></p>
                                            <div class="<?=  $config['foto'] ?>"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?=  htmlspecialchars($nombreCompleto) ?></h3>
                                            <p><?=   htmlspecialchars($subtitulo) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3>$<?=  number_format((float) $deuda['totalidad'], 0,',', '.') ?></h3>
                                            <?php if ((float) $deuda['matricula'] > 0): ?>
                                            <p>+ matricula ($<?= number_format((float) $deuda['matricula'], 0, ',', '.') ?>)</p>
                                            <?php else: ?>
                                                <p></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h2><?=  htmlspecialchars($fechaLimiteFmt) ?></h2>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="estado">
                                            <div class="<?= $config['estado'] ?>">
                                                <h3><?= htmlspecialchars($config['label']) ?></h3>
                                            </div>
                                            
                                            <div class="<?= $config['vig'] ?>">
                                                <p><?= htmlspecialchars($deuda['vigencia_texto']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?= htmlspecialchars($fechaPagoFmt) ?></h3>
                                            <?php if (!empty($deuda['advertencia_texto'])): ?>
                                                <p class="<?= $advertenciaClase ?>"><?= htmlspecialchars($deuda['advertencia_texto']) ?></p>
                                            <?php endif; ?>        
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h2> <?= htmlspecialchars($deuda['metodo_pago'] ?? '-') ?> </h2>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-accion">
                                            <?php if ($deuda['pago'] === 'pagado'): ?>
                                                <div class="Registro-pago-pagado">
                                                    <button class="btn-pago-pagado" type="button" disabled>
                                                        pagado 
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="Registro-pago">
                                                    <button class="btn-pago" type="button" data-id-deuda="<?= (int) $deuda['id_deudas'] ?>">
                                                        Registrar pago 
                                                    </button>
                                                </div>

                                                <div class="modal-registro" id="modalRegistro-<?= (int) $deuda['id_deudas'] ?>">
                                                    <div class="modal-registro-contenido" >
                                                        <button class="cerrar-registro" type="button">
                                                            &times;
                                                        </button>

                                                        <iframe 
                                                            src="/streepsoft/deudas/<?= (int) $deuda['id_deudas'] ?>/pago" 
                                                            class="iframe-registro">
                                                        </iframe>
                                                    </div>
                                                </div>
                                            <?php endif;  ?>

                                            <button class="btn-menu-accion" type="button">
                                                <span class="uil--ellipsis-v"></span>
                                            </button>

                                            <div class="menu-acciones">
                                                <button class="btn-editar">Editar</button>
                                                <button class="btn-perfil">Historial</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach;  ?>
                            </tbody>
                        </table>
                    </div>


                    <div class="card-paginacion">

                        <div class="info-registros" id="infoRegistros">
                            <P>Mostrando 1 - <?=  count($deudas) ?> de <?= count($deudas) ?>  Jugadores</P>
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



    <script src="/streepsoft/public/js/Jugadores/tapagos.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
</body>
</html>
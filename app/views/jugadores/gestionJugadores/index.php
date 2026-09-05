<?php
/** @var array $jugadores */

$totalAlumnos = count($jugadores);
$totalActivos = 0;
$totalInactivos = 0;
$totalConDeuda = 0;
$totalVencePronto = 0;
$hoy = new DateTime();

foreach ($jugadores as $j){
    if (($j['estado'] ?? '') === 'Activo'){
        $totalActivos++;
    } else {
        $totalInactivos++;
    }

    if (!empty($j['pago'])){
        $totalConDeuda++;
    }

    if (!empty($j['fecha_limite_pago'])){
        $limite = DateTime::createFromFormat('Y-m-d', $j['fecha_limite_pago']);
        if ($limite) {
            $dias = (int)$hoy->diff($limite)->format('%r%a');
            if ($dias >= 0 && $dias <= 30){
                $totalVencePronto ++;
            }
        }
    }
}

$pct = fn($n) => $totalAlumnos > 0 ? round($n / $totalAlumnos * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/tablejugadores.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <title>Jugadores</title>
</head>
<body>

    <div id="nav-card"></div>

    <div class="main-content">
        <div class="card-body">
            <div class="text-card">
                <h1>Bienvenido a Jugadores | Alumnos</h1>
            </div>
            <?php if (isset($_GET['success'])): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: <?= json_encode([
                            'creado' => 'Jugador registrado exitosamente',
                            'eliminado' => 'Jugador eliminado correctamente',
                        ][$_GET['success']] ?? 'Operación exitosa') ?>,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#f5c400',
                        background: '#232323',
                        color: '#ffffff',
                        iconColor: '#2ecc71'
                    }).then(() => {
                        // Limpia el ?success=... de la URL para que no
                        // vuelva a salir el mensaje si recargas la página
                        window.history.replaceState({}, '', '/streepsoft/jugadores/gestion');
                    });
                </script>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: <?= json_encode([
                            'csrf' => 'Token de seguridad inválido, intenta de nuevo.',
                            'campos_vacios' => 'Faltan campos obligatorios por completar.',
                            'fecha_invalida' => 'La fecha ingresada no es válida.',
                            'creacion_fallida' => 'No se pudo registrar el jugador.',
                            'eliminacion_fallida' => 'No se pudo eliminar el jugador.',
                        ][$_GET['error']] ?? 'Ocurrió un error.') ?>,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#f5c400',
                        background: '#232323',
                        color: '#ffffff',
                        iconColor: '#e74c3c'
                    }).then(() => {
                        window.history.replaceState({}, '', '/streepsoft/jugadores/gestion');
                    });
                </script>
            <?php endif; ?>

            <div class="card-ets">
                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="mingcute--user-add-fill"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2><?= $totalAlumnos ?></h2>
                        <h3>Total de Alumnos</h3>
                        <p>esta temporada</p>
                    </div>
                </div>

                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle-1">
                            <div class="boxicons--user-filled"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2><?= $totalActivos ?></h2>
                        <h3>Activos</h3>
                        <p><?= $pct($totalActivos) ?>% del total</p>
                    </div>
                </div>

                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle-2">
                            <div class="boxicons--user-filled-1"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2><?= $totalInactivos ?></h2>
                        <h3>Inactivos</h3>
                        <p><?= $pct($totalInactivos) ?>% del total</p>
                    </div>
                </div>
                
                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="si--money-fill"></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2><?= $totalConDeuda ?></h2>
                        <h3>con deuda</h3>
                        <p><?= $pct($totalConDeuda) ?>% del total</p>
                    </div>
                </div>

                <div class="card-alumno">
                    <div class="icon-circle">
                        <div class="circle">
                            <div class="solar--calendar-broken "></div>
                        </div>
                    </div>
                    <div class="text-alumno">
                        <h2><?= $totalVencePronto ?></h2>
                        <h3>Vence pronto</h3>
                        <p>proximos 30 dias</p>
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
                                <select class="custom-select" id="filtroBeca">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Todos</option>
                                    <option value="Beca completa">Beca</option>
                                    <option value="Media beca">Media-beca</option>
                                </select>
                            </div>

                            <div class="select-tipo">
                                <span class="material-symbols--brightness-1"></span>
                                <select class="custom-select" id="filtroEstado">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Estado</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Activo">Activo</option>
                                </select>
                            </div>

                            <div class="select-tipo">
                                <span class="tdesign--money-filled"></span>
                                <select class="custom-select" id="filtroPago">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="todo">Pago</option>
                                    <option value="pagado">pagado</option>
                                    <option value="mora">Mora</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-filter">
                            <button class="btn-filtro" id="btnFiltro">
                                <i class="tabler--filter-filled"></i>
                            </button>

                            <div class="menu-filtro" id="menuFiltro">

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
                                        Categoria
                                    </label>
                                    <label>
                                        <input type="checkbox" data-columna="5" checked>
                                        Acudientes
                                    </label>
                                    
                                    <label>
                                        <input type="checkbox" data-columna="6" checked>
                                        Instructor
                                    </label>
                                    <label>
                                        <input type="checkbox" data-columna="7" checked>
                                        Estado
                                    </label>
                                    <label>
                                        <input type="checkbox" data-columna="8" checked>
                                        fecha limite
                                    </label>
                                </div>
                            
                            </div>
                        </div>
                    </div>

                    <div class="card-two">
                        <div class="card-registrar">
                            <a href="#"  id="btnNuevoJugador"  class="registrar">
                                <span class="ic--round-plus"></span>
                                <h2>Nuevo Jugador</h2>
                            </a>
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
                                    <th>Apellidos</th>
                                    <th>Nombres</th>
                                    <th>Edad</th>
                                    <th>Categorias</th>
                                    <th>Acudientes</th>
                                    <th>Instructor</th>
                                    <th>Estado</th>
                                    <th>Fecha limite</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jugadores)): ?>
                                    <tr>
                                        <td colspan="8" style="margin: auto; position: relative; left: 170px;">
                                            No hay Jugadores Registrados Gracias.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php  foreach ($jugadores as $jugador): ?>
                                <tr data-beca="<?= htmlspecialchars($jugador['tipo_beca'] ?? '') ?>" data-estado="<?= htmlspecialchars($jugador['estado'] ?? '') ?>" data-pago="<?= htmlspecialchars($jugador['pago'] ?? '') ?>">
                                    <td>
                                        <div class="foto">
                                            <?php if (!empty($jugador['foto'])): ?>
                                                <img src="/streepsoft/public/Image/jugadores/<?= htmlspecialchars($jugador['foto'] ?? '') ?>" alt="Foto">
                                            <?php else: ?>
                                                <?php
                                                    // Usa las iniciales guardadas; si no hay, las calcula 
                                                    // A partir del primer nombre y primer apellido.
                                                    $iniciales = trim((string)($jugador['iniciales'] ?? ''));
                                                    if($iniciales === ''){
                                                        $iniciales = mb_strtoupper(
                                                            mb_substr((string)($jugador['nombres'] ?? ''), 0, 1) .
                                                            mb_substr((string)($jugador['apellidos'] ?? ''), 0, 1)
                                                        );
                                                    }
                                                    echo htmlspecialchars($iniciales);
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?= htmlspecialchars($jugador['apellidos']) ?></h3>
                                            <p><?= htmlspecialchars(trim(($jugador['tipo_documento'] ?? '') . ' ' . ($jugador['documentos'] ?? ''))) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h2><?= htmlspecialchars($jugador['nombres'])  ?></h2>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?= htmlspecialchars($jugador['edad']) ?></h3>
                                            <p><?= htmlspecialchars($jugador['fecha_nacimiento']) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h2><?= htmlspecialchars($jugador['categoria']) ?></h2>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?= htmlspecialchars($jugador['responsable_nombre']) ?></h3>
                                            <p><?= htmlspecialchars($jugador['numero_acudiente']) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h2><?= htmlspecialchars($jugador['instructor']) ?></h2>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="estado">
                                            <div class="ci"></div>
                                            <p><?= htmlspecialchars($jugador['estado']) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-text">
                                            <h3><?= htmlspecialchars($jugador['fecha_limite_pago']) ?></h3>
                                            <p class="table-text-estado">Mora 5 dias</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-accion">
                                            <button class="btn-menu-accion" type="button">
                                                <span class="uil--ellipsis-v"></span>
                                            </button>

                                            <div class="menu-acciones">
                                                <button class="btn-editar" type="button" data-id-jugador="<?= (int) $jugador['id_jugadores'] ?>">Editar</button>
                                                <button class="btn-perfil">ver perfil</button>
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
            <iframe src="" class="iframe-registro" id="iframeRegistro"></iframe>
        </div>
    </div>

    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script src="/streepsoft/public/js/jugadores/table.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
</body>
</html>




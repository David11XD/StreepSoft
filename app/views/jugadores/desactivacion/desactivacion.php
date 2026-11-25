<?php
/** @var array $jugadores */
/** @var array $historialCambios */
$jugadores = is_array($jugadores ?? null) ? $jugadores : [];
$historialCambios = is_array($historialCambios ?? null) ? $historialCambios : [];
$categoriasDisponibles = array_values(array_unique(array_filter(array_map(
    static fn(array $jugador): string => trim((string) ($jugador['categoria'] ?? '')),
    $jugadores
))));
sort($categoriasDisponibles, SORT_NATURAL | SORT_FLAG_CASE);
$esc = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/desactivacion.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <title>Desactivación de Jugadores</title>
</head>
<body>
    <div id="nav-card"></div>

    <div class="main-content">
        <div class="card-body">
            <div class="text-card">
                <h1>Desactivación de Jugadores</h1>
            </div>

            <div class="panel-filtro">

                <div class="panel-filtro-header">
                    <h2>Encontrar Jugadores</h2>
                    <p class="panel-filtro-nota">Define la condición y luego selecciona a quiénes aplicar el cambio</p>
                </div>

                <div class="panel-filtros">
                    <div class="filtros-avanzados">
                        <div class="campo-filtro">
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

                        <div class="campo-filtro">
                            <label for="filtroCategoria">Categoría</label>
                            <select id="filtroCategoria">
                                <option value="todo">Todas</option>
                                <?php foreach ($categoriasDisponibles as $categoria): ?>
                                    <option value="<?= $esc($categoria) ?>"><?= $esc($categoria) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo-filtro">
                            <label for="filtroEstado">Estado actual</label>
                            <select id="filtroEstado">
                                <option value="todo">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="retirado">Retirado</option>
                            </select>
                        </div>


                    </div>

                    <div class="filtros-rapidos">
                        <button type="button" class="btn-filtro-rapido activo" data-meses="todos">Todos los jugadores</button>
                        <button type="button" class="btn-filtro-rapido" data-meses="1">1+ meses sin pago</button>
                        <button type="button" class="btn-filtro-rapido" data-meses="2">2+ meses sin pago</button>
                        <button type="button" class="btn-filtro-rapido" data-meses="3">3+ meses sin pago</button>
                    </div>
                </div>
            </div>

            <section class="panel-resultados">
                <div class="panel-resultados-header">
                    <h2>Jugadores registrados</h2>
                    <span class="resultados-contador" id="contadorResultados">
                        <?= count($jugadores) ?> jugador<?= count($jugadores) === 1 ? '' : 'es' ?> registrados
                    </span>
                </div>

                <div class="table-responsive">
                    <table id="tablaDesactivacion">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkTodos" aria-label="Seleccionar todos los jugadores visibles"></th>
                                <th></th>
                                <th>Jugador</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Meses sin pago</th>
                                <th>Deuda pendiente</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jugadores as $jugador): ?>
                                    <?php
                                        $id = (int) ($jugador['id'] ?? 0);
                                        $nombreCompleto = trim(($jugador['nombres'] ?? '') . ' ' . ($jugador['apellidos'] ?? ''));
                                        $estado = strtolower((string) ($jugador['estado'] ?? 'activo'));
                                        $estadoPermitido = in_array($estado, ['activo', 'inactivo', 'retirado'], true) ? $estado : 'activo';
                                        $meses = (int) ($jugador['meses_sin_pago'] ?? 0);
                                        $fecha = $jugador['fecha_desactivacion'] ?? null;
                                        $fechaObj = $fecha ? date_create($fecha) : false;
                                    ?>
                                    <tr data-id="<?= $id ?>" data-meses="<?= $meses ?>" data-estado="<?= $esc($estadoPermitido) ?>">
                                        <td>
                                            <input type="checkbox"
                                                   class="check-jugador"
                                                   data-id="<?= $id ?>"
                                                   aria-label="Seleccionar <?= $esc($nombreCompleto) ?>"
                                                   <?= $estadoPermitido === 'retirado' ? 'disabled title="Los jugadores retirados no se modifican desde esta sección."' : '' ?>>
                                        </td>
                                        <td><div class="avatar-iniciales"><?= $esc($jugador['iniciales'] ?? '') ?></div></td>
                                        <td>
                                            <div class="celda-jugador-texto">
                                                <h3><?= $esc($nombreCompleto) ?></h3>
                                                <p><?= $esc(($jugador['documento'] ?? '') !== '' ? $jugador['documento'] : 'Documento no registrado') ?></p>
                                            </div>
                                        </td>
                                        <td><?= $esc($jugador['categoria'] ?? 'Sin categoría') ?></td>
                                        <td><span class="badge-estado badge-<?= $esc($estadoPermitido) ?>"><?= $esc(ucfirst($estadoPermitido)) ?></span></td>
                                        <td><?= $meses ?> <?= $meses === 1 ? 'mes' : 'meses' ?></td>
                                        <td class="celda-deuda">$<?= number_format((float) ($jugador['deuda'] ?? 0), 0, ',', '.') ?></td>
                                        <td>
                                            <?= $fechaObj ? $fechaObj->format('d/m/Y') : '—' ?>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                            <tr id="estadoVacioJugadores" hidden>
                                <td colspan="8">No hay jugadores que coincidan con la búsqueda o los filtros.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="barra-seleccion oculto" id="barraSeleccion">
                    <span id="textoSeleccion">0 jugadores seleccionados</span>
                    <div class="barra-seleccion-acciones">
                        <button type="button" class="btn-secundario" id="btnCancelarSeleccion">Cancelar selección</button>
                        <button type="button" class="btn-primario" id="btnCambiarEstado">Cambiar estado</button>
                    </div>
                </div>

                <div class="resultados-footer" id="footerResultados">
                    <div class="card-paginacion" id="paginacionJugadores">
                        <div class="info-registros">
                            <p id="infoRegistrosJugadores">Mostrando 0 - 0 de 0 Jugadores</p>
                        </div>
                        <div class="paginacion">
                            <div class="Pagina">
                                <button type="button" class="pagina-btn anterior" id="btnAnteriorJugadores">Anterior</button>
                                <div id="numerosPaginasJugadores"></div>
                                <button type="button" class="pagina-btn siguiente" id="btnSiguienteJugadores">Siguiente</button>
                            </div>
                        </div>
                        <div class="cantidad-registros">
                            <div class="registros">
                                <label for="cantidadRegistrosJugadores">Mostrar</label>
                                <select id="cantidadRegistrosJugadores">
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
            </section>
        </div>

        <section class="panel-historial">
            <div class="panel-historial-text">
                <h2>Historial de cambios</h2>
            </div>

            <div class="table-responsive">
                <table id="tablaHistorial">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Realizado por</th>
                            <th>Jugadores afectados</th>
                            <th>Estado aplicado</th>
                            <th>Motivo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historialCambios as $cambio): ?>
                            <?php
                                $estadoHistorial = strtolower((string) ($cambio['estado_aplicado'] ?? 'inactivo'));
                                $fechaHistorial = trim((string) ($cambio['fecha'] ?? ''));
                            ?>
                            <tr data-row-historial="<?= (int) ($cambio['id'] ?? 0) ?>" data-fecha="<?= htmlspecialchars($fechaHistorial, ENT_QUOTES, 'UTF-8') ?>">
                                <td><?= htmlspecialchars($fechaHistorial, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($cambio['realizado_por'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) ($cambio['jugadores_afectados'] ?? 0) ?> jugadores</td>
                                <td><span class="badge-estado badge-<?= htmlspecialchars($estadoHistorial, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($estadoHistorial), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="celda-motivo"><?= htmlspecialchars($cambio['motivo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><button type="button" class="btn-ver-detalle" data-cambio-id="<?= (int) ($cambio['id'] ?? 0) ?>">Ver detalle</button></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="estadoVacioHistorial" hidden>
                            <td colspan="6">No hay cambios de estado que coincidan con la fecha buscada.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-paginacion" id="paginacionHistorial">
                <div class="info-registros">
                    <p id="infoRegistrosHistorial">Mostrando 0 - 0 de 0 Cambios</p>
                </div>
                <div class="paginacion">
                    <div class="Pagina">
                        <button type="button" class="pagina-btn anterior" id="btnAnteriorHistorial">Anterior</button>
                        <div id="numerosPaginasHistorial"></div>
                        <button type="button" class="pagina-btn siguiente" id="btnSiguienteHistorial">Siguiente</button>
                    </div>
                </div>
                <div class="cantidad-registros">
                    <div class="registros">
                        <label for="cantidadRegistrosHistorial">Mostrar</label>
                        <select id="cantidadRegistrosHistorial">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                        <span>registros</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal-registro" id="modalRegistro" aria-hidden="true">
        <div class="modal-registro-contenido" role="dialog" aria-modal="true" aria-labelledby="modalRegistroTitulo">
            <div class="modal-registro-header">
                <h2 id="modalRegistroTitulo">Gestión de estado de jugadores</h2>
                <button type="button" id="cerrarRegistro" class="cerrar-registro" aria-label="Cerrar ventana">&times;</button>
            </div>
            <iframe src="about:blank" class="iframe-registro" id="iframeRegistro" title="Cambiar estado de jugadores" loading="eager"></iframe>
        </div>
    </div>

    <script src="/streepsoft/public/js/jugadores/desactivacion.js"></script> 
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
</body>

</html>
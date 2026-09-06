<?php

/** @var array $jugadores
 * @var array $historialCambios
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/desactivacion.css">
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

                <div class="filtros-rapidos">
                    <button type="button" class="btn-filtro-rapido activo" data-meses="2">2+ meses sin pago</button>
                    <button type="button" class="btn-filtro-rapido" data-meses="3">3+ meses sin pago</button>
                    <button type="button" class="btn-filtro-rapido" data-meses="1">1+ meses sin pago</button>
                </div>

                <div class="filtros-avanzados">
                    <div class="campo-filtro">
                        <label for="sinPagoDesde">Sin pago desde</label>
                        <select id="sinPagoDesde">
                            <option value="1">Hace 1 mes</option>
                            <option value="2" selected>Hace 2 meses</option>
                            <option value="3">Hace 3 meses</option>
                        </select>
                    </div>

                    <div class="campo-filtro">
                        <label for="filtroCategoria">Categoría</label>
                        <select id="filtroCategoria">
                            <option value="todo">Todas</option>
                            <option value="Sub-12">Sub-12</option>
                            <option value="Sub-14">Sub-14</option>
                            <option value="Sub-20">Sub-20</option>
                        </select>
                    </div>

                    <div class="campo-filtro campo-nombre">
                        <label for="filtroNombre">Nombre</label>
                        <input type="text" id="filtroNombre" placeholder="Jugador">
                    </div>

                    <button type="button" class="btn-buscar" id="btnBuscar">
                        <i class="fi fi-rr-search"></i> Buscar
                    </button>
                </div>
            </div>

            <div class="panel-resultados">

                <div class="panel-resultados-header">
                    <h2>Resultados</h2>
                    <span class="resultados-contador"><?= count($jugadores) ?> jugadores con 2 o más meses sin pago</span>
                </div>

                <div class="table-responsive">
                    <table id="tablaDesactivacion">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkTodos"></th>
                                <th>Jugador</th>
                                <th>Categoría</th>
                                <th>Meses sin pago</th>
                                <th>Deuda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jugadores)): ?>
                                <tr>
                                    <td colspan="5">No hay jugadores que cumplan este filtro.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($jugadores as $jugador): ?>
                                <tr data-id="<?= (int)$jugador['id'] ?>">
                                    <td>
                                        <input type="checkbox" class="check-jugador" data-id="<?= (int)$jugador['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="celda-jugador">
                                            <div class="avatar-iniciales"><?= htmlspecialchars($jugador['iniciales']) ?></div>
                                            <div class="celda-jugador-texto">
                                                <h3><?= htmlspecialchars($jugador['nombres'] . ' ' . $jugador['apellidos']) ?></h3>
                                                <p><?= htmlspecialchars($jugador['documento']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($jugador['categoria']) ?></td>
                                    <td><?= (int)$jugador['meses_sin_pago'] ?> meses</td>
                                    <td class="celda-deuda">$<?= number_format($jugador['deuda'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="resultados-footer">
                    Mostrando <?= min(count($jugadores), 5) ?> de <?= count($jugadores) ?> resultados · <a href="#">ver los <?= count($jugadores) ?></a>
                </div>

                <div class="barra-seleccion oculto" id="barraSeleccion">
                    <span id="textoSeleccion">0 Jugadores seleccionados</span>
                    <div class="barra-seleccion-acciones">
                        <button type="button" class="btn-secundario" id="btnCancelarSeleccion">Cancelar selección</button>
                        <button type="button" class="btn-primario" id="btnCambiarEstado">Cambiar estado</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-historial">
            <h2>Historial de cambios masivos</h2>

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
                        <?php if (empty($historialCambios)): ?>
                            <tr>
                                <td colspan="6">Todavía no se han registrado cambios masivos.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($historialCambios as $cambio): ?>
                            <tr>
                                <td><?= htmlspecialchars($cambio['fecha']) ?></td>
                                <td><?= htmlspecialchars($cambio['realizado_por']) ?></td>
                                <td><?= (int)$cambio['jugadores_afectados'] ?> jugadores</td>
                                <td>
                                    <span class="badge-estado badge-<?= strtolower($cambio['estado_aplicado']) ?>">
                                        <?= htmlspecialchars($cambio['estado_aplicado']) ?>
                                    </span>
                                </td>
                                <td class="celda-motivo"><?= htmlspecialchars($cambio['motivo']) ?></td>
                                <td>
                                    <button type="button" class="btn-ver-detalle" data-cambio-id="<?= (int)$cambio['id'] ?>">
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal-overlay oculto" id="modalWizard">
        <div class="modal-box modal-wizard">

            <div class="modal-header">
                <h2>Cambiar estado a varios jugadores</h2>
                <button type="button" class="modal-cerrar" data-cerrar-modal="modalWizard">&times;</button>
            </div>

            <div class="wizard-pasos" id="wizardIndicador">
                <div class="wizard-paso-indicador activo" data-paso-indicador="1">
                    <span class="wizard-paso-numero">1</span>
                </div>
                <div class="wizard-linea"></div>
                <div class="wizard-paso-indicador" data-paso-indicador="2">
                    <span class="wizard-paso-numero">2</span>
                </div>
                <div class="wizard-linea"></div>
                <div class="wizard-paso-indicador" data-paso-indicador="3">
                    <span class="wizard-paso-numero">3</span>
                </div>
            </div>

            <div class="modal-body">

                <!-- PASO 1 -->
                <div class="wizard-paso-contenido" data-paso="1">
                    <h3 class="wizard-pregunta">¿A qué estado quieres cambiarlo?</h3>
                    <p class="wizard-subtexto">5 jugadores seleccionados recibirán el mismo cambio de estado.</p>

                    <label class="opcion-estado">
                        <input type="radio" name="nuevoEstado" value="Inactivo" checked>
                        <div class="opcion-estado-texto">
                            <strong>Inactivo</strong>
                            <p>El jugador no aparece en cobros activos, pero conserva su historial y puede reactivarse después.</p>
                        </div>
                    </label>

                    <label class="opcion-estado">
                        <input type="radio" name="nuevoEstado" value="Retirado">
                        <div class="opcion-estado-texto">
                            <strong>Retirado</strong>
                            <p>El jugador se considera fuera del club de forma definitiva. Su historial se conserva.</p>
                        </div>
                    </label>

                    <div class="campo-filtro campo-ancho">
                        <label for="motivoCambio">Motivo del cambio</label>
                        <select id="motivoCambio">
                            <option value="">Selecciona un motivo</option>
                            <option value="mora">2+ meses sin pago mensualidad</option>
                            <option value="fin_temporada">Fin de temporada, no renovaron matrícula</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="campo-filtro campo-ancho">
                        <label for="descripcionCambio">Descripción</label>
                        <textarea id="descripcionCambio" rows="3" placeholder="Detalle opcional..."></textarea>
                    </div>
                </div>

                <!-- PASO 2 -->
                <div class="wizard-paso-contenido oculto" data-paso="2">
                    <h3 class="wizard-pregunta">Revisa a quiénes se aplicará el cambio</h3>
                    <p class="wizard-subtexto">Puedes quitar a algún jugador de la lista antes de confirmar.</p>

                    <div class="lista-revision" id="listaRevision">
                        <div class="item-revision" data-id="1">
                            <div class="celda-jugador">
                                <div class="avatar-iniciales">SR</div>
                                <div class="celda-jugador-texto">
                                    <h3>Santiago Rúa · Sub-14</h3>
                                    <p>TI 1.038.221.554</p>
                                </div>
                            </div>
                            <button type="button" class="btn-quitar" data-id="1">Quitar</button>
                        </div>

                        <div class="item-revision" data-id="2">
                            <div class="celda-jugador">
                                <div class="avatar-iniciales">MG</div>
                                <div class="celda-jugador-texto">
                                    <h3>Mariana Gil · Sub-12</h3>
                                    <p>TI 1.041.887.220</p>
                                </div>
                            </div>
                            <button type="button" class="btn-quitar" data-id="2">Quitar</button>
                        </div>

                        <div class="item-revision" data-id="3">
                            <div class="celda-jugador">
                                <div class="avatar-iniciales">JR</div>
                                <div class="celda-jugador-texto">
                                    <h3>Juan Restrepo · Sub-20</h3>
                                    <p>CC 1.045.117.602</p>
                                </div>
                            </div>
                            <button type="button" class="btn-quitar" data-id="3">Quitar</button>
                        </div>

                        <div class="item-revision" data-id="4">
                            <div class="celda-jugador">
                                <div class="avatar-iniciales">VF</div>
                                <div class="celda-jugador-texto">
                                    <h3>Valentina Franco · Sub-12</h3>
                                    <p>TI 1.092.330.981</p>
                                </div>
                            </div>
                            <button type="button" class="btn-quitar" data-id="4">Quitar</button>
                        </div>
                    </div>
                </div>

                <!-- PASO 3 -->
                <div class="wizard-paso-contenido oculto" data-paso="3">
                    <h3 class="wizard-pregunta">Confirmar cambio masivo</h3>
                    <p class="wizard-subtexto">Última revisión antes de aplicar el cambio.</p>

                    <div class="resumen-confirmacion">
                        <div class="resumen-item">
                            <span class="resumen-etiqueta">Jugadores afectados</span>
                            <strong id="resumenCantidad">4</strong>
                        </div>
                        <div class="resumen-item">
                            <span class="resumen-etiqueta">Nuevo estado</span>
                            <strong id="resumenEstado" class="resumen-estado-inactivo">Inactivo</strong>
                        </div>
                    </div>

                    <label class="aviso-confirmacion">
                        <input type="checkbox" id="checkEntendido">
                        <span>
                            Entiendo que este cambio se aplicará a todos los jugadores listados
                            y quedará registrado con mi usuario, la fecha y el motivo indicado.
                            Puedo revertir el estado individualmente después si es necesario.
                        </span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secundario" id="btnWizardAtras">Cancelar</button>
                <button type="button" class="btn-primario" id="btnWizardSiguiente">Continuar</button>
            </div>

        </div>
    </div>

    <div class="modal-overlay oculto" id="modalDetalleHistorial">
        <div class="modal-box modal-detalle-historial">

            <div class="modal-header">
                <h2 id="detalleHistorialTitulo">Historial de cambios masivos — 15/07/2026</h2>
                <button type="button" class="modal-cerrar" data-cerrar-modal="modalDetalleHistorial">&times;</button>
            </div>

            <div class="modal-body">
                <div class="seccion-detalles-sup">
                    <p class="detalle-realizado-por">
                        Realizado por <strong id="detalleHistorialRealizadoPor">David Aguirre</strong>
                        · <span class="badge-estado badge-inactivo" id="detalleHistorialBadge">Inactivo</span>
                    </p>

                    <h3 class="detalle-subtitulo">Motivo</h3>
                    <p class="detalle-motivo-texto" id="detalleHistorialMotivo">
                        Jugadores con 3 o más meses sin registrar pago, sin respuesta
                        del acudiente tras contacto telefónico.
                    </p>
                </div>

                <div class="seccion-jugadores-card">
                    <h3 class="detalle-subtitulo" id="detalleHistorialContadorTitulo">Jugadores afectados (7)</h3>
                    <div class="lista-revision" id="detalleHistorialLista">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secundario" data-cerrar-modal="modalDetalleHistorial">Cerrar</button>
            </div>

        </div>
    </div>

    <div class="modal-overlay oculto" id="modalCambioCompletado">
        <div class="modal-box modal-completado">

            <div class="modal-header modal-header-simple">
                <h2>Cambio completado</h2>
                <button type="button" class="modal-cerrar" data-cerrar-modal="modalCambioCompletado">&times;</button>
            </div>

            <div class="modal-body modal-body-centrado">
                <div class="icono-exito">
                    <i class="fi fi-rr-check"></i>
                </div>
                <p class="completado-resumen">Cambio aplicado a <strong>5 jugadores</strong></p>
                <p class="completado-detalle">
                    Los jugadores seleccionados ahora están marcados como Inactivo.
                    Puedes revisar el detalle en el historial de cambios masivos.
                </p>
            </div>

            <div class="modal-footer modal-footer-centrado">
                <button type="button" class="btn-primario" data-cerrar-modal="modalCambioCompletado">Entendido</button>
            </div>

        </div>
    </div>

    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/jugadores/desactivacion.js"></script>
</body>

</html>
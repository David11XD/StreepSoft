<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos | Streepsoft</title>
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/pagos/pagos.css">
</head>
<body>
    <div id="nav-card"></div>
    <div class="main-content">
        <div class="pagos-container">
            <h1>Historial de Pagos</h1>

            <div class="sub-container">
                <div class="tabs-pagos">
                    <button class="tab-btn" data-tab="metodos">Métodos de Pago</button>
                    <button class="tab-btn active" data-tab="correccion">Corrección de Pagos</button>
                </div>


                <div class="pagos-panel">

                    <div class="tab-contenido" id="tab-metodos">
                        <p class="placeholder-tab">Aquí va el contenido de Métodos de Pago.</p>
                    </div>

                    <div class="tab-contenido active" id="tab-correccion">
                        <div class="tabla-wrapper">
                            <table class="tabla-pagos">
                                <thead>
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Concepto</th>
                                        <th>Fecha</th>
                                        <th>Valor</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <!-- Fila 1: Santiago Rúa -->
                                    <tr class="fila-pago" data-id="1">
                                        <td class="col-alumno">
                                            <div class="alumno-info">
                                                <div class="avatar-iniciales">SR</div>
                                                <div>
                                                    <strong>Santiago Rúa</strong>
                                                    <span class="sub-categoria">Sub-14</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Mensualidad junio</td>
                                        <td>05/06/2026</td>
                                        <td>$90.000</td>
                                        <td>Nequi</td>
                                        <td><span class="badge-editado">Editado</span></td>
                                        <td class="col-acciones">
                                            <div class="acciones-wrapper">
                                                <button class="btn-icono btn-editar"
                                                    title="Editar"
                                                    data-id="1"
                                                    data-alumno="Santiago Rúa"
                                                    data-registrado="05/06/2026"
                                                    data-valor="90000"
                                                    data-fecha="2026-06-05"
                                                    data-metodo="Nequi"
                                                    data-concepto="Mensualidad"
                                                    data-concepto-texto="Mensualidad junio">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </button>
                                                <button class="btn-icono btn-historial" title="Ver historial">
                                                    <i class="fi fi-rr-pending"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="fila-historial oculto" data-historial-de="1">
                                        <td colspan="7">
                                            <div class="detalle-historial">
                                                <span class="punto-azul"></span>
                                                <div>
                                                    <p><strong>Editado por Administrador</strong> · 10/06/2026, 4:32 p.m.</p>
                                                    <p class="cambio-valor">
                                                        <strong>Valor:</strong>
                                                        <span class="valor-anterior">$80.000</span> →
                                                        <span class="valor-nuevo">$90.000</span>
                                                    </p>
                                                    <p class="motivo-texto">Motivo: "Se registró el valor sin el recargo por mora, se corrige a valor real cancelado."</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Fila 2: Mariana Gil -->
                                    <tr class="fila-pago" data-id="2">
                                        <td class="col-alumno">
                                            <div class="alumno-info">
                                                <div class="avatar-iniciales">MG</div>
                                                <div>
                                                    <strong>Mariana Gil</strong>
                                                    <span class="sub-categoria">Sub-12</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Pago Uniforme</td>
                                        <td>03/05/2026</td>
                                        <td>$90.000</td>
                                        <td>Efectivo</td>
                                        <td><span class="badge-original">Original</span></td>
                                        <td class="col-acciones">
                                            <div class="acciones-wrapper">
                                                <button class="btn-icono btn-editar"
                                                    title="Editar"
                                                    data-id="2"
                                                    data-alumno="Mariana Gil"
                                                    data-registrado="03/05/2026"
                                                    data-valor="90000"
                                                    data-fecha="2026-05-03"
                                                    data-metodo="Efectivo"
                                                    data-concepto="Uniforme"
                                                    data-concepto-texto="Pago Uniforme">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </button>
                                                <button class="btn-icono btn-historial" title="Ver historial">
                                                    <i class="fi fi-rr-pending"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="fila-historial oculto" data-historial-de="2">
                                        <td colspan="7">
                                            <div class="detalle-historial detalle-sin-cambios">
                                                <span class="punto-gris"></span>
                                                <p>El registro se encuentra sin modificaciones.</p>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Fila 3: Tomás Prieto -->
                                    <tr class="fila-pago" data-id="3">
                                        <td class="col-alumno">
                                            <div class="alumno-info">
                                                <div class="avatar-iniciales">TP</div>
                                                <div>
                                                    <strong>Tomás Prieto</strong>
                                                    <span class="sub-categoria">Sub-14</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Matricula 2026</td>
                                        <td>14/02/2026</td>
                                        <td>$120.000</td>
                                        <td>Efectivo</td>
                                        <td><span class="badge-editado">Editado</span></td>
                                        <td class="col-acciones">
                                            <div class="acciones-wrapper">
                                                <button class="btn-icono btn-editar"
                                                    title="Editar"
                                                    data-id="3"
                                                    data-alumno="Tomás Prieto"
                                                    data-registrado="14/02/2026"
                                                    data-valor="120000"
                                                    data-fecha="2026-02-14"
                                                    data-metodo="Efectivo"
                                                    data-concepto="Matricula"
                                                    data-concepto-texto="Matricula 2026">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </button>
                                                <button class="btn-icono btn-historial" title="Ver historial">
                                                    <i class="fi fi-rr-pending"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="fila-historial oculto" data-historial-de="3">
                                        <td colspan="7">
                                            <div class="detalle-historial">
                                                <span class="punto-azul"></span>
                                                <div>
                                                    <p><strong>Editado por Administrador</strong> · 08/02/2026, 9:10 a.m.</p>
                                                    <p class="cambio-valor">
                                                        <strong>Valor:</strong>
                                                        <span class="valor-anterior">$110.000</span> →
                                                        <span class="valor-nuevo">$120.000</span>
                                                    </p>
                                                    <p class="motivo-texto">Motivo: "Ajuste por actualización de tarifa de matrícula."</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Fila 4: Mario Rodríguez -->
                                    <tr class="fila-pago" data-id="4">
                                        <td class="col-alumno">
                                            <div class="alumno-info">
                                                <div class="avatar-iniciales">MR</div>
                                                <div>
                                                    <strong>Mario Rodriguez</strong>
                                                    <span class="sub-categoria">Sub-17</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Pago Torneo</td>
                                        <td>03/05/2026</td>
                                        <td>$50.000</td>
                                        <td>Efectivo</td>
                                        <td><span class="badge-original">Original</span></td>
                                        <td class="col-acciones">
                                            <div class="acciones-wrapper">
                                                <button class="btn-icono btn-editar"
                                                    title="Editar"
                                                    data-id="4"
                                                    data-alumno="Mario Rodriguez"
                                                    data-registrado="03/05/2026"
                                                    data-valor="50000"
                                                    data-fecha="2026-05-03"
                                                    data-metodo="Efectivo"
                                                    data-concepto="Torneo"
                                                    data-concepto-texto="Pago Torneo">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </button>
                                                <button class="btn-icono btn-historial" title="Ver historial">
                                                    <i class="fi fi-rr-pending"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="fila-historial oculto" data-historial-de="4">
                                        <td colspan="7">
                                            <div class="detalle-historial detalle-sin-cambios">
                                                <span class="punto-gris"></span>
                                                <p>El registro se encuentra sin modificaciones.</p>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalEditarPago" class="modal-pago oculto">
        <div class="modal-pago-contenido">
            <div class="modal-pago-header">
                <div>
                    <h2>Editar Pago</h2>
                    <p class="modal-pago-subtitulo" id="modalSubtitulo">—</p>
                </div>
                <button class="cerrar-modal-pago" id="cerrarModalPago">&times;</button>
            </div>

            <div class="comparativa-pago">
                <div class="caja-registro">
                    <p class="caja-titulo-actual">Registro actual</p>
                    <div class="dato-fila"><span>Valor</span><strong id="actualValor">—</strong></div>
                    <div class="dato-fila"><span>Fecha</span><strong id="actualFecha">—</strong></div>
                    <div class="dato-fila"><span>Método</span><strong id="actualMetodo">—</strong></div>
                    <div class="dato-fila"><span>Concepto</span><strong id="actualConcepto">—</strong></div>
                </div>

                <div class="flecha-comparativa">→</div>

                <div class="caja-registro caja-corregida">
                    <p class="caja-titulo-corregido">Valores corregidos</p>
                    <div class="dato-fila"><span>Valor</span><strong id="previewValor">—</strong></div>
                    <div class="dato-fila"><span>Fecha</span><strong id="previewFecha">—</strong></div>
                    <div class="dato-fila"><span>Método</span><strong id="previewMetodo">—</strong></div>
                    <div class="dato-fila"><span>Concepto</span><strong id="previewConcepto">—</strong></div>
                </div>
            </div>

            <form id="formEditarPago">
                <div class="campos-edicion">
                    <div class="campo-edicion">
                        <label for="inputValor">Valor del pago</label>
                        <input type="number" id="inputValor" required>
                    </div>
                    <div class="campo-edicion">
                        <label for="inputFecha">Fecha del pago</label>
                        <input type="date" id="inputFecha" required>
                    </div>
                    <div class="campo-edicion">
                        <label for="inputMetodo">Método de pago</label>
                        <select id="inputMetodo">
                            <option value="Nequi">Nequi</option>
                            <option value="Efectivo">Efectivo</option>
                        </select>
                    </div>
                    <div class="campo-edicion">
                        <label for="inputConcepto">Concepto</label>
                        <select id="inputConcepto">
                            <option value="Mensualidad">Mensualidad</option>
                            <option value="Matricula">Matrícula</option>
                            <option value="Uniforme">Uniforme</option>
                            <option value="Torneo">Torneo</option>
                        </select>
                    </div>
                </div>

                <div class="aviso-motivo">
                    <i class="fi fi-rr-info"></i>
                    El motivo de la corrección es obligatorio y quedará visible en el historial de auditoría.
                </div>

                <div class="campo-edicion campo-ancho">
                    <label for="inputMotivoTipo">Motivo de la corrección</label>
                    <select id="inputMotivoTipo" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="Error en el valor registrado">Error en el valor registrado</option>
                        <option value="Error en el método de pago">Error en el método de pago</option>
                        <option value="Error en la fecha">Error en la fecha</option>
                        <option value="Error en el concepto">Error en el concepto</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="campo-edicion campo-ancho">
                    <label for="inputMotivoTexto">Describe el cambio realizado</label>
                    <textarea id="inputMotivoTexto" rows="3" required></textarea>
                </div>

                <div class="historial-mini">
                    <p class="caja-titulo">Historial de este pago</p>
                    <div id="historialMiniLista"></div>
                </div>

                <div class="acciones-modal-pago">
                    <button type="button" class="btn-cancelar-pago" id="cancelarModalPago">Cancelar</button>
                    <button type="submit" class="btn-guardar-pago">Guardar corrección</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="/streepsoft/public/js/navbar/script.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/pagos/pagos.js"></script>

    <-- Chatbot--
        <script src="https://cdn.botpress.cloud/webchat/v3.7/inject.js"></script>
        <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514195634-UH0HGKBC.js" defer></script>
</body>

</html>
<?php
/** @var array $jugador Datos reales del jugador seleccionado. */
/** @var array $historial Pagos históricos reales del jugador. */
/** @var array|null $proximaDeuda Próxima deuda activa del jugador. */

$nombreCompleto = trim(($jugador['nombres'] ?? '') . ' ' . ($jugador['apellidos'] ?? ''));
$categoria = trim((string) ($jugador['categoria'] ?? ''));
$iniciales = strtoupper(
    mb_substr((string) ($jugador['nombres'] ?? ''), 0, 1) .
    mb_substr((string) ($jugador['apellidos'] ?? ''), 0, 1)
);
$foto = trim((string) ($jugador['foto'] ?? ''));
$rutaFoto = $foto !== ''
    ? '/streepsoft/public/Image/jugadores/' . rawurlencode(basename($foto))
    : '';

$textoSiguienteDeuda = 'Sin próxima deuda registrada';
if (!empty($proximaDeuda['fecha_limite_pago'])) {
    $fechaSiguiente = date_create($proximaDeuda['fecha_limite_pago']);
    if ($fechaSiguiente) {
        $mesesEspanol = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];
        $textoSiguienteDeuda = 'Siguiente deuda ' .
            $fechaSiguiente->format('j') . ' de ' .
            $mesesEspanol[(int) $fechaSiguiente->format('n')] . ' de ' .
            $fechaSiguiente->format('Y');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($titulo ?? 'Historial de pagos', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/historial.css" />
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
</head>
<body>
    <div id="nav-card"></div>

    <div class="main-content">

        <div class="ges-navegacion">
            <a href="/streepsoft/jugadores/deudas" class="ges-btn-volver">
                <i class="fi fi-rr-arrow-left"></i>
                Volver al listado
            </a>

            <nav class="ges-migas" aria-label="Ruta de navegacion">
                <a href="/streepsoft/jugadores/deudas">Deudas</a>
                <span class="ges-migas-separador">&gt;</span>
                <span class="ges-migas-actual"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></span>
            </nav>
        </div>


        <div class="card-body">
            <div class="text-card">
                <h1>Historial de Pagos</h1>
            </div>


            <div class="card-otp">
                <div class="card-options">
                    <div class="card-one">
                        <div class="informacion-jugador">
                            <div class="foto-jugador">
                                <?php if ($rutaFoto !== ''): ?>
                                    <img
                                        src="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Foto de <?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                        onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                                    />
                                <?php endif; ?>
                                <span class="foto-iniciales-historial" <?= $rutaFoto !== '' ? 'hidden' : '' ?>>
                                    <?= htmlspecialchars($iniciales !== '' ? $iniciales : 'J', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            
                            <div class="card-información">
                                <div class="informacion">
                                    <h3>
                                        <?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>
                                        <?= $categoria !== '' ? ' - ' . htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') : '' ?>
                                    </h3>
                                    <p><?= htmlspecialchars($textoSiguienteDeuda, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>

                                <div class="boton-pdf" >
                                    <form method="get" action="/streepsoft/jugadores/deudas/historial/<?= (int) $jugador['id_jugadores'] ?>/pdf">
                                        <button type="submit" title="Descargar PDF de Jugador">
                                            <p>Descargar PDF</p>
                                            <span class="meteor-icons--download"></span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-two">
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
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['actualizado'])): ?>
                <p class="mensaje-historial-ok" role="status">El pago histórico se actualizó correctamente.</p>
            <?php endif; ?>

            <div class="card-tb">
                <div class="card-tables">
                    <div class="table-responsive">
                        <table id="tablaJugadores">
                            <thead>
                                <tr>
                                    <th>Fecha limite de pago</th>
                                    <th>Fecha de pago</th>
                                    <th>Matricula</th>
                                    <th>Mensualidad</th>
                                    <th>Metodo</th>
                                    <th>Concepto</th>
                                    <th>Total pagado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historial)): ?>
                                    <tr class="fila-vacia">
                                        <td colspan="8">Este jugador todavía no tiene pagos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($historial as $pago): ?>
                                        <?php
                                            $fechaLimiteObj = !empty($pago['fecha_limite_pago'])
                                                ? date_create($pago['fecha_limite_pago'])
                                                : false;
                                            $fechaPagoObj = !empty($pago['fecha_pago'])
                                                ? date_create($pago['fecha_pago'])
                                                : false;
                                            $fechaLimite = $fechaLimiteObj ? $fechaLimiteObj->format('d-m-Y') : '-';
                                            $fechaPago = $fechaPagoObj ? $fechaPagoObj->format('d-m-Y') : '-';
                                            $matricula = (float) ($pago['matricula'] ?? 0);
                                            $totalCiclo = (float) ($pago['totalidad'] ?? 0);
                                            // La mensualidad se almacena separada; fallback para datos antiguos.
                                            $mensualidad = (float) ($pago['mensualidad'] ?? max(0, $totalCiclo - $matricula));
                                            $totalPagado = (float) ($pago['valor_pagado'] ?? $totalCiclo);
                                            $concepto = trim((string) ($pago['concepto'] ?? ''));
                                            if ($concepto === '') {
                                                $concepto = 'Sin concepto';
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="table-text">
                                                    <h3><?= htmlspecialchars($fechaLimite, ENT_QUOTES, 'UTF-8') ?></h3>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <h3><?= htmlspecialchars($fechaPago, ENT_QUOTES, 'UTF-8') ?></h3>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <h2>$<?= number_format($matricula, 0, ',', '.') ?> cop</h2>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <h2>$<?= number_format($mensualidad, 0, ',', '.') ?> cop</h2>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <p><?= htmlspecialchars($pago['metodo_pago'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <p><?= htmlspecialchars($concepto, ENT_QUOTES, 'UTF-8') ?></p>      
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-text">
                                                    <h2>$<?= number_format($totalPagado, 0, ',', '.') ?> cop</h2>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-accion">
                                                    <button class="btn-menu-accion" type="button" aria-label="Abrir acciones del pago">
                                                        <span class="uil--ellipsis-v"></span>
                                                    </button>

                                                    <div class="menu-acciones">
                                                        <button
                                                            class="btn-editar-historial"
                                                            type="button"
                                                            data-id-historial="<?= (int) $pago['id_historial'] ?>">
                                                            Editar
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>


                    <div class="card-paginacion">

                        <div class="info-registros" id="infoRegistros">
                            <p>Mostrando 1 - 5 de 12 Pagos</p>
                        </div>

                        <div class="paginacion" id="paginacion">
                            <div class="Pagina">

                                <button type="button"
                                        class="pagina-btn anterior"
                                        id="btnAnterior">
                                    Anterior
                                </button>

                                <div id="numerosPaginas"></div>

                                <button type="button"
                                        class="pagina-btn siguiente"
                                        id="btnSiguiente">
                                    Siguiente
                                </button>

                            </div>
                        </div>

                        <div class="cantidad-registros">
                            <div class="registros">
                                <label for="cantidadRegistros">Mostrar</label>

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

    <!-- Modal único para editar un pago histórico, usando el mismo diseño del registro de pagos. -->
    <div class="modal-registro" id="modalEditarHistorial" aria-hidden="true">
        <div class="modal-registro-contenido">
            <button class="cerrar-registro" id="cerrarModalEditarHistorial" type="button" aria-label="Cerrar edición">
                &times;
            </button>
            <iframe
                id="iframeEditarHistorial"
                class="iframe-registro"
                src="about:blank"
                title="Editar pago histórico">
            </iframe>
        </div>
    </div>

    <script src="/streepsoft/public/js/jugadores/historialJugador.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
</body>
</html>
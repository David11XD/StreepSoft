<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadistica | Streepsoft</title>
    <link rel="stylesheet" href="/streepsoft/public/css/dashboard/dash.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
</head>

<body>

    <div id="nav-card"></div>


    <div class="main-content">
        <div class="card-dashboard">
            <div class="card-admin">
                <div class="card-text">
                    <h1>¡<?php echo $saludo ?? 'Bienvenido'; ?>, <?php echo htmlspecialchars($admin['nombre_completo'] ?? 'Administrador'); ?>!</h1>
                    <p>Aqui tienes resumen general de Cop Colombia Internacional</p>
                </div>

                <div class="card-fetch">
                    <div class="mdi-light--calendar "></div>
                    <p><?php echo date('d-m-Y');?></p>
                </div>
            </div>

            <div class="card-estadisticas">
                <div class="card-info">
                    <div class="icon">
                        <div class="fa6-solid--users"></div>
                    </div>

                    <div class="info">
                        <h2>Total de <span>Jugadores</span> </h2>
                        <h3><?= number_format((int)($totalJugadores ?? 0), 0, ',', '.') ?></h3>
                        <p>+ <?= number_format((int)($jugadoresNuevosMes ?? 0), 0, ',', '.') ?> este mes </p>
                    </div>
                </div>

                <div class="card-info">
                    <div class="icon-money">
                        <div class="tdesign--money-filled"></div>
                    </div>
                    <div class="info">
                        <h2>Pagos del <span>Mes</span></h2>
                        <h3>$<?= number_format((float)($pagosMesActual ?? 0), 0, ',', '.') ?></h3>
                        <p> <?= htmlspecialchars($variacionPagosTexto ?? 'Sin comparación disponible', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="card-info">
                    <div class="icon">
                        <div class="wi--time-3"></div>
                    </div>

                    <div class="info">
                        <h2>Jugadores en <span>Mora</span></h2>
                        <h3><?= number_format((int)($jugadoresEnMora ?? 0), 0, ',', '.') ?></h3>
                        <p>0.1 vs este mes </p>
                    </div>
                </div>

                <div class="card-info">
                    <div class="icon">
                        <div class="ph--soccer-ball"></div>
                    </div>

                    <div class="info">
                        <h2>Nuestras <span>sedes</span></h2>
                        <h3>2</h3>
                        <p>1 nacional 1 Internacional </p>
                    </div>
                </div>
            </div>

            <div class="card-table">
                <div class="card-uno">
                    <div class="card-ingresos">
                        <div class="text-ingesos">
                            <h3>Ingresos <span>mensuales</span></h3>

                            <div class="select-wrapper">
                                <select class="custom-select" id="anioIngresos" aria-label="Año de ingresos">
                                    <?php foreach (($aniosDisponibles ?? [(int) date('Y')]) as $anio): ?>
                                        <option value="<?= (int) $anio ?>" <?= (int) $anio === (int) ($anioActual ?? date('Y')) ? 'selected' : '' ?>>
                                            <?= (int) $anio ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card-etd">
                            <canvas id="ingresosChart"></canvas>
                        </div>
                    </div>

                    <div class="card-document">
                        <div class="text-document">
                            <h3>Documentos <span>Pendientes</span></h3>
                        </div>

                        <div class="card-certi">
                            <?php
                                $faltantesDocumentos = $faltantesDocumentos ?? [];
                                $itemsDocumentos = [
                                    'eps' => 'Certificado EPS',
                                    'consentimiento' => 'Consentimiento informado',
                                    'idrd' => 'Ficha IDRD',
                                ];
                            ?>
                            <?php foreach ($itemsDocumentos as $clave => $etiqueta): ?>
                                <div class="card-certificado">
                                    <div class="at-icons--file-document"></div>
                                    <div class="text-certificado">
                                        <h3><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></h3>
                                    </div>
                                    <div class="card-red">
                                        <p><?= number_format((int)($faltantesDocumentos[$clave] ?? 0), 0, ',', '.') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <a href="/streepsoft/documentos" class="ver-documentos">
                            <span>Ver Todos los documentos</span>

                            <span class="flecha">›</span>
                        </a>

                    </div>
                </div>


                <div class="card-dos">
                    <div class="card-jugadores">
                        <div class="text-jugadores">
                            <h3>Jugadores</h3>
                        </div>

                        <div class="grafica">
                            <div class="grafica-categorias">
                                <div class="grafica-dona">
                                    <canvas id="graficaCategorias"></canvas>
                                </div>

                                <div class="leyenda-categorias" id="leyendaCategorias"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-pagos">
                        <div class="text-pagos">
                            <h3>Pagos <span>Recientes</span></h3>
                        </div>

                        <div class="tabla-pagos-container">
                            <table class="tabla-pagos">
                                <tbody>
                                    <?php if (!empty($deudasRecientes)): ?>
                                        <?php foreach ($deudasRecientes as $deuda): ?>
                                            <?php
                                                $nombreDeuda = trim(
                                                    (string) ($deuda['nombres'] ?? '') . ' ' .
                                                    (string) ($deuda['apellidos'] ?? '')
                                                );
                                                if ($nombreDeuda === '') {
                                                    $nombreDeuda = 'Jugador no disponible';
                                                }

                                                $categoriaDeuda = trim((string) ($deuda['categoria'] ?? ''));
                                                $periodoDeuda = trim(
                                                    (string) ($deuda['mes'] ?? '') . ' ' .
                                                    (string) ($deuda['anio'] ?? '')
                                                );
                                                $fotoDeuda = trim((string) ($deuda['foto'] ?? ''));
                                                $fotoUrl = $fotoDeuda !== ''
                                                    ? '/streepsoft/public/Image/jugadores/' . rawurlencode($fotoDeuda)
                                                    : '/streepsoft/public/Image/usuario.png';

                                                $fechaLimiteObj = !empty($deuda['fecha_limite_pago'])
                                                    ? date_create((string) $deuda['fecha_limite_pago'])
                                                    : false;
                                                $mesesCortos = [
                                                    1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr',
                                                    5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago',
                                                    9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'
                                                ];
                                                $fechaLimiteTexto = $fechaLimiteObj
                                                    ? $fechaLimiteObj->format('d') . ' ' .
                                                        $mesesCortos[(int) $fechaLimiteObj->format('n')] . ' ' .
                                                        $fechaLimiteObj->format('Y')
                                                    : '-';
                                            ?>
                                            <tr>
                                                <td class="jugador">
                                                    <img
                                                        src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                        alt="<?= htmlspecialchars($nombreDeuda, ENT_QUOTES, 'UTF-8') ?>"
                                                        onerror="this.onerror=null;this.src='/streepsoft/public/Image/usuario.png';"
                                                    >
                                                    <div class="datos-jugador">
                                                        <span class="nombre"><?= htmlspecialchars($nombreDeuda, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <span class="categoria"><?= htmlspecialchars(trim($categoriaDeuda) ? : 'Sin categoría', ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </td>
                                                <td class="pago">
                                                    $<?= number_format((float) ($deuda['totalidad'] ?? 0), 0, ',', '.') ?>
                                                </td>
                                                <td class="fecha">
                                                    <?= htmlspecialchars($fechaLimiteTexto, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center; padding:18px; color:#aaa;">
                                                No hay deudas registradas actualmente.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>


                            <!-- BOTÓN FINAL -->

                            <a href="/streepsoft/jugadores/deudas" class="ver-pagos">
                                <span>Ver Todos los pagos</span>

                                <span class="flecha">›</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
        <script>
            // Datos calculados por DashboardController desde historial_deuda.
            // Se publican antes de cargar estadistica.js para que Chart.js
            // consuma únicamente ingresos realmente registrados.
            window.DASHBOARD_INGRESOS = <?= json_encode(
                $ingresosPorAnio ?? [],
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
            ) ?: '{}' ?>;

            // Distribución real de jugadores activos por categoría,
            // consultada por DashboardController desde Estadistica.
            window.DASHBOARD_CATEGORIAS = <?= json_encode(
                $jugadoresPorCategoria ?? [],
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
            ) ?: '[]' ?>;
        </script>
        <script src="/streepsoft/public/js/dashboard/estadistica.js?v=20261030-2"></script>
        <script src="/streepsoft/public/js/dashboard/grafica.js?v=20261031-1"></script>
        <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
        <script src="/streepsoft/public/js/timer/time.js"></script>
        <script>
            // ✅ BLOQUEAR RETROCESO EN DASHBOARD

            // Detener cualquier intento de navegación hacia atrás
            window.history.pushState(null, null, window.location.href);

            window.addEventListener('popstate', function(event) {
                // Bloquear silenciosamente
                window.history.pushState(null, null, window.location.href);
            });
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"
            integrity="sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw=="
            crossorigin="anonymous"></script>
        <script src="/streepsoft/public/js/navbar/script.js"></script>
            <script src="https://cdn.botpress.cloud/webchat/v3.7/inject.js"></script>
            <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514195634-UH0HGKBC.js" defer></script>


</body>

</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadistica | Streepssoft</title>
    <link rel="stylesheet" href="/streepsoft/public/css/dashboard/dash.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
</head>

<body>

    <div id="nav-card"></div>


    <div class="main-content">
        <div class="card-dashboard">
            <div class="card-admin">
                <div class="card-text">
                    <h1>¡Bienvenido, Administrador!</h1>
                    <p>Aqui tienes resumen general de tu escuela de fubtbol</p>
                </div>

                <div class="card-fetch">
                    <div class="mdi-light--calendar "></div>
                    <p>12-05-2026</p>
                </div>
            </div>

            <div class="card-estadisticas">
                <div class="card-info">
                    <div class="icon">
                        <div class="fa6-solid--users"></div>
                    </div>

                    <div class="info">
                        <h2>Total de <span>Jugadores</span> </h2>
                        <h3>5</h3>
                        <p>0 este mes </p>
                    </div>
                </div>

                <div class="card-info">
                    <div class="icon-money">
                        <div class="tdesign--money-filled"></div>
                    </div>
                    <div class="info">
                        <h2>Pagos del <span>Mes</span></h2>
                        <h3>$1.000.000</h3>
                        <p> %1.5 vs este mes</p>
                    </div>
                </div>

                <div class="card-info">
                    <div class="icon">
                        <div class="wi--time-3"></div>
                    </div>

                    <div class="info">
                        <h2>Jugadores en <span>Mora</span></h2>
                        <h3>3</h3>
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
                            <h3>Ingresos <span>mensulaes</span></h3>

                            <div class="select-wrapper">
                                <select class="custom-select">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="2026"> 2026</option>
                                    <option value="2025"> 2025 </option>
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
                            <div class="card-certificado">
                                <div class="basil--document-outline"></div>
                                <div class="text-certificado">
                                    <h3>Certidicado Medico</h3>
                                </div>
                                <div class="card-red">
                                    <p>4</p>
                                </div>
                            </div>

                            <div class="card-certificado">
                                <div class="basil--document-outline"></div>
                                <div class="text-certificado">
                                    <h3>Autorizaciones</h3>
                                </div>
                                <div class="card-red">
                                    <p>4</p>
                                </div>
                            </div>

                            <div class="card-certificado">
                                <div class="basil--document-outline"></div>
                                <div class="text-certificado">
                                    <h3>Foto Actulizadas</h3>
                                </div>
                                <div class="card-red">
                                    <p>4</p>
                                </div>
                            </div>
                        </div>

                        <a href="#" class="ver-documentos">
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
                                    <tr>
                                        <td class="jugador">
                                            <img src="/streepsoft/public/Image/usuario.png" alt="Juan Perez">

                                            <div class="datos-jugador">
                                                <span class="nombre">Juan Perez</span>
                                                <span class="categoria">Sub 17</span>
                                            </div>
                                        </td>

                                        <td class="pago">
                                            $80.000
                                        </td>

                                        <td class="fecha">
                                            12 may 2026
                                        </td>
                                    </tr>


                                    <tr>
                                        <td class="jugador">
                                            <img src="/streepsoft/public/Image/usuario.png" alt="Luis Martinez">

                                            <div class="datos-jugador">
                                                <span class="nombre">Luis Martinez</span>
                                                <span class="categoria">Sub 15</span>
                                            </div>
                                        </td>

                                        <td class="pago">
                                            $80.000
                                        </td>

                                        <td class="fecha">
                                            12 may 2026
                                        </td>
                                    </tr>


                                    <tr>
                                        <td class="jugador">
                                            <img src="/streepsoft/public/Image/usuario.png" alt="Juan Nuñes">

                                            <div class="datos-jugador">
                                                <span class="nombre">Juan Nuñes</span>
                                                <span class="categoria">Sub 20</span>
                                            </div>
                                        </td>

                                        <td class="pago">
                                            $80.000
                                        </td>

                                        <td class="fecha">
                                            12 may 2026
                                        </td>
                                    </tr>


                                    <tr>
                                        <td class="jugador">
                                            <img src="/streepsoft/public/Image/usuario.png" alt="Andres Rodriguez">

                                            <div class="datos-jugador">
                                                <span class="nombre">Andres Rodriguez</span>
                                                <span class="categoria">Sub 12</span>
                                            </div>
                                        </td>

                                        <td class="pago">
                                            $80.000
                                        </td>

                                        <td class="fecha">
                                            12 may 2026
                                        </td>
                                    </tr>

                                </tbody>
                            </table>


                            <!-- BOTÓN FINAL -->

                            <a href="#" class="ver-pagos">
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
        <script src="/streepsoft/public/js/dashboard/estadistica.js"></script>
        <script src="/streepsoft/public/js/dashboard/grafica.js"></script>
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nav | streepsoft </title>
    <link rel="stylesheet" href="/streepsoft/public/css/hamburguesa/hamburguer.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="/streepsoft/public/css/notificaciones/notificaciones.css">
</head>

<body>
    <div class="navbar">
        <nav class="main-navbar">
            <div class="navbar-group">
                <button type="button" class="navbar-item ntf-bell" id="ntf-bell" title="Notificaciones" aria-label="Abrir notificaciones" aria-expanded="false">
                    <div class="mingcute--notification-line"></div>
                    <span class="ntf-bell-count" id="ntf-bell-count" hidden>0</span>
                </button>

                <div class="line"></div>


                <a href="/streepsoft/perfil/administrador" class="navbar-items" title="Usuario">
                    <img src="/streepsoft/public/Image/admins/<?php echo htmlspecialchars($admin['foto']); ?>" alt="usuario" class="navbar-icon">

                    <div class="navbar-user-info">
                        <h1><?php echo isset($admin['nombre_completo']) ? htmlspecialchars($admin['nombre_completo']) : 'Administrador'; ?></h1>
                        <p><?php echo isset($admin['usuario']) ? htmlspecialchars($admin['usuario']) : ''; ?></p>
                    </div>
                </a>
            </div>

            <div class="navbar-group">
                <button class="navbar-item btn-menu" onclick="toggleMenu()" aria-label="Abrir menú">
                    <img src="/streepsoft/public/Image/menu.png" alt="hamburguesa" class="navbar-icon-1">
                </button>
                <div class="navbar-logo">
                    <img src="/streepsoft/public/Image/logo.png" alt="logo">
                </div>
            </div>
        </nav>
    </div>

    <aside id="side-menu" class="sidebar">

        <nav class="sidebar-links">

            <!-- INICIO  -->
            <a href="/streepsoft/dashboard" class="sidebar-link" title="Inicio">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 10.5L12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-9z" />
                        <path d="M9 21v-6h6v6" />
                    </svg>
                </span>

                <div class="sidebar-text">
                    Inicio
                </div>

            </a>


            <!-- JUGADORES -->
            <div class="sidebar-item">

                <button
                    class="sidebar-link players-btn"
                    onclick="togglePlayers()"
                    title="Jugadores">

                    <span class="sidebar-icon-1">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3" />
                            <circle cx="17" cy="9" r="2.5" />
                            <path d="M3.5 20c.5-3.5 2.5-5.5 5.5-5.5s5 2 5.5 5.5" />
                            <path d="M14 15c3-.5 5.5 1.5 6 5" />
                        </svg>
                    </span>

                    <div class="sidebar-text">
                        Jugadores
                    </div>

                    <span class="sidebar-arrow">
                        ›
                    </span>

                </button>


                <!-- SUBMENÚ DE JUGADORES -->
                <div id="players-menu" class="players-menu">

                    <a href="/streepsoft/jugadores/gestion">
                        <span class="submenu-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>

                        <div>Jugadores</div>
                    </a>


                    <a href="/streepsoft/instructores/listar">
                        <span class="submenu-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v8" />
                                <path d="M8 12h8" />
                            </svg>
                        </span>

                        <div>Instructor</div>
                    </a>


                    <a href="/streepsoft/jugadores/desactivacion">
                        <span class="submenu-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M3 12a9 9 0 1 0 3-6.7" />
                                <path d="M3 4v6h6" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>

                        <div>Desactivar</div>
                    </a>

                </div>

            </div>


            <a href="/streepsoft/jugadores/deudas" class="sidebar-link" title="Pagos">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="13" rx="1" />
                        <path d="M3 10h18" />
                        <path d="M7 15h4" />
                    </svg>
                </span>

                <div class="sidebar-text">
                    Pagos
                </div>

            </a>

            <a href="/streepsoft/documentos" class="sidebar-link" title="Documentos">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h9l5 5v13H5z" />
                        <path d="M14 3v6h5" />
                        <path d="M8 13h8" />
                        <path d="M8 17h6" />
                    </svg>
                </span>

                <div class="sidebar-text">
                    Documentos
                </div>

            </a>

            <a href="/streepsoft/cierre-anio" class="sidebar-link" title="Cierre y Apertura de Año">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path d="M3 10h18" />
                        <path d="M8 3v4" />
                        <path d="M16 3v4" />
                    </svg>
                </span>

                <div class="sidebar-text">
                    Cierre y Apertura de Año
                </div>

            </a>

        </nav>

        <form method="POST" action="/streepsoft/logout" class="sidebar-footer" onsubmit="return confirm('¿Realmente deseas cerrar sesión?');">
            <input type="hidden" name="_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
            <button class="cerrar-sesion players-btn">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4" />
                        <path d="M14 8l4 4-4 4" />
                        <path d="M8 12h10" />
                    </svg>
                </span>
                <div class="sidebar-text">
                    Cerrar sesión
                </div>
            </button>
        </form>
    </aside>

    <!-- Modal de notificaciones (se abre con la campana y se cierra al tocar fuera) -->
    <div class="ntf-drawer-backdrop" id="ntf-drawer-backdrop"></div>
    <aside class="ntf-drawer" id="ntf-drawer" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Notificaciones" data-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <header class="ntf-drawer-header">
            <div class="ntf-drawer-title">
                <h2>Notificaciones</h2>
                <button class="ntf-config-toggle" type="button" id="abrir-config" title="Configuración de notificaciones" aria-label="Configuración de notificaciones">⚙</button>
            </div>

            <!-- Al tocar una caja se filtra la lista; al volver a tocarla se quitan los filtros -->
            <div class="ntf-drawer-stats">
                <button type="button" class="ntf-stat alta" data-filtro="alta" aria-pressed="false">
                    <span>Prioridad alta</span>
                    <strong id="ntf-drawer-alta">0</strong>
                </button>
                <button type="button" class="ntf-stat seguimiento" data-filtro="seguimiento" aria-pressed="false">
                    <span>Seguimiento</span>
                    <strong id="ntf-drawer-seguimiento">0</strong>
                </button>
            </div>
        </header>

        <div class="ntf-drawer-list" id="ntf-drawer-list">
            <p class="ntf-drawer-loading">Cargando notificaciones...</p>
        </div>
    </aside>

    <!-- Configuración de los recordatorios (se abre con el engranaje del modal) -->
    <div class="ntf-modal" id="modal-config" aria-hidden="true">
        <div class="ntf-modal-card" role="dialog" aria-labelledby="ntf-config-titulo">

            <button class="ntf-close" id="cerrar-config" type="button" aria-label="Cerrar">&times;</button>

            <h2 id="ntf-config-titulo">Configuración de recordatorios</h2>
            <p>Activa los avisos y define cada cuánto se repiten.</p>

            <form id="form-config">
                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_mora" value="1"> Jugadores en mora</label>
                    <label class="ntf-frequency">Repetir cada (días)
                        <input type="number" name="frecuencia_mora" min="1" max="60" step="1">
                    </label>
                </div>

                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_inactivos" value="1"> Jugadores inactivos</label>
                    <label class="ntf-frequency">Repetir cada (días)
                        <input type="number" name="frecuencia_inactivos" min="1" max="60" step="1">
                    </label>
                </div>

                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_vencimientos" value="1"> Pagos próximos a vencer</label>
                    <label class="ntf-frequency">Avisar con (días) de anticipación
                        <input type="number" name="dias_anticipacion" min="1" max="60" step="1">
                    </label>
                </div>

                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_documentos_pendientes" value="1"> Documentos pendientes</label>
                    <label class="ntf-frequency">Repetir cada (días)
                        <input type="number" name="frecuencia_documentos" min="1" max="60" step="1">
                    </label>
                </div>

                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_pagos" value="1"> Pagos registrados</label>
                    <label class="ntf-frequency">Desde ($, 0 = todos)
                        <input type="number" class="ntf-monto" name="monto_pago_importante" min="0" max="100000000" step="1000">
                    </label>
                </div>

                <div class="ntf-setting">
                    <label><input type="checkbox" name="activar_documentos_rechazados" value="1"> Documentos rechazados (al producirse)</label>
                </div>

                <button class="ntf-save" type="submit">Guardar configuración</button>
                <p class="ntf-feedback" id="config-feedback" role="status"></p>
            </form>

        </div>
    </div>

    <div id="overlay" class="overlay" onclick="toggleMenu()"></div>

    <script src="/streepsoft/public/js/notificaciones/notificaciones.js"></script>
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
</body>

</html>
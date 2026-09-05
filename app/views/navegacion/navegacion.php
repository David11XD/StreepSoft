<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nav | streepsooft </title>
    <link rel="stylesheet" href="/streepsoft/public/css/hamburguesa/hamburguer.css">
</head>
<body>
    <div class="navbar">
        <nav class="main-navbar">
            <div class="navbar-group">
                <a href="#" class="navbar-item" title="Notificaciones">
                    <div class="mingcute--notification-line"></div>
                </a>

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
                    <img src="/streepsoft/public/Image/CopColombiaInternacional.png" alt="logo">
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
                        <path d="M3 10.5L12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-9z"/>
                        <path d="M9 21v-6h6v6"/>
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
                    title="Jugadores"
                >

                    <span class="sidebar-icon-1">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3"/>
                            <circle cx="17" cy="9" r="2.5"/>
                            <path d="M3.5 20c.5-3.5 2.5-5.5 5.5-5.5s5 2 5.5 5.5"/>
                            <path d="M14 15c3-.5 5.5 1.5 6 5"/>
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
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>

                        <div>Alumno</div>
                    </a>


                    <a href="#">
                        <span class="submenu-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 8v8"/>
                                <path d="M8 12h8"/>
                            </svg>
                        </span>

                        <div>Instructor</div>
                    </a>


                    <a href="#">
                        <span class="submenu-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v6h6"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>

                        <div>Desactivar</div>
                    </a>

                </div>

            </div>


            <a href="/streepsoft/jugadores/deudas" class="sidebar-link" title="Pagos">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="13" rx="1"/>
                        <path d="M3 10h18"/>
                        <path d="M7 15h4"/>
                    </svg>
                </span>

                <div class="sidebar-text">
                    Pagos
                </div>

            </a>

            <a href="#" class="sidebar-link" title="Documentos">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h9l5 5v13H5z"/>
                        <path d="M14 3v6h5"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h6"/>
                    </svg>
                </span>

                <div class="sidebar-text">
                    Documentos
                </div>

            </a>

            <a href="#" class="sidebar-link" title="Actualización de datos">

                <div class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3a9 9 0 1 0 8.5 6"/>
                        <path d="M12 7v5l3 2"/>
                        <path d="M16 3h5v5"/>
                    </svg>
                </div>

                <div class="sidebar-text">
                    Actualización de datos
                </div>

            </a>

        </nav>

        <form method="POST" action="/streepsoft/logout" class="sidebar-footer" onsubmit="return confirm('¿Realmente deseas cerrar sesión?');">
            <input type="hidden" name="_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
            <button class="cerrar-sesion">

                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/>
                        <path d="M14 8l4 4-4 4"/>
                        <path d="M8 12h10"/>
                    </svg>
                </span>
                <div class="sidebar-text">
                    Cerrar sesión
                </div>
            </button>
        </form>
    </aside>

    <div id="overlay" class="overlay" onclick="toggleMenu()"></div>

    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
</body>
</html>




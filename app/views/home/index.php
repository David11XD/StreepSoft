<?php
// Quick login SOLO si:
// 1. Existe cookie quick_login_data (no sesión)
// 2. NO fue logout manual (no existe cookie logout_manual)
$quickLoginDisponible = (
    isset($_COOKIE['quick_login_data']) &&
    !isset($_COOKIE['logout_manual'])
);

// Debug
error_log("Home - quickLoginDisponible: " . ($quickLoginDisponible ? 'true' : 'false'));
error_log("Home - COOKIE quick_login_data: " . (isset($_COOKIE['quick_login_data']) ? 'true' : 'false'));
error_log("Home - COOKIE logout_manual: " . (isset($_COOKIE['logout_manual']) ? 'true' : 'false'));

$remainingMs = 0;
if ($quickLoginDisponible) {
    $quickData = SessionTimeout::getQuickLoginData();
    $remainingMs = $quickData ? max(0, ($quickData['expires_at'] - time()) * 1000) : 0;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Streepsotf</title>
    <link rel="stylesheet" href="/streepsoft/public/css/homepanel/panel.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
</head>

<body>
    <div class="nav-des">
        <nav>
            <img src="/streepsoft/public/Image/logo.png" alt="CopColombia">

            <?php if ($quickLoginDisponible): ?>
                <form method="POST" action="<?= url('/quick-login') ?>" style="display:inline;">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <button type="submit" class="iniciar">
                        Inicio rapido
                    </button>
                </form>
            <?php else: ?>
                <a href="<?= url('/login') ?>">
                    <button class="iniciar">
                        <span>Iniciar Sesión</span>
                        <svg class="icono-login" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </a>
            <?php endif; ?>

        </nav>
        <div class="linea"></div>
    </div>


    <div class="des">
        <div class="imagenes">

            <div class="slide">
                <img src="/streepsoft/public/Image/collaege.png" alt="imagen-1">
                <div class="overlay">
                    <h1><span>Cop</span>&nbsp;<span>Co</span>lombia</h1>
                    <p>!Cumpliendo Sueños he ilusiones!</p>
                </div>
            </div>

            <div class="slide">
                <img src="/streepsoft/public/Image/collaege-2.png" alt="imagen-2">
                <div class="overlay">
                    <h1>Cada partido es una <span>oportunidad</span>.</h1>
                    <p>para demostrar quién eres.</p>
                </div>
            </div>

            <div class="slide">
                <img src="/streepsoft/public/Image/10.png" alt="imagen-3">
                <div class="overlay">
                    <h1>Entren<span>amiento</span> profesional</h1>
                    <p>Supera tus límites cada día</p>
                </div>
            </div>
        </div>

        <div class="zona-hero zona-hero-izq" id="heroZonaIzq"></div>
        <div class="zona-hero zona-hero-der" id="heroZonaDer"></div>

        <div class="indicadores"></div>
    </div>

    <!--Barra de estadísticas-->
    <div class="stats-bar">
        <div class="stat">
            <h2>+100</h2>
            <p>Alumnos activos</p>
        </div>
        <div class="stat">
            <h2>6</h2>
            <p>Entrenadores certificados</p>
        </div>
        <div class="stat">
            <h2>10</h2>
            <p>Países visitados</p>
        </div>
        <div class="stat">
            <h2>8</h2>
            <p>Años de trayectoria</p>
        </div>
    </div>

    <!--Mision y Vision-->
    <div class="proposito">
        <p class="kicker">Nuestro propósito</p>
        <h2>Visión y Misión</h2>
    </div>

    <div class="tarjetas-proposito">
        <div class="tarjeta">
            <h3>Vi<span>si</span>ón</h3>
            <p>Ser una organización social líder a nivel nacional e internacional, en el cumplimiento de sueños de NNA, comprometida con la igualdad de oportunidades, mediante alianzas estratégicas que multipliquen el impacto en nuestros programas y actividades que promuevan la implementación de ODS.</p>
        </div>

        <div class="tarjeta">
            <h3><span>Mi</span>sión</h3>
            <p>Somos una organización con enfoque social, deportivo, educativo y de cultura de Paz, que utiliza diferentes estrategias en sinergia con los ODS para mitigar y combatir flagelos en los que se ven expuestos NNAJ en Colombia.</p>
        </div>
    </div>

    <!--Galeria de Imagenes-->
    <div class="trayectoria-header">
        <h3>Nuestra trayectoria</h3>
        <a href="#" id="verGaleriaBtn">Ver galería completa →</a>
    </div>

    <div class="bento-grid">
        <div class="bento-item bento-ancho" data-index="1"> <!-- destacada: Cruyff -->
            <img src="/streepsoft/public/Image/collaege-16.jpg" alt="imagen-6">
        </div>
        <div class="bento-item bento-alto" data-index="2"> <!-- cuadrada -->
            <img src="/streepsoft/public/Image/20.png" alt="imagen-6">
        </div>
        <div class="bento-item" data-index="3">
            <img src="/streepsoft/public/Image/collaege-13.avif" alt="imagen-7">
        </div>
        <div class="bento-item" data-index="4">
            <img src="/streepsoft/public/Image/collaege-15.jpg" alt="imagen-8">
        </div>
        <div class="bento-item" data-index="5">
            <img src="/streepsoft/public/Image/collaege-14.avif" alt="imagen-9">
        </div>
        <div class="bento-item" data-index="6">
            <img src="/streepsoft/public/Image/collaege-12.jpg" alt="imagen-10">
        </div>
    </div>

    <!-- Fotos extra: solo aparecen en "Ver galería completa"-->
    <div class="galeria-extra" style="display: none;">
        <img src="/streepsoft/public/Image/21.jpg" alt="foto extra 1">
        <img src="/streepsoft/public/Image/19.png" alt="foto extra 2">
        <img src="/streepsoft/public/Image/11.png" alt="foto extra 3">
    </div>

    <!-- Ver galería -->
    <div id="modalGaleria" class="modal-galeria oculto">
        <span id="cerrarModal" class="cerrar">&times;</span>

        <button class="flecha-galeria flecha-izq" id="zonaIzq">⟨</button>

        <div class="visor-foto">
            <img id="fotoActual" src="" alt="Foto galería">
        </div>

        <button class="flecha-galeria flecha-der" id="zonaDer">⟩</button>

        <div class="contador-fotos"><span id="contador">1 / 8</span></div>
    </div>


    <footer class="footer">
        <div class="footer-copy">
            <p>© 2026 Streepsotf — <span>Cop Co</span>lombia Internacional. Todos los derechos reservados</p>
        </div>

        <div class="footer-redes">

            <a href="https://www.facebook.com/copcolombiainterna/?locale=es_LA" aria-label="Facebook" target="_blank">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.7-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z" />
                </svg>
            </a>
            <a href="https://www.instagram.com/copinternacional/" aria-label="Instagram" target="_blank">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M12 2c2.7 0 3.1 0 4.1.1 1 .1 1.7.2 2.3.5.6.3 1.1.6 1.6 1.1.5.5.9 1 1.1 1.6.3.6.4 1.3.5 2.3.1 1 .1 1.4.1 4.1s0 3.1-.1 4.1c-.1 1-.2 1.7-.5 2.3a4.6 4.6 0 0 1-1.1 1.6 4.6 4.6 0 0 1-1.6 1.1c-.6.3-1.3.4-2.3.5-1 .1-1.4.1-4.1.1s-3.1 0-4.1-.1c-1-.1-1.7-.2-2.3-.5a4.6 4.6 0 0 1-1.6-1.1 4.6 4.6 0 0 1-1.1-1.6c-.3-.6-.4-1.3-.5-2.3C2 15.1 2 14.7 2 12s0-3.1.1-4.1c.1-1 .2-1.7.5-2.3.3-.6.6-1.1 1.1-1.6.5-.5 1-.9 1.6-1.1.6-.3 1.3-.4 2.3-.5C8.9 2 9.3 2 12 2zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zm5.2-8.4a1.2 1.2 0 1 0 0-2.4 1.2 1.2 0 0 0 0 2.4z" />
                </svg>
            </a>
            <a href="#" aria-label="WhatsApp">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" target="_blank">
                    <path d="M20.5 3.5A11 11 0 0 0 3 17.4L2 22l4.7-1.2A11 11 0 1 0 20.5 3.5zM12 20a9 9 0 0 1-4.6-1.3l-.3-.2-3 .8.8-2.9-.2-.3A9 9 0 1 1 12 20zm4.9-6.7c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a7.3 7.3 0 0 1-3.7-3.2c-.3-.5.3-.4.8-1.4.1-.2 0-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s1 2.6 1.1 2.8c.1.2 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.6-.7 1.9-1.3.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z" />
                </svg>
            </a>

            <img src="/streepsoft/public/assets/img/logo.png" alt="Streepsoft" class="footer-logo">

        </div>
    </footer>

    <?php if ($quickLoginDisponible): ?>
    <script>
        const QUICK_LOGIN_REMAINING = <?= (int)$remainingMs ?>;
        setTimeout(() => { window.location.reload(); }, QUICK_LOGIN_REMAINING);
    </script>
    <?php endif; ?>
    <script>
        // BLOQUEAR RETROCESO EN HOME
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function(event) {
            event.preventDefault();
            window.history.pushState(null, null, window.location.href);
        });
    </script>
</body>

    <script>
        let index = 0;
        const slides = document.querySelectorAll('.slide');
        const total = slides.length;
        const contenedor = document.querySelector('.imagenes');
        const indicadores = document.querySelector('.indicadores')

        /* Crear punticos */

        for (let i = 0; i < total; i++) {
            let punto = document.createElement('span');
            punto.addEventListener('click', () => {
                index = i;
                actualizar();
            });
            indicadores.appendChild(punto);
        }

        function actualizar() {
            contenedor.style.transform = `translateX(-${index * 100}%)`;

            document.querySelectorAll('.indicadores span').forEach((p, i) => {
                p.classList.toggle('activo', i === index);
            });
        }

        /* Botones */
        document.querySelector('#heroZonaDer').onclick = () => {
            index = (index + 1) % total;
            actualizar();
        }

        document.querySelector('#heroZonaIzq').onclick = () => {
            index = (index - 1 + total) % total;
            actualizar();
        }

        /* Automatico */

        setInterval(() => {
            index = (index + 1) % total;
            actualizar();
        }, 6000);

        actualizar();


        /*Swipe en móvil para el hero*/
        /* Es para que en celulares y tablets donde se pueda pasar de foto deslizando el dedo */
        let heroTouchStartX = 0;
        const heroContenedor = document.querySelector('.des');

        heroContenedor.addEventListener('touchstart', (e) => {
            heroTouchStartX = e.touches[0].clientX;
        });

        heroContenedor.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchEndX - heroTouchStartX;
            if (Math.abs(diff) > 50) {
                if (diff < 0) index = (index + 1) % total;
                else index = (index - 1 + total) % total;
                actualizar();
            }
        });
    </script>
    <script src="/streepsoft/public/js/main/galeria.js"></script>
        <script src="https://cdn.botpress.cloud/webchat/v3.7/inject.js"></script>
        <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514195634-UH0HGKBC.js" defer></script>


</body>

</html>
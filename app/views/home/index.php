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
    <title>Inicio | Streepsoft</title>
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
        <p>© 2026 Streepsoft — <span>Cop Co</span>lombia Internacional. Todos los derechos reservados</p>
    </div>

    <div class="footer-redes">

        <!-- Página web -->
        <a href="https://www.constructorsofpeace.org/" aria-label="Sitio web" target="_blank">
            <svg viewBox="204 671 32 32" width="18" height="18" fill="currentColor">
                <path d="M231.596,694.829 C229.681,694.192 227.622,693.716 225.455,693.408 C225.75,691.675 225.907,689.859 225.957,688 L233.962,688 C233.783,690.521 232.936,692.854 231.596,694.829 Z M223.434,700.559 C224.1,698.95 224.645,697.211 225.064,695.379 C226.862,695.645 228.586,696.038 230.219,696.554 C228.415,698.477 226.073,699.892 223.434,700.559 Z M220.971,700.951 C220.649,700.974 220.328,701 220,701 C219.672,701 219.352,700.974 219.029,700.951 C218.178,699.179 217.489,697.207 216.979,695.114 C217.973,695.027 218.98,694.976 220,694.976 C221.02,694.976 222.027,695.027 223.022,695.114 C222.511,697.207 221.822,699.179 220.971,700.951 Z M209.781,696.554 C211.414,696.038 213.138,695.645 214.936,695.379 C215.355,697.211 215.9,698.95 216.566,700.559 C213.927,699.892 211.586,698.477 209.781,696.554 Z M208.404,694.829 C207.064,692.854 206.217,690.521 206.038,688 L214.043,688 C214.093,689.859 214.25,691.675 214.545,693.408 C212.378,693.716 210.319,694.192 208.404,694.829 Z M208.404,679.171 C210.319,679.808 212.378,680.285 214.545,680.592 C214.25,682.325 214.093,684.141 214.043,686 L206.038,686 C206.217,683.479 207.064,681.146 208.404,679.171 Z M216.566,673.441 C215.9,675.05 215.355,676.789 214.936,678.621 C213.138,678.356 211.414,677.962 209.781,677.446 C211.586,675.523 213.927,674.108 216.566,673.441 Z M219.029,673.049 C219.352,673.027 219.672,673 220,673 C220.328,673 220.649,673.027 220.971,673.049 C221.822,674.821 222.511,676.794 223.022,678.886 C222.027,678.973 221.02,679.024 220,679.024 C218.98,679.024 217.973,678.973 216.979,678.886 C217.489,676.794 218.178,674.821 219.029,673.049 Z M223.954,688 C223.9,689.761 223.74,691.493 223.439,693.156 C222.313,693.058 221.168,693 220,693 C218.832,693 217.687,693.058 216.562,693.156 C216.26,691.493 216.1,689.761 216.047,688 L223.954,688 Z M216.047,686 C216.1,684.239 216.26,682.507 216.562,680.844 C217.687,680.942 218.832,681 220,681 C221.168,681 222.313,680.942 223.438,680.844 C223.74,682.507 223.9,684.239 223.954,686 L216.047,686 Z M230.219,677.446 C228.586,677.962 226.862,678.356 225.064,678.621 C224.645,676.789 224.1,675.05 223.434,673.441 C226.073,674.108 228.415,675.523 230.219,677.446 Z M231.596,679.171 C232.936,681.146 233.783,683.479 233.962,686 L225.957,686 C225.907,684.141 225.75,682.325 225.455,680.592 C227.622,680.285 229.681,679.808 231.596,679.171 Z M220,671 C211.164,671 204,678.163 204,687 C204,695.837 211.164,703 220,703 C228.836,703 236,695.837 236,687 C236,678.163 228.836,671 220,671 Z"/>
            </svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/copcolombiainternacional/" aria-label="Facebook" target="_blank">
            <svg viewBox="0 0 512 512" width="18" height="18" fill="currentColor">
                <path d="M283.122,122.174c0,5.24,0,22.319,0,46.583h83.424l-9.045,74.367h-74.379 c0,114.688,0,268.375,0,268.375h-98.726c0,0,0-151.653,0-268.375h-51.443v-74.367h51.443c0-29.492,0-50.463,0-56.302 c0-27.82-2.096-41.02,9.725-62.578C205.948,28.32,239.308-0.174,297.007,0.512c57.713,0.711,82.04,6.263,82.04,6.263 l-12.501,79.257c0,0-36.853-9.731-54.942-6.263C293.539,83.238,283.122,94.366,283.122,122.174z"/>
            </svg>
        </a>

        <!-- Instagram -->
        <a href="https://www.instagram.com/copinternacional/" aria-label="Instagram" target="_blank">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18ZM12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z"/>
                <path d="M18 5C17.4477 5 17 5.44772 17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5Z"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.65396 4.27606C1 5.55953 1 7.23969 1 10.6V13.4C1 16.7603 1 18.4405 1.65396 19.7239C2.2292 20.8529 3.14708 21.7708 4.27606 22.346C5.55953 23 7.23969 23 10.6 23H13.4C16.7603 23 18.4405 23 19.7239 22.346C20.8529 21.7708 21.7708 20.8529 22.346 19.7239C23 18.4405 23 16.7603 23 13.4V10.6C23 7.23969 23 5.55953 22.346 4.27606C21.7708 3.14708 20.8529 2.2292 19.7239 1.65396C18.4405 1 16.7603 1 13.4 1H10.6C7.23969 1 5.55953 1 4.27606 1.65396C3.14708 2.2292 2.2292 3.14708 1.65396 4.27606ZM13.4 3H10.6C8.88684 3 7.72225 3.00156 6.82208 3.0751C5.94524 3.14674 5.49684 3.27659 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3.27659 5.49684 3.14674 5.94524 3.0751 6.82208C3.00156 7.72225 3 8.88684 3 10.6V13.4C3 15.1132 3.00156 16.2777 3.0751 17.1779C3.14674 18.0548 3.27659 18.5032 3.43597 18.816C3.81947 19.5686 4.43139 20.1805 5.18404 20.564C5.49684 20.7234 5.94524 20.8533 6.82208 20.9249C7.72225 20.9984 8.88684 21 10.6 21H13.4C15.1132 21 16.2777 20.9984 17.1779 20.9249C18.0548 20.8533 18.5032 20.7234 18.816 20.564C19.5686 20.1805 20.1805 19.5686 20.564 18.816C20.7234 18.5032 20.8533 18.0548 20.9249 17.1779C20.9984 16.2777 21 15.1132 21 13.4V10.6C21 8.88684 20.9984 7.72225 20.9249 6.82208C20.8533 5.94524 20.7234 5.49684 20.564 5.18404C20.1805 4.43139 19.5686 3.81947 18.816 3.43597C18.5032 3.27659 18.0548 3.14674 17.1779 3.0751C16.2777 3.00156 15.1132 3 13.4 3Z"/>
            </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/573134731884" aria-label="WhatsApp" target="_blank">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path d="M6.014 8.00613C6.12827 7.1024 7.30277 5.87414 8.23488 6.01043L8.23339 6.00894C9.14051 6.18132 9.85859 7.74261 10.2635 8.44465C10.5504 8.95402 10.3641 9.4701 10.0965 9.68787C9.7355 9.97883 9.17099 10.3803 9.28943 10.7834C9.5 11.5 12 14 13.2296 14.7107C13.695 14.9797 14.0325 14.2702 14.3207 13.9067C14.5301 13.6271 15.0466 13.46 15.5548 13.736C16.3138 14.178 17.0288 14.6917 17.69 15.27C18.0202 15.546 18.0977 15.9539 17.8689 16.385C17.4659 17.1443 16.3003 18.1456 15.4542 17.9421C13.9764 17.5868 8 15.27 6.08033 8.55801C5.97237 8.24048 5.99955 8.12044 6.014 8.00613Z"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 23C10.7764 23 10.0994 22.8687 9 22.5L6.89443 23.5528C5.56462 24.2177 4 23.2507 4 21.7639V19.5C1.84655 17.492 1 15.1767 1 12C1 5.92487 5.92487 1 12 1C18.0751 1 23 5.92487 23 12C23 18.0751 18.0751 23 12 23ZM6 18.6303L5.36395 18.0372C3.69087 16.4772 3 14.7331 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C11.0143 21 10.552 20.911 9.63595 20.6038L8.84847 20.3397L6 21.7639V18.6303Z"/>
            </svg>
        </a>

    </div>


</footer>

    <?php if ($quickLoginDisponible): ?>
        <script>
            const QUICK_LOGIN_REMAINING = <?= (int)$remainingMs ?>;
            setTimeout(() => {
                window.location.reload();
            }, QUICK_LOGIN_REMAINING);
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
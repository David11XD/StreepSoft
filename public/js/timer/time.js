
// ✅ AUTO-LOGOUT POR INACTIVIDAD (10 minutos)
const DASHBOARD_TIMEOUT = 10 * 60 * 1000; // 600,000 ms = 10 min
let inactivityTimer = null;
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        // Al redirigir, SessionTimeout::check() (en public/index.php)
        // detecta que pasaron +10 min, destruye la sesión y deja
        // el cookie de "Inicio Rápido" disponible por 2 minutos.
        window.location.href = '/streepsoft/';
    }, DASHBOARD_TIMEOUT);
}
['click', 'keypress', 'mousemove', 'scroll', 'touchstart'].forEach(evento => {
    document.addEventListener(evento, resetInactivityTimer);
});
resetInactivityTimer(); // iniciar al cargar la página

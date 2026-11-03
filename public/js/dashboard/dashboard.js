// Variables globales
const sideMenu = document.getElementById('side-menu');
const overlay = document.getElementById('overlay');
const body = document.body;
 
// ========== TOGGLE SIDEBAR ==========
function toggleMenu() {
    body.classList.toggle('sidebar-open');
    overlay.classList.toggle('active');
}
 
// ========== TOGGLE PLAYERS SUBMENU ==========
function togglePlayers() {
    const playersMenu = document.getElementById('players-menu');
    const playersBtn = document.querySelector('.players-btn');
    
    playersMenu.classList.toggle('active');
    playersBtn.classList.toggle('active');
}
 
// ========== CERRAR SIDEBAR EN MOBILE AL HACER CLICK EN UN LINK ==========
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
        // Solo cerrar si está en móvil
        if (window.innerWidth <= 768) {
            body.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        }
    });
});
 
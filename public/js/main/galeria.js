// Toma automáticamente todas las fotos del bento grid 
const fotosDestacadas = Array.from(document.querySelectorAll('.bento-item img')).map(img => img.src);
const fotosExtra = Array.from(document.querySelectorAll('.galeria-extra img')).map(img => img.src);
const fotos = [...fotosDestacadas, ...fotosExtra];
let indiceActual = 0;

const modal = document.getElementById('modalGaleria');
const fotoActual = document.getElementById('fotoActual');
const contador = document.getElementById('contador');

function mostrarFoto(i) {
    fotoActual.style.opacity = 0;
    setTimeout(() => {
        fotoActual.src = fotos[i];
        contador.textContent = `${i + 1} / ${fotos.length}`;
        fotoActual.style.opacity = 1;
    }, 120);
}

function abrirGaleria(indiceInicial = 0) {
    indiceActual = indiceInicial;
    mostrarFoto(indiceActual);
    modal.classList.remove('oculto');
}

// Abre la galería si le dan clic al enlace "Ver galería completa"
document.getElementById('verGaleriaBtn').addEventListener('click', (e) => {
    e.preventDefault();
    abrirGaleria(0);
});

// Abre la galería empezando justo en la foto que clicaron del bento grid
document.querySelectorAll('.bento-item').forEach((item, i) => {
    item.addEventListener('click', () => abrirGaleria(i));
});

// Cerrar el modal
document.getElementById('cerrarModal').addEventListener('click', () => {
    modal.classList.add('oculto');
});

// Clic en zonas invisibles (izquierda = anterior, derecha = siguiente)
document.getElementById('zonaIzq').addEventListener('click', () => {
    indiceActual = (indiceActual - 1 + fotos.length) % fotos.length;
    mostrarFoto(indiceActual);
});

document.getElementById('zonaDer').addEventListener('click', () => {
    indiceActual = (indiceActual + 1) % fotos.length;
    mostrarFoto(indiceActual);
});

// Swipe en móvil
let touchStartX = 0;
modal.addEventListener('touchstart', (e) => {
    touchStartX = e.touches[0].clientX;
});
modal.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].clientX;
    const diff = touchEndX - touchStartX;
    if (Math.abs(diff) > 50) {
        if (diff < 0) indiceActual = (indiceActual + 1) % fotos.length;      // swipe izq → siguiente
        else indiceActual = (indiceActual - 1 + fotos.length) % fotos.length; // swipe der → anterior
        mostrarFoto(indiceActual);
    }
});

// Flechas del teclado (bonus para desktop)
document.addEventListener('keydown', (e) => {
    if (modal.classList.contains('oculto')) return;
    if (e.key === 'ArrowRight') document.getElementById('zonaDer').click();
    if (e.key === 'ArrowLeft') document.getElementById('zonaIzq').click();
    if (e.key === 'Escape') modal.classList.add('oculto');
});
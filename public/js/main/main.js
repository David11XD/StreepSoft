let index = 0;
const slides = document.querySelectorAll('.slide');
const total = slides.length;
const contenedor = document.querySelector('.imagenes');
const indicadores = document.querySelector('.indicadores')

/* Crear punticos */

for (let i = 0; i < total; i++){
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
document.querySelector('.next').onclick = () => {
    index = (index + 1) % total;
    actualizar();
}

document.querySelector('.prev').onclick = () => {
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
// Pinta el ancho de cada barra de progreso segun los documentos entregados.

document.addEventListener('DOMContentLoaded', () => {
    const barras = document.querySelectorAll('.doc-progreso');

    barras.forEach((barra) => {
        const entregados = Number(barra.dataset.entregados);
        const total = Number(barra.dataset.total);
        const porcentaje = total > 0 ? (entregados / total) * 100 : 0;

        barra.querySelector('.doc-progreso-relleno').style.width = porcentaje + '%';
    });
});

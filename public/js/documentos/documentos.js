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


// Filtros reales del listado de jugadores.
document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.querySelector('.doc-tabla tbody');
    if (!tabla) return;
    const filas = [...tabla.querySelectorAll('tr[data-nombre]')];
    const buscar = document.getElementById('doc-buscar');
    const categoria = document.getElementById('filtro-categoria');
    const documentacion = document.getElementById('filtro-documentacion');

    function filtrar() {
        const q = (buscar?.value || '').trim().toLocaleLowerCase('es');
        const cat = categoria?.value || '';
        const estado = documentacion?.value || '';
        filas.forEach(fila => {
            const coincideTexto = (fila.dataset.nombre + ' ' + fila.dataset.documento).toLocaleLowerCase('es').includes(q);
            const coincideCategoria = !cat || fila.dataset.categoria === cat;
            const coincideEstado = !estado || fila.dataset.estado === estado;
            fila.hidden = !(coincideTexto && coincideCategoria && coincideEstado);
        });
    }
    buscar?.addEventListener('input', filtrar);
    categoria?.addEventListener('change', filtrar);
    documentacion?.addEventListener('change', filtrar);
});

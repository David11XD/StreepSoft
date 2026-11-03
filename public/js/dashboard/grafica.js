(() => {
    const canvas = document.getElementById('graficaCategorias');
    const leyenda = document.getElementById('leyendaCategorias');
    if (!canvas || typeof Chart === 'undefined') return;

    const filas = Array.isArray(window.DASHBOARD_CATEGORIAS)
        ? window.DASHBOARD_CATEGORIAS
        : [];
    const categorias = filas.map(fila => String(fila.categoria || 'Sin categoría'));
    const cantidades = filas.map(fila => Number(fila.total || 0));
    const colores = ['#D09E10', '#FFE000', '#B88B08', '#806000', '#777777', '#A67C00', '#C5A028'];
    const total = cantidades.reduce((suma, valor) => suma + valor, 0);

    if (leyenda) leyenda.replaceChildren();

    if (categorias.length === 0) {
        if (leyenda) {
            const mensaje = document.createElement('p');
            mensaje.className = 'sin-datos-categorias';
            mensaje.textContent = 'No hay jugadores activos registrados para mostrar por categoría.';
            leyenda.appendChild(mensaje);
        }
        return;
    }

    // Si el dashboard inicializa este script más de una vez, elimina la gráfica anterior.
    if (Chart.getChart(canvas)) {
        Chart.getChart(canvas).destroy();
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: categorias,
            datasets: [{
                data: cantidades,
                backgroundColor: categorias.map((_, i) => colores[i % colores.length]),
                borderWidth: 0,
                hoverOffset: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => {
                            const valor = Number(context.raw || 0);
                            const porcentaje = total > 0 ? ((valor / total) * 100).toFixed(0) : '0';
                            return ` ${valor} jugadores (${porcentaje}%)`;
                        }
                    }
                }
            }
        }
    });

    if (!leyenda) return;

    filas.forEach((fila, index) => {
        const cantidad = Number(fila.total || 0);
        const porcentaje = total > 0 ? ((cantidad / total) * 100).toFixed(0) : '0';
        const item = document.createElement('div');
        item.classList.add('item-categoria');

        const color = document.createElement('span');
        color.className = 'color-categoria';
        color.style.background = colores[index % colores.length];

        const nombre = document.createElement('span');
        nombre.className = 'nombre-categoria';
        nombre.textContent = String(fila.categoria || 'Sin categoría');

        const cantidadTexto = document.createElement('span');
        cantidadTexto.className = 'cantidad-categoria';
        cantidadTexto.textContent = `${cantidad} (${porcentaje}%)`;

        item.append(color, nombre, cantidadTexto);
        leyenda.appendChild(item);
    });
})();

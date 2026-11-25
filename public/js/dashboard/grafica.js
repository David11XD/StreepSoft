(() => {
    const canvas = document.getElementById('graficaCategorias');
    const leyenda = document.getElementById('leyendaCategorias');
    if (!canvas || typeof Chart === 'undefined') return;

    // Cada fila es UNA categoría con su instructor y la cantidad de jugadores activos
    // (consulta jugadoresPorInstructorCategoria del modelo Estadistica). Ya llegan
    // todas las categorías y en orden (Sub-6 ... Sub-20); aquí solo se dibujan.
    const filas = Array.isArray(window.DASHBOARD_INSTRUCTORES)
        ? window.DASHBOARD_INSTRUCTORES
        : [];
    const etiquetas = filas.map(fila => String(fila.categoria || 'Sin categoría'));
    const instructores = filas.map(fila => String(fila.instructor || 'Sin instructor'));
    const cantidades = filas.map(fila => Number(fila.total_jugadores || 0));
    const porcentajes = filas.map(fila => Number(fila.porcentaje || 0).toFixed(1));
    const colores = ['#D09E10', '#FFE000', '#B88B08', '#806000', '#777777', '#A67C00', '#C5A028'];
    const totalAlumnos = cantidades.reduce((suma, valor) => suma + valor, 0);

    const textoAlumnos = cantidad => `${cantidad} ${cantidad === 1 ? 'alumno' : 'alumnos'}`;

    if (leyenda) leyenda.replaceChildren();

    if (etiquetas.length === 0) {
        if (leyenda) {
            const mensaje = document.createElement('p');
            mensaje.className = 'sin-datos-categorias';
            mensaje.textContent = 'No hay categorías registradas.';
            leyenda.appendChild(mensaje);
        }
        return;
    }

    // Si el dashboard inicializa este script más de una vez, elimina la gráfica anterior.
    if (Chart.getChart(canvas)) {
        Chart.getChart(canvas).destroy();
    }

    // Sin alumnos en ninguna categoría se dibuja un anillo gris en lugar de un gráfico vacío.
    const hayAlumnos = totalAlumnos > 0;

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: hayAlumnos ? etiquetas : ['Sin alumnos'],
            datasets: [{
                data: hayAlumnos ? cantidades : [1],
                backgroundColor: hayAlumnos
                    ? etiquetas.map((_, i) => colores[i % colores.length])
                    : ['#3a3a3a'],
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
                    enabled: hayAlumnos,
                    callbacks: {
                        title: context => {
                            const index = context[0].dataIndex;
                            return `${etiquetas[index]} · ${instructores[index]}`;
                        },
                        label: context => {
                            const valor = Number(context.raw || 0);
                            return ` ${textoAlumnos(valor)} (${porcentajes[context.dataIndex]}%)`;
                        }
                    }
                }
            }
        }
    });

    if (!leyenda) return;

    // Una fila por categoría: Sub-6 · Instructor · 4 alumnos
    filas.forEach((fila, index) => {
        const item = document.createElement('div');
        item.classList.add('item-categoria');

        const color = document.createElement('span');
        color.className = 'color-categoria';
        color.style.background = colores[index % colores.length];

        const nombre = document.createElement('span');
        nombre.className = 'nombre-categoria';
        nombre.textContent = etiquetas[index];

        const instructor = document.createElement('span');
        instructor.className = 'instructor-categoria';
        instructor.textContent = instructores[index];
        instructor.title = instructores[index];

        const cantidadTexto = document.createElement('span');
        cantidadTexto.className = 'cantidad-categoria';
        cantidadTexto.textContent = textoAlumnos(cantidades[index]);

        item.append(color, nombre, instructor, cantidadTexto);
        leyenda.appendChild(item);
    });
})();

const ctxCategorias = document.getElementById('graficaCategorias');

const categorias = [
    'Sub 10',
    'Sub 12',
    'Sub 15',
    'Sub 17',
    'Sub 20'
];

const cantidades = [
    13,
    17,
    16,
    15,
    20
];

const colores = [
    '#D09E10',
    '#FFE000',
    '#B88B08',
    '#806000',
    '#777777'
];

const total = cantidades.reduce((a, b) => a + b, 0);

new Chart(ctxCategorias, {
    type: 'doughnut',

    data: {
        labels: categorias,

        datasets: [{
            data: cantidades,

            backgroundColor: colores,

            borderWidth: 0,

            hoverOffset: 0
        }]
    },

    options: {
        responsive: true,

        maintainAspectRatio: false,

        cutout: '55%',

        plugins: {
            legend: {
                display: false
            },

            tooltip: {
                callbacks: {
                    label: function(context) {

                        const valor = context.raw;

                        const porcentaje =
                            ((valor / total) * 100).toFixed(0);

                        return ` ${valor} jugadores (${porcentaje}%)`;
                    }
                }
            }
        }
    }
});

const leyenda = document.getElementById('leyendaCategorias');

categorias.forEach((categoria, index) => {

    const cantidad = cantidades[index];

    const porcentaje =
        ((cantidad / total) * 100).toFixed(0);

    const item = document.createElement('div');

    item.classList.add('item-categoria');

    item.innerHTML = `
        <span 
            class="color-categoria"
            style="background: ${colores[index]}"
        ></span>

        <span class="nombre-categoria">
            ${categoria}
        </span>

        <span class="cantidad-categoria">
            ${cantidad} (${porcentaje}%)
        </span>
    `;

    leyenda.appendChild(item);
});
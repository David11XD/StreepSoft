(() => {
    const canvas = document.getElementById('ingresosChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const datosPorAnio = (window.DASHBOARD_INGRESOS && typeof window.DASHBOARD_INGRESOS === 'object')
        ? window.DASHBOARD_INGRESOS
        : {};
    const selector = document.getElementById('anioIngresos');
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const formatoCOP = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    });

    const anios = Object.keys(datosPorAnio).sort((a, b) => Number(b) - Number(a));
    const anioInicial = selector?.value || anios[0] || String(new Date().getFullYear());
    const datosIniciales = normalizarDatos(datosPorAnio[anioInicial]);

    function normalizarDatos(datos) {
        if (!Array.isArray(datos)) return Array(12).fill(0);
        return Array.from({ length: 12 }, (_, indice) => {
            const valor = Number(datos[indice] ?? 0);
            return Number.isFinite(valor) && valor > 0 ? valor : 0;
        });
    }

    const ingresosChart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ingresos mensuales',
                data: datosIniciales,
                borderColor: '#D4AF37',
                backgroundColor: 'rgba(212, 175, 55, 0.15)',
                borderWidth: 3,
                fill: true,
                pointBackgroundColor: '#111',
                pointBorderColor: '#D4AF37',
                pointHoverBackgroundColor: '#D4AF37',
                pointHoverBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.25
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                x: {
                    grid: { color: 'rgba(128, 128, 128, 0.2)', drawBorder: false },
                    ticks: { color: '#A9A9A9' }
                },
                y: {
                    beginAtZero: true,
                    // No se fija un máximo artificial: Chart.js escala el eje
                    // según el mayor ingreso mensual real del año seleccionado.
                    grid: { color: 'rgba(128, 128, 128, 0.2)', drawBorder: false },
                    ticks: {
                        color: '#A9A9A9',
                        callback: value => formatoCOP.format(Number(value || 0))
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#D4AF37',
                        font: { family: "'Inter', sans-serif", size: 14 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#D4AF37',
                    bodyColor: '#fff',
                    borderColor: '#D4AF37',
                    borderWidth: 1,
                    callbacks: {
                        label: context => ' ' + formatoCOP.format(Number(context.raw || 0))
                    }
                }
            }
        }
    });

    if (selector) {
        selector.addEventListener('change', () => {
            const anioSeleccionado = selector.value;
            ingresosChart.data.datasets[0].data = normalizarDatos(datosPorAnio[anioSeleccionado]);
            ingresosChart.update();
        });
    }
})();
(() => {
    const canvas = document.getElementById('ingresosChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const datosPorAnio = (window.DASHBOARD_INGRESOS && typeof window.DASHBOARD_INGRESOS === 'object')
        ? window.DASHBOARD_INGRESOS
        : {};
    const selector = document.getElementById('anioIngresos');
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const formatoCOP = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    });

    const anios = Object.keys(datosPorAnio).sort((a, b) => Number(b) - Number(a));
    const anioInicial = selector?.value || anios[0] || String(new Date().getFullYear());
    const datosIniciales = normalizarDatos(datosPorAnio[anioInicial]);

    function normalizarDatos(datos) {
        if (!Array.isArray(datos)) return Array(12).fill(0);
        return Array.from({ length: 12 }, (_, indice) => {
            const valor = Number(datos[indice] ?? 0);
            return Number.isFinite(valor) && valor > 0 ? valor : 0;
        });
    }

    const ingresosChart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ingresos mensuales',
                data: datosIniciales,
                borderColor: '#D4AF37',
                backgroundColor: 'rgba(212, 175, 55, 0.15)',
                borderWidth: 3,
                fill: true,
                pointBackgroundColor: '#111',
                pointBorderColor: '#D4AF37',
                pointHoverBackgroundColor: '#D4AF37',
                pointHoverBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.25
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                x: {
                    grid: { color: 'rgba(128, 128, 128, 0.2)', drawBorder: false },
                    ticks: { color: '#A9A9A9' }
                },
                y: {
                    beginAtZero: true,
                    // No se fija un máximo artificial: Chart.js escala el eje
                    // según el mayor ingreso mensual real del año seleccionado.
                    grid: { color: 'rgba(128, 128, 128, 0.2)', drawBorder: false },
                    ticks: {
                        color: '#A9A9A9',
                        callback: value => formatoCOP.format(Number(value || 0))
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#D4AF37',
                        font: { family: "'Inter', sans-serif", size: 14 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#D4AF37',
                    bodyColor: '#fff',
                    borderColor: '#D4AF37',
                    borderWidth: 1,
                    callbacks: {
                        label: context => ' ' + formatoCOP.format(Number(context.raw || 0))
                    }
                }
            }
        }
    });

    if (selector) {
        selector.addEventListener('change', () => {
            const anioSeleccionado = selector.value;
            ingresosChart.data.datasets[0].data = normalizarDatos(datosPorAnio[anioSeleccionado]);
            ingresosChart.update();
        });
    }
})();

const ctx = document.getElementById('ingresosChart').getContext('2d');

const ingresosChart = new Chart(ctx, {
    type: 'line', 
    data: {
        labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
        datasets: [{
            label: 'Ingresos Mensuales',
            data: [1200000, 1500000, 1350000, 1800000, 1600000, 2100000],
            borderColor: '#D4AF37', // Dorado
            backgroundColor: 'rgba(212, 175, 55, 0.15)', // Dorado translúcido para el fondo
            borderWidth: 3,
            fill: true,
            pointBackgroundColor: '#111', // Puntos negros
            pointBorderColor: '#D4AF37',
            pointHoverBackgroundColor: '#D4AF37',
            pointHoverBorderColor: '#fff',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4 // Aplica un efecto "glassmorphism" o curvo a la línea
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            // Animación de aparición fluida
            y: {
                duration: 2000, // Duración en milisegundos (2 segundos)
                easing: 'easeOutBounce' // Efecto de rebote sutil al cargar
            },
            x: {
                duration: 1500,
                easing: 'easeInOutQuart' 
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(128, 128, 128, 0.2)', // Gris tenue para la cuadrícula
                    drawBorder: false
                },
                ticks: {
                    color: '#A9A9A9' // Texto de los meses en gris claro
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(128, 128, 128, 0.2)',
                    drawBorder: false
                },
                ticks: {
                    color: '#A9A9A9'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#D4AF37', // Título de la leyenda en dorado
                    font: {
                        family: "'Inter', sans-serif",
                        size: 14
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)', // Fondo oscuro para el tooltip
                titleColor: '#D4AF37',
                bodyColor: '#fff',
                borderColor: '#D4AF37',
                borderWidth: 1
            }
        }
    }
});
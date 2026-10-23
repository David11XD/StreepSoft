const valorInput = document.querySelector('input[type="number"]');
const fechaInput = document.querySelector('input[type="date"]');
const selects = document.querySelectorAll('.custom-select');
const conceptoInput = document.querySelector('input[type="text"]');

// Elementos donde se muestran los datos
const detalles = document.querySelectorAll('.info-pagos p');

const valorDetalle = detalles[0];
const fechaDetalle = detalles[1];
const metodoDetalle = detalles[2];
const conceptoDetalle = detalles[3];
const descuentoDetalle = detalles[4];

const totalDetalle = document.querySelector('.text-total p');


// =============================
// VALOR
// =============================

valorInput.addEventListener('input', () => {

    const valor = Number(valorInput.value);

    if (!valor) {
        valorDetalle.textContent = '';
        totalDetalle.textContent = '';
        return;
    }

    valorDetalle.textContent =
        '$' + valor.toLocaleString('es-CO') + ' cop';

    calcularTotal();
});


// =============================
// FECHA
// =============================

fechaInput.addEventListener('change', () => {

    if (!fechaInput.value) {
        fechaDetalle.textContent = '';
        return;
    }

    const partes = fechaInput.value.split('-');

    const año = partes[0];
    const mes = partes[1];
    const dia = partes[2];

    fechaDetalle.textContent =
        `${dia}/${mes}/${año}`;
});


// =============================
// MÉTODO DE PAGO
// =============================

selects[0].addEventListener('change', () => {

    const opcion = selects[0].options[
        selects[0].selectedIndex
    ];

    metodoDetalle.textContent = opcion.textContent;
});


// =============================
// CONCEPTO
// =============================

conceptoInput.addEventListener('input', () => {

    conceptoDetalle.textContent =
        conceptoInput.value.trim();
});


// =============================
// DESCUENTO
// =============================

selects[1].addEventListener('change', () => {

    const opcion = selects[1].options[
        selects[1].selectedIndex
    ];

    descuentoDetalle.textContent = opcion.textContent;

    calcularTotal();
});

// =============================
// CALCULAR TOTAL
// =============================

function calcularTotal() {

    const valor = Number(valorInput.value);

    if (!valor) {
        totalDetalle.textContent = '';
        return;
    }

    const descuento =
        selects[1].options[
            selects[1].selectedIndex
        ].textContent;

    let porcentaje = 0;

    if (descuento.includes('20%')) {
        porcentaje = 20;
    }

    if (descuento.includes('100%')) {
        porcentaje = 100;
    }

    const descuentoValor =
        valor * (porcentaje / 100);

    const total =
        valor - descuentoValor;

    totalDetalle.textContent =
        '$' + total.toLocaleString('es-CO') + ' cop';
}

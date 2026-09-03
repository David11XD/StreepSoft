// ===== Pestañas =====
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.dataset.tab;

        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-contenido').forEach(c => c.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});

// ===== Historial expandible =====
document.querySelectorAll('.btn-historial').forEach(btn => {
    btn.addEventListener('click', () => {
        const fila = btn.closest('.fila-pago');
        const id = fila.dataset.id;
        const filaHistorial = document.querySelector(`.fila-historial[data-historial-de="${id}"]`);
        filaHistorial.classList.toggle('oculto');
    });
});

// Modal Editar Pago 
const modalPago = document.getElementById('modalEditarPago');
const formEditarPago = document.getElementById('formEditarPago');
let pagoActual = null; // guarda los datos del botón que abrió el modal

// Abrir el modal, llenando "Registro actual" con los data-* del botón clickeado
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
        pagoActual = btn.dataset; // guarda TODOS los data-* de ese botón

        document.getElementById('modalSubtitulo').textContent =
            `${pagoActual.alumno} · ${pagoActual.conceptoTexto} · Registrado ${pagoActual.registrado}`;

        document.getElementById('actualValor').textContent = `$${Number(pagoActual.valor).toLocaleString('es-CO')}`;
        document.getElementById('actualFecha').textContent = formatearFecha(pagoActual.fecha);
        document.getElementById('actualMetodo').textContent = pagoActual.metodo;
        document.getElementById('actualConcepto').textContent = pagoActual.conceptoTexto;

        // Precarga los inputs con los valores actuales
        document.getElementById('inputValor').value = pagoActual.valor;
        document.getElementById('inputFecha').value = pagoActual.fecha;
        document.getElementById('inputMetodo').value = pagoActual.metodo;
        document.getElementById('inputConcepto').value = pagoActual.concepto;
        document.getElementById('inputMotivoTipo').value = '';
        document.getElementById('inputMotivoTexto').value = '';

        actualizarPreview(); // llena "Valores corregidos" con esos mismos datos iniciales

        cargarHistorialMini(pagoActual.id);

        modalPago.classList.remove('oculto');
    });
});

// Actualiza en vivo la caja "Valores corregidos" mientras el usuario escribe
function actualizarPreview() {
    const valor = document.getElementById('inputValor').value;
    const fecha = document.getElementById('inputFecha').value;
    const metodo = document.getElementById('inputMetodo').value;
    const concepto = document.getElementById('inputConcepto').value;

    const elValor = document.getElementById('previewValor');
    const elFecha = document.getElementById('previewFecha');
    const elMetodo = document.getElementById('previewMetodo');
    const elConcepto = document.getElementById('previewConcepto');

    elValor.textContent = valor ? `$${Number(valor).toLocaleString('es-CO')}` : '—';
    elFecha.textContent = fecha ? formatearFecha(fecha) : '—';
    elMetodo.textContent = metodo;
    elConcepto.textContent = concepto;

    // Compara cada campo contra su valor original y marca si cambió
    elValor.classList.toggle('valor-modificado', valor !== pagoActual.valor);
    elFecha.classList.toggle('valor-modificado', fecha !== pagoActual.fecha);
    elMetodo.classList.toggle('valor-modificado', metodo !== pagoActual.metodo);
    elConcepto.classList.toggle('valor-modificado', concepto !== pagoActual.concepto);
}

['inputValor', 'inputFecha', 'inputMetodo', 'inputConcepto'].forEach(id => {
    document.getElementById(id).addEventListener('input', actualizarPreview);
});

// Cerrar el modal
function cerrarModalPago() {
    modalPago.classList.add('oculto');
    formEditarPago.reset();
}
document.getElementById('cerrarModalPago').addEventListener('click', cerrarModalPago);
document.getElementById('cancelarModalPago').addEventListener('click', cerrarModalPago);

// Guardar (por ahora, sin backend — solo simula y cierra)
formEditarPago.addEventListener('submit', (e) => {
    e.preventDefault();

    const motivoTipo = document.getElementById('inputMotivoTipo').value;
    const motivoTexto = document.getElementById('inputMotivoTexto').value;

    if (!motivoTipo || !motivoTexto.trim()) {
        alert('Debes seleccionar un motivo y describir el cambio.');
        return;
    }

    // Aquí, más adelante, va la llamada fetch() al backend
    console.log('Guardando corrección para el pago ID:', pagoActual.id, {
        valor: document.getElementById('inputValor').value,
        fecha: document.getElementById('inputFecha').value,
        metodo: document.getElementById('inputMetodo').value,
        motivoTipo,
        motivoTexto
    });

    alert('Corrección guardada (simulado). El siguiente paso es conectar esto a la base de datos.');
    cerrarModalPago();
});

function formatearFecha(fechaISO) {
    const [anio, mes, dia] = fechaISO.split('-');
    return `${dia}/${mes}/${anio}`;
}

function cargarHistorialMini(id) {
    const contenedor = document.getElementById('historialMiniLista');
    contenedor.innerHTML = ''; // limpia lo que haya quedado de una apertura anterior

    const filaHistorial = document.querySelector(`.fila-historial[data-historial-de="${id}"]`);

    if (filaHistorial) {
        // Clona el contenido de .detalle-historial que ya existe en la tabla
        const detalle = filaHistorial.querySelector('.detalle-historial');
        const clon = detalle.cloneNode(true); // true = copia también su contenido interno
        contenedor.appendChild(clon);
    } else {
        contenedor.innerHTML = '<p class="sin-historial">Este pago no tiene ediciones previas.</p>';
    }
}
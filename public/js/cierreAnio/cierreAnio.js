document.addEventListener('DOMContentLoaded', () => {

    const toggleGeneracionAuto = document.getElementById('toggle-generacion-auto');
    const toggleEstadoTexto = document.getElementById('toggle-estado-texto');

    toggleGeneracionAuto.addEventListener('change', () => {
        if (toggleGeneracionAuto.checked) {
            toggleEstadoTexto.textContent = 'Activado';
            toggleEstadoTexto.style.color = '#2ecc71'; // verde
        } else {
            toggleEstadoTexto.textContent = 'Desactivado';
            toggleEstadoTexto.style.color = '#999'; // gris
        }
    });

    const modalCierre = document.getElementById('modalCierreAnio');
    const btnAbrirCierre = document.getElementById('btn-iniciar-cierre');
    const btnAtras = document.getElementById('btnWizardCierreAtras');
    const btnSiguiente = document.getElementById('btnWizardCierreSiguiente');
    const indicadores = modalCierre.querySelectorAll('.wizard-paso-indicador');
    const pasosContenido = modalCierre.querySelectorAll('.wizard-paso-contenido');
    const checkConfirmarCierre = document.getElementById('checkConfirmarCierre');

    let pasoActual = 1;

    function mostrarPaso(numeroPaso) {
        pasosContenido.forEach(paso => {
            paso.classList.toggle('oculto', paso.dataset.paso !== String(numeroPaso));
        });

        indicadores.forEach(indicador => {
            indicador.classList.toggle('activo', indicador.dataset.pasoIndicador === String(numeroPaso));
        });

        if (numeroPaso === 1) {
            btnAtras.textContent = 'Cancelar';
            btnSiguiente.textContent = 'Continuar';
        } else {
            btnAtras.textContent = 'Atrás';
            btnSiguiente.textContent = 'Confirmar cierre';
            checkConfirmarCierre.checked = false; // exige reconfirmar cada vez que se llega a este paso
        }

        btnSiguiente.disabled = (numeroPaso === 2 && !checkConfirmarCierre.checked);
        pasoActual = numeroPaso;
    }

    function abrirModalCierre() {
        mostrarPaso(1);
        modalCierre.classList.remove('oculto');
    }

    function cerrarModalCierre() {
        modalCierre.classList.add('oculto');
    }

    btnAbrirCierre.addEventListener('click', abrirModalCierre);

    document.querySelectorAll('[data-cerrar-modal="modalCierreAnio"]').forEach(elemento => {
        elemento.addEventListener('click', cerrarModalCierre);
    });

    btnAtras.addEventListener('click', () => {
        if (pasoActual === 1) {
            cerrarModalCierre();
        } else {
            mostrarPaso(1);
        }
    });

    btnSiguiente.addEventListener('click', () => {
        if (pasoActual === 1) {
            mostrarPaso(2);
        } else {
            // Fase de backend: aquí irá la petición real que cierra el año.
            cerrarModalCierre();
        }
    });

    checkConfirmarCierre.addEventListener('change', () => {
        btnSiguiente.disabled = !checkConfirmarCierre.checked;
    });

    const modalHistorial = document.getElementById('modalHistorialAnio');
    const historialTitulo = document.getElementById('historialAnioTitulo');
    const badgeCerrado = document.getElementById('badgeAnioCerrado');
    const badgeActivo = document.getElementById('badgeAnioActivo');

    function abrirHistorialAnio(anio, estado) {
        historialTitulo.textContent = `Historial de pagos — Año ${anio}`;

        badgeCerrado.classList.toggle('oculto', estado !== 'cerrado');
        badgeActivo.classList.toggle('oculto', estado !== 'activo');

        modalHistorial.classList.remove('oculto');
    }

    function cerrarHistorialAnio() {
        modalHistorial.classList.add('oculto');
    }

    // Punto de entrada 1 y 2: links de las tarjetas
    document.querySelectorAll('.tarjeta-ciclo-link').forEach(link => {
        link.addEventListener('click', (evento) => {
            evento.preventDefault();
            abrirHistorialAnio(link.dataset.anio, link.dataset.estado);
        });
    });

    // Punto de entrada 3: botón del panel de consulta
    const selectAnioHistorial = document.getElementById('select-anio-historial');
    const btnVerPagosAnio = document.getElementById('btn-ver-pagos-anio');

    btnVerPagosAnio.addEventListener('click', () => {
        const anioElegido = selectAnioHistorial.value;
        if (anioElegido === '') return;
        abrirHistorialAnio(anioElegido, 'cerrado');
    });

    // Cierre: todo lo que tenga data-cerrar-modal="modalHistorialAnio"
    document.querySelectorAll('[data-cerrar-modal="modalHistorialAnio"]').forEach(elemento => {
        elemento.addEventListener('click', cerrarHistorialAnio);
    });

});
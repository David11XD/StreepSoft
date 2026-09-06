document.addEventListener('DOMContentLoaded', () => {

    // 
    // 1. REFERENCIAS AL DOM
    // 
    // Buscamos una sola vez los elementos que vamos a usar
    // varias veces, y los guardamos en una constante.
    // Esto evita repetir document.getElementById(...) por todo
    // el archivo, y es más rápido para el navegador.

    const btnCambiarEstado = document.getElementById('btnCambiarEstado');
    const modalWizard = document.getElementById('modalWizard');


    // 
    // 2. SISTEMA GENÉRICO DE MODALES
    // 

    function abrirModal(modal) {
        modal.classList.remove('oculto');
    }

    function cerrarModal(modal) {
        modal.classList.add('oculto');
    }

    // Busca TODOS los botones que tengan el atributo data-cerrar-modal,
    // sin importar en qué modal estén ni cuántos existan.
    const botonesCerrar = document.querySelectorAll('[data-cerrar-modal]');

    botonesCerrar.forEach((boton) => {
        boton.addEventListener('click', () => {
            // dataset.cerrarModal lee el valor del atributo
            // data-cerrar-modal="modalWizard" -> "modalWizard"
            const idModal = boton.dataset.cerrarModal;
            const modal = document.getElementById(idModal);
            cerrarModal(modal);
        });
    });

    // 
    // 3. SELECCIÓN DE JUGADORES (checkboxes)
    // 

    const checkTodos = document.getElementById('checkTodos');
    const checksJugador = document.querySelectorAll('.check-jugador');
    const panelResultados = document.querySelector('.panel-resultados');
    const barraSeleccion = document.getElementById('barraSeleccion');
    const textoSeleccion = document.getElementById('textoSeleccion');
    const btnCancelarSeleccion = document.getElementById('btnCancelarSeleccion');

    // Le pregunta al DOM, en el momento, cuáles checkboxes están marcados.
    // No guardamos esto en una variable aparte: así nunca se desincroniza.
    function obtenerSeleccionados() {
        return Array.from(checksJugador).filter((check) => check.checked);
    }

    function actualizarBarraSeleccion() {
        const seleccionados = obtenerSeleccionados();
        const cantidad = seleccionados.length;

        textoSeleccion.textContent = `${cantidad} Jugadores seleccionados`;

        if (cantidad > 0) {
            barraSeleccion.classList.remove('oculto');
        } else {
            barraSeleccion.classList.add('oculto');
        }

        // "Seleccionar todos" debe verse marcado SOLO si
        // la cantidad de marcados es igual al total de filas.
        checkTodos.checked = cantidad > 0 && cantidad === checksJugador.length;
    }

    // Cada checkbox de fila avisa cuando cambia
    checksJugador.forEach((check) => {
        check.addEventListener('change', actualizarBarraSeleccion);
    });

    // El checkbox maestro marca/desmarca a todos los demás
    checkTodos.addEventListener('change', () => {
        checksJugador.forEach((check) => {
            check.checked = checkTodos.checked;
        });
        actualizarBarraSeleccion();
    });

    // "Cancelar selección" limpia todo
    btnCancelarSeleccion.addEventListener('click', () => {
        checksJugador.forEach((check) => {
            check.checked = false;
        });
        actualizarBarraSeleccion();
    });

    // 
    // 4. CONSTRUIR EL WIZARD CON LA SELECCIÓN REAL
    // 

    const wizardSubtitulo = document.querySelector('[data-paso="1"] .wizard-subtexto');
    const listaRevision = document.getElementById('listaRevision');
    const resumenCantidad = document.getElementById('resumenCantidad');

    // Lee, directamente de la fila de la tabla, los datos que
    // necesitamos mostrar en el wizard.
    function obtenerDatosDesdeFila(check) {
        const fila = check.closest('tr');
        return {
            id: check.dataset.id,
            iniciales: fila.querySelector('.avatar-iniciales').textContent.trim(),
            nombreCompleto: fila.querySelector('.celda-jugador-texto h3').textContent.trim(),
            documento: fila.querySelector('.celda-jugador-texto p').textContent.trim(),
            categoria: fila.children[2].textContent.trim(),
        };
    }

    // Crea el HTML de un solo item de la lista de revisión (Paso 2)
    function crearItemRevision(jugador) {
        const item = document.createElement('div');
        item.className = 'item-revision';
        item.dataset.id = jugador.id;

        item.innerHTML = `
            <div class="celda-jugador">
                <div class="avatar-iniciales">${jugador.iniciales}</div>
                <div class="celda-jugador-texto">
                    <h3>${jugador.nombreCompleto} · ${jugador.categoria}</h3>
                    <p>${jugador.documento}</p>
                </div>
            </div>
            <button type="button" class="btn-quitar" data-id="${jugador.id}">Quitar</button>
        `;

        return item;
    }

    function actualizarContadorWizard() {
        const cantidad = listaRevision.querySelectorAll('.item-revision').length;
        resumenCantidad.textContent = cantidad;
        wizardSubtitulo.textContent = `${cantidad} jugadores seleccionados recibirán el mismo cambio de estado.`;
    }

    // Se ejecuta CADA VEZ que se abre el wizard: limpia el Paso 2
    // y lo reconstruye desde cero con la selección actual.
    function construirWizardDesdeSeleccion() {
        listaRevision.innerHTML = '';

        obtenerSeleccionados().forEach((check) => {
            const jugador = obtenerDatosDesdeFila(check);
            listaRevision.appendChild(crearItemRevision(jugador));
        });

        actualizarContadorWizard();
    }

    // "Quitar" dentro de la lista de revisión:
    listaRevision.addEventListener('click', (evento) => {
        const boton = evento.target.closest('.btn-quitar');
        if (!boton) return; // el clic fue en otra parte del item, ignorar

        const id = boton.dataset.id;

        boton.closest('.item-revision').remove();
        actualizarContadorWizard();

        // Mantenemos sincronizada la tabla original: si lo quitas
        // del wizard, también se desmarca su checkbox de verdad.
        const checkOriginal = document.querySelector(`.check-jugador[data-id="${id}"]`);
        if (checkOriginal) {
            checkOriginal.checked = false;
            actualizarBarraSeleccion();
        }
    });

    // 
    // 5. NAVEGACIÓN DEL WIZARD (pasos 1 → 2 → 3)
    // 

    const pasosContenido = document.querySelectorAll('.wizard-paso-contenido');
    const pasosIndicador = document.querySelectorAll('.wizard-paso-indicador');
    const btnWizardAtras = document.getElementById('btnWizardAtras');
    const btnWizardSiguiente = document.getElementById('btnWizardSiguiente');
    const radiosEstado = document.querySelectorAll('input[name="nuevoEstado"]');
    const resumenEstado = document.getElementById('resumenEstado');
    const checkEntendido = document.getElementById('checkEntendido');
    const modalCambioCompletado = document.getElementById('modalCambioCompletado');

    let pasoActual = 1;

    // Deja visible SOLO el contenido del paso indicado,
    // y actualiza el indicador de círculos a juego.
    function mostrarPaso(numero) {
        pasoActual = numero;

        pasosContenido.forEach((contenido) => {
            const esEsePaso = Number(contenido.dataset.paso) === numero;
            contenido.classList.toggle('oculto', !esEsePaso);
        });

        pasosIndicador.forEach((indicador) => {
            const numeroIndicador = Number(indicador.dataset.pasoIndicador);
            indicador.classList.remove('activo', 'completado');
            if (numeroIndicador === numero) {
                indicador.classList.add('activo');
            } else if (numeroIndicador < numero) {
                indicador.classList.add('completado');
            }
        });

        actualizarFooterWizard();
    }

    // Decide qué dicen los botones del pie, y si "Confirmar" está bloqueado
    function actualizarFooterWizard() {
        if (pasoActual === 1) {
            btnWizardAtras.textContent = 'Cancelar';
            btnWizardSiguiente.textContent = 'Continuar';
            btnWizardSiguiente.disabled = false;
        } else if (pasoActual === 2) {
            btnWizardAtras.textContent = 'Atrás';
            btnWizardSiguiente.textContent = 'Continuar';
            btnWizardSiguiente.disabled = false;
        } else {
            btnWizardAtras.textContent = 'Atrás';
            btnWizardSiguiente.textContent = 'Confirmar cambio';
            btnWizardSiguiente.disabled = !checkEntendido.checked;
        }
    }

    // Refleja en el Paso 3 cuál radio quedó marcado en el Paso 1
    function actualizarResumenEstado() {
        const radioMarcado = document.querySelector('input[name="nuevoEstado"]:checked');
        const estado = radioMarcado.value;

        resumenEstado.textContent = estado;
        resumenEstado.classList.remove('resumen-estado-inactivo', 'resumen-estado-retirado');
        resumenEstado.classList.add(
            estado === 'Retirado' ? 'resumen-estado-retirado' : 'resumen-estado-inactivo'
        );
    }

    radiosEstado.forEach((radio) => {
        radio.addEventListener('change', actualizarResumenEstado);
    });

    // El checkbox de confirmación habilita/deshabilita "Confirmar cambio"
    checkEntendido.addEventListener('change', actualizarFooterWizard);

    btnWizardAtras.addEventListener('click', () => {
        if (pasoActual === 1) {
            cerrarModal(modalWizard);
        } else {
            mostrarPaso(pasoActual - 1);
        }
    });

    btnWizardSiguiente.addEventListener('click', () => {
        if (pasoActual < 3) {
            mostrarPaso(pasoActual + 1);
        } else {
            // Estábamos en el Paso 3: esto es la confirmación final
            cerrarModal(modalWizard);
            abrirModal(modalCambioCompletado);
            mostrarPaso(1); // deja el wizard listo para la próxima apertura
        }
    });

    // 
    // 6. MODAL DE DETALLE DEL HISTORIAL
    // 

    const botonesVerDetalle = document.querySelectorAll('.btn-ver-detalle');
    const detalleHistorialTitulo = document.getElementById('detalleHistorialTitulo');
    const detalleHistorialRealizadoPor = document.getElementById('detalleHistorialRealizadoPor');
    const detalleHistorialBadge = document.getElementById('detalleHistorialBadge');
    const detalleHistorialMotivo = document.getElementById('detalleHistorialMotivo');
    const detalleHistorialContadorTitulo = document.getElementById('detalleHistorialContadorTitulo');
    const detalleHistorialLista = document.getElementById('detalleHistorialLista');

    // NOTA: esto es data de PRUEBA, igual que $jugadores en el controlador.
    // En la fase de backend, esto se reemplaza por una consulta real
    // que traiga el detalle del cambio masivo según su id.
    const detalleHistorialData = {
        '1': {
            fecha: '15/07/2026',
            realizadoPor: 'David Aguirre',
            estado: 'Inactivo',
            motivoLargo: 'Jugadores con 3 o más meses sin registrar pago, sin respuesta del acudiente tras contacto telefónico.',
            jugadores: [
                { iniciales: 'SR', nombreCompleto: 'Santiago Rúa', categoria: 'Sub-14', documento: 'TI 1.038.221.554' },
                { iniciales: 'MG', nombreCompleto: 'Mariana Gil', categoria: 'Sub-12', documento: 'TI 1.041.887.220' },
                { iniciales: 'JR', nombreCompleto: 'Juan Restrepo', categoria: 'Sub-20', documento: 'CC 1.045.117.602' },
                { iniciales: 'AC', nombreCompleto: 'Andrés Castaño', categoria: 'Sub-14', documento: 'TI 1.055.221.586' },
                { iniciales: 'VF', nombreCompleto: 'Valentina Franco', categoria: 'Sub-12', documento: 'TI 1.092.330.981' },
            ],
        },
        '2': {
            fecha: '02/03/2026',
            realizadoPor: 'Valentina Aguirre',
            estado: 'Retirado',
            motivoLargo: 'Fin de temporada: los jugadores listados no renovaron matrícula para el nuevo ciclo.',
            jugadores: [
                { iniciales: 'JD', nombreCompleto: 'Jugador Demo 1', categoria: 'Sub-16', documento: 'TI 1.000.000.001' },
                { iniciales: 'JD', nombreCompleto: 'Jugador Demo 2', categoria: 'Sub-16', documento: 'TI 1.000.000.002' },
                { iniciales: 'JD', nombreCompleto: 'Jugador Demo 3', categoria: 'Sub-16', documento: 'TI 1.000.000.003' },
                { iniciales: 'JD', nombreCompleto: 'Jugador Demo 4', categoria: 'Sub-16', documento: 'TI 1.000.000.004' },
            ],
        },
    };

    function crearItemDetalle(jugador) {
        const item = document.createElement('div');
        item.className = 'item-revision';

        item.innerHTML = `
            <div class="celda-jugador">
                <div class="celda-jugador-texto">
                    <h3>${jugador.nombreCompleto} · ${jugador.categoria}</h3>
                    <p>${jugador.documento}</p>
                </div>
            </div>
            <button type="button" class="btn-reactivar">Reactivar</button>
        `;

        return item;
    }

    function abrirDetalleHistorial(cambioId) {
        const datos = detalleHistorialData[cambioId];
        if (!datos) return; // por seguridad, si el id no existe en nuestra data de prueba

        detalleHistorialTitulo.textContent = `Historial de cambios masivos — ${datos.fecha}`;
        detalleHistorialRealizadoPor.textContent = datos.realizadoPor;
        detalleHistorialMotivo.textContent = datos.motivoLargo;

        detalleHistorialBadge.textContent = datos.estado;
        detalleHistorialBadge.className = `badge-estado badge-${datos.estado.toLowerCase()}`;

        detalleHistorialContadorTitulo.textContent = `Jugadores afectados (${datos.jugadores.length})`;

        detalleHistorialLista.innerHTML = '';
        datos.jugadores.forEach((jugador) => {
            detalleHistorialLista.appendChild(crearItemDetalle(jugador));
        });

        const modalDetalleHistorial = document.getElementById('modalDetalleHistorial');
        abrirModal(modalDetalleHistorial);
    }

    botonesVerDetalle.forEach((boton) => {
        boton.addEventListener('click', () => {
            abrirDetalleHistorial(boton.dataset.cambioId);
        });
    });


    // 
    // ABRIR EL WIZARD 
    // 
    btnCambiarEstado.addEventListener('click', () => {
        construirWizardDesdeSeleccion();
        checkEntendido.checked = false;
        mostrarPaso(1);
        actualizarResumenEstado();
        abrirModal(modalWizard);
    });

});
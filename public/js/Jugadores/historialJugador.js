/**
 * Historial de pagos: filtro por fechas y paginación del lado del cliente.
 * Vista esperada:
 *  - tabla: #tablaJugadores
 *  - buscador: #buscarJugador
 *  - contador: #infoRegistros
 *  - selector: #cantidadRegistros
 *  - navegación: #btnAnterior, #btnSiguiente, #numerosPaginas
 *
 * El filtro revisa únicamente las columnas 0 (fecha límite) y 1 (fecha de pago).
 */
document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.getElementById('tablaJugadores');
    const buscador = document.getElementById('buscarJugador');
    const infoRegistros = document.getElementById('infoRegistros');
    const selectorCantidad = document.getElementById('cantidadRegistros');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const numerosPaginas = document.getElementById('numerosPaginas');

    if (!tabla || !tabla.tBodies.length) {
        console.error('Historial: no se encontró #tablaJugadores o su tbody.');
        return;
    }

    const tbody = tabla.tBodies[0];
    const filas = Array.from(tbody.querySelectorAll('tr')).filter((fila) =>
        !fila.classList.contains('fila-vacia') &&
        !fila.classList.contains('fila-sin-resultados-js') &&
        fila.cells.length > 0
    );
    const filaVaciaPHP = tbody.querySelector('tr.fila-vacia');

    // Modal único de edición: reutiliza las clases visuales del modal de registrar pago.
    const modalEditarHistorial = document.getElementById('modalEditarHistorial');
    const iframeEditarHistorial = document.getElementById('iframeEditarHistorial');
    const botonCerrarModalEditar = document.getElementById('cerrarModalEditarHistorial');

    function abrirModalEdicion(idHistorial) {
        if (!modalEditarHistorial || !iframeEditarHistorial) {
            console.error('No se encontró el modal de edición del historial.');
            return;
        }

        iframeEditarHistorial.src = `/streepsoft/jugadores/deudas/historial/editar/${idHistorial}`;
        modalEditarHistorial.classList.add('activo');
        modalEditarHistorial.setAttribute('aria-hidden', 'false');
    }

    function cerrarModalEdicion() {
        if (!modalEditarHistorial || !iframeEditarHistorial) return;

        modalEditarHistorial.classList.remove('activo');
        modalEditarHistorial.setAttribute('aria-hidden', 'true');
        iframeEditarHistorial.src = 'about:blank';
    }

    botonCerrarModalEditar?.addEventListener('click', cerrarModalEdicion);

    modalEditarHistorial?.addEventListener('click', (event) => {
        if (event.target === modalEditarHistorial) cerrarModalEdicion();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modalEditarHistorial?.classList.contains('activo')) {
            cerrarModalEdicion();
        }
    });

    // El formulario dentro del iframe avisa al guardar o cancelar.
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return;
        if (!iframeEditarHistorial || event.source !== iframeEditarHistorial.contentWindow) return;

        if (event.data === 'cerrarModalPago') {
            cerrarModalEdicion();
            return;
        }

        if (event.data && event.data.tipo === 'pago-historial-actualizado') {
            cerrarModalEdicion();
            window.location.reload();
        }
    });

    const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];

    let paginaActual = 1;
    let filasFiltradas = [...filas];
    let registrosPorPagina = leerCantidad();

    if (buscador) {
        buscador.placeholder = 'Buscar fecha';
        buscador.setAttribute('aria-label', 'Buscar por fecha límite o fecha de pago');
        buscador.setAttribute('inputmode', 'numeric');
    }

    if (numerosPaginas) {
        numerosPaginas.style.display = 'flex';
        numerosPaginas.style.alignItems = 'center';
        numerosPaginas.style.gap = '6px';
    }

    function leerCantidad() {
        const valor = Number.parseInt(selectorCantidad?.value || '5', 10);
        return Number.isFinite(valor) && valor > 0 ? valor : 5;
    }

    function normalizarTexto(valor) {
        return String(valor ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('es')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function soloDigitos(valor) {
        return String(valor ?? '').replace(/\D/g, '');
    }

    function variantesFecha(valor) {
        const texto = String(valor ?? '').trim();
        const variantes = [texto];

        // Formato habitual de la vista: dd-mm-aaaa; también admite dd/mm/aaaa y dd.mm.aaaa.
        let coincidencia = texto.match(/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})$/);
        if (coincidencia) {
            const dia = Number(coincidencia[1]);
            const mes = Number(coincidencia[2]);
            const anio = coincidencia[3];
            if (mes >= 1 && mes <= 12 && dia >= 1 && dia <= 31) {
                const dd = String(dia).padStart(2, '0');
                const mm = String(mes).padStart(2, '0');
                variantes.push(`${dd}/${mm}/${anio}`, `${dd}-${mm}-${anio}`, `${dd}.${mm}.${anio}`);
                variantes.push(`${meses[mes - 1]} ${anio}`, `${dd} ${meses[mes - 1]} ${anio}`);
            }
        }

        // También contempla una fecha SQL ISO: aaaa-mm-dd.
        coincidencia = texto.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (coincidencia) {
            const anio = coincidencia[1];
            const mes = Number(coincidencia[2]);
            const dia = Number(coincidencia[3]);
            if (mes >= 1 && mes <= 12 && dia >= 1 && dia <= 31) {
                const dd = String(dia).padStart(2, '0');
                const mm = String(mes).padStart(2, '0');
                variantes.push(`${dd}/${mm}/${anio}`, `${dd}-${mm}-${anio}`, `${meses[mes - 1]} ${anio}`);
            }
        }

        return variantes;
    }

    function coincideFechaCelda(textoFecha, termino) {
        const fechaNormalizada = normalizarTexto(textoFecha);
        const terminoNormalizado = normalizarTexto(termino);
        if (!terminoNormalizado) return true;

        const terminoDigitos = soloDigitos(termino);
        const variantes = variantesFecha(textoFecha);

        return variantes.some((variante) => {
            const varianteNormalizada = normalizarTexto(variante);
            if (varianteNormalizada.includes(terminoNormalizado)) return true;

            // Permite escribir 23/09/2026 aunque la celda muestre 23-09-2026.
            const varianteDigitos = soloDigitos(variante);
            return terminoDigitos.length > 0 && varianteDigitos.includes(terminoDigitos);
        }) || fechaNormalizada.includes(terminoNormalizado);
    }

    function filaCoincide(fila, termino) {
        if (!termino) return true;
        const fechaLimite = fila.cells[0]?.textContent ?? '';
        const fechaPago = fila.cells[1]?.textContent ?? '';
        return coincideFechaCelda(fechaLimite, termino) || coincideFechaCelda(fechaPago, termino);
    }

    function obtenerFilaMensaje() {
        let fila = tbody.querySelector('tr.fila-sin-resultados-js');
        if (!fila) {
            fila = document.createElement('tr');
            fila.className = 'fila-sin-resultados-js';
            const celda = document.createElement('td');
            celda.colSpan = 8;
            celda.textContent = 'No se encontraron pagos para esa fecha.';
            fila.appendChild(celda);
            tbody.appendChild(fila);
        }
        return fila;
    }

    function actualizarContador(inicio, fin, total) {
        if (!infoRegistros) return;
        let texto = infoRegistros.querySelector('p');
        if (!texto) {
            texto = document.createElement('p');
            infoRegistros.replaceChildren(texto);
        }
        texto.textContent = total === 0
            ? 'Mostrando 0 - 0 de 0 pagos'
            : `Mostrando ${inicio} - ${fin} de ${total} pagos`;
    }

    function crearNumerosPaginas(totalPaginas) {
        if (!numerosPaginas) return;
        numerosPaginas.replaceChildren();

        for (let numero = 1; numero <= totalPaginas; numero += 1) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'pagina-numero';
            boton.textContent = String(numero);
            boton.setAttribute('aria-label', `Ir a la página ${numero}`);
            boton.setAttribute('aria-current', numero === paginaActual ? 'page' : 'false');

            // Garantiza contraste visible con el fondo oscuro de la paginación.
            boton.style.minWidth = '30px';
            boton.style.height = '30px';
            boton.style.padding = '0 7px';
            boton.style.border = '1px solid #555';
            boton.style.borderRadius = '4px';
            boton.style.cursor = 'pointer';
            boton.style.fontSize = '13px';
            boton.style.color = numero === paginaActual ? '#171717' : '#eeeeee';
            boton.style.background = numero === paginaActual ? '#d6a000' : 'transparent';

            boton.addEventListener('click', () => {
                paginaActual = numero;
                renderizar();
            });
            numerosPaginas.appendChild(boton);
        }
    }

    function renderizar() {
        const total = filasFiltradas.length;
        const totalPaginas = Math.ceil(total / registrosPorPagina);
        paginaActual = totalPaginas === 0 ? 1 : Math.min(Math.max(paginaActual, 1), totalPaginas);

        const inicioIndice = (paginaActual - 1) * registrosPorPagina;
        const finIndice = Math.min(inicioIndice + registrosPorPagina, total);
        const filasPagina = new Set(filasFiltradas.slice(inicioIndice, finIndice));

        filas.forEach((fila) => {
            fila.hidden = !filasPagina.has(fila);
        });

        if (filaVaciaPHP) filaVaciaPHP.hidden = filas.length > 0;
        const filaMensaje = obtenerFilaMensaje();
        filaMensaje.hidden = total !== 0 || filas.length === 0;

        actualizarContador(total === 0 ? 0 : inicioIndice + 1, finIndice, total);
        crearNumerosPaginas(totalPaginas);

        if (btnAnterior) btnAnterior.disabled = total === 0 || paginaActual <= 1;
        if (btnSiguiente) btnSiguiente.disabled = total === 0 || paginaActual >= totalPaginas;
    }

    function aplicarFiltro() {
        const termino = buscador?.value ?? '';
        filasFiltradas = filas.filter((fila) => filaCoincide(fila, termino));
        paginaActual = 1;
        renderizar();
    }

    buscador?.addEventListener('input', aplicarFiltro);

    selectorCantidad?.addEventListener('change', () => {
        registrosPorPagina = leerCantidad();
        paginaActual = 1;
        renderizar();
    });

    btnAnterior?.addEventListener('click', () => {
        if (paginaActual > 1) {
            paginaActual -= 1;
            renderizar();
        }
    });

    btnSiguiente?.addEventListener('click', () => {
        const totalPaginas = Math.ceil(filasFiltradas.length / registrosPorPagina);
        if (paginaActual < totalPaginas) {
            paginaActual += 1;
            renderizar();
        }
    });



    // Menú de acciones y edición del pago histórico seleccionado.
    tbody.addEventListener('click', (event) => {
        const botonMenu = event.target.closest('.btn-menu-accion');
        if (botonMenu) {
            const contenedor = botonMenu.closest('.table-accion');
            const menu = contenedor?.querySelector('.menu-acciones');
            if (!menu) return;

            document.querySelectorAll('.menu-acciones.mostrar').forEach((abierto) => {
                if (abierto !== menu) abierto.classList.remove('mostrar');
            });
            menu.classList.toggle('mostrar');
            return;
        }

        const botonEditar = event.target.closest('.btn-editar-historial');
        if (botonEditar) {
            const idHistorial = Number.parseInt(botonEditar.dataset.idHistorial || '', 10);
            if (!Number.isInteger(idHistorial) || idHistorial <= 0) {
                console.error('No se encontró un ID de historial válido para editar.');
                return;
            }
            const menu = botonEditar.closest('.menu-acciones');
            menu?.classList.remove('mostrar');
            abrirModalEdicion(idHistorial);
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.table-accion')) {
            document.querySelectorAll('.menu-acciones.mostrar').forEach((menu) => {
                menu.classList.remove('mostrar');
            });
        }
    });

    // Si no hay filas cargadas desde PHP, conserva el mensaje vacío de la vista.
    renderizar();
});

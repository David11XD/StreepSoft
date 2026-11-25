/*
 * Navegación del modal principal de desactivación y sus vistas internas.
 * El backend valida y registra los cambios; el iframe solo coordina la interfaz.
 */
(function () {
    'use strict';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    // ------------------- Página principal -------------------
    function iniciarPaginaPrincipal() {
        const modal = $('#modalRegistro');
        const iframe = $('#iframeRegistro');
        const tituloModal = $('#modalRegistroTitulo');
        if (!modal || !iframe) return;

        const vistasPermitidas = new Map([
            ['desactivar', '/streepsoft/jugadores/desactivacion/desactivar'],
            ['confirmacion', '/streepsoft/jugadores/desactivacion/confirmacion'],
            ['historial', '/streepsoft/jugadores/desactivacion/historial'],
            ['inactivos', '/streepsoft/jugadores/inactivos']
        ]);

        function abrirVista(ruta, titulo, parametros = {}) {
            const rutaReal = vistasPermitidas.get(String(ruta));
            if (!rutaReal) {
                console.error('Vista de desactivación no permitida:', ruta);
                return;
            }
            const url = new URL(rutaReal, window.location.origin);
            Object.entries(parametros).forEach(([clave, valor]) => {
                if (valor !== undefined && valor !== null) {
                    url.searchParams.set(clave, typeof valor === 'string' ? valor : JSON.stringify(valor));
                }
            });
            if (tituloModal) tituloModal.textContent = titulo || 'Gestión de desactivación';
            modal.classList.add('activo');
            modal.setAttribute('aria-hidden', 'false');
            iframe.src = url.href;
        }

        function cerrarModal() {
            modal.classList.remove('activo');
            modal.setAttribute('aria-hidden', 'true');
            iframe.src = 'about:blank';
        }

        $('#cerrarRegistro')?.addEventListener('click', cerrarModal);
        modal.addEventListener('click', (evento) => {
            if (evento.target === modal) cerrarModal();
        });
        document.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape' && modal.classList.contains('activo')) cerrarModal();
        });

        window.addEventListener('message', (evento) => {
            if (evento.source !== iframe.contentWindow) return;
            const mensaje = evento.data;
            if (!mensaje || typeof mensaje !== 'object') return;
            if (mensaje.tipo === 'cerrar-modal-principal') {
                const recargar = mensaje.recargar === true;
                cerrarModal();
                if (recargar) window.location.reload();
                return;
            }
            if (mensaje.tipo === 'abrir-vista' && typeof mensaje.ruta === 'string') {
                abrirVista(mensaje.ruta, mensaje.titulo, mensaje.parametros || {});
            }
        });

        // Paginación independiente para cada tabla. Las filas que no coinciden
        // con los filtros siguen ocultas aunque cambie la página.
        function crearPaginador(config) {
            const filas = config.filas;
            const info = $(config.info);
            const contenedorNumeros = $(config.numeros);
            const anterior = $(config.anterior);
            const siguiente = $(config.siguiente);
            const selectorCantidad = $(config.cantidad);
            const estadoVacio = config.estadoVacio ? $(config.estadoVacio) : null;

            let filasFiltradas = [...filas];
            let paginaActual = 1;
            let porPagina = Number(selectorCantidad?.value || 5);
            let filasPagina = [];

            function crearBotonNumero(numero, activo = false) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = `pagina-numero${activo ? ' activo' : ''}`;
                boton.textContent = String(numero);
                boton.setAttribute('aria-label', `Ir a la página ${numero}`);
                if (activo) boton.setAttribute('aria-current', 'page');
                boton.addEventListener('click', () => {
                    paginaActual = numero;
                    renderizar();
                });
                return boton;
            }

            function renderizarNumeros(totalPaginas, totalRegistros) {
                if (!contenedorNumeros) return;
                contenedorNumeros.innerHTML = '';

                // Si hay registros, siempre mostramos al menos el número 1.
                // Al filtrar puede quedar una sola página: la paginación debe
                // seguir visible aunque Anterior/Siguiente estén deshabilitados.
                if (totalRegistros === 0) return;

                let paginas = [];
                if (totalPaginas <= 7) {
                    paginas = Array.from({ length: totalPaginas }, (_, i) => i + 1);
                } else {
                    const conjunto = new Set([1, totalPaginas, paginaActual, paginaActual - 1, paginaActual + 1]);
                    if (paginaActual <= 3) [2, 3, 4].forEach(n => conjunto.add(n));
                    if (paginaActual >= totalPaginas - 2) [totalPaginas - 3, totalPaginas - 2, totalPaginas - 1].forEach(n => conjunto.add(n));
                    paginas = [...conjunto].filter(n => n >= 1 && n <= totalPaginas).sort((a, b) => a - b);
                }

                let anteriorNumero = 0;
                paginas.forEach((numero) => {
                    if (anteriorNumero && numero - anteriorNumero > 1) {
                        const puntos = document.createElement('span');
                        puntos.className = 'pagina-puntos';
                        puntos.textContent = '…';
                        contenedorNumeros.appendChild(puntos);
                    }
                    contenedorNumeros.appendChild(crearBotonNumero(numero, numero === paginaActual));
                    anteriorNumero = numero;
                });
            }

            function renderizar() {
                const total = filasFiltradas.length;
                const totalPaginas = Math.max(1, Math.ceil(total / porPagina));
                paginaActual = Math.min(Math.max(1, paginaActual), totalPaginas);
                const inicio = (paginaActual - 1) * porPagina;
                const fin = Math.min(inicio + porPagina, total);
                filasPagina = filasFiltradas.slice(inicio, fin);

                filas.forEach(fila => { fila.hidden = true; });
                filasPagina.forEach(fila => { fila.hidden = false; });
                if (estadoVacio) estadoVacio.hidden = total !== 0;

                if (info) {
                    const desde = total === 0 ? 0 : inicio + 1;
                    info.textContent = `Mostrando ${desde} - ${fin} de ${total} ${config.etiqueta}`;
                }
                if (anterior) anterior.disabled = paginaActual <= 1 || total === 0;
                if (siguiente) siguiente.disabled = paginaActual >= totalPaginas || total === 0;
                renderizarNumeros(totalPaginas, total);
                if (typeof config.alRenderizar === 'function') config.alRenderizar();
            }

            anterior?.addEventListener('click', () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    renderizar();
                }
            });
            siguiente?.addEventListener('click', () => {
                const totalPaginas = Math.max(1, Math.ceil(filasFiltradas.length / porPagina));
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    renderizar();
                }
            });
            selectorCantidad?.addEventListener('change', () => {
                const valor = Number(selectorCantidad.value);
                porPagina = [5, 10, 15, 20].includes(valor) ? valor : 5;
                paginaActual = 1;
                renderizar();
            });

            renderizar();
            return {
                establecerFilas(lista, reiniciar = true) {
                    filasFiltradas = Array.isArray(lista) ? lista : [];
                    if (reiniciar) paginaActual = 1;
                    renderizar();
                },
                incluyeEnFiltro(fila) {
                    return filasFiltradas.includes(fila);
                },
                obtenerFilasPagina() {
                    return [...filasPagina];
                }
            };
        }

        const filasJugadores = $$('#tablaDesactivacion tbody tr[data-id]');
        const filasHistorial = $$('#tablaHistorial tbody tr[data-row-historial]');
        let paginadorJugadores = null;
        paginadorJugadores = crearPaginador({
            filas: filasJugadores,
            info: '#infoRegistrosJugadores',
            numeros: '#numerosPaginasJugadores',
            anterior: '#btnAnteriorJugadores',
            siguiente: '#btnSiguienteJugadores',
            cantidad: '#cantidadRegistrosJugadores',
            estadoVacio: '#estadoVacioJugadores',
            etiqueta: 'Jugadores',
            alRenderizar: () => {
                // Al cambiar de página, actualizar el estado de Seleccionar todos.
                if (paginadorJugadores) actualizarSeleccion();
            }
        });
        const paginadorHistorial = crearPaginador({
            filas: filasHistorial,
            info: '#infoRegistrosHistorial',
            numeros: '#numerosPaginasHistorial',
            anterior: '#btnAnteriorHistorial',
            siguiente: '#btnSiguienteHistorial',
            cantidad: '#cantidadRegistrosHistorial',
            estadoVacio: '#estadoVacioHistorial',
            etiqueta: 'Cambios'
        });

        const checks = $$('.check-jugador');
        const checkTodos = $('#checkTodos');
        const barra = $('#barraSeleccion');
        const textoSeleccion = $('#textoSeleccion');
        const btnCambiarEstado = $('#btnCambiarEstado');

        function seleccionados() {
            // Incluye selecciones hechas en otras páginas, pero descarta filas
            // que hayan dejado de coincidir con los filtros actuales.
            return checks.filter((check) => check.checked && paginadorJugadores.incluyeEnFiltro(check.closest('tr')));
        }

        function actualizarSeleccion() {
            const cantidad = seleccionados().length;
            if (textoSeleccion) textoSeleccion.textContent = `${cantidad} jugador${cantidad === 1 ? '' : 'es'} seleccionado${cantidad === 1 ? '' : 's'}`;
            barra?.classList.toggle('oculto', cantidad === 0);
            if (checkTodos) {
                const visibles = paginadorJugadores.obtenerFilasPagina()
                    .map(fila => $('.check-jugador', fila))
                    .filter(check => check && !check.disabled);
                checkTodos.checked = visibles.length > 0 && visibles.every(check => check.checked);
                checkTodos.indeterminate = visibles.some(check => check.checked) && !checkTodos.checked;
            }
        }

        checks.forEach((check) => check.addEventListener('change', actualizarSeleccion));
        checkTodos?.addEventListener('change', () => {
            paginadorJugadores.obtenerFilasPagina().forEach((fila) => {
                const check = $('.check-jugador', fila);
                if (check && !check.disabled) check.checked = checkTodos.checked;
            });
            actualizarSeleccion();
        });
        $('#btnCancelarSeleccion')?.addEventListener('click', () => {
            checks.forEach((check) => { check.checked = false; });
            actualizarSeleccion();
        });

        $('#btnVerInactivos')?.addEventListener('click', () => {
            abrirVista('inactivos', 'Jugadores inactivos');
        });

        btnCambiarEstado?.addEventListener('click', () => {
            const jugadores = seleccionados().map((check) => {
                const fila = check.closest('tr');
                return {
                    id: check.dataset.id,
                    iniciales: $('.avatar-iniciales', fila)?.textContent.trim() || '',
                    nombreCompleto: $('.celda-jugador-texto h3', fila)?.textContent.trim() || '',
                    documento: $('.celda-jugador-texto p', fila)?.textContent.trim() || '',
                    categoria: fila.children[3]?.textContent.trim() || ''
                };
            });
            if (!jugadores.length) {
                window.alert('Selecciona al menos un jugador para continuar.');
                return;
            }
            abrirVista('desactivar', 'Cambiar estado a varios jugadores', { jugadores: jugadores });
        });

        let filtroMesesActivo = 'todos';
        $('#buscarJugador')?.addEventListener('input', filtrarTabla);
        $('#filtroCategoria')?.addEventListener('change', filtrarTabla);
        $('#filtroEstado')?.addEventListener('change', filtrarTabla);
        $$('.btn-filtro-rapido').forEach((boton) => boton.addEventListener('click', () => {
            $$('.btn-filtro-rapido').forEach((otro) => otro.classList.toggle('activo', otro === boton));
            filtroMesesActivo = boton.dataset.meses || 'todos';
            filtrarTabla();
        }));

        // Permite reconocer fechas escritas como 23/09/2026, 23-09-2026,
        // 23.09.2026 o incluso una parte como 23/09 o 09/2026.
        function obtenerDigitosFecha(valor) {
            return String(valor ?? '').replace(/\D/g, '');
        }

        // Convierte fechas de la base de datos (YYYY-MM-DD o datetime) y
        // fechas visibles (DD/MM/YYYY) a DDMMYYYY para comparar con el buscador.
        function normalizarFechaParaBusqueda(valor) {
            const texto = String(valor ?? '').trim();
            if (!texto) return '';

            let coincidencia = texto.match(/^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})/);
            if (coincidencia) {
                const dia = coincidencia[1].padStart(2, '0');
                const mes = coincidencia[2].padStart(2, '0');
                const anio = coincidencia[3];
                return `${dia}${mes}${anio}`;
            }

            coincidencia = texto.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
            if (coincidencia) {
                const anio = coincidencia[1];
                const mes = coincidencia[2].padStart(2, '0');
                const dia = coincidencia[3].padStart(2, '0');
                return `${dia}${mes}${anio}`;
            }

            return obtenerDigitosFecha(texto);
        }

        function esBusquedaDeFecha(valor) {
            const texto = String(valor ?? '').trim();
            const digitos = obtenerDigitosFecha(texto);
            return texto !== '' && /^[\d\s/.:-]+$/.test(texto)
                && digitos.length >= 4 && digitos.length <= 8;
        }

        function coincideFecha(fecha, digitosBuscados) {
            if (!digitosBuscados) return false;
            const fechaNormalizada = normalizarFechaParaBusqueda(fecha);
            return fechaNormalizada.includes(digitosBuscados);
        }

        function filtrarTabla() {
            const textoOriginal = ($('#buscarJugador')?.value || '').trim();
            const texto = textoOriginal.toLocaleLowerCase('es');
            const busquedaFecha = esBusquedaDeFecha(textoOriginal);
            const digitosFecha = obtenerDigitosFecha(textoOriginal);
            const filtroMeses = filtroMesesActivo;
            const mesesMinimos = Number(filtroMeses);
            const categoria = $('#filtroCategoria')?.value || 'todo';
            const estado = $('#filtroEstado')?.value || 'todo';
            const coincidencias = [];

            filasJugadores.forEach((fila) => {
                const contenido = fila.textContent.toLocaleLowerCase('es');
                const fechaFila = fila.lastElementChild?.textContent || '';
                const coincideTexto = texto === ''
                    || contenido.includes(texto)
                    || (busquedaFecha && coincideFecha(fechaFila, digitosFecha));
                const meses = Number(fila.dataset.meses || 0);
                const categoriaFila = fila.children[3]?.textContent.trim() || '';
                const cumpleMeses = filtroMeses === 'todos' || meses >= mesesMinimos;
                const estadoFila = (fila.dataset.estado || '').toLowerCase();
                const cumpleEstado = estado === 'todo' || estadoFila === estado;
                const mostrar = coincideTexto && cumpleMeses && cumpleEstado
                    && (categoria === 'todo' || categoriaFila === categoria);

                if (mostrar) {
                    coincidencias.push(fila);
                } else {
                    const check = $('.check-jugador', fila);
                    if (check) check.checked = false;
                }
            });

            // La misma búsqueda por fecha también filtra el historial de cambios.
            // Si se busca por nombre u otro texto, el historial permanece completo.
            const coincidenciasHistorial = busquedaFecha
                ? filasHistorial.filter((fila) => {
                    const fecha = fila.dataset.fecha || $('td:first-child', fila)?.textContent || '';
                    return coincideFecha(fecha, digitosFecha);
                })
                : filasHistorial;

            const contador = $('#contadorResultados');
            if (contador) contador.textContent = `${coincidencias.length} jugador${coincidencias.length === 1 ? '' : 'es'} coincide${coincidencias.length === 1 ? '' : 'n'} con los filtros`;
            paginadorJugadores.establecerFilas(coincidencias, true);
            paginadorHistorial.establecerFilas(coincidenciasHistorial, true);
            actualizarSeleccion();
        }

        $$('.btn-ver-detalle').forEach((boton) => {
            boton.addEventListener('click', () => {
                abrirVista('historial', 'Detalle del historial', { id: boton.dataset.cambioId || '0' });
            });
        });

        // Mostrar todos los jugadores al abrir; el filtro de mora es opcional.
        filtrarTabla();
    }

    // ------------------- Vista de registro / wizard -------------------
    function iniciarVistaRegistro() {
        const listaRevision = $('#listaRevision');
        const btnSiguiente = $('#btnWizardSiguiente');
        if (!listaRevision || !btnSiguiente) return;

        const params = new URLSearchParams(window.location.search);
        let jugadores = [];
        try {
            jugadores = JSON.parse(params.get('jugadores') || '[]');
            if (!Array.isArray(jugadores)) jugadores = [];
        } catch (_) { jugadores = []; }
        // No inventar un jugador de respaldo: el backend debe recibir solo IDs seleccionados.

        const subtitulo = $('#wizardSubtitulo');
        const pasosContenido = $$('.wizard-paso-contenido');
        const pasosIndicador = $$('.wizard-paso-indicador');
        const btnAtras = $('#btnWizardAtras');
        const checkEntendido = $('#checkEntendido');
        const radiosEstado = $$('input[name="nuevoEstado"]');
        let pasoActual = 1;

        function escapar(texto) {
            return String(texto ?? '').replace(/[&<>"']/g, (caracter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[caracter]));
        }
        function pintarJugadores() {
            listaRevision.innerHTML = '';
            jugadores.forEach((jugador) => {
                const item = document.createElement('div');
                item.className = 'item-revision';
                item.dataset.id = jugador.id;
                item.innerHTML = `<div class="celda-jugador"><div class="avatar-iniciales">${escapar(jugador.iniciales)}</div><div class="celda-jugador-texto"><h3>${escapar(jugador.nombreCompleto)} · ${escapar(jugador.categoria)}</h3><p>${escapar(jugador.documento)}</p></div></div><button type="button" class="btn-quitar" data-id="${escapar(jugador.id)}">Quitar</button>`;
                listaRevision.appendChild(item);
            });
            actualizarCantidad();
        }
        function actualizarCantidad() {
            if (subtitulo) subtitulo.textContent = `${jugadores.length} jugador${jugadores.length === 1 ? '' : 'es'} seleccionados recibirán el mismo cambio.`;
            $('#resumenCantidad').textContent = String(jugadores.length);
        }
        function estadoSeleccionado() {
            return $('input[name="nuevoEstado"]:checked')?.value || 'Inactivo';
        }
        function actualizarResumen() {
            const estado = estadoSeleccionado();
            const elemento = $('#resumenEstado');
            elemento.textContent = estado;
            elemento.classList.toggle('resumen-estado-retirado', estado === 'Retirado');
            elemento.classList.toggle('resumen-estado-activo', estado === 'Activo');
            elemento.classList.toggle('resumen-estado-inactivo', estado === 'Inactivo');
            $('#resumenMotivo').textContent = $('#motivoCambio').value || 'Sin motivo seleccionado';
        }
        function mostrarPaso(numero) {
            pasoActual = numero;
            pasosContenido.forEach((paso) => paso.classList.toggle('oculto', Number(paso.dataset.paso) !== numero));
            pasosIndicador.forEach((indicador) => {
                const n = Number(indicador.dataset.pasoIndicador);
                indicador.classList.toggle('activo', n === numero);
                indicador.classList.toggle('completado', n < numero);
            });
            btnAtras.textContent = numero === 1 ? 'Cancelar' : 'Atrás';
            btnSiguiente.textContent = numero === 3 ? 'Confirmar cambio' : 'Continuar';
            btnSiguiente.disabled = (numero === 3 && !checkEntendido.checked) || jugadores.length === 0;
            if (numero === 3) actualizarResumen();
        }
        listaRevision.addEventListener('click', (evento) => {
            const boton = evento.target.closest('.btn-quitar');
            if (!boton) return;
            jugadores = jugadores.filter((jugador) => String(jugador.id) !== String(boton.dataset.id));
            pintarJugadores();
            btnSiguiente.disabled = jugadores.length === 0;
        });
        radiosEstado.forEach((radio) => radio.addEventListener('change', actualizarResumen));
        $('#motivoCambio').addEventListener('change', actualizarResumen);
        checkEntendido.addEventListener('change', () => mostrarPaso(pasoActual));
        btnAtras.addEventListener('click', () => {
            if (pasoActual === 1) {
                window.parent.postMessage({ tipo: 'cerrar-modal-principal' }, '*');
            } else {
                mostrarPaso(pasoActual - 1);
            }
        });
        btnSiguiente.addEventListener('click', () => {
            if (pasoActual === 1) {
                if (!$('#motivoCambio').value) {
                    window.alert('Selecciona el motivo del cambio para continuar.');
                    return;
                }
                if (!jugadores.length) {
                    window.alert('Debe quedar al menos un jugador en la lista.');
                    return;
                }
                mostrarPaso(2);
            } else if (pasoActual === 2) {
                if (!jugadores.length) {
                    window.alert('Debe quedar al menos un jugador en la lista.');
                    return;
                }
                mostrarPaso(3);
            } else {
                if (!checkEntendido.checked) return;
                guardarCambioMasivo();
            }
        });
        async function guardarCambioMasivo() {
            btnSiguiente.disabled = true;
            const token = $('#csrfTokenDesactivacion')?.value || '';
            if (!token) {
                window.alert('No se encontró el token de seguridad. Cierra este modal, recarga la página y vuelve a intentarlo. No se ha guardado ningún cambio.');
                btnSiguiente.disabled = false;
                return;
            }
            const payload = new URLSearchParams();
            payload.set('_token', token);
            payload.set('jugadores', JSON.stringify(jugadores.map((jugador) => Number(jugador.id))));
            payload.set('estado', estadoSeleccionado().toLowerCase());
            payload.set('motivo', $('#motivoCambio')?.value || '');
            payload.set('descripcion', $('#descripcionCambio')?.value || '');

            try {
                const respuesta = await fetch('/streepsoft/jugadores/desactivacion/aplicar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: payload.toString()
                });
                const tipoContenido = respuesta.headers.get('content-type') || '';
                if (!tipoContenido.includes('application/json')) {
                    throw new Error('El servidor no devolvió una respuesta válida. La sesión puede haber expirado: recarga la página e inicia sesión de nuevo. No se ha confirmado el cambio.');
                }
                const resultado = await respuesta.json();
                if (!respuesta.ok || !resultado.ok) {
                    throw new Error(resultado.mensaje || 'No se pudo guardar el cambio.');
                }
                window.parent.postMessage({
                    tipo: 'abrir-vista',
                    ruta: 'confirmacion',
                    titulo: 'Cambio completado',
                    parametros: { cantidad: resultado.cantidad, estado: resultado.estado }
                }, window.location.origin);
            } catch (error) {
                window.alert(error.message || 'Ocurrió un error al guardar el cambio.');
                btnSiguiente.disabled = false;
                mostrarPaso(pasoActual);
            }
        }

        pintarJugadores();
        mostrarPaso(1);
    }

    // ------------------- Vista de resultado -------------------
    function iniciarVistaResultado() {
        const boton = $('#btnEntendido');
        if (!boton) return;
        const params = new URLSearchParams(window.location.search);
        const cantidad = Number(params.get('cantidad') || 0);
        const estado = params.get('estado') || 'Inactivo';
        $('#cantidadAfectados').textContent = `${cantidad} jugador${cantidad === 1 ? '' : 'es'}`;
        $('#estadoAplicado').textContent = estado;
        boton.addEventListener('click', () => window.parent.postMessage({ tipo: 'cerrar-modal-principal', recargar: true }, window.location.origin));
    }

    // ------------------- Vista de historial / detalle -------------------
    function iniciarVistaHistorial() {
        const lista = $('#detalleHistorialLista');
        const token = $('#csrfTokenReactivacion')?.value || '';
        (lista ? $$('.btn-reactivar', lista) : []).forEach((boton) => {
            boton.addEventListener('click', async () => {
                const idJugador = boton.dataset.jugadorId;
                if (!idJugador) return;
                if (!window.confirm('¿Deseas reactivar este jugador?')) return;

                boton.disabled = true;
                if (!token) {
                    window.alert('No se encontró el token de seguridad. Cierra el detalle, recarga la página y vuelve a intentarlo.');
                    boton.disabled = false;
                    return;
                }

                const payload = new URLSearchParams();
                payload.set('_token', token);
                payload.set('id_jugadores', idJugador);
                try {
                    const respuesta = await fetch('/streepsoft/jugadores/desactivacion/reactivar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: payload.toString()
                    });
                    // Leer primero como texto para detectar respuestas HTML de error
                    // (ruta inexistente, redirección al login o error PHP).
                    const textoRespuesta = await respuesta.text();
                    let resultado;
                    try {
                        resultado = JSON.parse(textoRespuesta);
                    } catch (parseError) {
                        const pareceHtml = /<!doctype html|<html|<br\s*\/?/i.test(textoRespuesta);
                        throw new Error(pareceHtml
                            ? 'El servidor devolvió HTML en lugar de JSON. Comprueba la ruta de reactivación, que exista JugadorController::reactivarJugador y que la sesión siga activa.'
                            : 'El servidor devolvió una respuesta no válida al reactivar el jugador.');
                    }
                    if (!respuesta.ok || !resultado.ok) {
                        throw new Error(resultado.mensaje || 'No se pudo reactivar el jugador.');
                    }
                    const activo = document.createElement('span');
                    activo.className = 'badge-estado badge-activo';
                    activo.textContent = 'Activo';
                    boton.replaceWith(activo);
                } catch (error) {
                    window.alert(error.message || 'Error al reactivar el jugador.');
                    boton.disabled = false;
                }
            });
        });

        $('#btnCerrarDetalle')?.addEventListener('click', () => {
            window.parent.postMessage({ tipo: 'cerrar-modal-principal' }, window.location.origin);
        });
        $('#btnVolverMenu')?.addEventListener('click', () => {
            window.parent.postMessage({ tipo: 'cerrar-modal-principal' }, window.location.origin);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        iniciarPaginaPrincipal();
        iniciarVistaRegistro();
        iniciarVistaResultado();
        iniciarVistaHistorial();
    });
})();

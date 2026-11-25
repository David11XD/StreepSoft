const btnFiltro = document.getElementById("btnFiltro");
const menuFiltro = document.getElementById("menuFiltro");
const tabla = document.getElementById("tablaJugadores");
const tbody = tabla.querySelector("tbody");
const cantidadRegistros = document.getElementById("cantidadRegistros");
const infoRegistros = document.getElementById("infoRegistros");
const numerosPaginas = document.getElementById("numerosPaginas");
const btnAnterior = document.getElementById("btnAnterior");
const btnSiguiente = document.getElementById("btnSiguiente");
const buscador = document.getElementById("buscarJugador");
const filtroBeca = document.getElementById("filtroBeca");
const filtroEstado = document.getElementById("filtroEstado");
const filtroPago = document.getElementById("filtroPago");

let paginaActual = 1;

const checkboxesColumnas = menuFiltro.querySelectorAll(
    'input[type="checkbox"][data-columna]'
);

function mostrarOcultarColumna(numeroColumna, mostrar) {

    const columna = Number(numeroColumna) + 1;

    const celdas = document.querySelectorAll(
        `#tablaJugadores tr > *:nth-child(${columna})`
    );

    celdas.forEach(celda => {
        celda.style.display = mostrar ? "" : "none";
    });
}


// Recuperar configuración guardada
checkboxesColumnas.forEach(checkbox => {

    const numeroColumna = checkbox.dataset.columna;

    const estadoGuardado = localStorage.getItem(
        `columna_${numeroColumna}`
    );

    // Si existe una configuración guardada,
    // utilizarla
    if (estadoGuardado !== null) {
        checkbox.checked = estadoGuardado === "true";
    }

    // Aplicar estado al cargar la página
    mostrarOcultarColumna(
        numeroColumna,
        checkbox.checked
    );


    // Guardar cuando cambie
    checkbox.addEventListener("change", function () {

        localStorage.setItem(
            `columna_${numeroColumna}`,
            this.checked
        );

        mostrarOcultarColumna(
            numeroColumna,
            this.checked
        );

    });

});

// Abrir/cerrar menú de orden
btnFiltro.addEventListener("click", () => {
    menuFiltro.classList.toggle("mostrar");
});

// Opciones de orden (A-Z / Z-A)
document.querySelectorAll(".menu-filtro button").forEach(boton => {
    boton.addEventListener("click", () => {
        const orden = boton.dataset.orden;
        const filas = Array.from(tbody.querySelectorAll("tr"));

        filas.sort((a, b) => {
            // Columna 1 = Apellidos (columna 0 es la foto)
            const apellidoA = a.cells[1].textContent.trim();
            const apellidoB = b.cells[1].textContent.trim();

            return orden === "az"
                ? apellidoA.localeCompare(apellidoB, "es")
                : apellidoB.localeCompare(apellidoA, "es");
        });

        filas.forEach(fila => tbody.appendChild(fila));
        menuFiltro.classList.remove("mostrar");
        paginaActual = 1;
        actualizarTabla();
    });
});

// ======================================
// FILTRO COMBINADO: búsqueda + 3 selects
// ======================================
function filaCumpleFiltros(fila) {
    const texto = buscador.value.trim().toLowerCase();
    if (texto && !fila.textContent.toLowerCase().includes(texto)) {
        return false;
    }

    const beca = filtroBeca.value;
    if (beca !== "todo" && fila.dataset.beca !== beca) {
        return false;
    }

    const estadoJugador = filtroEstado.value;
    if (estadoJugador !== "todo" && fila.dataset.estado !== estadoJugador) {
        return false;
    }

    const estadoDeuda = filtroPago.value;
    if (estadoDeuda !== "todo" && fila.dataset.pago !== estadoDeuda) {
        return false;
    }

    return true;
}

function obtenerFilasFiltradas() {
    return Array.from(tbody.querySelectorAll("tr")).filter(filaCumpleFiltros);
}

// ======================================
// PAGINACIÓN (sobre las filas ya filtradas)
// ======================================
function actualizarTabla() {
    const todasLasFilas = Array.from(tbody.querySelectorAll("tr"));
    const filasFiltradas = obtenerFilasFiltradas();

    // Oculta primero las que no cumplen ningún filtro
    todasLasFilas.forEach(fila => {
        if (!filasFiltradas.includes(fila)) {
            fila.style.display = "none";
        }
    });

    const totalRegistros = filasFiltradas.length;
    const registrosPorPagina = parseInt(cantidadRegistros.value);
    const totalPaginas = Math.ceil(totalRegistros / registrosPorPagina) || 1;

    if (paginaActual > totalPaginas) {
        paginaActual = totalPaginas;
    }

    const inicio = (paginaActual - 1) * registrosPorPagina;
    const fin = Math.min(inicio + registrosPorPagina, totalRegistros);

    filasFiltradas.forEach((fila, index) => {
        fila.style.display = (index >= inicio && index < fin) ? "" : "none";
    });

    if (totalRegistros === 0) {
        infoRegistros.textContent = "Mostrando 0 - 0 de 0 jugadores";
    } else {
        infoRegistros.textContent =
            `Mostrando ${inicio + 1} - ${fin} de ${totalRegistros} jugadores`;
        infoRegistros.style.marginTop = "12px";
        infoRegistros.style.marginLeft = "5px";
        infoRegistros.style.fontFamily = '"Poppins", sans-serif';
        infoRegistros.style.fontSize = "clamp(10px, 2vw, 12px)";
        infoRegistros.style.fontWeight = "400";
        infoRegistros.style.color = "#c1bdbd";
    }

    crearNumerosPaginas(totalPaginas);

    btnAnterior.disabled = paginaActual === 1;
    btnSiguiente.disabled = paginaActual === totalPaginas || totalPaginas === 0;
}

function crearNumerosPaginas(totalPaginas) {
    numerosPaginas.innerHTML = "";

    for (let i = 1; i <= totalPaginas; i++) {
        const boton = document.createElement("button");
        boton.classList.add("pagina-numero");
        boton.textContent = i;

        if (i === paginaActual) {
            boton.classList.add("activo");
        }

        boton.addEventListener("click", () => {
            paginaActual = i;
            actualizarTabla();
        });

        numerosPaginas.appendChild(boton);
    }
}

btnAnterior.addEventListener("click", () => {
    if (paginaActual > 1) {
        paginaActual--;
        actualizarTabla();
    }
});

btnSiguiente.addEventListener("click", () => {
    const totalPaginas = Math.ceil(obtenerFilasFiltradas().length / parseInt(cantidadRegistros.value)) || 1;
    if (paginaActual < totalPaginas) {
        paginaActual++;
        actualizarTabla();
    }
});

cantidadRegistros.addEventListener("change", () => {
    paginaActual = 1;
    actualizarTabla();
});

// Búsqueda y los 3 filtros: todos reinician a página 1 y recalculan
[buscador, filtroBeca, filtroEstado, filtroPago].forEach(control => {
    const evento = control === buscador ? "input" : "change";
    control.addEventListener(evento, () => {
        paginaActual = 1;
        actualizarTabla();
    });
});

// Delegación de eventos para los botones de acción (Editar / Ver perfil)
tbody.addEventListener("click", (e) => {
    if (e.target.closest(".btn-menu-accion")) {
        const fila = e.target.closest(".table-accion");
        const menu = fila.querySelector(".menu-acciones");

        document.querySelectorAll(".menu-acciones").forEach(m => {
            if (m !== menu) m.classList.remove("mostrar");
        });

        menu.classList.toggle("mostrar");
    }
});

document.addEventListener("click", (e) => {
    if (!e.target.closest(".table-accion")) {
        document.querySelectorAll(".menu-acciones").forEach(m => {
            m.classList.remove("mostrar");
        });
    }
});

// Iniciar
actualizarTabla();


const btnNuevoJugador = document.getElementById("btnNuevoJugador");
const modalRegistro = document.getElementById("modalRegistro");
const cerrarRegistro = document.querySelector(".cerrarRegistro");
const iframeRegistro = document.getElementById("iframeRegistro");

// Modal reutilizable
function abrirModalRegistro(url){
    iframeRegistro.src = url;
    modalRegistro.classList.add("activo");
}

function cerrarModalRegistro(){
    modalRegistro.classList.remove("activo");
    // Limpiar el src al cerrar: si no, la próxima vez que se abra
    // (aunque sea para otro jugador) por un instante se alcanza a
    // ver el formulario anterior mientras carga el nuevo.
    iframeRegistro.src = "";
}

// Abrir modal
if (btnNuevoJugador) {
    btnNuevoJugador.addEventListener("click", (e) => {
        e.preventDefault();
        abrirModalRegistro("/streepsoft/jugadores/deudas");
    });
}

if (tbody) {
    tbody.addEventListener("click", (e) => {
        const botonEditar = e.target.closest(".btn-editar-historial");
        if(botonEditar){
            abrirModalRegistro("/streepsoft/" + botonEditar.dataset.idJugador);
        }
    })

    tbody.addEventListener("click", (e) => {
        const botonPerfil = e.target.closest(".btn-historial");
        if (botonPerfil) {
            const id = Number(botonPerfil.dataset.idJugador);
            if (!Number.isInteger(id) || id <= 0) {
                console.error("No se encontró un ID de jugador válido para abrir el historial.");
                return;
            }

            window.location.href = `/streepsoft/jugadores/deudas/historial/${id}`;
        }
    });
}

if (tbody) {
    tbody.addEventListener("click", (e) => {
        const btnPago = e.target.closest(".btn-pago");
        if (btnPago) {
            const idDeuda = btnPago.dataset.idDeuda;
            const modalId = "modalRegistro-" + idDeuda;
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.add("activo");
            }
        }
    });
}

// Cerrar modal (botón X)
document.addEventListener("click", (e) => {
    const btnCerrar = e.target.closest(".cerrar-registro");
    if (btnCerrar) {
        const modal = btnCerrar.closest(".modal-registro");
        if (modal) {
            modal.classList.remove("activo");  // Cierra SOLO ese modal
        }
    }
});

// Cerrar haciendo clic fuera del contenido
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal-registro")) {
        e.target.classList.remove("activo");
    }
});

// Cerrar con tecla ESC
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        const modalActivo = document.querySelector(".modal-registro.activo");
        if (modalActivo) {
            modalActivo.classList.remove("activo");
        }
    }
});

// Mostrar/ocultar el recaudo anual y recordar la elección entre recargas.
(() => {
    const botonOjo = document.getElementById('btnOjoRecaudo');
    const valorRecaudo = document.getElementById('valorRecaudoAnual');

    if (!botonOjo || !valorRecaudo) return;

    const clavePreferencia = 'streepsoft_recaudo_anual_oculto';
    const iconoVisible = `
        <svg class="icono-ojo" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>`;
    const iconoOculto = `
        <svg class="icono-ojo" viewBox="0 0 24 24" aria-hidden="true">
            <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
            <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"></path>
        </svg>`;

    let oculto = false;
    try {
        oculto = localStorage.getItem(clavePreferencia) === 'true';
    } catch (error) {
        console.warn('No se pudo recuperar la preferencia de privacidad del recaudo.', error);
    }

    const aplicarEstado = () => {
        valorRecaudo.textContent = oculto ? '••••••••' : valorRecaudo.dataset.valor;
        botonOjo.innerHTML = oculto ? iconoOculto : iconoVisible;
        botonOjo.setAttribute('aria-pressed', oculto ? 'true' : 'false');
        botonOjo.setAttribute('aria-label', oculto ? 'Mostrar recaudo anual' : 'Ocultar recaudo anual');
        botonOjo.title = oculto ? 'Mostrar recaudo anual' : 'Ocultar recaudo anual';
    };

    botonOjo.addEventListener('click', () => {
        oculto = !oculto;
        try {
            localStorage.setItem(clavePreferencia, String(oculto));
        } catch (error) {
            console.warn('No se pudo guardar la preferencia de privacidad del recaudo.', error);
        }
        aplicarEstado();
    });

    aplicarEstado();
})();

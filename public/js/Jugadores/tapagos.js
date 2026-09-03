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

const checkboxes = menuFiltro.querySelectorAll(
    'input[type="checkbox"]'
);

buscador.addEventListener("input", function () {
    paginaActual = 1;
    actualizarTabla();
});

// Los 3 selects de filtro también reinician a página 1 y recalculan
[filtroBeca, filtroEstado, filtroPago].forEach(control => {
    if (!control) return; // por si algún id no existe todavía en el HTML
    control.addEventListener("change", () => {
        paginaActual = 1;
        actualizarTabla();
    });
});

checkboxes.forEach(checkbox => {

    const numeroColumna = checkbox.dataset.columna;

    // Recuperar estado guardado
    const guardado = localStorage.getItem(
        `columna_${numeroColumna}`
    );

    if (guardado !== null) {
        checkbox.checked = guardado === "true";
    }

    // Aplicar estado al cargar
    mostrarOcultarColumna(
        numeroColumna,
        checkbox.checked
    );


    // Cuando el usuario cambia el checkbox
    checkbox.addEventListener("change", function () {

        const estado = this.checked;

        // Guardar estado
        localStorage.setItem(
            `columna_${numeroColumna}`,
            estado
        );

        // Mostrar / ocultar
        mostrarOcultarColumna(
            numeroColumna,
            estado
        );

    });

});

function mostrarOcultarColumna(numeroColumna, mostrar) {

    const numero = Number(numeroColumna) + 1;

    const celdas = document.querySelectorAll(
        `#tablaJugadores tr > *:nth-child(${numero})`
    );

    celdas.forEach(celda => {

        celda.style.display = mostrar
            ? ""
            : "none";

    });

}

// Abrir/cerrar menú
btnFiltro.addEventListener("click", () => {
    menuFiltro.classList.toggle("mostrar");
});

// Opciones de orden
document.querySelectorAll(".menu-filtro button").forEach(boton => {

    boton.addEventListener("click", () => {

        const orden = boton.dataset.orden;

        const filas = Array.from(tbody.querySelectorAll("tr"));

        filas.sort((a, b) => {

            // Columna 1 = Nombres (columna 0 es la foto)
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


// (mostrarRegistros() mostraba/ocultaba filas por índice, sin tener en
// cuenta búsqueda ni filtros -- quedó reemplazada por actualizarTabla(),
// que primero filtra y luego pagina sobre el resultado filtrado.)
cantidadRegistros.addEventListener("change", () => {
    paginaActual = 1;
    actualizarTabla();
});


function filaCumpleFiltros(fila) {
    const texto = (buscador?.value ?? "").trim().toLowerCase();
    if (texto && !fila.textContent.toLowerCase().includes(texto)) {
        return false;
    }

    const beca = filtroBeca?.value ?? "todo";
    if (beca !== "todo" && fila.dataset.beca !== beca) {
        return false;
    }

    const estado = filtroEstado?.value ?? "todo";
    if (estado !== "todo" && fila.dataset.estado !== estado) {
        return false;
    }

    const pago = filtroPago?.value ?? "todo";
    if (pago !== "todo" && fila.dataset.estado !== pago) {
        return false;
    }

    return true;
}

function obtenerFilasFiltradas() {
    return Array.from(tbody.querySelectorAll("tr")).filter(filaCumpleFiltros);
}

function actualizarTabla() {

    const todasLasFilas = Array.from(tbody.querySelectorAll("tr"));
    const filas = obtenerFilasFiltradas();

    // Oculta primero las que no cumplen ningún filtro
    todasLasFilas.forEach(fila => {
        if (!filas.includes(fila)) {
            fila.style.display = "none";
        }
    });

    const totalRegistros = filas.length;

    const registrosPorPagina = parseInt(cantidadRegistros.value);

    const totalPaginas = Math.ceil(
        totalRegistros / registrosPorPagina
    );


    // Evitar que la página actual sea mayor
    // que el número de páginas disponibles

    if (paginaActual > totalPaginas) {
        paginaActual = totalPaginas || 1;
    }


    // ======================================
    // CALCULAR DESDE Y HASTA
    // ======================================

    const inicio = (paginaActual - 1) * registrosPorPagina;

    const fin = Math.min(
        inicio + registrosPorPagina,
        totalRegistros
    );


    // ======================================
    // MOSTRAR / OCULTAR FILAS
    // ======================================

    filas.forEach((fila, index) => {

        if (index >= inicio && index < fin) {

            fila.style.display = "";

        } else {

            fila.style.display = "none";

        }

    });


    // ======================================
    // TEXTO
    // ======================================

    if (totalRegistros === 0) {

        infoRegistros.textContent =
            "Mostrando 0 - 0 de 0 jugadores";

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


    // ======================================
    // CREAR NÚMEROS DE PÁGINA
    // ======================================

    crearNumerosPaginas(totalPaginas);


    // ======================================
    // BOTONES ANTERIOR / SIGUIENTE
    // ======================================

    btnAnterior.disabled = paginaActual === 1;

    btnSiguiente.disabled =
        paginaActual === totalPaginas || totalPaginas === 0;

}


// ======================================
// CREAR NÚMEROS
// ======================================

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


// ======================================
// BOTÓN ANTERIOR
// ======================================

btnAnterior.addEventListener("click", () => {

    if (paginaActual > 1) {

        paginaActual--;

        actualizarTabla();

    }

});


// ======================================
// BOTÓN SIGUIENTE
// ======================================

btnSiguiente.addEventListener("click", () => {

    const totalRegistros =
        tbody.querySelectorAll("tr").length;

    const registrosPorPagina =
        parseInt(cantidadRegistros.value);

    const totalPaginas =
        Math.ceil(totalRegistros / registrosPorPagina);


    if (paginaActual < totalPaginas) {

        paginaActual++;

        actualizarTabla();

    }

});


// ======================================
// CAMBIAR CANTIDAD DE REGISTROS
// ======================================

cantidadRegistros.addEventListener("change", () => {

    paginaActual = 1;

    actualizarTabla();

});


// ======================================
// INICIAR
// ======================================

actualizarTabla();

// Abrir/cerrar el modal de "Registrar pago" de CADA fila
document.querySelectorAll(".btn-pago[data-id-deuda]").forEach(boton => {
    boton.addEventListener("click", () => {
        const modal = document.getElementById("modalRegistro-" + boton.dataset.idDeuda);
        if (modal) {
            modal.classList.add("activo");
        }
    });
});

document.querySelectorAll(".modal-registro").forEach(modal => {
    const btnCerrar = modal.querySelector(".cerrar-registro");
    if (btnCerrar) {
        btnCerrar.addEventListener("click", () => {
            modal.classList.remove("activo");
        });
    }

    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.classList.remove("activo");
        }
    });
});

// Cerrar con ESC cualquier modal que este abierto
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        document.querySelectorAll(".modal-registro.activo").forEach(modal => {
            modal.classList.remove("activo");
        });
    }
});

// El formulario de "Registrar pago" vive dentro de un <iframe>. Su botón
// "Cancelar" avisa por postMessage (no puede tocar el DOM del padre
// directamente) para que cerremos el modal desde aquí afuera.
window.addEventListener("message", (e) => {
    if (e.data === "cerrarModalPago") {
        document.querySelectorAll(".modal-registro.activo").forEach(modal => {
            modal.classList.remove("activo");
        });
    }
});
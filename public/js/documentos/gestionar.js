// Tarjeta "Documentos obligatorios de inscripcion"
// Maneja: seleccionar un documento, vista previa, subir (selector o arrastrar y soltar),
// verificar, revertir, rechazar, quitar, descargar y abrir.

// Texto de la pastilla de cada estado
const ETIQUETA_ESTADO = {
    pendiente: 'Pendiente',
    verificar: 'Subido',
    verificado: 'Verificado',
};

const TAMANO_MAXIMO = 5 * 1024 * 1024; // 5 MB en bytes

// ESTADO: toda la informacion que puede cambiar mientras el usuario usa la tarjeta
const estado = {
    documentos: DATOS_TARJETA.documentos.map((doc) => ({ ...doc, url: '', motivoRechazo: null })),
    seleccionado: DATOS_TARJETA.documentos[0].clave,
    confirmacion: null,   // caja abierta: 'rechazo', 'quitar' o null (ninguna)
    motivo: 'Ilegible',
};

const contenedorFilas = document.getElementById('tdocFilas');
const contenedorDetalle = document.getElementById('tdocDetalle');
const cajaAviso = document.getElementById('tdocAviso');
let temporizadorAviso = null;

/* AYUDANTES */

// Atajo para buscar un elemento por su id
function elemento(id) {
    return document.getElementById(id);
}

function buscarDocumento(clave) {
    return estado.documentos.find((doc) => doc.clave === clave);
}

// Fecha de hoy en formato corto: "25 oct 2026"
function fechaHoy() {
    return new Date()
        .toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })
        .replace(/\./g, '');
}

function formatearTamano(bytes) {
    if (bytes < 1024 * 1024) {
        return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Muestra el mensaje flotante y lo esconde solo despues de 3.6 segundos
function mostrarAviso(mensaje, tipo = 'ok') {
    cajaAviso.textContent = mensaje;
    cajaAviso.className = 'tdoc-aviso aviso-' + tipo;
    cajaAviso.hidden = false;

    clearTimeout(temporizadorAviso);
    temporizadorAviso = setTimeout(() => {
        cajaAviso.hidden = true;
    }, 3600);
}

// Revisa que el archivo sea PDF, JPG o PNG y que pese maximo 5 MB
function archivoValido(archivo) {
    if (!/\.(pdf|jpe?g|png)$/i.test(archivo.name)) {
        mostrarAviso('Formato no permitido. Usa PDF, JPG o PNG.', 'error');
        return false;
    }
    if (archivo.size > TAMANO_MAXIMO) {
        mostrarAviso('El archivo supera los 5 MB permitidos.', 'error');
        return false;
    }
    return true;
}

// Deja un documento sin archivo (estado pendiente)
function vaciarDocumento(doc) {
    if (doc.url) {
        URL.revokeObjectURL(doc.url);
    }
    Object.assign(doc, {
        estado: 'pendiente', archivo: '', tamano: '', fechaSubida: '', subidoPor: '',
        fechaVerificacion: '', verificadoPor: '', url: '', motivoRechazo: null,
    });
}

// Si el archivo lo subio el usuario usamos su URL; si es de los datos quemados,
// creamos un archivo de texto de ejemplo para que Descargar/Abrir funcionen
function referenciaArchivo(doc) {
    if (doc.url) {
        return { url: doc.url, nombre: doc.archivo };
    }
    const ejemplo = new Blob(['Documento de ejemplo: ' + doc.archivo], { type: 'text/plain' });
    return { url: URL.createObjectURL(ejemplo), nombre: doc.archivo + '.txt' };
}

function seleccionar(clave) {
    estado.seleccionado = clave;
    estado.confirmacion = null;
}

/* ACCIONES */

// Guarda un archivo nuevo en un documento (desde el selector o arrastrando)
function adjuntarArchivo(clave, archivo) {
    if (!archivo || !archivoValido(archivo)) {
        return;
    }

    const doc = buscarDocumento(clave);
    vaciarDocumento(doc); // limpia lo anterior (por si es un Reemplazar)

    // URL.createObjectURL crea una direccion temporal para ver el archivo sin subirlo a un servidor
    Object.assign(doc, {
        estado: 'verificar', archivo: archivo.name, tamano: formatearTamano(archivo.size),
        fechaSubida: fechaHoy(), subidoPor: DATOS_TARJETA.usuario, url: URL.createObjectURL(archivo),
    });

    seleccionar(clave);
    mostrarAviso(doc.nombreCompleto + ' cargado. Queda por verificar.');
    dibujar();
}

function verificar() {
    const doc = buscarDocumento(estado.seleccionado);
    doc.estado = 'verificado';
    doc.fechaVerificacion = fechaHoy();
    doc.verificadoPor = DATOS_TARJETA.usuario;

    // Si hay otro documento subido esperando revision, saltamos a el
    const siguiente = estado.documentos.find((d) => d.clave !== doc.clave && d.estado === 'verificar');
    if (siguiente) {
        seleccionar(siguiente.clave);
        mostrarAviso(doc.nombre + ' verificado. Pasamos al siguiente por revisar.');
    } else {
        mostrarAviso(doc.nombreCompleto + ' verificado.');
    }
}

function revertirVerificacion() {
    const doc = buscarDocumento(estado.seleccionado);
    doc.estado = 'verificar';
    doc.fechaVerificacion = '';
    doc.verificadoPor = '';
    mostrarAviso('La verificación de ' + doc.nombre + ' fue revertida.');
}

function confirmarRechazo() {
    const doc = buscarDocumento(estado.seleccionado);
    vaciarDocumento(doc);
    doc.motivoRechazo = { motivo: estado.motivo, fecha: fechaHoy() };
    estado.confirmacion = null;
    mostrarAviso(doc.nombre + ' rechazado. Vuelve a estado pendiente.');
}

function confirmarQuitar() {
    const doc = buscarDocumento(estado.seleccionado);
    vaciarDocumento(doc);
    estado.confirmacion = null;
    mostrarAviso(doc.nombreCompleto + ' eliminado. Vuelve a estado pendiente.');
}

function descargar() {
    const doc = buscarDocumento(estado.seleccionado);
    const archivo = referenciaArchivo(doc);

    // Truco: se crea un enlace invisible con "download" y se le hace clic
    const enlace = document.createElement('a');
    enlace.href = archivo.url;
    enlace.download = archivo.nombre;
    enlace.click();

    mostrarAviso('Descargando ' + doc.archivo + '…');
}

function abrir() {
    const doc = buscarDocumento(estado.seleccionado);
    window.open(referenciaArchivo(doc).url, '_blank');
}

// Busca el siguiente documento que no este verificado, empezando despues del actual
function irAlSiguiente() {
    const claves = estado.documentos.map((doc) => doc.clave);
    const posicion = claves.indexOf(estado.seleccionado);
    const orden = claves.slice(posicion + 1).concat(claves.slice(0, posicion + 1));
    const destino = orden.find((clave) => buscarDocumento(clave).estado !== 'verificado');

    if (!destino) {
        mostrarAviso('Todos los documentos ya están verificados.');
        return;
    }
    seleccionar(destino);
    dibujar();
}

/* DIBUJAR (pasar el estado a la pantalla) */

// Pone el texto y el color correctos en una pastilla de estado
function pintarChip(chip, estadoDoc) {
    chip.className = 'tdoc-chip chip-' + estadoDoc;
    chip.textContent = ETIQUETA_ESTADO[estadoDoc];
}

// Actualiza las 4 filas de la izquierda
function dibujarFilas() {
    estado.documentos.forEach((doc) => {
        const fila = contenedorFilas.querySelector(`[data-clave="${doc.clave}"]`);
        const esSeleccionado = doc.clave === estado.seleccionado;
        const meta = fila.querySelector('.tdoc-fila-meta');

        fila.classList.toggle('seleccionada', esSeleccionado);
        fila.setAttribute('aria-current', esSeleccionado);

        // La clase del circulo decide el color y cual de los 3 iconos se ve
        fila.querySelector('.tdoc-circulo').className = 'tdoc-circulo circulo-' + doc.estado;
        pintarChip(fila.querySelector('.tdoc-chip'), doc.estado);

        // Texto pequeño debajo del nombre
        meta.classList.toggle('meta-rechazo', doc.estado === 'pendiente' && !!doc.motivoRechazo);
        if (doc.estado !== 'pendiente') {
            meta.textContent = doc.archivo + ' · ' + doc.tamano;
        } else if (doc.motivoRechazo) {
            meta.textContent = 'Rechazado: ' + doc.motivoRechazo.motivo;
        } else {
            meta.textContent = 'Sin archivo';
        }
    });
}

// Actualiza el panel derecho con el documento seleccionado
function dibujarDetalle() {
    const doc = buscarDocumento(estado.seleccionado);
    const tieneArchivo = doc.estado !== 'pendiente';
    const extension = tieneArchivo ? doc.archivo.split('.').pop().toLowerCase() : 'pdf';
    const esImagen = !!doc.url && /\.(jpe?g|png)$/i.test(doc.archivo);

    // 1. Partes que dependen solo del estado: data-estados="pendiente", "verificar verificado", etc.
    contenedorDetalle.querySelectorAll('[data-estados]').forEach((parte) => {
        parte.hidden = !parte.dataset.estados.split(' ').includes(doc.estado);
    });

    // 2. Cabecera
    elemento('tdocNombre').textContent = doc.nombreCompleto;
    elemento('tdocDescripcion').textContent = doc.descripcion;
    pintarChip(elemento('tdocChip'), doc.estado);

    // 3. Aviso de rechazo
    elemento('tdocAlerta').hidden = !(doc.estado === 'pendiente' && doc.motivoRechazo);
    if (doc.motivoRechazo) {
        elemento('tdocAlertaTexto').textContent = 'Rechazado el ' + doc.motivoRechazo.fecha
            + ' · Motivo: ' + doc.motivoRechazo.motivo + '. Sube un nuevo archivo para continuar.';
    }

    // 4. Vista previa: imagen real (JPG/PNG subido) o "hoja" de ejemplo (PDF)
    const mostrarHoja = tieneArchivo && !esImagen;
    elemento('tdocImagen').hidden = !esImagen;
    elemento('tdocImagen').src = esImagen ? doc.url : '';
    elemento('tdocHoja').hidden = !mostrarHoja;
    elemento('tdocNotaVista').hidden = !mostrarHoja;
    elemento('tdocExtension').textContent = extension.toUpperCase();
    elemento('tdocNotaVista').textContent = doc.url
        ? 'Vista previa no disponible para este formato. Usa Abrir para verlo.'
        : 'Vista previa de ejemplo';

    // 5. Datos del archivo
    if (tieneArchivo) {
        const verificacion = elemento('tdocVerificacion');
        elemento('tdocArchivo').textContent = doc.archivo;
        elemento('tdocTamano').textContent = doc.tamano;
        elemento('tdocSubido').textContent = doc.fechaSubida + ' · ' + doc.subidoPor;
        verificacion.className = 'tdoc-dato-valor dato-' + doc.estado;
        verificacion.textContent = doc.estado === 'verificado'
            ? doc.fechaVerificacion + ' · ' + doc.verificadoPor
            : 'Pendiente de verificar';
        elemento('tdocRuta').textContent =
            'uploads/documentos/' + DATOS_TARJETA.numeroDocumento + '/' + doc.clave + '.' + extension;
    }

    // 6. Cajas de confirmacion
    elemento('tdocConfirmarRechazo').hidden = !(estado.confirmacion === 'rechazo' && doc.estado === 'verificar');
    elemento('tdocConfirmarQuitar').hidden = !(estado.confirmacion === 'quitar' && tieneArchivo);
    elemento('tdocMotivo').value = estado.motivo;
}

// Mantiene al dia la barra del perfil (arriba) con los cambios hechos en la tarjeta
function actualizarResumenPerfil() {
    const docs = estado.documentos;
    const verificados = docs.filter((d) => d.estado === 'verificado').length;
    const faltanSubir = docs.filter((d) => d.estado === 'pendiente').map((d) => d.nombre);
    const faltanVerificar = docs.filter((d) => d.estado === 'verificar').map((d) => d.nombre);
    const entregados = docs.length - faltanSubir.length;

    // Misma regla que el PHP: [clase, texto de la etiqueta, mensaje de lo que falta]
    let resumen = ['completo', 'Completo', 'Todos los documentos están verificados'];
    if (faltanSubir.length > 0) {
        resumen = ['incompleto', 'Incompleto', 'Falta subir: ' + faltanSubir.join(', ')];
    } else if (faltanVerificar.length > 0) {
        resumen = ['verificar', 'Por verificar', 'Falta verificar: ' + faltanVerificar.join(', ')];
    }

    const etiqueta = document.querySelector('.ges-etiqueta-progreso');
    document.querySelector('.ges-progreso-conteo').textContent =
        verificados + ' de ' + docs.length + ' verificados · ' + entregados + ' entregados';
    etiqueta.className = 'ges-etiqueta-progreso progreso-' + resumen[0];
    etiqueta.textContent = resumen[1];
    document.querySelector('.ges-progreso-falta').textContent = resumen[2];

    // Las barritas estan en el mismo orden que los documentos
    document.querySelectorAll('.ges-segmento').forEach((segmento, i) => {
        segmento.className = 'ges-segmento segmento-' + docs[i].estado;
    });
}

function dibujar() {
    dibujarFilas();
    dibujarDetalle();
    actualizarResumenPerfil();
}

/* EVENTOS (lo que hace el usuario) */

// Clic en una fila de la lista -> seleccionar ese documento
contenedorFilas.addEventListener('click', (evento) => {
    const fila = evento.target.closest('.tdoc-fila');
    if (fila) {
        seleccionar(fila.dataset.clave);
        dibujar();
    }
});

// Arrastrar un archivo sobre una fila -> se sube a ESE documento
// (preventDefault en dragover: sin esto el navegador abriria el archivo)
contenedorFilas.addEventListener('dragover', (evento) => evento.preventDefault());
contenedorFilas.addEventListener('drop', (evento) => {
    const fila = evento.target.closest('.tdoc-fila');
    evento.preventDefault();
    if (fila) {
        adjuntarArchivo(fila.dataset.clave, evento.dataTransfer.files[0]);
    }
});

// Arrastrar un archivo sobre el panel de detalle -> se sube al documento seleccionado
contenedorDetalle.addEventListener('dragover', (evento) => evento.preventDefault());
contenedorDetalle.addEventListener('drop', (evento) => {
    evento.preventDefault();
    adjuntarArchivo(estado.seleccionado, evento.dataTransfer.files[0]);
});

// "Diccionario" de acciones: el valor de data-accion -> la funcion que se ejecuta
const ACCIONES = {
    'verificar': verificar,
    'revertir': revertirVerificacion,
    'abrir-rechazo': () => (estado.confirmacion = 'rechazo'),
    'confirmar-rechazo': confirmarRechazo,
    'preguntar-quitar': () => (estado.confirmacion = 'quitar'),
    'confirmar-quitar': confirmarQuitar,
    'cancelar': () => (estado.confirmacion = null),
    'descargar': descargar,
    'abrir': abrir,
};

// Clic en los botones del detalle (cada uno tiene data-accion)
contenedorDetalle.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-accion]');
    if (boton) {
        ACCIONES[boton.dataset.accion]();
        dibujar();
    }
});

// Cambios en el detalle: archivo elegido con el selector o motivo de rechazo
contenedorDetalle.addEventListener('change', (evento) => {
    if (evento.target.matches('.tdoc-input-archivo')) {
        adjuntarArchivo(estado.seleccionado, evento.target.files[0]);
        evento.target.value = '';
    }
    if (evento.target.id === 'tdocMotivo') {
        estado.motivo = evento.target.value;
    }
});

elemento('tdocSiguiente').addEventListener('click', irAlSiguiente);

// Primer dibujo al cargar la pagina
dibujar();

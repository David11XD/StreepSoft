   const pasosFormulario = document.querySelectorAll(".paso-formulario");
    const indicadores = document.querySelectorAll(".paso");

    const btnSiguiente = document.getElementById("btnSiguiente");
    const btnAnterior = document.getElementById("btnAnterior");
    const btnGuardar = document.getElementById("btnGuardar");

    let pasoActual = 0;


    function mostrarPaso() {

        // Ocultar todos los pasos
        pasosFormulario.forEach((paso) => {
            paso.classList.remove("activo");
        });

        // Mostrar solamente el paso actual
        pasosFormulario[pasoActual].classList.add("activo");


        // Actualizar los indicadores superiores
        indicadores.forEach((indicador, indice) => {

            indicador.classList.remove("activo", "completado");

            if (indice === pasoActual) {
                indicador.classList.add("activo");
            }

            if (indice < pasoActual) {
                indicador.classList.add("completado");
            }

        });


        // Ocultar el botón anterior en el primer paso
        btnAnterior.style.display =
            pasoActual === 0 ? "none" : "inline-flex";


        // Cambiar entre Siguiente y Guardar
        if (pasoActual === pasosFormulario.length - 1) {

            btnSiguiente.style.display = "none";
            btnGuardar.style.display = "inline-flex";

        } else {

            btnSiguiente.style.display = "inline-flex";
            btnGuardar.style.display = "none";

        }

    }


    // SIGUIENTE
    btnSiguiente.addEventListener("click", () => {

        if (pasoActual < pasosFormulario.length - 1) {

            pasoActual++;
            mostrarPaso();

        }

    });


    // ANTERIOR
    btnAnterior.addEventListener("click", () => {

        if (pasoActual > 0) {

            pasoActual--;
            mostrarPaso();

        }

    });


    // Iniciar en la primera página
    mostrarPaso();

const inputFoto = document.getElementById('inputFoto');
const zonaFoto = document.getElementById('zonaFoto');
const fotoMiniatura = document.getElementById('fotoMiniatura');
const modalRecorte = document.getElementById('modalRecorte');
const imagenRecorte = document.getElementById('imagenRecorte');
const cerrarRecorte = document.getElementById('cerrarRecorte');
const cancelarRecorte = document.getElementById('cancelarRecorte');
const aceptarRecorte = document.getElementById('aceptarRecorte');
const fotoBase64 = document.getElementById('fotoBase64');

let cropper = null;
let imagenSeleccionada = null;

// Abrir el selector de archivos al hacer clic en la zona
zonaFoto.addEventListener('click', (e) => {
    // Si ya hay foto, no impedimos que se vuelva a abrir para cambiarla
    inputFoto.click();
});

// Cuando se selecciona un archivo
inputFoto.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Validar tamaño (2 MB)
    if (file.size > 3 * 1024 * 1024) {
        alert('La imagen no debe superar los 2 MB.');
        inputFoto.value = '';
        return;
    }

    // Validar tipo
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        alert('Solo se permiten JPG o PNG.');
        inputFoto.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        imagenSeleccionada = event.target.result;
        // Mostrar modal con la imagen
        abrirModalRecorte(imagenSeleccionada);
    };
    reader.readAsDataURL(file);
});

function abrirModalRecorte(src) {
    imagenRecorte.src = src;
    modalRecorte.classList.add('activo');

    // Inicializar Cropper después de que la imagen se cargue
    imagenRecorte.onload = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropper = new Cropper(imagenRecorte, {
            aspectRatio: 1, // Cuadrado (para foto de perfil)
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    };

    // Si la imagen ya está en caché, puede que onload no se dispare
    if (imagenRecorte.complete) {
        imagenRecorte.onload();
    }
}

function cerrarModalRecorte() {
    modalRecorte.classList.remove('activo');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    inputFoto.value = ''; // Limpiar para permitir re-selección
}

// Cerrar con botón X
cerrarRecorte.addEventListener('click', cerrarModalRecorte);

// Cerrar con Cancelar
cancelarRecorte.addEventListener('click', cerrarModalRecorte);

// Aceptar: recortar y mostrar miniatura
aceptarRecorte.addEventListener('click', () => {
    if (!cropper) return;

    // Obtener la imagen recortada en formato base64 (calidad 0.9)
    const dataURL = cropper.getCroppedCanvas({
        width: 300,
        height: 300,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    }).toDataURL('image/jpeg', 0.9);

    // Mostrar la miniatura en el círculo
    fotoMiniatura.src = dataURL;
    fotoMiniatura.style.display = 'block';
    zonaFoto.classList.add('con-foto');

    // Guardar en el input oculto para enviar al servidor
    fotoBase64.value = dataURL;

    // Cerrar modal
    cerrarModalRecorte();
});

// Cerrar modal al hacer clic fuera del contenido
modalRecorte.addEventListener('click', (e) => {
    if (e.target === modalRecorte) {
        cerrarModalRecorte();
    }
});

// Cerrar con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalRecorte();
    }
});

const btnCancelar = document.getElementById('cerrarRegistro');

if (btnCancelar) {
    btnCancelar.addEventListener('click', function (e) {
        e.preventDefault(); // Por si acaso

        // Buscar el modal en el documento padre (tablaJugadores.html)
        const modalPadre = window.parent.document.getElementById('modalRegistro');

        if (modalPadre) {
            modalPadre.classList.remove('activo');
        }
    });
}

const fechaNacimientoInput = document.querySelector('input[name="fecha_nacimiento"]');
const edadInput = document.querySelector('input[name="edad"]');

// Cuando cambia la fecha de nacimiento, calcular edad
fechaNacimientoInput.addEventListener('change', function () {
    const fecha = new Date(this.value);
    if (isNaN(fecha)) {
        edadInput.value = '';
        return;
    }
    const hoy = new Date();
    let edad = hoy.getFullYear() - fecha.getFullYear();
    const mes = hoy.getMonth() - fecha.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fecha.getDate())) {
        edad--;
    }
    edadInput.value = edad >= 0 ? edad : '';
});

// Cuando cambia la edad, calcular fecha de nacimiento aproximada
edadInput.addEventListener('input', function () {
    const edad = parseInt(this.value);
    if (isNaN(edad) || edad < 0) {
        fechaNacimientoInput.value = '';
        return;
    }
    const hoy = new Date();
    const fechaNac = new Date(hoy.getFullYear() - edad, hoy.getMonth(), hoy.getDate());
    // Ajustar si la fecha calculada es futura (puede ocurrir si la edad es 0 y hoy no ha cumplido años)
    // Pero no es necesario ajustar, porque la edad es exacta.
    // Convertir a formato YYYY-MM-DD para input type="date"
    const year = fechaNac.getFullYear();
    const month = String(fechaNac.getMonth() + 1).padStart(2, '0');
    const day = String(fechaNac.getDate()).padStart(2, '0');
    fechaNacimientoInput.value = `${year}-${month}-${day}`;
});


const metodoPago = document.getElementById('metodoPago');
const grupoComprobante = document.getElementById('grupoComprobante');
const comprobanteInput = document.getElementById('comprobante');

if (metodoPago && grupoComprobante) {
    metodoPago.addEventListener('change', function() {
        const valor = this.options[this.selectedIndex]?.text ?? '';
        if (valor === 'Nequi' || valor === 'Transferencia') {
            grupoComprobante.style.display = 'block';
            comprobanteInput.required = true; // Opcional: hacerlo obligatorio
        } else {
            grupoComprobante.style.display = 'none';
            comprobanteInput.value = '';
            comprobanteInput.required = false;
        }
    });
}
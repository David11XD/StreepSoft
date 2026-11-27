   const pasosFormulario = document.querySelectorAll(".paso-formulario");
    const indicadores = document.querySelectorAll(".paso");

    const btnSiguiente = document.getElementById("btnSiguiente");
    const btnAnterior = document.getElementById("btnAnterior");
    const btnGuardar = document.getElementById("btnGuardar");

    let pasoActual = 0;

    // La define más abajo la validación del formulario (validarFormularioJugador).
    // Revisa los campos obligatorios del paso actual: los vacíos se pintan en rojo,
    // se muestra el aviso y devuelve false para que NO se avance de paso.
    let validarPasoActual = null;


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

        // Si el paso actual tiene campos obligatorios vacíos, se quedan en rojo y no se avanza
        if (validarPasoActual && !validarPasoActual()) return;

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

    // Validar tamaño (3 MB, el mismo límite que aplica el servidor)
    if (file.size > 3 * 1024 * 1024) {
        mostrarAvisoFormulario({
            titulo: 'No se pudo cargar la foto',
            mensajes: ['La imagen no debe superar los 3 MB.']
        });
        inputFoto.value = '';
        return;
    }

    // Validar tipo
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        mostrarAvisoFormulario({
            titulo: 'No se pudo cargar la foto',
            mensajes: ['Solo se permiten imágenes JPG o PNG.']
        });
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

// Importes COP del paso de inscripción: se muestran con separador de miles
// y el total se calcula con la operación matrícula + mensualidad.
(function configurarResumenPagoInicial() {
    const inputMatricula = document.getElementById('matriculaJugador');
    const inputMensualidad = document.getElementById('mensualidadJugador');
    const salidaMatricula = document.getElementById('resumenMatricula');
    const salidaMensualidad = document.getElementById('resumenMensualidad');
    const salidaTotal = document.getElementById('resumenTotalInicial');

    if (!inputMatricula || !inputMensualidad || !salidaMatricula || !salidaMensualidad || !salidaTotal) {
        return;
    }

    const soloDigitos = (valor) => String(valor || '').replace(/\D/g, '');
    const aNumero = (valor) => Number(soloDigitos(valor) || 0);
    const formatoCOP = (valor) => '$' + Number(valor || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 });

    const formatearCampo = (campo) => {
        const digitos = soloDigitos(campo.value);
        campo.value = digitos ? Number(digitos).toLocaleString('es-CO', { maximumFractionDigits: 0 }) : '';
    };

    const actualizarResumen = () => {
        const matricula = aNumero(inputMatricula.value);
        const mensualidad = aNumero(inputMensualidad.value);
        salidaMatricula.textContent = formatoCOP(matricula);
        salidaMensualidad.textContent = formatoCOP(mensualidad);
        salidaTotal.textContent = formatoCOP(matricula + mensualidad);
    };

    [inputMatricula, inputMensualidad].forEach((campo) => {
        campo.addEventListener('input', () => {
            formatearCampo(campo);
            actualizarResumen();
        });
        campo.addEventListener('blur', () => {
            formatearCampo(campo);
            actualizarResumen();
        });
    });

    actualizarResumen();
})();


// ---------------------------------------------------------------
// VALIDACIÓN DEL FORMULARIO (aviso tipo toast como el del login)
//
// El formulario tiene varios pasos y los pasos que no se ven están ocultos
// (display: none). Con la validación nativa del navegador, un campo vacío en un
// paso oculto bloquea el envío SIN decir nada. Por eso el <form> lleva
// "novalidate" y aquí se revisan todos los pasos al guardar: se avisa qué falta,
// se vuelve al primer paso con faltantes y se marcan los campos.
// El servidor vuelve a validar lo mismo (JugadorController::guardar).
// ---------------------------------------------------------------
(function validarFormularioJugador() {
    const formulario = document.getElementById('formjugador');
    if (!formulario) return;

    // Nombre con el que se le muestra cada campo al usuario
    const ETIQUETAS = {
        nombre1: 'Primer nombre',
        apellido1: 'Primer apellido',
        id_tipo_documento: 'Tipo de documento',
        documento: 'Identificación',
        fecha_nacimiento: 'Fecha de nacimiento',
        edad: 'Edad',
        sexo: 'Sexo',
        id_eps: 'EPS',
        id_instructor: 'Instructor',
        id_categorias: 'Categoría',
        responsable_nombres: 'Nombres del acudiente',
        responsable_apellidos: 'Apellidos del acudiente',
        responsable_id_tipo_documento: 'Tipo de documento del acudiente',
        responsable_identificacion: 'Identificación del acudiente',
        responsable_numero_celular: 'Celular del acudiente',
        Matricula: 'Matrícula',
        Mensualidad: 'Mensualidad',
        fecha_pago: 'Fecha de pago',
        id_metodo_pago: 'Método de pago',
        id_tipo_becas: 'Tipo de beca',
        comprobante: 'Número de comprobante'
    };

    function etiquetaDe(campo) {
        return ETIQUETAS[campo.name] || campo.name;
    }

    // El comprobante solo se pide cuando su grupo está visible (Nequi / Transferencia)
    function campoAplica(campo) {
        const grupo = campo.closest('#grupoComprobante');
        return !(grupo && grupo.style.display === 'none');
    }

    function hoyISO() {
        const hoy = new Date();
        return hoy.getFullYear() + '-' +
            String(hoy.getMonth() + 1).padStart(2, '0') + '-' +
            String(hoy.getDate()).padStart(2, '0');
    }

    // ambito: el paso que se quiere revisar (al pulsar Siguiente) o el formulario completo (al guardar)
    function revisarFormulario(ambito) {
        ambito = ambito || formulario;
        const faltantes = [];   // { orden, etiqueta }
        const mensajes = [];
        const invalidos = [];

        const fechaNacimiento = formulario.querySelector('[name="fecha_nacimiento"]');
        const fechaEnAmbito = !!fechaNacimiento && ambito.contains(fechaNacimiento);
        const fechaValor = fechaNacimiento ? fechaNacimiento.value : '';
        const fechaFutura = fechaValor !== '' && fechaValor > hoyISO();
        // La edad se calcula con la fecha de nacimiento: si la fecha falta o es futura,
        // la edad queda vacía pero no se avisa dos veces.
        const edadDependeDeFecha = fechaValor === '' || fechaFutura;

        ambito.querySelectorAll('input[required], select[required], textarea[required]').forEach((campo) => {
            if (!campoAplica(campo)) return;

            const valor = (campo.value || '').trim();

            if (valor === '') {
                invalidos.push(campo);
                if (campo.name === 'edad' && edadDependeDeFecha) return;
                faltantes.push({
                    orden: Object.keys(ETIQUETAS).indexOf(campo.name),
                    etiqueta: etiquetaDe(campo)
                });
                return;
            }

            // Reglas de formato del propio campo (por ejemplo, pattern="[0-9]*")
            if (campo.validity && campo.validity.patternMismatch) {
                mensajes.push(etiquetaDe(campo) + ': solo se permiten números.');
                invalidos.push(campo);
            }
        });

        if (fechaFutura && fechaEnAmbito) {
            mensajes.push('La fecha de nacimiento no puede ser futura.');
            invalidos.push(fechaNacimiento);
        }

        // Se avisa en el mismo orden en que se llena el formulario (no en el del código fuente)
        faltantes.sort((a, b) => a.orden - b.orden);
        invalidos.sort((a, b) => Object.keys(ETIQUETAS).indexOf(a.name) - Object.keys(ETIQUETAS).indexOf(b.name));

        return { faltantes: faltantes.map((f) => f.etiqueta), mensajes, invalidos };
    }

    function marcarInvalidos(invalidos, ambito) {
        (ambito || formulario).querySelectorAll('.campo-invalido').forEach((c) => c.classList.remove('campo-invalido'));
        invalidos.forEach((c) => c.classList.add('campo-invalido'));
    }

    // Pinta en rojo los campos con problema, lleva al paso donde está el primero,
    // le da el foco y muestra el aviso. Lo usan Guardar (todo el formulario) y Siguiente (un paso).
    function mostrarProblemas(resultado, ambito) {
        marcarInvalidos(resultado.invalidos, ambito);

        const primero = resultado.invalidos[0];
        const paso = primero ? primero.closest('.paso-formulario') : null;
        const indice = paso ? Array.prototype.indexOf.call(pasosFormulario, paso) : -1;
        if (indice >= 0) {
            pasoActual = indice;
            mostrarPaso();
            if (typeof primero.focus === 'function') primero.focus();
        }

        mostrarAvisoFormulario({
            titulo: resultado.faltantes.length
                ? 'Completa los campos obligatorios'
                : 'Revisa los datos ingresados',
            faltantes: resultado.faltantes,
            mensajes: resultado.mensajes
        });
    }

    // Se usa al pulsar "Siguiente": true = el paso está completo y se puede avanzar
    validarPasoActual = function () {
        const paso = pasosFormulario[pasoActual];
        const resultado = revisarFormulario(paso);
        if (resultado.faltantes.length === 0 && resultado.mensajes.length === 0 && resultado.invalidos.length === 0) {
            marcarInvalidos([], paso);
            return true;
        }
        mostrarProblemas(resultado, paso);
        return false;
    };

    // Un campo obligatorio tiene problema si está vacío (solo espacios cuenta como vacío)
    // o si su valor no cumple el formato (por ejemplo, letras donde solo van números).
    function campoConProblema(campo) {
        if ((campo.value || '').trim() === '') return true;
        return !!(campo.validity && campo.validity.patternMismatch);
    }

    function esObligatorioVisible(campo) {
        return !!(campo.matches && campo.matches('input[required], select[required], textarea[required]'))
            && campoAplica(campo);
    }

    // Al salir de un campo obligatorio vacío (o con formato incorrecto) se marca en rojo.
    // También ocurre al pulsar "Siguiente", porque el campo pierde el foco.
    formulario.addEventListener('focusout', (e) => {
        if (!esObligatorioVisible(e.target)) return;
        e.target.classList.toggle('campo-invalido', campoConProblema(e.target));
    });

    // Al corregir un campo se le quita la marca. Se revisan todos los marcados porque algunos
    // se llenan solos (la edad se calcula con la fecha de nacimiento).
    function quitarMarcasCorregidas() {
        formulario.querySelectorAll('.campo-invalido').forEach((campo) => {
            if (!campoConProblema(campo)) campo.classList.remove('campo-invalido');
        });
    }
    formulario.addEventListener('input', quitarMarcasCorregidas);
    formulario.addEventListener('change', quitarMarcasCorregidas);

    formulario.addEventListener('submit', (e) => {
        const resultado = revisarFormulario();
        if (resultado.faltantes.length === 0 && resultado.mensajes.length === 0) {
            return; // todo bien: el formulario se envía normalmente
        }

        e.preventDefault();
        mostrarProblemas(resultado, formulario);
    });
})();

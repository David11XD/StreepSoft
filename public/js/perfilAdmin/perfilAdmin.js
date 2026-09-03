document.addEventListener('DOMContentLoaded', () => {

    // --- Modal: Editar información ---
    // Buscamos en la página la ventana del modal y los botones mediante sus ID o Clases
    const modalEditarInfo = document.getElementById('modalEditarInfo');
    const botonEditarInfo = document.querySelector('.boton-editar-info');
    const cerrarModalEditarInfo = document.getElementById('cerrarModalEditarInfo');
    const cancelarModalEditarInfo = document.getElementById('cancelarModalEditarInfo');

    // Función que abre el modal agregando la clase 'activo' (el CSS se encarga de mostrarlo)
    function abrirModalEditarInfo() {
        modalEditarInfo.classList.add('activo');
    }

    // Función que cierra el modal quitando la clase 'activo' (el CSS vuelve a ocultarlo)
    function ocultarModalEditarInfo() {
        modalEditarInfo.classList.remove('activo');
    }

    // Al hacer clic en "Editar información", se ejecuta la función para abrir el modal
    botonEditarInfo?.addEventListener('click', abrirModalEditarInfo);

    // Al hacer clic en la 'X' para cerrar, se ejecuta la función para ocultar el modal
    cerrarModalEditarInfo?.addEventListener('click', ocultarModalEditarInfo);

    cancelarModalEditarInfo?.addEventListener('click', ocultarModalEditarInfo);

    const parametrosURL = new URLSearchParams(window.location.search);

    // Si hay un error, se abre el modal
    if (parametrosURL.has('error')) {
        abrirModalEditarInfo();
    }

    // Si hubo éxito, busca el id "notificacion-exito" y lo desaparece 
    if (parametrosURL.has('success')) {
        const notificacion = document.getElementById('notificacion-exito');

        if (notificacion) {
            setTimeout(() => {
                notificacion.style.transition = 'opacity 0.5s ease';
                notificacion.style.opacity = '0';
                setTimeout(() => notificacion.remove(), 500);
            }, 800);
        }
    }

    // Limpia la URL para que no vuelva a molestar al recargar
    if (parametrosURL.has('error') || parametrosURL.has('success')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    //Pregunta de confirmación antes de enviar el formulario
    const formEditarInfo = document.getElementById('formEditarInfo');
    const inputNombre = document.getElementById('input-nombre-completo');
    const inputTelefono = document.getElementById('input-telefono');
    const inputDocumento = document.getElementById('input-documento');

    formEditarInfo?.addEventListener('submit', function (evento) {
        evento.preventDefault(); // detiene el envío automático

        const huboCambios =
            inputNombre.value !== inputNombre.defaultValue ||
            inputTelefono.value !== inputTelefono.defaultValue ||
            inputDocumento.value !== inputDocumento.defaultValue;

        if (!huboCambios) {
            Swal.fire({
                title: 'Sin cambios',
                text: 'No modificaste ningún dato.',
                icon: 'info',
                confirmButtonText: 'Entendido',
                background: '#232323',
                color: '#ffffff',
                confirmButtonColor: '#D09E10'
            });
            return; // no seguimos, no se envía nada
        }

        Swal.fire({
            title: '¿Guardar los cambios?',
            text: 'Se va a actualizar tu información de perfil.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            background: '#232323',
            color: '#ffffff',
            confirmButtonColor: '#D09E10',
            cancelButtonColor: '#555555'
        }).then(function (resultado) {
            if (resultado.isConfirmed) {
                formEditarInfo.submit(); // ahora sí, se envía de verdad
            }
        });
    });

    // Tomamos TODOS los botones "Descargar" de la tabla de reportes de una vez
    const botonesDescargar = document.querySelectorAll('.boton-descargar');

    botonesDescargar.forEach((boton) => {
        boton.addEventListener('click', () => {

            // 1. y 2. Encontrar la fila y el <select>
            const fila = boton.closest('tr');
            const select = fila.querySelector('.select-formato');

            // 3. Leer el tipo de reporte y el formato
            const tipo = boton.dataset.tipo;
            const formato = select.value;

            // --- EFECTO VISUAL ---
            const textoOriginal = boton.innerHTML;
            boton.innerHTML = '<i class="fi fi-rr-spinner-alt"></i> Descargando...';
            boton.style.opacity = '0.7';
            boton.style.pointerEvents = 'none';

            // Restaurar el botón después de 2.5 segundos
            setTimeout(() => {
                boton.innerHTML = textoOriginal;
                boton.style.opacity = '1';
                boton.style.pointerEvents = 'auto';
            }, 2500);

            // 4. Armar la URL hacia el controlador 
            const url = `/streepsoft/reportes/generar?tipo=${tipo}&formato=${formato}`;

            // 5. Disparar la descarga. 
            window.location.href = url;
        });
    });

    // --- Cambiar foto ---
    const botonCambiarFoto = document.getElementById('botonCambiarFoto');
    const inputFoto = document.getElementById('inputFoto');
    const formCambiarFoto = document.getElementById('formCambiarFoto');

    botonCambiarFoto?.addEventListener('click', () => {
        inputFoto.click();
    });

    inputFoto?.addEventListener('change', () => {
        if (inputFoto.files.length > 0) { // No envia el formulario vacio
            formCambiarFoto.submit();
        }
    });

});
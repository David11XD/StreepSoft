/**
 * Aviso tipo "toast" para los formularios de jugador.
 * Mismo estilo que el aviso del login: esquina superior derecha, fondo oscuro,
 * ícono amarillo y barra de tiempo (SweetAlert2).
 *
 * Uso:
 *   mostrarAvisoFormulario({
 *       icono: 'error',                       // opcional (error | warning | success)
 *       titulo: 'Completa los campos obligatorios',
 *       faltantes: ['Primer nombre', 'EPS'],  // se muestran como "Faltan: ..."
 *       mensajes: ['La fecha no es válida.'], // un renglón por mensaje
 *       tiempo: 6000                          // opcional, en milisegundos
 *   });
 *
 * Si SweetAlert2 no carga (por ejemplo, sin internet), cae a un alert() normal
 * para que el usuario siempre reciba la información.
 *
 * Además, si la página trae window.AVISO_FORMULARIO (lo publica la vista cuando
 * el servidor rechazó el formulario), lo muestra al cargar.
 */
(function () {
    'use strict';

    function escaparHtml(texto) {
        return String(texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function mostrarAvisoFormulario(opciones) {
        opciones = opciones || {};
        var titulo = opciones.titulo || 'Ocurrió un error.';
        var faltantes = Array.isArray(opciones.faltantes) ? opciones.faltantes : [];
        var mensajes = Array.isArray(opciones.mensajes) ? opciones.mensajes : [];

        if (typeof Swal === 'undefined') {
            var plano = [titulo];
            if (faltantes.length) plano.push('Faltan: ' + faltantes.join(', '));
            mensajes.forEach(function (m) { plano.push(m); });
            alert(plano.join('\n'));
            return;
        }

        var html = '';
        if (faltantes.length) {
            html += '<div><strong>Faltan:</strong> ' + faltantes.map(escaparHtml).join(', ') + '</div>';
        }
        mensajes.forEach(function (mensaje) {
            html += '<div>' + escaparHtml(mensaje) + '</div>';
        });

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: opciones.icono || 'error',
            title: titulo,
            html: html || undefined,
            showConfirmButton: false,
            timer: opciones.tiempo || 6000,
            timerProgressBar: true,
            width: 'min(380px, 92vw)',

            background: '#232323',
            color: '#ffffff',
            iconColor: '#f5c400',

            customClass: { htmlContainer: 'aviso-formulario-detalle' },
            didOpen: function (toast) {
                // El modal de registro de la tabla usa z-index 9999: el aviso debe quedar por encima
                var contenedor = toast.closest ? toast.closest('.swal2-container') : null;
                if (contenedor) contenedor.style.zIndex = '10000';

                // Si el usuario pasa el mouse por encima, el aviso no se cierra mientras lo lee
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    window.mostrarAvisoFormulario = mostrarAvisoFormulario;

    function mostrarAvisoDelServidor() {
        if (window.AVISO_FORMULARIO) {
            mostrarAvisoFormulario(window.AVISO_FORMULARIO);
            window.AVISO_FORMULARIO = null;
        }
        // Limpia el ?error=... de la URL para que no se repita al recargar
        if (window.history && window.history.replaceState && /[?&]error=/.test(window.location.search)) {
            window.history.replaceState({}, '', window.location.pathname);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mostrarAvisoDelServidor);
    } else {
        mostrarAvisoDelServidor();
    }
})();

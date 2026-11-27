/**
 * FILTROS DE ESCRITURA PARA LOS INPUTS DEL JUGADOR
 * ------------------------------------------------------------------
 * ¿Para qué sirve?
 *   Impide que el usuario escriba (o pegue) caracteres que el campo no admite:
 *     - data-filtro="numeros" -> solo dígitos 0-9 (identificaciones, celular, camiseta, edad)
 *     - data-filtro="letras"  -> solo letras (con tildes, ñ, ü), espacios, guion y apóstrofo (nombres y apellidos)
 *
 * ¿Por qué hacía falta?
 *   - `pattern="[0-9]*"` NO bloquea la escritura: solo marca el campo como inválido al enviar
 *     el formulario, y este formulario usa `novalidate`, así que el navegador no lo revisa.
 *   - `inputmode="numeric"` solo cambia el teclado en el celular; en computador deja escribir todo.
 *   - Los nombres no tenían ninguna regla.
 *
 * ¿Cómo funciona?
 *   Usa "delegación de eventos": un único listener en `document` escucha el evento `input`
 *   (se dispara al teclear, pegar, arrastrar texto o autocompletar) y limpia el valor
 *   si el campo trae el atributo data-filtro. Así sirve para los formularios de crear y editar
 *   sin repetir código.
 *
 * IMPORTANTE: esto es solo comodidad para el usuario. La validación que realmente protege
 * es la del servidor (JugadorController::validarFormatoPersona), porque el JS se puede saltar.
 */
(function () {
    'use strict';

    // \p{L} = cualquier letra (incluye á é í ó ú ñ ü), \p{M} = marcas combinadas de tilde.
    // Se permiten espacio, guion y apóstrofo (ej.: "De la Cruz", "Pérez-Gómez", "D'Angelo").
    const NO_PERMITIDO_LETRAS = /[^\p{L}\p{M} '’-]/gu;
    const NO_PERMITIDO_NUMEROS = /\D/g; // \D = todo lo que NO sea dígito

    // Recibe el texto escrito y devuelve el texto ya limpio según el tipo de filtro
    function limpiar(valor, filtro) {
        if (filtro === 'numeros') {
            return valor.replace(NO_PERMITIDO_NUMEROS, '');
        }
        if (filtro === 'letras') {
            return valor
                .replace(NO_PERMITIDO_LETRAS, '') // quita números y símbolos
                .replace(/^\s+/, '')              // sin espacios al inicio
                .replace(/ {2,}/g, ' ');          // un solo espacio entre palabras
        }
        return valor;
    }

    document.addEventListener('input', function (evento) {
        const campo = evento.target;
        const filtro = campo && campo.dataset ? campo.dataset.filtro : undefined;

        // Solo actuamos sobre campos con data-filtro, y no mientras el usuario
        // compone un carácter (tildes con teclado de celular, etc.)
        if (!filtro || evento.isComposing) return;

        const original = campo.value;
        const limpio = limpiar(original, filtro);
        if (original === limpio) return;

        // Guardamos dónde estaba el cursor para que no salte al final del texto
        const posicion = campo.selectionStart ?? original.length;
        const posicionNueva = limpiar(original.slice(0, posicion), filtro).length;

        campo.value = limpio;
        if (typeof campo.setSelectionRange === 'function') {
            campo.setSelectionRange(posicionNueva, posicionNueva);
        }
    });
})();

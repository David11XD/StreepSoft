document.addEventListener('DOMContentLoaded', () => {
    const token = document.getElementById('csrfTokenReactivarInactivo')?.value || '';
    const botones = Array.from(document.querySelectorAll('.btn-activar-inactivo'));

    botones.forEach((boton) => {
        boton.addEventListener('click', async () => {
            const id = Number(boton.dataset.jugadorId || 0);
            if (!id) return;
            if (!window.confirm('¿Deseas activar este jugador y devolver su expediente al listado principal?')) return;

            boton.disabled = true;
            const payload = new URLSearchParams();
            payload.set('_token', token);
            payload.set('id_jugadores', String(id));

            try {
                const respuesta = await fetch('/streepsoft/jugadores/desactivacion/reactivar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: payload.toString()
                });
                const resultado = await respuesta.json();
                if (!respuesta.ok || !resultado.ok) {
                    throw new Error(resultado.mensaje || 'No se pudo activar el jugador.');
                }
                window.parent.postMessage({ tipo: 'cerrar-modal-principal', recargar: true }, window.location.origin);
            } catch (error) {
                window.alert(error.message || 'Ocurrió un error al activar el jugador.');
                boton.disabled = false;
            }
        });
    });

    document.getElementById('btnVolverInactivos')?.addEventListener('click', () => {
        window.parent.postMessage({ tipo: 'cerrar-modal-principal' }, window.location.origin);
    });
});

document.addEventListener('DOMContentLoaded', () => {

    const btnNuevoAlumno =
        document.getElementById('btnNuevoAlumno');

    const modal =
        document.getElementById('modalNuevoAlumno');

    const contenido =
        document.getElementById('contenidoNuevoAlumno');

    const btnCerrar =
        document.getElementById('cerrarModalAlumno');


    /* =====================================================
       ABRIR MODAL
       ===================================================== */

    btnNuevoAlumno.addEventListener('click', async (e) => {

        // Evita que el navegador vaya a /jugadores/crear
        e.preventDefault();

        const url = btnNuevoAlumno.getAttribute('href');

        try {

            // Mostrar modal
            modal.classList.add('activo');

            // Mensaje mientras carga
            contenido.innerHTML = `
                <div class="cargando-modal">
                    <p>Cargando formulario...</p>
                </div>
            `;


            // Obtener create.php
            const respuesta = await fetch(url);


            if (!respuesta.ok) {
                throw new Error(
                    `Error HTTP: ${respuesta.status}`
                );
            }


            // Convertir respuesta a HTML
            const html = await respuesta.text();


            // Insertar create.php
            contenido.innerHTML = html;


            /*
             * Ejecutamos nuevamente los scripts que
             * vienen dentro de create.php.
             */

            ejecutarScripts(contenido);


        } catch (error) {

            console.error(error);

            contenido.innerHTML = `
                <div style="
                    padding: 40px;
                    text-align: center;
                    color: white;
                ">
                    <h2>Error al cargar el formulario</h2>

                    <p>
                        No fue posible cargar
                        el formulario de nuevo alumno.
                    </p>
                </div>
            `;
        }

    });


    /* =====================================================
       CERRAR MODAL
       ===================================================== */

    btnCerrar.addEventListener('click', cerrarModal);


    /* =====================================================
       CERRAR AL HACER CLIC EN EL FONDO
       ===================================================== */

    modal.addEventListener('click', (e) => {

        if (e.target === modal) {
            cerrarModal();
        }

    });


    /* =====================================================
       CERRAR CON ESC
       ===================================================== */

    document.addEventListener('keydown', (e) => {

        if (
            e.key === 'Escape' &&
            modal.classList.contains('activo')
        ) {
            cerrarModal();
        }

    });


    /* =====================================================
       FUNCIÓN CERRAR
       ===================================================== */

    function cerrarModal() {

        modal.classList.remove('activo');

        /*
         * Esperamos a que termine la animación
         * antes de limpiar el contenido.
         */

        setTimeout(() => {

            contenido.innerHTML = '';

        }, 300);

    }


    /* =====================================================
       EJECUTAR SCRIPTS DEL HTML CARGADO
       ===================================================== */

    function ejecutarScripts(contenedor) {

        const scripts =
            contenedor.querySelectorAll('script');


        scripts.forEach(script => {

            const nuevoScript =
                document.createElement('script');


            if (script.src) {

                nuevoScript.src = script.src;

            } else {

                nuevoScript.textContent =
                    script.textContent;

            }


            document.body.appendChild(nuevoScript);

        });

    }

});


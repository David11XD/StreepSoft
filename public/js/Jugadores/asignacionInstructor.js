/**
 * Instructor <-> categoría en los formularios de jugador (crear y editar).
 *
 * Regla del club: una categoría pertenece a un solo instructor; un instructor puede tener varias.
 * Cada <option> de categoría trae data-instructor="<id del instructor activo>" (vacío si no tiene).
 *
 *  - Al elegir una categoría con instructor, se selecciona ese instructor.
 *  - Al elegir un instructor, se bloquean las categorías que pertenecen a OTRO instructor.
 *    Si la categoría marcada ya no es válida, se pone la única que tenga el instructor
 *    (o se deja vacía para que elija entre varias).
 *
 * El servidor aplica la misma regla al guardar (JugadorController::guardar y ::actualizar);
 * este script solo ahorra clics y evita combinaciones que el servidor corregiría.
 */
(function () {
    'use strict';

    function iniciar() {
        var instructor = document.querySelector('select[name="id_instructor"]');
        var categoria = document.querySelector('select[name="id_categorias"]');

        if (!instructor || !categoria) {
            return;
        }

        function opciones(select) {
            return Array.prototype.slice.call(select.options);
        }

        function habilitarTodas() {
            opciones(categoria).forEach(function (op) {
                op.disabled = false;
            });
        }

        // Al cambiar el instructor: solo quedan disponibles sus categorías y las que no tienen instructor
        instructor.addEventListener('change', function () {
            var idInstructor = instructor.value;

            if (idInstructor === '') {
                habilitarTodas();
                return;
            }

            opciones(categoria).forEach(function (op) {
                var dueno = op.getAttribute('data-instructor') || '';
                op.disabled = op.value !== '' && dueno !== '' && dueno !== idInstructor;
            });

            var actual = categoria.selectedOptions[0];
            if (actual && actual.value !== '' && !actual.disabled) {
                return; // la categoría elegida sigue siendo válida
            }

            var propias = opciones(categoria).filter(function (op) {
                return op.getAttribute('data-instructor') === idInstructor;
            });

            categoria.value = propias.length === 1 ? propias[0].value : '';
        });

        // Al cambiar la categoría: el instructor pasa a ser el de esa categoría
        categoria.addEventListener('change', function () {
            habilitarTodas();

            var opcion = categoria.selectedOptions[0];
            var idInstructor = opcion ? (opcion.getAttribute('data-instructor') || '') : '';

            var existe = opciones(instructor).some(function (op) {
                return op.value === idInstructor;
            });

            if (idInstructor !== '' && existe) {
                instructor.value = idInstructor;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();

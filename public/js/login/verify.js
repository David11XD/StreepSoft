document.addEventListener('DOMContentLoaded', () => {

    const inputs = document.querySelectorAll('.pin-input');
    const form = document.getElementById('pinForm');

    inputs.forEach((input, index) => {

        // Cuando se escribe un número
        input.addEventListener('input', (e) => {

            // Solo permitir números
            input.value = input.value.replace(/\D/g, '');

            // Si escribió un número, pasar al siguiente
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Manejar tecla Backspace
        input.addEventListener('keydown', (e) => {

            if (e.key === 'Backspace' && input.value === '' && index > 0) {
                inputs[index - 1].focus();
            }

        });

        // Permitir pegar el PIN completo
        input.addEventListener('paste', (e) => {

            e.preventDefault();

            const pastedData = e.clipboardData
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, inputs.length);

            pastedData.split('').forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                }
            });

            // Enfocar el último campo utilizado
            const lastIndex = pastedData.length - 1;

            if (lastIndex >= 0 && inputs[lastIndex]) {
                inputs[lastIndex].focus();
            }

        });

    });

});
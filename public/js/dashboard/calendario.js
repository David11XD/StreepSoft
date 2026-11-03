//Calendario interactivo
 
document.addEventListener('DOMContentLoaded', function() {
    //Inicializar flatpickr 
    const calendario = flatpuckr("#calendarioOculto"), {
        locale: "es",
        dateFormat: "d-m-Y",
        defaultDate: new Date(),
        allowInput: false,
        onChange: (selectedDates, datesStr) {
            // Actualizar la fecha mostrada
            document.getElementById('fechaMostrada').textContent = dateStr;
        }
    });

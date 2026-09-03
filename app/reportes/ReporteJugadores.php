<?php
declare(strict_types=1);

/**ReporteJugadores
 * Implementa ReporteDatos para el "Reporte de Jugadores".*/
class ReporteJugadores implements ReporteDatos
{
    private Jugador $jugadorModel;

    public function __construct(Jugador $jugadorModel)
    {
        $this->jugadorModel = $jugadorModel;
    }

     public function getTitulo(): string
    {
        return 'Reporte de Jugadores ' . date('Y');
    }

    public function getColumnas(): array
    {
        return ['Apellidos', 'Nombres', 'Documentos', 'Fecha de Nacimiento', 'Edad', 'Categoría', 
        'Tipo de Beca', 'Instructor', 'Estado', 'Fecha de Pago', 'Pago'];
    }

    public function getFilas(): array
    {
        $jugadores = $this->jugadorModel->obtenerTodos();
        $filas = [];

        foreach ($jugadores as $jugador) {
            // El orden de estos valores DEBE coincidir con el orden
            // definido en getColumnas() de arriba.
            $filas[] = [
                $jugador['apellidos'],
                $jugador['nombres'],
                $jugador['documentos'],
                $jugador['fecha_nacimiento'],
                $jugador['edad'],
                $jugador['categoria'],
                $jugador['tipo_beca'],
                $jugador['instructor'],
                $jugador['estado'],
                $jugador['fecha_pago'],
                $jugador['pago']
            ];
        }
        return $filas;
    }
}

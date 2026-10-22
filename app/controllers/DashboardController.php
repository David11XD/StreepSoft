<?php

declare(strict_types=1);

/**
 * DashboardController - Controla el dashboard
 */
class DashboardController extends Controller
{
    private Estadistica $estadisticaModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->estadisticaModel = new Estadistica($pdo);
    }

    /**
     * Mostrar página de estadísticas
     */
    public function index(): void
    {
        // Variables por defecto (seguras)
        $recaudacionMeses = [];
        $recaudacionTotal = 0;
        $totalJugadores = 0;
        $jugadoresConDeuda = 0;
        $deudaTotal = 0;
        $labels = json_encode([]);
        $datos = json_encode([]);
        $error = null;

        // Obtener datos del usuario actual
        $usuarioModel = new Usuario($this->pdo);
        $admin = $usuarioModel->obtenerPorId(Auth::id());

        // Saludo según la hora de entrada
        $hora = (int)date('H');
        if ($hora >= 6 && $hora < 12){
            $saludo = "Buenos días"; 
        }else if($hora >= 12 && $hora < 18){
            $saludo = "Buenas tardes";
        }else{
            $saludo = "Buenas noches";
        }

        
        try {
            // Intentar obtener datos, pero si falla, continuar con valores por defecto
            try {
                $recaudacionMeses = $this->estadisticaModel->recaudacionPorMes();
            } catch (Exception $e) {
                error_log("Dashboard: Error obteniendo recaudación por mes: " . $e->getMessage());
                $recaudacionMeses = [];
            }

            try {
                $recaudacionTotal = $this->estadisticaModel->recaudacionTotal();
            } catch (Exception $e) {
                error_log("Dashboard: Error obteniendo recaudación total: " . $e->getMessage());
                $recaudacionTotal = 0;
            }

            try {
                $totalJugadores = $this->estadisticaModel->totalJugadores();
            } catch (Exception $e) {
                error_log("Dashboard: Error obteniendo total jugadores: " . $e->getMessage());
                $totalJugadores = 0;
            }

            try {
                $jugadoresConDeuda = $this->estadisticaModel->jugadoresConDeuda();
            } catch (Exception $e) {
                error_log("Dashboard: Error obteniendo jugadores con deuda: " . $e->getMessage());
                $jugadoresConDeuda = 0;
            }

            try {
                $deudaTotal = $this->estadisticaModel->deudaTotal();
            } catch (Exception $e) {
                error_log("Dashboard: Error obteniendo deuda total: " . $e->getMessage());
                $deudaTotal = 0;
            }
        } catch (Exception $e) {
            error_log("Dashboard: Error general: " . $e->getMessage());
            $error = "Error al cargar datos del dashboard";
        }

        // Preparar datos para el gráfico
        $mesesDelAnio = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        $labels_array = [];
        $datos_array = [];

        foreach ($mesesDelAnio as $numero => $nombre) {
            $labels_array[] = $nombre;

            $monto = 0;
            foreach ($recaudacionMeses as $mes) {
                if ($mes['mes_numero'] == $numero) {
                    $monto = (float)$mes['total'];
                    break;
                }
            }
            $datos_array[] = $monto;
        }

        // Enviar datos a la vista
        $this->view('dashboard/index', [
            'titulo' => 'Estadísticas',
            'admin' => $admin, 
            'saludo' => $saludo,
            'fechaActual' =>  $fechaActual = date ('d-m-Y'),
            'recaudacionMeses' => $recaudacionMeses,
            'recaudacionTotal' => $recaudacionTotal,
            'totalJugadores' => $totalJugadores,
            'jugadoresConDeuda' => $jugadoresConDeuda,
            'deudaTotal' => $deudaTotal,
            'labels' => json_encode($labels_array),
            'datos' => json_encode($datos_array),
            'error' => $error

        ]);
    }
}

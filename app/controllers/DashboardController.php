<?php

declare(strict_types=1);

/** Controla la página principal de estadísticas. */
class DashboardController extends Controller
{
    private Estadistica $estadisticaModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->estadisticaModel = new Estadistica($pdo);
    }

    public function index(): void
    {
        $usuarioModel = new Usuario($this->pdo);
        $admin = $usuarioModel->obtenerPorId(Auth::id());

        $hora = (int) date('H');
        if ($hora >= 6 && $hora < 12) {
            $saludo = 'Buenos días';
        } elseif ($hora >= 12 && $hora < 18) {
            $saludo = 'Buenas tardes';
        } else {
            $saludo = 'Buenas noches';
        }

        // Cada indicador se consulta por separado para que un fallo aislado
        // no convierta todo el dashboard en una página con valores vacíos.
        $consultar = function (string $nombre, callable $consulta, $predeterminado) {
            try {
                return $consulta();
            } catch (Throwable $e) {
                error_log('Dashboard - ' . $nombre . ': ' . $e->getMessage());
                return $predeterminado;
            }
        };

        $anioActual = (int) date('Y');
        $anioAnterior = $anioActual - 1;
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $totalJugadores = $consultar('total de jugadores', fn() => $this->estadisticaModel->totalJugadores(), 0);
        $jugadoresNuevosMes = $consultar('jugadores nuevos del mes', fn() => $this->estadisticaModel->jugadoresNuevosMes(), 0);
        $pagosMesActual = $consultar('pagos del mes actual', fn() => $this->estadisticaModel->recaudacionMesActual(), 0.0);
        $pagosMesAnterior = $consultar('pagos del mes anterior', fn() => $this->estadisticaModel->recaudacionMesAnterior(), 0.0);
        $jugadoresEnMora = $consultar('jugadores en mora', fn() => $this->estadisticaModel->jugadoresEnMora(), 0);
        $totalInstructoresActivos = $consultar('instructores activos', fn() => $this->estadisticaModel->totalInstructoresActivos(), 0);
        $sinDocumento = $consultar('jugadores sin documento', fn() => $this->estadisticaModel->jugadoresSinDocumentoIdentidad(), 0);
        $faltantesDocumentos = $consultar('faltantes por tipo de documento', fn() => $this->estadisticaModel->faltantesPorTipoDocumento(), ['eps' => 0, 'consentimiento' => 0, 'idrd' => 0]);
        $sinFoto = $consultar('jugadores sin foto', fn() => $this->estadisticaModel->jugadoresSinFoto(), 0);
        $sinResponsable = $consultar('jugadores sin responsable', fn() => $this->estadisticaModel->jugadoresSinResponsable(), 0);
        $deudasRecientes = $consultar('deudas recientes', fn() => $this->estadisticaModel->deudasRecientes(4), []);
        $jugadoresPorCategoria = $consultar('jugadores por categoría', fn() => $this->estadisticaModel->jugadoresPorCategoria(), []);
        $recaudacionTotal = $consultar('recaudación anual', fn() => $this->estadisticaModel->recaudacionTotal($anioActual), 0.0);

        if ($pagosMesAnterior > 0) {
            $variacion = (($pagosMesActual - $pagosMesAnterior) / $pagosMesAnterior) * 100;
            $variacionPagosTexto = sprintf('%s%.1f%% vs. mes anterior', $variacion > 0 ? '+' : '', $variacion);
        } elseif ($pagosMesActual > 0) {
            $variacionPagosTexto = 'Sin pagos el mes anterior';
        } else {
            $variacionPagosTexto = 'Sin ingresos en ambos meses';
        }

        // Datos completos de enero a diciembre para el gráfico y selector de año.
        $ingresosPorAnio = [];
        foreach ([$anioActual, $anioAnterior] as $anio) {
            $porMes = array_fill(1, 12, 0.0);
            $registros = $consultar(
                'recaudación por mes ' . $anio,
                fn() => $this->estadisticaModel->recaudacionPorMes($anio),
                []
            );
            foreach ($registros as $registro) {
                $numeroMes = (int) ($registro['mes_numero'] ?? 0);
                if ($numeroMes >= 1 && $numeroMes <= 12) {
                    $porMes[$numeroMes] = (float) ($registro['total'] ?? 0);
                }
            }
            $ingresosPorAnio[$anio] = array_values($porMes);
        }

        $this->view('dashboard/index', [
            'titulo' => 'Estadísticas',
            'admin' => $admin,
            'saludo' => $saludo,
            'fechaActual' => date('d-m-Y'),
            'totalJugadores' => $totalJugadores,
            'jugadoresNuevosMes' => $jugadoresNuevosMes,
            'pagosMesActual' => $pagosMesActual,
            'variacionPagosTexto' => $variacionPagosTexto,
            'jugadoresEnMora' => $jugadoresEnMora,
            'totalInstructoresActivos' => $totalInstructoresActivos,
            'sinDocumento' => $sinDocumento,
            'faltantesDocumentos' => $faltantesDocumentos,
            'sinFoto' => $sinFoto,
            'sinResponsable' => $sinResponsable,
            'deudasRecientes' => $deudasRecientes,
            'jugadoresPorCategoria' => $jugadoresPorCategoria,
            'recaudacionTotal' => $recaudacionTotal,
            'anioActual' => $anioActual,
            'anioAnterior' => $anioAnterior,
            'meses' => array_values($meses),
            'ingresosPorAnio' => $ingresosPorAnio,
        ]);
    }
}

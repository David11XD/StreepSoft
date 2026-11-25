<?php













?>
class MensualidadController extends Controller
{
    private Mensualidad $mensualidadModel;
    private Jugador $jugadorModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->mensualidadModel = new Mensualidad($pdo);
        $this->jugadorModel = new Jugador($pdo);
    }

    // Listar todas las mensualidades del año actual
    public function listar(): void
    {
        $pagos = $this->mensualidadModel->obtenerTodosDelAnio();
        $valorMensualidad = $this->mensualidadModel->getValorMensualidad();
        $meses = $this->mensualidadModel->getMeses();

        $this->view('mensualidad/listar', [
            'pagos' => $pagos,
            'valorMensualidad' => $valorMensualidad,
            'meses' => $meses,
            'anio' => date('Y'),
            'titulo' => 'Gestión de Mensualidades'
        ]);
    }

    // Muestra el formulario con el cual se paga la mensualidad
    public function registrarForm(): void
    {
        $meses = $this->mensualidadModel->getMeses();
        $mesesCodigos = $this->mensualidadModel->getMesesCodigos();
        $valorMensualidad = $this->mensualidadModel->getValorMensualidad();

        // Obtiene a los jugadores activos
        $jugadores = $this->jugadorModel->obtenerActivos();
        
        $this->view('mensualidad/registrar', [
            'jugadores' => $jugadores,
            'meses' => $meses,
            'mesesCodigos' => $mesesCodigos,
            'valorMensualidad' => $valorMensualidad,
            'titulo' => 'Registrar Mensualidad'
        ]);
    }

    // Guardar Nueva Mensualidad
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/mensualidad/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/mensualidad/registrar');
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $mes = $_POST['mes'] ?? '';
        $valor = (float)($_POST['valor'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($jugadorId <= 0 || empty($mes) || $valor <= 0 || empty($metodoPago)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            $this->redirect('/mensualidad/registrar');
            return;
        }

        $jugador = $this->jugadorModel->obtenerPorId($jugadorId);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/mensualidad/registrar');
            return;
        }

        if ($this->mensualidadModel->mesPagado($jugadorId, $mes)) {
            $mesNombre = $this->mensualidadModel->getMeses()[$mes] ?? $mes;
            $_SESSION['error'] = "Este jugador ya pagó la mensualidad de {$mesNombre}.";
            $this->redirect('/mensualidad/registrar');
            return;
        }

        if ($this->mensualidadModel->registrar($jugadorId, $mes, $valor, $metodoPago, $fechaPago)) {
            $mesNombre = $this->mensualidadModel->getMeses()[$mes] ?? $mes;
            $_SESSION['success'] = "Mensualidad de {$mesNombre} registrada correctamente para " . $jugador['nombre'] . " " . $jugador['apellido'];
        } else {
            $_SESSION['error'] = "Error al registrar la mensualidad.";
        }

        $this->redirect('/mensualidad/listar');
    }

    // Matriz de pagos de un jugador
    public function matriz(int $id): void
    {
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/mensualidad/listar');
            return;
        }

        $matriz = $this->mensualidadModel->obtenerMatrizPagos($id);
        $meses = $this->mensualidadModel->getMeses();
        $valorMensualidad = $this->mensualidadModel->getValorMensualidad();

        $this->view('mensualidad/matriz', [
            'jugador' => $jugador,
            'matriz' => $matriz,
            'meses' => $meses,
            'valorMensualidad' => $valorMensualidad,
            'anio' => date('Y'),
            'titulo' => 'Matriz de Pagos'
        ]);
    }

    // Listado jugadores que deben mensualidad
    public function morosos(string $mes): void
    {
        $meses = $this->mensualidadModel->getMeses();
        
        if (!isset($meses[$mes])) {
            $_SESSION['error'] = "Mes no válido.";
            $this->redirect('/mensualidad/listar');
            return;
        }

        $morosos = $this->mensualidadModel->obtenerMorososPorMes($mes);
        $mesNombre = $meses[$mes];

        $this->view('mensualidad/morosos', [
            'morosos' => $morosos,
            'mes' => $mes,
            'mesNombre' => $mesNombre,
            'anio' => date('Y'),
            'titulo' => "Morosos - {$mesNombre}"
        ]);
    }

    // Redirige
    // private function redirect(string $url): void
    // {
    //     header('Location: /proyecto' . $url);
    //     exit;
    // }
}

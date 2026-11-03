<?php

declare(strict_types=1);

class MatriculaController extends Controller
{
    private Matricula $matriculaModel;
    private Jugador $jugadorModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->matriculaModel = new Matricula($pdo);
        $this->jugadorModel = new Jugador($pdo);
    }

    
     //Listar todas las matrículas del año actual
     
     
    public function listar(): void
    {
        $matriculas = $this->matriculaModel->obtenerTodasDelAnio();
        $noPagos = $this->matriculaModel->obtenerNoPagosMatricula();
        $valorMatricula = $this->matriculaModel->getValorMatricula();

        $this->view('matricula/listar', [
            'matriculas' => $matriculas,
            'noPagos' => $noPagos,
            'valorMatricula' => $valorMatricula,
            'anio' => date('Y'),
            'titulo' => 'Gestión de Matrículas'
        ]);
    }

    
    //Mostrar formulario para registrar matrícula
     
    public function registrarForm(): void
    {
        $jugadores = $this->matriculaModel->obtenerMorososMatricula();
        $valorMatricula = $this->matriculaModel->getValorMatricula();

        $this->view('matricula/registrar', [
            'jugadores' => $jugadores,
            'valorMatricula' => $valorMatricula,
            'titulo' => 'Registrar Matrícula'
        ]);
    }

    
     //Guardar una nueva matrícula
    
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/matricula/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/matricula/registrar');
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $valor = (float)($_POST['valor'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($jugadorId <= 0 || $valor <= 0 || empty($metodoPago)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            $this->redirect('/matricula/registrar');
            return;
        }

        $jugador = $this->jugadorModel->obtenerPorId($jugadorId);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/matricula/registrar');
            return;
        }

        if ($this->matriculaModel->pagoRealizado($jugadorId)) {
            $_SESSION['error'] = "Este jugador ya pagó la matrícula del año actual.";
            $this->redirect('/matricula/registrar');
            return;
        }

        if ($this->matriculaModel->registrar($jugadorId, $valor, $metodoPago, $fechaPago)) {
            $_SESSION['success'] = "Matrícula registrada correctamente para " . $jugador['nombre'] . " " . $jugador['apellido'];
        } else {
            $_SESSION['error'] = "Error al registrar la matrícula.";
        }

        $this->redirect('/matricula/listar');
    }

    /**
     * Redirigir a una URL
     */
    // private function redirect(string $url): void
    // {
    //     header('Location: /proyecto' . $url);
    //     exit;
    // }
}
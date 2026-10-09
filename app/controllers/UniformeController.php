<?php

class UniformeController extends Controller
{
    private Uniforme $uniformeModel;
    private Jugador $jugadorModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->uniformeModel = new Uniforme($pdo);
        $this->jugadorModel = new Jugador($pdo);
    }

    
     //Ver estado del uniforme de un jugador
     
     
    public function ver(int $id): void
    {
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/jugadores/gestion');
            return;
        }

        $uniforme = $this->uniformeModel->obtenerPorJugador($id);
        $abonos = $uniforme ? $this->uniformeModel->obtenerAbonos($uniforme['id']) : [];

        $this->view('uniforme/ver', [
            'jugador' => $jugador,
            'uniforme' => $uniforme,
            'abonos' => $abonos,
            'titulo' => 'Estado del Uniforme'
        ]);
    }

    
     //Mostrar formulario para abonar al uniforme
     
    public function abonarForm(int $id): void
    {
        $uniforme = $this->uniformeModel->obtenerPorJugador($id);

        if (!$uniforme) {
            $_SESSION['error'] = "Este jugador no tiene uniforme registrado.";
            $this->redirect('/jugadores/gestion');
            return;
        }

        $jugador = $this->jugadorModel->obtenerPorId($id);

        $this->view('uniforme/abonar', [
            'jugador' => $jugador,
            'uniforme' => $uniforme,
            'titulo' => 'Abonar al Uniforme'
        ]);
    }

    
      //Registrar abono al uniforme
     
    public function abonar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/uniforme/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/uniforme/abonar/' . $_POST['jugador_id']);
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $uniformeId = (int)($_POST['uniforme_id'] ?? 0);
        $valor = (float)($_POST['valor'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($uniformeId <= 0 || $valor <= 0 || empty($metodoPago)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            $this->redirect('/uniforme/abonar/' . $jugadorId);
            return;
        }

        $uniforme = $this->uniformeModel->obtenerPorJugador($jugadorId);

        if (!$uniforme) {
            $_SESSION['error'] = "Uniforme no encontrado.";
            $this->redirect('/jugadores/gestion');
            return;
        }

        if ($valor > $uniforme['saldo_pendiente']) {
            $_SESSION['error'] = "El abono no puede ser mayor al saldo pendiente: $" . number_format($uniforme['saldo_pendiente'], 0);
            $this->redirect('/uniforme/abonar/' . $jugadorId);
            return;
        }

        if ($this->uniformeModel->registrarAbono($uniformeId, $valor, $metodoPago, $fechaPago)) {
            $jugador = $this->jugadorModel->obtenerPorId($jugadorId);
            $_SESSION['success'] = "Abono de $" . number_format($valor, 0) . " registrado correctamente para " . $jugador['nombre'] . " " . $jugador['apellido'];
        } else {
            $_SESSION['error'] = "Error al registrar el abono.";
        }

        $this->redirect('/uniforme/ver/' . $jugadorId);
    }

    
     //Listar morosos de uniforme
     

    /**
     * Redirigir a una URL
     */
    // private function redirect(string $url): void
    // {
    //     header('Location: /proyecto' . $url);
    //     exit;
    // }
}
<?php

class DocumentoController extends Controller
{
    private Documento $documentoModel;
    private Jugador $jugadorModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->documentoModel = new Documento($pdo);
        $this->jugadorModel = new Jugador($pdo);
    }

    
     //Ver documentos de un jugador
     
    public function ver(int $id): void
    {
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/jugadores/gestion');
            return;
        }

        $documentos = $this->documentoModel->obtenerPorJugador($id);
        $resumen = $this->documentoModel->getResumen($id);

        $this->view('documentos/ver', [
            'jugador' => $jugador,
            'documentos' => $documentos,
            'resumen' => $resumen,
            'titulo' => 'Documentos del Jugador'
        ]);
    }

    
    //Mostrar formulario para actualizar documentos
    
    public function editarForm(int $id): void
    {
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/jugadores/gestion');
            return;
        }

        $documentos = $this->documentoModel->obtenerPorJugador($id);

        $this->view('documentos/editar', [
            'jugador' => $jugador,
            'documentos' => $documentos,
            'titulo' => 'Actualizar Documentos'
        ]);
    }


    //Actualizar documentos de un jugador
     
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/jugadores/gestion');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/documentos/editar/' . $_POST['jugador_id']);
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);

        $datos = [
            'doc_identidad' => isset($_POST['doc_identidad']) ? 1 : 0,
            'consentimiento' => isset($_POST['consentimiento']) ? 1 : 0,
            'ficha_idrd' => isset($_POST['ficha_idrd']) ? 1 : 0,
            'cert_eps' => isset($_POST['cert_eps']) ? 1 : 0
        ];

        if ($this->documentoModel->actualizarMultiples($jugadorId, $datos)) {
            $_SESSION['success'] = "Documentos actualizados correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar los documentos.";
        }

        $this->redirect('/documentos/ver/' . $jugadorId);
    }

    
    //Marcar un documento específico como entregado
    
    public function marcar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/jugadores/gestion');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/documentos/editar/' . $_POST['jugador_id']);
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $campo = $_POST['campo'] ?? '';
        $valor = (int)($_POST['valor'] ?? 0);

        if ($this->documentoModel->actualizarDocumento($jugadorId, $campo, $valor)) {
            $_SESSION['success'] = "Documento actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el documento.";
        }

        $this->redirect('/documentos/ver/' . $jugadorId);
    }

    
     //Listar jugadores con documentos incompletos
     
     
    public function incompletos(): void
    {
        $jugadores = $this->documentoModel->obtenerIncompletos();

        $this->view('documentos/incompletos', [
            'jugadores' => $jugadores,
            'titulo' => 'Documentos Incompletos'
        ]);
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
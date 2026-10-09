<?php

class ConfiguracionController extends Controller
{
    private Configuracion $configuracionModel;
    private Usuario $usuarioModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->configuracionModel = new Configuracion($pdo);
        $this->usuarioModel = new Usuario($pdo);
    }

    /**
     * Mostrar panel de configuración
     * Ruta: /configuracion (GET)
     */
    public function index(): void
    {
        $usuarioId = $_SESSION['user_id'] ?? 0;
        $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
        $estadisticas = $this->usuarioModel->obtenerEstadisticas();

        // Obtener configuraciones actuales
        $configPagos = $this->configuracionModel->obtenerConfiguracionPagos();
        $diaGracia = $configPagos['dias_gracia'];
        $diaPago = $configPagos['dia_cobro'];

        $this->view('configuracion/index', [
            'usuario' => $usuario,
            'estadisticas' => $estadisticas,
            'dia_gracia' => $diaGracia,
            'dia_cobro' => $diaPago,
            'titulo' => 'Configuración'
        ]);
    }

    /**
     * Guardar configuración de deudas (días de gracia y día de pago)
     * Ruta: /configuracion/guardar-deudas (POST)
     */
    public function guardarDeudas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Error de seguridad']);
            return;
        }

        $diaGracia = (int) ($_POST['dia_gracia'] ?? 5);
        $diaPago = (int) ($_POST['dia_cobro'] ?? $_POST['dia_pago'] ?? 1);

        // Validaciones
        if ($diaGracia < 0 || $diaGracia > 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Días de gracia debe estar entre 0 y 30']);
            return;
        }

        if ($diaPago < 1 || $diaPago > 31) {
            http_response_code(400);
            echo json_encode(['error' => 'Día de pago debe estar entre 1 y 31']);
            return;
        }

        try {
            // Guardar/actualizar días de gracia
            $this->configuracionModel->actualizar('dias_gracia', (string) $diaGracia);
            
            // Guardar/actualizar día de pago
            $this->configuracionModel->actualizar('dia_cobro', (string) $diaPago);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensaje' => 'Configuración guardada correctamente'
            ]);
        } catch (Exception $e) {
            error_log("ConfiguracionController::guardarDeudas - " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar la configuración']);
        }
    }

    /**
     * Actualizar perfil del director
     * Ruta: /configuracion/actualizar-perfil (POST)
     */
    public function actualizarPerfil(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/configuracion');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/configuracion');
            return;
        }

        $usuarioId = $_SESSION['user_id'] ?? 0;
        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombreCompleto) || empty($usuario) || empty($email)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            $this->redirect('/configuracion');
            return;
        }

        // Verificar usuario duplicado
        if ($this->usuarioModel->usuarioExiste($usuario, $usuarioId)) {
            $_SESSION['error'] = "El nombre de usuario ya está en uso.";
            $this->redirect('/configuracion');
            return;
        }

        // Verificar email duplicado
        if ($this->usuarioModel->emailExiste($email, $usuarioId)) {
            $_SESSION['error'] = "El email ya está en uso.";
            $this->redirect('/configuracion');
            return;
        }

        if ($this->usuarioModel->actualizarPerfil($usuarioId, $nombreCompleto, $usuario, $email)) {
            $_SESSION['success'] = "Perfil actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el perfil.";
        }

        $this->redirect('/configuracion');
    }

    /**
     * Cambiar contraseña
     * Ruta: /configuracion/cambiar-password (POST)
     */
    public function cambiarPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/configuracion');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/configuracion');
            return;
        }

        $usuarioId = $_SESSION['user_id'] ?? 0;
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNuevo = $_POST['password_nuevo'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';

        if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConfirmar)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            $this->redirect('/configuracion');
            return;
        }

        if ($passwordNuevo !== $passwordConfirmar) {
            $_SESSION['error'] = "Las contraseñas nuevas no coinciden.";
            $this->redirect('/configuracion');
            return;
        }

        if (strlen($passwordNuevo) < 6) {
            $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
            $this->redirect('/configuracion');
            return;
        }

        // Verificar contraseña actual
        if (!$this->usuarioModel->verificarPassword($usuarioId, $passwordActual)) {
            $_SESSION['error'] = "La contraseña actual es incorrecta.";
            $this->redirect('/configuracion');
            return;
        }

        if ($this->usuarioModel->cambiarPassword($usuarioId, $passwordNuevo)) {
            $_SESSION['success'] = "Contraseña actualizada correctamente.";
        } else {
            $_SESSION['error'] = "Error al cambiar la contraseña.";
        }

        $this->redirect('/configuracion');
    }

    /**
     * Subir foto de perfil (director)
     * Ruta: /configuracion/subir-foto (POST)
     */
    public function subirFoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/configuracion');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/configuracion');
            return;
        }

        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "No se seleccionó ninguna foto.";
            $this->redirect('/configuracion');
            return;
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreFoto = 'director_' . $_SESSION['user_id'] . '.' . $extension;
        $rutaFoto = __DIR__ . '/../../public/uploads/perfil/' . $nombreFoto;

        // Crear carpeta si no existe
        if (!is_dir(__DIR__ . '/../../public/uploads/perfil')) {
            mkdir(__DIR__ . '/../../public/uploads/perfil', 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
            // Guardar ruta en configuración
            $this->configuracionModel->actualizar('foto_director', '/uploads/perfil/' . $nombreFoto);
            $_SESSION['success'] = "Foto actualizada correctamente.";
        } else {
            $_SESSION['error'] = "Error al subir la foto.";
        }

        $this->redirect('/configuracion');
    }
}
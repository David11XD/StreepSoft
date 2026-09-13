<?php

class InstructorController extends Controller
{
    private Instructor $instructorModel;
    private Categoria $categoriaModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->instructorModel = new Instructor($pdo);
        $this->categoriaModel = new Categoria($pdo);
    }

    
     //Listar todos los instructores
    
    public function listar(): void
    {
        $instructores = $this->instructorModel->obtenerTodos();

        $totalInstructores = count($instructores);
        $totalActivos = count(array_filter($instructores, fn($i) => $i['estado'] === 'activo'));
        $totalInactivos = count(array_filter($instructores, fn($i) => $i['estado'] === 'inactivo'));

        $totalJugadoresAsignados = 0;
        try {
            $jugadorModel = new Jugador($this->pdo);
            $jugadores = $jugadorModel->obtenerTodos();
            $totalJugadoresAsignados = count(array_filter($jugadores, fn($j) => $j['id_instructor'] > 0));
        } catch (Exception $e) {
            error_log("Contar jugadores asignados: " . $e->getMessage());
        }


        $this->view('instructores/index', [
            'instructores' => $instructores,
            'titulo' => 'Gestión de Instructores',
            'totalInstructores' => $totalInstructores,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'totalJugadoresAsignados' => $totalJugadoresAsignados,
        ]);
    }

    
     //Mostrar formulario para crear instructor
    
    public function crearForm(): void
    {
        $categorias = $this->categoriaModel->obtenerTodas();

        $this->view('instructores/agregar', [
            'categorias' => $categorias,
            'titulo' => 'Crear Instructor'
        ]);
    }

    
     //Guardar nuevo instructor
     
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/instructores/listar');
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/instructores/crear?error=csrf');
        }

        $estado = trim($_POST['estado'] ?? '');

        $estado = strtolower($estado);
        if (!in_array($estado, ['activo', 'inactivo'], true)){
            $this->redirect('/streepsoft/instructores/crear?error=estado_invalido');
        }

        // Antes se leían nombre/categoria_id/telefono/email (ninguno
        // existe en la tabla real). Estos sí coinciden con las
        // columnas reales de `instructor`.
        $datos = [
            'nombres' => trim($_POST['nombres'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'edad' => (int) ($_POST['edad'] ?? 0) ?: null,
            'numero_celular' => trim($_POST['numero_celular'] ?? ''),
            'id_categorias' => (int) ($_POST['id_categorias'] ?? 0),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'         => $estado,
        ];

        try {
            // ✅ PROCESAR FOTO SI SE ENVIÓ
            $fotoBase64 = $_POST['foto_base64'] ?? null;
            if (!empty($fotoBase64)) {
                $nombreFoto = $this->procesarFotoInstructor($fotoBase64);
                $datos['foto'] = $nombreFoto;  // ✅ AGREGAR A LOS DATOS
            }
            if ($this->instructorModel->crear($datos)) {
                $this->redirect('/streepsoft/instructores/listar?success=creado');
            }
        } catch (Exception $e) {
            error_log("Guardar instructor (foto): " . $e->getMessage());
            $this->redirect('/streepsoft/instructores/crear?error=creacion_fallida');
        }

        if ($datos['nombres'] === '' || $datos['apellidos'] === '' || $datos['id_categorias'] <= 0) {
            $this->redirect('/streepsoft/instructores/crear?error=campos_vacios');
        }

        if ($this->instructorModel->crear($datos)) {
            $this->redirect('/streepsoft/instructores/listar?success=creado');
        } else {
            $this->redirect('/streepsoft/instructores/crear?error=creacion_fallida');
        }
    }

    
    //Mostrar formulario para editar instructor
     
    public function editarForm(int $id): void
    {
        $instructor = $this->instructorModel->obtenerPorId($id);

        if (!$instructor) {
            $this->redirect('/streepsoft/instructores/listar?error=instructor_invalido');
        }

        $categorias = $this->categoriaModel->obtenerTodas();

        $this->view('instructores/editar', [
            'instructor' => $instructor,
            'categorias' => $categorias,
            'titulo' => 'Editar Instructor'
        ]);
    }

    
    //Actualizar instructor
     
    public function actualizar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/instructores/listar');
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=csrf');
        }

        $instructor = $this->instructorModel->obtenerPorId($id);
        if (!$instructor) {
            $this->redirect('/streepsoft/instructores/listar?error=instructor_invalido');
        }

        $estado = strtolower(trim($_POST['estado'] ?? ''));
        if (!in_array($estado, ['activo', 'inactivo'], true)){
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=estado_invalido');
        }

        $datos = [
            'nombres' => trim($_POST['nombres'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'edad' => (int) ($_POST['edad'] ?? 0) ?: null,
            'numero_celular' => trim($_POST['numero_celular'] ?? ''),
            'id_categorias' => (int) ($_POST['id_categorias'] ?? 0),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'         => $estado,
        ];

        try {
            $fotoBase64 = $_POST['foto_base64'] ?? null;
            if (!empty($fotoBase64)) {
                
                if (!empty($instructor['foto']) && $instructor['foto'] !== 'default.jpg') {
                    $rutaAnterior = __DIR__ . '/../../public/Image/instructores/' . $instructor['foto'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                $nombreFoto = $this->procesarFotoInstructor($fotoBase64);
                $datos['foto'] = $nombreFoto;  
            }

            if ($this->instructorModel->actualizar($id, $datos)) {
                $this->redirect('/streepsoft/instructores/listar?success=actualizado');
            }

        } catch (Exception $e) {
            error_log("Actualizar instructor (foto): " . $e->getMessage());
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=actualizacion_fallida');
        }

        if ($datos['nombres'] === '' || $datos['apellidos'] === '' || $datos['id_categorias'] <= 0) {
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=campos_vacios');
        }

        if ($this->instructorModel->actualizar($id, $datos)) {
            $this->redirect('/streepsoft/instructores/listar?success=actualizado');
        } else {
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=actualizacion_fallida');
        }
    }


     //Retirar instructor se cambia a estado inactivo
    
    public function retirar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/instructores/listar');
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/instructores/listar?error=csrf');
        }

        if ($this->instructorModel->retirar($id)) {
            $this->redirect('/streepsoft/instructores/listar?success=retirado');
        } else {
            $this->redirect('/streepsoft/instructores/listar?error=retiro_fallido');
        }
    }

    public function subirFoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        // Recibir ID del instructor y foto base64
        $idInstructor = (int) ($_POST['id_instructor'] ?? 0);
        $fotoBase64 = $_POST['foto_base64'] ?? null;

        if ($idInstructor <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de instructor inválido']);
            return;
        }

        try {
            // Obtener instructor actual para ver si ya tiene foto
            $instructor = $this->instructorModel->obtenerPorId($idInstructor);
            if (!$instructor) {
                throw new Exception('Instructor no encontrado');
            }

            // Procesar la foto
            $nombreFoto = $this->procesarFotoInstructor($fotoBase64);

            // Eliminar foto anterior si existe
            if (!empty($instructor['foto']) && $instructor['foto'] !== 'default.jpg') {
                $rutaAnterior = __DIR__ . '/../../public/Image/instructores/' . $instructor['foto'];
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            // Actualizar en BD
            $this->instructorModel->actualizarFoto($idInstructor, $nombreFoto);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensaje' => 'Foto actualizada correctamente',
                'foto' => $nombreFoto
            ]);
        } catch (Exception $e) {
            error_log('Subir foto instructor: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function procesarFotoInstructor(?string $dataUrl): ?string
    {
        if (empty($dataUrl)) {
            return null;
        }

        // El data URL viene como "data:image/jpeg;base64,xxxxx"
        if (!preg_match('/^data:(image\/(jpeg|png));base64,(.+)$/', $dataUrl, $match)) {
            throw new Exception('Formato de imagen no válido');
        }

        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mimeDeclarado = $match[1];
        $contenidoBase64 = $match[3];

        // Decodificar base64
        $binario = base64_decode($contenidoBase64, true);
        if ($binario === false) {
            throw new Exception('No se pudo leer la imagen enviada');
        }

        // Validar tamaño máximo (3 MB)
        $tamanoMaximo = 3 * 1024 * 1024;
        if (strlen($binario) > $tamanoMaximo) {
            throw new Exception('La foto supera el tamaño máximo permitido (3 MB)');
        }

        // Validar MIME type real del contenido (no confiar solo en lo declarado)
        $info = getimagesizefromstring($binario);
        if ($info === false || !isset($tiposPermitidos[$info['mime']])) {
            throw new Exception('Formato de imagen no permitido (solo JPG o PNG)');
        }

        // Crear carpeta de instructores si no existe
        $carpetaDestino = __DIR__ . '/../../public/Image/instructores';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        // Generar nombre de archivo único y seguro (nunca datos del cliente)
        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$info['mime']];

        // Guardar archivo
        if (file_put_contents($carpetaDestino . '/' . $nombreArchivo, $binario) === false) {
            throw new Exception('No se pudo guardar la foto en el servidor');
        }

        return $nombreArchivo;
    }

    /**
     * Eliminar foto del instructor
     */
    public function eliminarFoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $idInstructor = (int) ($_POST['id_instructor'] ?? 0);

        if ($idInstructor <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de instructor inválido']);
            return;
        }

        try {
            $instructor = $this->instructorModel->obtenerPorId($idInstructor);
            if (!$instructor) {
                throw new Exception('Instructor no encontrado');
            }

            // Eliminar archivo físico si existe
            if (!empty($instructor['foto']) && $instructor['foto'] !== 'default.jpg') {
                $rutaFoto = __DIR__ . '/../../public/Image/instructores/' . $instructor['foto'];
                if (file_exists($rutaFoto)) {
                    unlink($rutaFoto);
                }
            }

            // Actualizar BD (guardar null o 'default.jpg')
            $this->instructorModel->actualizarFoto($idInstructor, null);

            http_response_code(200);
            echo json_encode(['success' => true, 'mensaje' => 'Foto eliminada correctamente']);
        } catch (Exception $e) {
            error_log('Eliminar foto instructor: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    
}
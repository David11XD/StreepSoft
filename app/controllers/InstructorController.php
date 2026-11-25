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
        $categorias = $this->categoriaModel->obtenerConInstructor();

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
            'categorias' => $this->categoriasDelFormulario(),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'         => $estado,
        ];

        // Se valida ANTES de guardar (antes esta validación quedaba después de guardar y nunca se aplicaba)
        if ($datos['nombres'] === '' || $datos['apellidos'] === '') {
            $this->redirect('/streepsoft/instructores/crear?error=campos_vacios');
        }

        // Se declara fuera del try para poder eliminar la foto si algo falla
        $nombreFoto = null;

        try {
            // Procesar la foto si se envió
            $fotoBase64 = $_POST['foto_base64'] ?? null;
            if (!empty($fotoBase64)) {
                $nombreFoto = $this->procesarFotoInstructor($fotoBase64);
                $datos['foto'] = $nombreFoto;
            }

            if ($this->instructorModel->crear($datos)) {
                $this->redirect('/streepsoft/instructores/listar?success=creado');
            }

            // crear() no guardó nada: la foto que se alcanzó a subir queda sin uso, se elimina
            $this->eliminarArchivoFotoInstructor($nombreFoto);
            $this->redirect('/streepsoft/instructores/crear?error=creacion_fallida');
        } catch (Exception $e) {
            error_log("Guardar instructor: " . $e->getMessage());
            $this->eliminarArchivoFotoInstructor($nombreFoto);
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

        $categorias = $this->categoriaModel->obtenerConInstructor();

        $this->view('instructores/editar', [
            'instructor' => $instructor,
            'categorias' => $categorias,
            'idsSeleccionados' => $this->instructorModel->obtenerIdsCategorias($id),
            'idInstructorActual' => $id,
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
            'categorias' => $this->categoriasDelFormulario(),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'         => $estado,
        ];

        // Se valida ANTES de guardar (antes esta validación quedaba después de guardar y nunca se aplicaba)
        if ($datos['nombres'] === '' || $datos['apellidos'] === '') {
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=campos_vacios');
        }

        // Se declara fuera del try para poder eliminar la foto nueva si algo falla
        $nombreFoto = null;

        try {
            $fotoBase64 = $_POST['foto_base64'] ?? null;
            if (!empty($fotoBase64)) {
                // 1) Primero se guarda la foto nueva: si falla, la foto actual no se toca
                $nombreFoto = $this->procesarFotoInstructor($fotoBase64);
                $datos['foto'] = $nombreFoto;
            }

            if ($this->instructorModel->actualizar($id, $datos)) {
                // 2) La BD ya usa la foto nueva: ahora sí se elimina la anterior
                if ($nombreFoto !== null) {
                    $this->eliminarArchivoFotoInstructor($instructor['foto'] ?? null);
                }
                $this->redirect('/streepsoft/instructores/listar?success=actualizado');
            }

            // actualizar() no guardó nada: la foto nueva queda sin uso, se elimina
            $this->eliminarArchivoFotoInstructor($nombreFoto);
            $this->redirect('/streepsoft/instructores/editar/' . $id . '?error=actualizacion_fallida');
        } catch (Exception $e) {
            error_log("Actualizar instructor: " . $e->getMessage());
            $this->eliminarArchivoFotoInstructor($nombreFoto);
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

        // Se declara fuera del try para poder eliminar la foto nueva si algo falla
        $nombreFoto = null;

        try {
            // Obtener instructor actual para ver si ya tiene foto
            $instructor = $this->instructorModel->obtenerPorId($idInstructor);
            if (!$instructor) {
                throw new Exception('Instructor no encontrado');
            }

            // Procesar la foto nueva (si falla, la foto actual no se toca)
            $nombreFoto = $this->procesarFotoInstructor($fotoBase64);

            // Actualizar en BD
            if (!$this->instructorModel->actualizarFoto($idInstructor, $nombreFoto)) {
                throw new Exception('No se pudo guardar la foto en la base de datos');
            }

            // La BD ya usa la foto nueva: ahora sí se elimina la anterior
            $this->eliminarArchivoFotoInstructor($instructor['foto'] ?? null);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensaje' => 'Foto actualizada correctamente',
                'foto' => $nombreFoto
            ]);
        } catch (Exception $e) {
            error_log('Subir foto instructor: ' . $e->getMessage());
            // Si la foto nueva alcanzó a guardarse pero no quedó en la BD, se elimina
            $this->eliminarArchivoFotoInstructor($nombreFoto);
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Ids de las categorías marcadas en el formulario (checkboxes id_categorias[]).
     * Puede venir vacío: un instructor puede quedarse sin categorías.
     *
     * @return int[]
     */
    private function categoriasDelFormulario(): array
    {
        $ids = $_POST['id_categorias'] ?? [];
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            fn (int $v): bool => $v > 0
        )));
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
     * Elimina del disco una foto de instructor (solo dentro de public/Image/instructores).
     */
    private function eliminarArchivoFotoInstructor(?string $nombreFoto): void
    {
        if (empty($nombreFoto) || $nombreFoto === 'default.jpg') {
            return;
        }

        // basename() evita borrar archivos fuera de la carpeta si la BD trae un valor raro
        $ruta = __DIR__ . '/../../public/Image/instructores/' . basename($nombreFoto);

        if (is_file($ruta) && !unlink($ruta)) {
            error_log('InstructorController::eliminarArchivoFotoInstructor - No se pudo eliminar: ' . $ruta);
        }
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

            // Primero se actualiza la BD: si falla, el archivo sigue existiendo y no queda una referencia rota
            if (!$this->instructorModel->actualizarFoto($idInstructor, null)) {
                throw new Exception('No se pudo actualizar la foto en la base de datos');
            }

            // Ahora sí se elimina el archivo físico
            $this->eliminarArchivoFotoInstructor($instructor['foto'] ?? null);

            http_response_code(200);
            echo json_encode(['success' => true, 'mensaje' => 'Foto eliminada correctamente']);
        } catch (Exception $e) {
            error_log('Eliminar foto instructor: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    
}
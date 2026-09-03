<?php

class InstructorController extends Controller
{
    private Instructor $instructorModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->instructorModel = new Instructor($pdo);
    }

    
     //Listar todos los instructores
    
    public function listar(): void
    {
        $instructores = $this->instructorModel->obtenerTodos();

        $this->view('instructores/listar', [
            'instructores' => $instructores,
            'titulo' => 'Gestión de Instructores'
        ]);
    }

    
     //Mostrar formulario para crear instructor
    
    public function crearForm(): void
    {
        $categorias = $this->getCategorias();

        $this->view('instructores/crear', [
            'categorias' => $categorias,
            'titulo' => 'Crear Instructor'
        ]);
    }

    
     //Guardar nuevo instructor
     
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/instructores/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad.";
            $this->redirect('/instructores/crear');
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre) || $categoriaId <= 0) {
            $_SESSION['error'] = "Nombre y categoría son obligatorios.";
            $this->redirect('/instructores/crear');
            return;
        }

        $sql = "INSERT INTO instructores (nombre, categoria_id, telefono, email, activo) 
                VALUES (:nombre, :categoria_id, :telefono, :email, 1)";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            ':nombre' => $nombre,
            ':categoria_id' => $categoriaId,
            ':telefono' => $telefono,
            ':email' => $email
        ])) {
            $_SESSION['success'] = "Instructor creado correctamente.";
        } else {
            $_SESSION['error'] = "Error al crear el instructor.";
        }

        $this->redirect('/instructores/listar');
    }

    
     //Mostrar formulario para editar instructor
     
    public function editarForm(int $id): void
    {
        $instructor = $this->instructorModel->obtenerPorId($id);

        if (!$instructor) {
            $_SESSION['error'] = "Instructor no encontrado.";
            $this->redirect('/instructores/listar');
            return;
        }

        $categorias = $this->getCategorias();

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
            $this->redirect('/instructores/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad.";
            $this->redirect('/instructores/editar/' . $id);
            return;
        }

        $instructor = $this->instructorModel->obtenerPorId($id);

        if (!$instructor) {
            $_SESSION['error'] = "Instructor no encontrado.";
            $this->redirect('/instructores/listar');
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $categoriaId <= 0) {
            $_SESSION['error'] = "Nombre y categoría son obligatorios.";
            $this->redirect('/instructores/editar/' . $id);
            return;
        }

        $sql = "UPDATE instructores 
                SET nombre = :nombre, 
                    categoria_id = :categoria_id, 
                    telefono = :telefono, 
                    email = :email,
                    activo = :activo
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':categoria_id' => $categoriaId,
            ':telefono' => $telefono,
            ':email' => $email,
            ':activo' => $activo
        ])) {
            $_SESSION['success'] = "Instructor actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el instructor.";
        }

        $this->redirect('/instructores/listar');
    }


     //Retirar instructor se cambia a estado inactivo
    
    public function retirar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/instructores/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad.";
            $this->redirect('/instructores/listar');
            return;
        }

        $instructor = $this->instructorModel->obtenerPorId($id);

        if (!$instructor) {
            $_SESSION['error'] = "Instructor no encontrado.";
            $this->redirect('/instructores/listar');
            return;
        }

        if ($this->instructorModel->retirar($id)) {
            $_SESSION['success'] = "Instructor retirado correctamente.";
        } else {
            $_SESSION['error'] = "Error al retirar el instructor.";
        }

        $this->redirect('/instructores/listar');
    }

    
     //Obtener categorías para selects
     
    private function getCategorias(): array
    {
        global $pdo;
        $sql = "SELECT id, nombre FROM categorias WHERE activa = 1 ORDER BY nombre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // private function redirect(string $url): void
    // {
    //     header('Location: /proyecto' . $url);
    //     exit;
    // }
}
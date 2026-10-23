<?php

declare(strict_types=1);

class BecaController extends Controller
{
    private Jugador $jugadorModel;

    //Tipos de beca en porcentajes
    private const TIPOS_BECA = [
        'sin_beca' => 0,
        'beca_25' => 25,
        'media_beca' => 50,
        'beca_completa' => 100,
    ];

    //Porcentajes validos
    private const PORCENTAJES_VALIDOS = [0, 25, 50, 100];


    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->jugadorModel = new Jugador($pdo);
    }


    public function listar(): void 
    {
        $jugadores = $this->jugadorModel->obtenerTodosConBeca();

        // Calcular estadisticas rapidas
        $estadisticas = $this->calcularEstadisticas($jugadores);

        $this->view('becas/listar',[
            'jugadores' => $jugadores,
            'estadisticas' => $estadisticas,
            'tiposBeca' => self::TIPOS_BECA,
            'titulo' => 'Gestion de Becas'
        ]);
    }

    //Mostrar formulario para asignar beca a un jugador especifico
    public function asignar(int $id): void
    {
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if(!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/becas/listar');
            return;
        }

        $porcentajeActual = self::TIPOS_BECA[$jugador['tipo_beca']] ?? 0;

        $this->view('becas/asignar', [
            'jugador' => $jugador,
            'porcentajeActual' => $porcentajeActual,
            'porcentajes' => self::PORCENTAJES_VALIDOS,
            'titulo' => 'Asignar Beca'
        ]);
    }

    //Guardar beca de un jugador
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/becas/listar');
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/becas/listar');
            return;
        }

        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $porcentajeBeca = (int)($_POST['porcentaje_beca'] ?? 0);

        $jugador = $this->jugadorModel->obtenerPorId($jugadorId);

        if(!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/becas/listar');
            return;
        }

        // Validar que el porcentaje sea valido
        if(!in_array($porcentajeBeca, self::PORCENTAJES_VALIDOS)) {
            $_SESSION['error'] = "Porcentaje de beca no valido. Debe ser 0, 25, 50 o 100.";
            $this->redirect('/becas/asignar/' . $jugadorId);
            return;
        }

        // Obtener el tipo de beca correspondiente
        $tipoBeca = $this->porcentajeToTipoBeca($porcentajeBeca);

        // Actualizar solo el campo tipo_beca
        $datos = ['tipo_beca' => $tipoBeca];

        if($this->jugadorModel->actualizar($jugadorId, $datos)) {
            $nombreCompleto = $jugador['nombre'] . ' ' . $jugador['apellido'];
            $_SESSION['success'] = "Beca del {$porcentajeBeca}% asignada correctamente a {$nombreCompleto}.";
        } else {
            $_SESSION['error'] = "Error al asignar la beca.";
        }

        $this->redirect('/becas/listar');
    }

    //Retirar Beca Jugador
    public function desasignar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/becas/listar');
            return;
        }

        // Validar CSRF
        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = "Error de seguridad. Intenta de nuevo.";
            $this->redirect('/becas/listar');
            return;
        }

        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $_SESSION['error'] = "Jugador no encontrado.";
            $this->redirect('/becas/listar');
            return;
        }

        // Actualizar a sin beca 
        $datos = ['tipo_beca' => 'sin_beca'];

        if($this->jugadorModel->actualizar($id, $datos)) {
            $nombreCompleto = $jugador['nombre'] . ' ' . $jugador['apellido'];
            $_SESSION['success'] = "Beca retirada correctamente para {$nombreCompleto}.";
        } else {
            $_SESSION['error'] = "Error al retirar la beca.";
        }

        $this->redirect('/becas/listar');
    }

    // Becas por Categoria
    public function reporte(): void
    {
        $categorias = $this->getCategoriasConEstadisticas();

        $this->view('becas/reporte', [
            'categorias' => $categorias,
            'tiposBeca' => self::TIPOS_BECA,
            'porcentajes' => self::PORCENTAJES_VALIDOS,
            'titulo' => 'Reporte de Becas'
        ]);
    }

    // ─ METODOS PRIVADOS ─

    /**
     * Convertir porcentaje a tipo_beca
     */
    private function porcentajeToTipoBeca(int $porcentaje): string
    {
        return match($porcentaje) {
            0 => 'sin_beca',
            25 => 'beca_25',
            50 => 'media_beca',
            100 => 'beca_completa',
            default => 'sin_beca'
        };
    }

    /**
     * Calcular estadisticas de becas
     */
    private function calcularEstadisticas(array $jugadores): array
    {
        $estadisticas = [];
        $total = count($jugadores);

        foreach (self::TIPOS_BECA as $tipo => $porcentaje) {
            $cantidad = 0;
            foreach ($jugadores as $j) {
                if ($j['tipo_beca'] === $tipo) {
                    $cantidad++;
                }
            }
            $estadisticas[$tipo] = [
                'cantidad' => $cantidad,
                'porcentaje' => $total > 0 ? round(($cantidad / $total) * 100, 2) : 0,
                'nombre' => $porcentaje . '%'
            ];
        }

        $estadisticas['total'] = $total;
        return $estadisticas;
    }

    /**
     * Obtener categorías con estadísticas de becas
     */
    private function getCategoriasConEstadisticas(): array
    {
        global $pdo;

        $sql = "SELECT c.id, c.nombre,
                       COUNT(j.id) AS total_jugadores,
                       SUM(j.tipo_beca = 'sin_beca') AS sin_beca,
                       SUM(j.tipo_beca = 'beca_25') AS beca_25,
                       SUM(j.tipo_beca = 'media_beca') AS media_beca,
                       SUM(j.tipo_beca = 'beca_completa') AS beca_completa
                FROM categorias c
                LEFT JOIN jugadores j ON c.id = j.categoria_id AND j.estado = 'activo'
                GROUP BY c.id, c.nombre
                ORDER BY c.nombre ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
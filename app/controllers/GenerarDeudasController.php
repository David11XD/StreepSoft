<?php
declare(strict_types=1);

class GeneradorDeudasController extends Controller
{
    private $generador;

    public function __construct()
    {
        parent::__construct();
        require_once APP_PATH . '/utils/GeneradorDeudas.php';
        $this->generador = new GeneradorDeudas($this->pdo);
    }


    public function generarProximas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        try {
            $resultado = $this->generador->generarDeudasProximoMes();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'generadas' => $resultado['generadas'],
                'mensaje' => $resultado['mensaje'],
                'errores' => $resultado['errores']
            ]);
        } catch (Exception $e) {
            error_log("GeneradorDeudasController::generarProximas - " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function actualizarMoras(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        try {
            $resultado = $this->generador->actualizarMoras();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'actualizadas' => $resultado['actualizadas'],
                'errores' => $resultado['errores']
            ]);
        } catch (Exception $e) {
            error_log("GeneradorDeudasController::actualizarMoras - " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

?>
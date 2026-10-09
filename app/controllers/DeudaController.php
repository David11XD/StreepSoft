<?php
declare(strict_types=1);

class DeudaController extends Controller
{
    private Deuda $deudaModel;
    private MetodoPago $metodoPagoModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->deudaModel = new Deuda($pdo);
        $this->metodoPagoModel = new MetodoPago($pdo);
    }

    public function listar(): void
    {
        try {
            $this->deudaModel->marcarVencidaComoMora();

            $deudas = $this->deudaModel->obtenerTodasConEstado();
            $resumen = $this->deudaModel->obtenerResumen();
        } catch (Exception $e) {
            error_log('Deudas (listar): ' . $e->getMessage());
            $deudas = [];
            $resumen = [
                'total_alumnos' => 0, 'total_general' => 0,
                'total_pendiente' => 0, 'total_mora' => 0, 'total_recaudo' => 0,
                'porcentaje_pendiente' => 0, 'porcentaje_mora' => 0, 'porcentaje_recaudo' => 0,
            ];
        }

        $this->view('jugadores/deudasJugadores/index', [
            'titulo' => 'Deudas de Jugadores',
            'deudas' => $deudas,
            'resumen' => $resumen,
        ]);
    }


    public function mostrarPago(int $id): void
    {
        $idDeuda = $id;

        try {
            $deuda = $this->deudaModel->obtenerPorId($idDeuda);

            if (!$deuda) {
                http_response_code(404);
                echo 'Deuda no encontrada';
                return;
            }

            $metodos = $this->metodoPagoModel->obtenerTodos();

            $this->view('jugadores/deudasJugadores/create', [
                'deuda' => $deuda,
                'metodos' => $metodos,
                'csrfToken' => $_SESSION['csrf_token'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('DeudaController::mostrarPago - ID deuda ' . $idDeuda . ' - ' . $e->getMessage());
            http_response_code(500);
            echo 'No se pudo cargar el formulario de registro de pago.';
        }
    }

    public function registrarPago(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/jugadores/deudas');
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/jugadores/deudas?error=csrf');
        }

        $idDeuda = (int) ($_POST['id_deudas'] ?? 0);

        if ($idDeuda <= 0) {
            $this->redirect('/streepsoft/jugadores/deudas?error=deuda_invalida');
        }

        $valor = (float) ($_POST['valor'] ?? 0);
        $fechaPago = trim($_POST['fecha_pago'] ?? '') ?: date('Y-m-d');
        $idMetodoPago = (int) ($_POST['id_metodo_pago'] ?? 0);
        $concepto = trim($_POST['concepto'] ?? '');
        $descuento = (int) ($_POST['descuento_porcentaje'] ?? 0);

        if ($valor <= 0 || $idMetodoPago <= 0) {
            $this->redirect('/streepsoft/deudas/' . $idDeuda . '/pago?error=campos_vacios');
        }

        $valorPagado = round($valor * (1 - ($descuento / 100)), 2);

        try {
            $ok = $this->deudaModel->registrarPago($idDeuda, [
                'fecha_pago' => $fechaPago,
                'id_metodo_pago' => $idMetodoPago,
                'concepto' => $concepto !== '' ? $concepto : null,
                'descuento_porcentaje' => $descuento,
                'valor_pagado' => $valorPagado,
            ]);

            if (!$ok) {
                $this->redirect('/streepsoft/deudas/' . $idDeuda . '/pago?error=no_guardado');
            }
        } catch (Throwable $e) {
            error_log('Deudas (registrarPago): ' . $e->getMessage());
            $this->redirect('/streepsoft/deudas/' . $idDeuda . '/pago?error=no_guardado');
        }

        $this->redirect('/streepsoft/jugadores/deudas?ok=pago_registrado');
    }
}
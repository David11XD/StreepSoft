<?php
declare(strict_types=1);

class DeudaController extends Controller
{
    private Deuda $deudaModel;
    private MetodoPago $metodoPagoModel;
    private Jugador $jugadorModel;
    private HistorialDeuda $historialModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->deudaModel = new Deuda($pdo);
        $this->metodoPagoModel = new MetodoPago($pdo);
        $this->jugadorModel = new Jugador($pdo);
        $this->historialModel = new HistorialDeuda($pdo);
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


    /** Muestra el historial de pagos de un jugador concreto. */
    public function historialJugador(int $id): void
    {
        if ($id <= 0) {
            http_response_code(400);
            echo 'Identificador de jugador inválido.';
            return;
        }

        try {
            $jugador = $this->jugadorModel->obtenerParaHistorial($id);

            if (!$jugador) {
                http_response_code(404);
                echo 'No se encontró el jugador solicitado.';
                return;
            }

            $historial = $this->historialModel->obtenerPorJugador($id);
            $proximaDeuda = $this->deudaModel->obtenerSiguientePorJugador($id);

            $this->view('jugadores/deudasJugadores/historial', [
                'titulo' => 'Historial de pagos',
                'jugador' => $jugador,
                'historial' => $historial,
                'proximaDeuda' => $proximaDeuda,
            ]);
        } catch (Throwable $e) {
            error_log('DeudaController::historialJugador - ID jugador ' . $id . ' - ' . $e->getMessage());
            http_response_code(500);
            echo 'No se pudo cargar el historial de pagos del jugador.';
        }
    }



    /** Abre el formulario con los datos existentes de un pago histórico. */
    public function editarPagoHistorial(int $id): void
    {
        if ($id <= 0) {
            http_response_code(400);
            echo 'Identificador de pago inválido.';
            return;
        }

        try {
            $pago = $this->historialModel->obtenerPorId($id);
            if (!$pago) {
                http_response_code(404);
                echo 'No se encontró el pago histórico solicitado.';
                return;
            }

            $this->view('jugadores/deudasJugadores/Editar', [
                'titulo' => 'Editar pago histórico',
                'pago' => $pago,
                'metodos' => $this->metodoPagoModel->obtenerTodos(),
                'csrfToken' => $_SESSION['csrf_token'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('DeudaController::editarPagoHistorial - ID ' . $id . ' - ' . $e->getMessage());
            http_response_code(500);
            echo 'No se pudo cargar el formulario de edición del pago.';
        }
    }

    /** Guarda la corrección del mismo registro en historial_deuda. */
    public function actualizarPagoHistorial(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/jugadores/deudas');
        }

        $pagoActual = $this->historialModel->obtenerPorId($id);
        if (!$pagoActual) {
            http_response_code(404);
            echo 'No se encontró el pago que se quiere editar.';
            return;
        }

        $rutaEdicion = '/streepsoft/jugadores/deudas/historial/editar/' . $id;

        if (!$this->validateCSRFToken((string) ($_POST['_token'] ?? ''))) {
            $this->redirect($rutaEdicion . '?error=csrf');
        }

        $fechaLimite = trim((string) ($_POST['fecha_limite_pago'] ?? ''));
        $fechaPago = trim((string) ($_POST['fecha_pago'] ?? ''));
        $matriculaRaw = trim((string) ($_POST['matricula'] ?? ''));
        $totalidadRaw = trim((string) ($_POST['totalidad'] ?? ''));
        $valorPagadoRaw = trim((string) ($_POST['valor_pagado'] ?? ''));
        $metodoRaw = trim((string) ($_POST['id_metodo_pago'] ?? ''));
        $concepto = trim((string) ($_POST['concepto'] ?? ''));
        $descuento = filter_var($_POST['descuento_porcentaje'] ?? 0, FILTER_VALIDATE_INT);

        $fechaValida = static function (string $fecha): bool {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                return false;
            }
            $obj = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
            $errores = DateTimeImmutable::getLastErrors();
            return $obj !== false
                && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
                && $obj->format('Y-m-d') === $fecha;
        };

        if (
            !$fechaValida($fechaLimite)
            || !$fechaValida($fechaPago)
            || !is_numeric($matriculaRaw)
            || !is_numeric($totalidadRaw)
            || !is_numeric($valorPagadoRaw)
            || (float) $matriculaRaw < 0
            || (float) $totalidadRaw < 0
            || (float) $valorPagadoRaw < 0
            || $descuento === false
            || $descuento < 0
            || $descuento > 100
            || mb_strlen($concepto) > 255
        ) {
            $this->redirect('/streepsoft/jugadores/deudas/historial/editar/' . $id . '?error=datos');
        }

        $idMetodoPago = null;
        if ($metodoRaw !== '') {
            $idMetodoPago = filter_var($metodoRaw, FILTER_VALIDATE_INT);
            $idsValidos = array_map(
                static fn(array $metodo): int => (int) $metodo['id_metodo_pago'],
                $this->metodoPagoModel->obtenerTodos()
            );
            if ($idMetodoPago === false || !in_array((int) $idMetodoPago, $idsValidos, true)) {
                $this->redirect('/streepsoft/jugadores/deudas/historial/editar/' . $id . '?error=metodo');
            }
        }

        try {
            $actualizado = $this->historialModel->actualizar($id, [
                'matricula' => round((float) $matriculaRaw, 2),
                'totalidad' => round((float) $totalidadRaw, 2),
                'fecha_limite_pago' => $fechaLimite,
                'fecha_pago' => $fechaPago,
                'id_metodo_pago' => $idMetodoPago,
                'concepto' => $concepto !== '' ? $concepto : null,
                'descuento_porcentaje' => $descuento,
                'valor_pagado' => round((float) $valorPagadoRaw, 2),
            ]);

            if (!$actualizado) {
                $this->redirect('/streepsoft/jugadores/deudas/historial/editar/' . $id . '?error=guardar');
            }

            // Vuelve a cargar la vista editada dentro del mismo iframe.
            // Editar.php avisará al historial padre para cerrar el modal y refrescar la tabla.
            $this->redirect($rutaEdicion . '?actualizado=1');
        } catch (Throwable $e) {
            error_log('DeudaController::actualizarPagoHistorial - ID ' . $id . ' - ' . $e->getMessage());
            $this->redirect('/streepsoft/jugadores/deudas/historial/editar/' . $id . '?error=guardar');
        }
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
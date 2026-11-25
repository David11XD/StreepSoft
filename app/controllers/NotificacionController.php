<?php
declare(strict_types=1);

class NotificacionController extends Controller
{
    private Notificacion $model;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new Notificacion($pdo);
    }

    /** JSON para el modal de notificaciones que se abre desde la campana. */
    public function panel(): void
    {
        try {
            $this->actualizarAvisos();

            $this->json([
                'ok'             => true,
                'notificaciones' => $this->conUrl($this->model->listarPendientes()),
                'resumen'        => $this->model->resumen(),
                'configuracion'  => $this->model->obtenerConfiguracion(),
                'token'          => $this->tokenCsrf(),
            ]);
        } catch (Throwable $e) {
            error_log('Notificaciones panel: ' . $e->getMessage());
            $this->json(['ok' => false, 'mensaje' => 'No se pudieron cargar las notificaciones.'], 500);
        }
    }

    /** Marcar un aviso como revisado o resuelto. */
    public function estado(): void
    {
        if (!$this->validateCSRFToken((string) ($_POST['_token'] ?? ''))) {
            $this->json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');

        try {
            if ($id < 1 || !$this->model->cambiarEstado($id, $estado)) {
                $this->json([
                    'ok' => false,
                    'mensaje' => $estado === 'resuelta'
                        ? 'El problema sigue pendiente. La notificación permanecerá hasta que se solucione.'
                        : 'No se pudo actualizar la notificación.',
                ], 422);
            }
            $this->json(['ok' => true, 'resumen' => $this->model->resumen()]);
        } catch (Throwable $e) {
            error_log('Notificaciones estado: ' . $e->getMessage());
            $this->json(['ok' => false, 'mensaje' => 'No se pudo actualizar la notificación.'], 500);
        }
    }

    public function guardarConfiguracion(): void
    {
        if (!$this->validateCSRFToken((string) ($_POST['_token'] ?? ''))) {
            $this->json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
        }

        try {
            $this->model->guardarConfiguracion($_POST);
            // Para que los cambios (días de anticipación, monto, avisos activados...) se vean ya.
            $this->model->generarAutomaticas();
            $this->json(['ok' => true, 'mensaje' => 'Configuración guardada.']);
        } catch (Throwable $e) {
            error_log('Notificaciones configuración: ' . $e->getMessage());
            $this->json(['ok' => false, 'mensaje' => 'No se pudo guardar la configuración.'], 500);
        }
    }

    /**
     * Sincroniza los avisos al abrir el panel para que las prioridades y las
     * deudas recién pagadas se reflejen inmediatamente. Un fallo aquí no debe
     * tumbar la pantalla: se registra y se muestra lo que haya.
     */
    private function actualizarAvisos(): void
    {
        try {
            $this->model->generarAutomaticas();
        } catch (Throwable $e) {
            error_log('Notificaciones generación: ' . $e->getMessage());
        }
    }

    /**
     * Convierte la ruta guardada en BD en una URL válida con la base del proyecto.
     * Acepta rutas relativas ("jugadores/deudas") y también las antiguas que
     * incluían la carpeta del proyecto ("/streepsoft/jugadores/deudas"), para
     * no duplicarla (/streepsoft/streepsoft/... daba 404).
     */
    private function conUrl(array $avisos): array
    {
        $carpeta = trim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');

        foreach ($avisos as &$aviso) {
            $ruta = trim((string) ($aviso['url'] ?? ''));

            if ($ruta !== '' && preg_match('#^https?://#i', $ruta) !== 1) {
                $ruta = ltrim($ruta, '/');
                if ($carpeta !== '' && ($ruta === $carpeta || str_starts_with($ruta, $carpeta . '/'))) {
                    $ruta = ltrim(substr($ruta, strlen($carpeta)), '/');
                }
                $ruta = url($ruta);
            }

            $aviso['url'] = $ruta === '' ? null : $ruta;
        }
        unset($aviso);

        return $avisos;
    }

    private function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['csrf_token'];
    }
}

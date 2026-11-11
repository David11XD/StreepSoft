<?php
declare(strict_types=1);

class DocumentoController extends Controller
{
    private Documento $documentoModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->documentoModel = new Documento($pdo);
    }

    public function verificacion(): void
    {
        $alumnos = $this->documentoModel->listarJugadoresDocumentos();
        $totalAlumnos = count($alumnos);
        $completos = count(array_filter($alumnos, fn($a) => $a['estado'] === 'completo'));
        $porVerificar = array_sum(array_column($alumnos, 'por_verificar'));
        $incompletos = $totalAlumnos - $completos;
        $this->view('documentos/verificacion', [
            'alumnos' => $alumnos,
            'resumenDocumentos' => ['total'=>$totalAlumnos, 'completos'=>$completos, 'incompletos'=>$incompletos, 'verificar'=>$porVerificar],
            'titulo' => 'Documentos | Alumnos'
        ]);
    }

    public function gestionar(int $id): void
    {
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $jugador = $this->documentoModel->obtenerJugador($id);
        if (!$jugador) {
            $_SESSION['error'] = 'Jugador no encontrado.';
            $this->redirect('/streepsoft/documentos');
        }
        $documentos = $this->documentoModel->obtenerPorJugador($id);
        $total = count($documentos);
        $verificados = count(array_filter($documentos, fn($d) => $d['estado'] === 'verificado'));
        $pendientes = array_column(array_filter($documentos, fn($d) => $d['estado'] === 'pendiente'), 'nombre');
        $porVerificar = array_column(array_filter($documentos, fn($d) => $d['estado'] === 'verificar'), 'nombre');
        $estadoGeneral = count($pendientes) ? ['clase'=>'incompleto','texto'=>'Incompleto']
            : (count($porVerificar) ? ['clase'=>'verificar','texto'=>'Por verificar'] : ['clase'=>'completo','texto'=>'Completo']);
        $this->view('documentos/gestionar', [
            'alumnoId'=>$id, 'alumno'=>[
                'nombre'=>trim($jugador['nombres'].' '.$jugador['apellidos']),
                'iniciales'=>$jugador['iniciales'] ?: mb_strtoupper(mb_substr($jugador['nombres'],0,1).mb_substr($jugador['apellidos'],0,1)),
                'documento'=>trim((string)$jugador['documento']), 'categoria'=>$jugador['categoria'],
                'foto'=>$jugador['foto'] ?? null,
                'activo'=>$jugador['estado']==='activo'
            ],
            'documentos'=>$documentos, 'totalDocumentos'=>$total, 'verificados'=>$verificados,
            'entregados'=>$total-count($pendientes), 'faltanSubir'=>$pendientes, 'faltanVerificar'=>$porVerificar,
            'estadoGeneral'=>$estadoGeneral,
            'datosTarjeta'=>['documentos'=>$documentos,'numeroDocumento'=>(string)$id,'usuario'=>Auth::nombre() ?? 'Administrador','jugadorId'=>$id,'csrfToken'=>$_SESSION['csrf_token'] ?? ''],
            'titulo'=>'Documentos del alumno'
        ]);
    }

    public function datos(int $id): void
    {
        $jugador = $this->documentoModel->obtenerJugador($id);
        if (!$jugador) $this->json(['ok'=>false,'mensaje'=>'Jugador no encontrado.'],404);
        $this->json(['ok'=>true,'documentos'=>$this->documentoModel->obtenerPorJugador($id)]);
    }

    public function accion(): void
    {
        if (!$this->validateCSRFToken((string)($_POST['_token'] ?? ''))) $this->json(['ok'=>false,'mensaje'=>'La sesión de seguridad expiró. Recarga la página.'],419);
        $jugadorId = (int)($_POST['jugador_id'] ?? 0);
        $clave = (string)($_POST['clave'] ?? '');
        $accion = (string)($_POST['accion'] ?? '');
        $tipos = Documento::tipos();
        if ($jugadorId < 1 || !isset($tipos[$clave])) $this->json(['ok'=>false,'mensaje'=>'Datos del documento no válidos.'],422);
        $jugador = $this->documentoModel->obtenerJugador($jugadorId);
        if (!$jugador) $this->json(['ok'=>false,'mensaje'=>'Jugador no encontrado.'],404);

        $registro = null;
        if ($accion !== 'subir') {
            $registro = $this->documentoModel->obtenerRegistro((int)($_POST['documento_id'] ?? 0));
            if (!$registro || (int)$registro['id_jugadores'] !== $jugadorId || $registro['clave'] !== $clave) {
                $this->json(['ok'=>false,'mensaje'=>'No se encontró el documento solicitado.'],404);
            }
        }

        $uid = Auth::id() ?? 0;
        $usuario = Auth::nombre() ?? 'Administrador';

        try {
            if ($accion === 'subir') {
                if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) $this->json(['ok'=>false,'mensaje'=>'Selecciona un archivo PDF válido.'],422);
                $file = $_FILES['archivo'];
                if ($file['size'] > 10 * 1024 * 1024) $this->json(['ok'=>false,'mensaje'=>'El PDF supera el límite de 10 MB.'],422);
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if ($finfo->file($file['tmp_name']) !== 'application/pdf' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                    $this->json(['ok'=>false,'mensaje'=>'Solo se permiten archivos PDF.'],422);
                }
                $storage = dirname(APP_PATH).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'documentos_jugadores'.DIRECTORY_SEPARATOR.$jugadorId;
                if (!is_dir($storage) && !mkdir($storage, 0750, true) && !is_dir($storage)) throw new RuntimeException('No se pudo crear el directorio privado.');
                $nombreSeguro = bin2hex(random_bytes(18)).'.pdf';
                $destino = $storage.DIRECTORY_SEPARATOR.$nombreSeguro;
                if (!move_uploaded_file($file['tmp_name'], $destino)) throw new RuntimeException('No se pudo guardar el archivo.');
                $rutaRelativa = $jugadorId.'/'.$nombreSeguro;
                $anterior = $this->documentoModel->obtenerPorJugadorYClave($jugadorId, $clave);
                try {
                    if (!$this->documentoModel->guardarArchivo($jugadorId,$clave,basename($file['name']),$rutaRelativa,(int)$file['size'],$uid,$usuario)) throw new RuntimeException('No se pudo registrar el documento.');
                } catch (Throwable $e) { @unlink($destino); throw $e; }
                if ($anterior) {
                    $rutaAnterior = $this->rutaFisica((string)$anterior['ruta_archivo']);
                    if ($rutaAnterior && is_file($rutaAnterior)) @unlink($rutaAnterior);
                }
                $this->json(['ok'=>true,'mensaje'=>'Documento guardado. Queda pendiente de verificación.']);
            }

            if ($accion === 'verificar') {
                $ok = $this->documentoModel->cambiarEstado((int)$registro['id'],'verificado',null,$uid,$usuario);
            } elseif ($accion === 'revertir') {
                $ok = $this->documentoModel->cambiarEstado((int)$registro['id'],'verificar',null,null,null);
            } elseif ($accion === 'rechazar') {
                $motivo = trim((string)($_POST['motivo'] ?? 'Documento rechazado'));
                $ok = $this->documentoModel->cambiarEstado((int)$registro['id'],'rechazado',$motivo,null,null);
            } elseif ($accion === 'eliminar') {
                $ruta = $this->rutaFisica((string)$registro['ruta_archivo']);
                $ok = $this->documentoModel->eliminarRegistro((int)$registro['id']);
                if ($ok && is_file($ruta)) @unlink($ruta);
            } else {
                $this->json(['ok'=>false,'mensaje'=>'Acción no reconocida.'],422);
            }
            if (!$ok) throw new RuntimeException('No se pudo aplicar el cambio.');
            $this->json(['ok'=>true,'mensaje'=>'Operación realizada correctamente.']);
        } catch (Throwable $e) {
            error_log('DocumentoController::accion: '.$e->getMessage());
            $this->json(['ok'=>false,'mensaje'=>'No se pudo completar la operación. Revisa el registro de errores.'],500);
        }
    }

    public function archivo(int $id): void
    {
        $registro = $this->documentoModel->obtenerRegistro($id);
        if (!$registro) { http_response_code(404); exit('Documento no encontrado.'); }
        $ruta = $this->rutaFisica((string)$registro['ruta_archivo']);
        if (!is_file($ruta)) { http_response_code(404); exit('El archivo no está disponible.'); }
        $descargar = ($_GET['modo'] ?? 'preview') === 'descargar';
        header('Content-Type: application/pdf');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: '.filesize($ruta));
        header('Content-Disposition: '.($descargar ? 'attachment' : 'inline').'; filename="'.rawurlencode(basename($registro['nombre_original'])).'"');
        readfile($ruta);
        exit;
    }

    private function rutaFisica(string $relativa): string
    {
        $base = realpath(dirname(APP_PATH).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'documentos_jugadores');
        if ($base === false) return '';
        $ruta = realpath($base.DIRECTORY_SEPARATOR.$relativa);
        if ($ruta === false || !str_starts_with($ruta, $base.DIRECTORY_SEPARATOR)) return '';
        return $ruta;
    }
}

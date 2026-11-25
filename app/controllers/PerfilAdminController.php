<?php
class PerfilAdminController extends Controller
{
    public function perfil(): void
    {
        $usuarioModel = new Usuario($this->pdo);
        $admin = $usuarioModel->obtenerPorId(Auth::id());

        $actividadModel = new Actividad($this->pdo);
        $actividad = $actividadModel->obtenerRecientes(Auth::id());

        $estadisticaModel = new Estadistica($this->pdo);
        $stats = [
            'jugadores' => $estadisticaModel->totalJugadores(),
            'mora' => $estadisticaModel->jugadoresEnMora(),
            'pagos' => $estadisticaModel->pagosRegistrados(),
            'instructores' => $estadisticaModel->totalInstructores()
        ];

        $configuracionModel = new Configuracion($this->pdo);
        $configPagos = $configuracionModel->obtenerConfiguracionPagos();
        $diaCobro = $configPagos['dia_cobro'];
        $diasGracia = $configPagos['dias_gracia'];

        $this->view('perfilAdmin/perfil', [
            'admin' => $admin,
            'actividad' => $actividad,
            'stats' => $stats,
            'diaCobro' => $diaCobro,
            'diasGracia' => $diasGracia,
        ]);
    }

    public function guardarConfiguracionPagos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('perfil/administrador'));
            return;
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')){
            $this->redirect(url('perfil/administrador?error=csrf'));
            return;
        }

        $diaCobro = (int) ($_POST['dia_cobro'] ?? 0);
        $diasGracia = (int) ($_POST['dias_gracia'] ?? 0);

        if ($diaCobro < 1 || $diaCobro > 31){
            $this->redirect(url('perfil/administrador?error=dia_invalido'));
            return;
        }

        if ($diasGracia < 0 || $diasGracia > 30){
            $this->redirect(url('perfil/administrador?error=gracia_invalida'));
            return;
        }

        try {
            $configuracionModel = new Configuracion($this->pdo);
            $guardado = $configuracionModel->actualizarMultiples([
                'dia_cobro' => $diaCobro,
                'dias_gracia' => $diasGracia,
            ]);

            if (!$guardado) {
                throw new RuntimeException('No se pudo guardar la configuración de pagos.');
            }
        } catch (Throwable $e) {
            error_log('PerfilAdminController::guardarConfiguracionPagos - ' . $e->getMessage());
            $this->redirect(url('perfil/administrador?error=configuracion_guardado'));
            return;
        }

        $this->redirect(url('perfil/administrador?success=configuracion_guardada&dia_cobro=' . $diaCobro . '&dias_gracia=' . $diasGracia));
    }


    public function actualizarPerfil(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/perfil/administrador');
            return;
        }

        $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $documentoIdentidad = trim($_POST['documento_identidad'] ?? '');

        if (empty($nombreCompleto) || empty($telefono) || empty($documentoIdentidad)) {
            $this->redirect('/streepsoft/perfil/administrador?error=campos_vacios');
            return;
        }

        // ---------- AQUÍ VAN LAS VALIDACIONES NUEVAS ----------
        if (!preg_match('/^[\p{L}\s]+$/u', $nombreCompleto)) {
            $this->redirect('/streepsoft/perfil/administrador?error=nombre_invalido');
            return;
        }

        if (!preg_match('/^[0-9]+$/', $telefono)) {
            $this->redirect('/streepsoft/perfil/administrador?error=telefono_invalido');
            return;
        }

        if (!preg_match('/^[0-9]+$/', $documentoIdentidad)) {
            $this->redirect('/streepsoft/perfil/administrador?error=documento_invalido');
            return;
        }

        $usuarioModel = new Usuario($this->pdo);

        if ($usuarioModel->actualizarPerfil(Auth::id(), $nombreCompleto, $telefono, $documentoIdentidad)) {

            $actividadModel = new Actividad($this->pdo);
            $actividadModel->registrar(Auth::id(), 'Actualizó su información de perfil');

            $this->redirect('/streepsoft/perfil/administrador?success=actualizado');
        } else {
            $this->redirect('/streepsoft/perfil/administrador?error=actualizacion_fallida');
        }
    }

    public function cambiarFoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/perfil/administrador');
            return;
        }

        // Se declara fuera del try para poder limpiarla en el catch si algo falla
        $nombreFoto = null;
        $fotoGuardadaEnBD = false;

        try {
            $usuarioModel = new Usuario($this->pdo);

            // 1) Leer la foto actual ANTES de subir la nueva (para poder borrarla después)
            $adminActual = $usuarioModel->obtenerPorId(Auth::id());
            $fotoAnterior = $adminActual['foto'] ?? null;

            // 2) Subir la nueva foto al disco
            $nombreFoto = $this->subirFotoAdmin($_FILES['foto'] ?? null);

            if ($nombreFoto === null) {
                $this->redirect('/streepsoft/perfil/administrador?error=foto_no_enviada');
                return;
            }

            // 3) Apuntar la BD a la nueva foto
            $fotoGuardadaEnBD = $usuarioModel->actualizarFoto(Auth::id(), $nombreFoto);

            if ($fotoGuardadaEnBD) {
                // 4) La BD ya usa la nueva: la anterior quedó huérfana, se elimina del disco
                if (!empty($fotoAnterior) && $fotoAnterior !== $nombreFoto) {
                    $this->eliminarFotoAdmin($fotoAnterior);
                }
                $this->redirect('/streepsoft/perfil/administrador?success=foto_actualizada');
            } else {
                // La BD no se actualizó: se borra la nueva para no dejarla huérfana
                $this->eliminarFotoAdmin($nombreFoto);
                $this->redirect('/streepsoft/perfil/administrador?error=foto_no_guardada');
            }
        } catch (Exception $e) {
            error_log('PerfilAdminController::cambiarFoto - ' . $e->getMessage());

            // Si la foto nueva alcanzó a guardarse en disco pero la BD no la usa, se borra
            if ($nombreFoto !== null && !$fotoGuardadaEnBD) {
                $this->eliminarFotoAdmin($nombreFoto);
            }

            $this->redirect('/streepsoft/perfil/administrador?error=foto_invalida');
        }
    }

    /* Elimina del disco una foto de administrador (solo dentro de public/Image/admins) */
    private function eliminarFotoAdmin(?string $nombreFoto): void
    {
        if (empty($nombreFoto)) {
            return;
        }

        // basename() evita que un valor raro de la BD (ej. "../../algo") borre archivos fuera de la carpeta
        $ruta = __DIR__ . '/../../public/Image/admins/' . basename($nombreFoto);

        if (is_file($ruta) && !unlink($ruta)) {
            error_log('PerfilAdminController::eliminarFotoAdmin - No se pudo eliminar: ' . $ruta);
        }
    }

    private function subirFotoAdmin(?array $archivo): ?string
    {
        if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir la foto');
        }

        $tamanoMaximo = 3 * 1024 * 1024; // 2 MB
        if ($archivo['size'] > $tamanoMaximo) {
            throw new Exception('La foto supera el tamaño máximo de 2MB');
        }

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ];

        /* Abre el archivo y lee sus primeros bytes reales para identificar el tipo verdadero,*/
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);

        if (!isset($tiposPermitidos[$mime])) {
            throw new Exception('Formato de imagen no permitido (solo JPG o PNG)');
        }

        $carpetaDestino = __DIR__ . '/../../public/Image/admins';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

                        /* genera un nombre de archivo aleatorio */
        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$mime];

        if (!move_uploaded_file($archivo['tmp_name'], $carpetaDestino . '/' . $nombreArchivo)) {
            throw new Exception('No se pudo guardar la foto en el servidor');
        }

        return $nombreArchivo;
    }
}
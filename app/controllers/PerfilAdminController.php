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

        $this->view('perfilAdmin/perfil', [
            'admin' => $admin,
            'actividad' => $actividad,
            'stats' => $stats
        ]);
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

        try {
            $nombreFoto = $this->subirFotoAdmin($_FILES['foto'] ?? null);

            if ($nombreFoto === null) {
                $this->redirect('/streepsoft/perfil/administrador?error=foto_no_enviada');
                return;
            }

            $usuarioModel = new Usuario($this->pdo);

            if ($usuarioModel->actualizarFoto(Auth::id(), $nombreFoto)) {
                $this->redirect('/streepsoft/perfil/administrador?success=foto_actualizada');
            } else {
                $this->redirect('/streepsoft/perfil/administrador?error=foto_no_guardada');
            }
        } catch (Exception $e) {
            $this->redirect('/streepsoft/perfil/administrador?error=foto_invalida');
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

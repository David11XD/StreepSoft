<?php
declare(strict_types=1);

/**
 * JugadorController - Controla todas las acciones de jugadores
 * 
 * ¿Qué hace?
 * - Muestra lista de jugadores
 * - Muestra deudas de jugadores
 * - Procesa creación, actualización, eliminación
 */
class JugadorController extends Controller
{
    private Jugador $jugadorModel;
    private Categoria $categoriaModel;
    private Instructor $instructorModel;
    private Eps $epsModel;
    private TipoDocumento $tipoDocumentoModel;
    private Documento $documentoModel;
    private Deuda $deudaModel;
    private MetodoPago $metodoPagoModel;
    private TipoBeca $tipoBecaModel;
    private CambioEstadoJugador $cambioEstadoModel;

    /**
     * Constructor
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);  // Llamar al constructor de Controller
        $this->jugadorModel = new Jugador($pdo);
        $this->categoriaModel = new Categoria($pdo);
        $this->instructorModel = new Instructor($pdo);
        $this->epsModel = new Eps($pdo);
        $this->tipoDocumentoModel = new TipoDocumento($pdo);
        $this->documentoModel = new Documento($pdo);
        $this->deudaModel = new Deuda($pdo);
        $this->metodoPagoModel = new MetodoPago($pdo);
        $this->tipoBecaModel = new TipoBeca($pdo);
        $this->cambioEstadoModel = new CambioEstadoJugador($pdo);
    }

    /**
     * Mostrar lista de jugadores en gestión
     * 
     * Se ejecuta cuando accedes a /jugadores/gestion
     */
    public function gestion(): void
    {
        // Obtener todos los jugadores del modelo
        // Las consultas se aíslan para que un fallo al filtrar la tabla
        // no borre también los datos utilizados por los contadores.
        try {
            // Todos los registros se conservan para los contadores estadísticos.
            $jugadores = $this->jugadorModel->obtenerTodos();
        } catch (Throwable $e) {
            error_log("Gestión de jugadores (contadores): " . $e->getMessage());
            $jugadores = [];
        }

        try {
            // La tabla principal omite los inactivos, que se reactivan desde Desactivación.
            $jugadoresVisibles = $this->jugadorModel->obtenerVisiblesGestion();
        } catch (Throwable $e) {
            error_log("Gestión de jugadores (tabla visible): " . $e->getMessage());
            $jugadoresVisibles = [];
        }

        $diasGracia = 5;
        try {
            $diasGracia = (new Configuracion($this->pdo))->obtenerDiasGracia();
        } catch (Throwable $e) {
            error_log("Gestión de jugadores (días de gracia): " . $e->getMessage());
        }

        // Aviso que dejó guardar() al rechazar el registro de un jugador (se muestra una sola vez)
        $avisoRegistro = $_SESSION['aviso_registro_jugador'] ?? null;
        unset($_SESSION['aviso_registro_jugador']);

        $this->view('jugadores/gestionJugadores/index', [
            'avisoRegistro' => $avisoRegistro,
            'diasGracia' => $diasGracia,
            'jugadores' => $jugadores,
            'jugadoresVisibles' => $jugadoresVisibles,
            'titulo' => 'Gestión de Jugadores'
        ]);
    }

        /**
     * Muestra todos los jugadores registrados y el historial de cambios de estado.
     * La lista incluye activos, inactivos y retirados; la mora no limita los resultados.
     */
    public function desactivacion(): void
    {
        try {
            // Limpieza de reactivaciones cumplidas (24 h). No afecta jugadores ni
            // los registros de desactivación; solo elimina el movimiento de reactivación.
            $this->cambioEstadoModel->limpiarReactivacionesVencidas();

            $jugadores = $this->cambioEstadoModel->obtenerTodosLosJugadores();
            $historialCambios = $this->cambioEstadoModel->obtenerHistorialCambios();
        } catch (Throwable $e) {
            error_log('Desactivación de jugadores: ' . $e->getMessage());
            $jugadores = [];
            $historialCambios = [];
        }

        $this->view('jugadores/desactivacion/desactivacion', [
            'jugadores' => $jugadores,
            'historialCambios' => $historialCambios,
            'titulo' => 'Desactivación de Jugadores',
        ]);
    }

    /** Carga el asistente dentro del iframe del modal principal. */
    public function vistaDesactivar(): void
    {
        $this->view('jugadores/desactivacion/desactivar', [
            'titulo' => 'Cambiar estado a jugadores',
        ]);
    }

    /** Carga la confirmación final tras aplicar correctamente el cambio. */
    public function vistaConfirmacion(): void
    {
        $this->view('jugadores/desactivacion/confirmacion', [
            'titulo' => 'Cambio completado',
        ]);
    }

    /** Muestra el detalle guardado de un cambio masivo. */
    public function detalleHistorial(): void
    {
        $idCambio = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
        $cambio = $idCambio > 0 ? $this->cambioEstadoModel->obtenerCambio($idCambio) : null;
        $jugadoresCambio = $cambio ? $this->cambioEstadoModel->obtenerJugadoresCambio($idCambio) : [];

        $this->view('jugadores/desactivacion/historial', [
            'titulo' => 'Detalle del historial',
            'cambio' => $cambio,
            'jugadoresCambio' => $jugadoresCambio,
        ]);
    }

    /** Endpoint POST que persiste un cambio masivo con CSRF y transacción. */
    public function aplicarCambioMasivo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
        }
        if (!$this->validateCSRFToken((string) ($_POST['_token'] ?? ''))) {
            $this->json(['ok' => false, 'mensaje' => 'La sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.'], 403);
        }

        $ids = json_decode((string) ($_POST['jugadores'] ?? '[]'), true);
        if (!is_array($ids)) {
            $this->json(['ok' => false, 'mensaje' => 'La lista de jugadores no es válida.'], 422);
        }

        try {
            $resultado = $this->cambioEstadoModel->aplicarCambioMasivo(
                $ids,
                (int) (Auth::id() ?? 0),
                (string) ($_POST['estado'] ?? ''),
                (string) ($_POST['motivo'] ?? ''),
                isset($_POST['descripcion']) ? (string) $_POST['descripcion'] : null
            );
            $this->json(['ok' => true] + $resultado);
        } catch (InvalidArgumentException $e) {
            $this->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('JugadorController::aplicarCambioMasivo: ' . $e->getMessage());
            $mensaje = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo guardar el cambio de estado. Revisa el registro de errores.';
            $this->json(['ok' => false, 'mensaje' => $mensaje], 500);
        }
    }

    /**
     * Reactiva un jugador desde el detalle del historial.
     * Restaura su ficha y sus deudas en una única transacción del modelo.
     */
    public function reactivarJugador(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
        }

        if (!$this->validateCSRFToken((string) ($_POST['_token'] ?? ''))) {
            $this->json([
                'ok' => false,
                'mensaje' => 'La sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.',
            ], 403);
        }

        $idJugador = filter_var($_POST['id_jugadores'] ?? null, FILTER_VALIDATE_INT);
        if (!$idJugador || $idJugador <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'El identificador del jugador no es válido.'], 422);
        }

        try {
            $resultado = $this->cambioEstadoModel->aplicarCambioMasivo(
                [(int) $idJugador],
                (int) (Auth::id() ?? 0),
                'activo',
                'Reactivación de jugador',
                'Reactivación individual desde el detalle del historial.'
            );

            $this->json(['ok' => true] + $resultado);
        } catch (InvalidArgumentException $e) {
            $this->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('JugadorController::reactivarJugador: ' . $e->getMessage());
            $mensaje = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo reactivar el jugador ni restaurar sus deudas. Revisa el registro de errores.';
            $this->json(['ok' => false, 'mensaje' => $mensaje], 500);
        }
    }

    public function deudas(): void
    {
        // Obtener solo jugadores con deuda
        try {
            $jugadores = $this->jugadorModel->obtenerConDeuda();
        } catch (Exception $e){
            error_log("Deudas jugadores: " . $e->getMessage());
            $jugadores = [];
        }
        

        // Enviar datos a la vista
        $this->view('jugadores/deudasJugadores/index', [
            'jugadores' => $jugadores,
            'titulo' => 'Deudas de Jugadores'
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo jugador
     * Se ejecuta cuando accedes a /jugadores/crear (GET)
     */
    public function crear(): void
    {
        try {
            $categorias = $this->categoriaModel->obtenerConInstructor();
            $instructores = $this->instructorModel->obtenerTodos();
            $epsList = $this->epsModel->obtenerTodas();
            $tiposDocumento = $this->tipoDocumentoModel->obtenerTodos();
            $metodoPago = $this->metodoPagoModel->obtenerTodos();
            $tipoBeca = $this->tipoBecaModel->obtenerTodas();
        } catch (Exception $e) {
            error_log("Crear jugador (cargar catálogos): " . $e->getMessage());
            $categorias = [];
            $instructores = [];
            $epsList = [];
            $tiposDocumento = [];
            $metodoPago = [];
            $tipoBeca = [];
        }

        // Aviso para quien entre directo a esta página con ?error=... (guardar() ya no vuelve aquí:
        // sus errores se muestran en la tabla de jugadores con el modal abierto).
        $avisoCrear = null;

        if (isset($_GET['error']) && is_string($_GET['error'])) {
            $mensajesError = [
                'csrf' => 'Token de seguridad inválido, intenta de nuevo.',
                'campos_vacios' => 'Faltan campos obligatorios por completar.',
                'fecha_invalida' => 'La fecha ingresada no es válida.',
                'creacion_fallida' => 'No se pudo guardar el alumno, intenta de nuevo.',
            ];
            $avisoCrear = [
                'icono' => 'error',
                'titulo' => $mensajesError[$_GET['error']] ?? 'Ocurrió un error.',
                'faltantes' => [],
                'mensajes' => [],
            ];
        }

        $this->view('jugadores/gestionJugadores/create', [
            'titulo' => 'Crear Jugador',
            'avisoCrear' => $avisoCrear,
            'categorias' => $categorias,
            'instructores' => $instructores,
            'epsList' => $epsList,
            'tiposDocumento' => $tiposDocumento,
            'metodoPago' => $metodoPago,
            'tiposBeca' => $tipoBeca,
        ]);
    }


    public function editar(int $id): void
    {
        $idJugador = (int) $id;
        $jugador = $this->jugadorModel->obtenerParaEditar($idJugador);

        if(!$jugador){
            echo "Jugador no encontrado";
            return;
        }

        try{
            $categorias = $this->categoriaModel->obtenerConInstructor();
            $instructores = $this->instructorModel->obtenerTodos();
            $epsList = $this->epsModel->obtenerTodas();
            $tiposDocumento = $this->tipoDocumentoModel->obtenerTodos();
            $metodoPago = $this->metodoPagoModel->obtenerTodos();
            $tipoBeca = $this->tipoBecaModel->obtenerTodas();

            $responsableModel = new Responsable($this->pdo);
            $responsables = $responsableModel->obtenerTodos();

        }catch (Exception $e) {
            error_log("Editar jugador (cargar catálogos): ". $e->getMessage());
            $categorias = [];
            $instructores = [];
            $epsList = [];
            $tiposDocumento = [];
            $metodoPago = [];
            $tipoBeca = [];
            $responsables = [];
        }

        $this->view('jugadores/gestionJugadores/edit' ,[
            'titulo' => 'Editar Jugador',
            'jugador' => $jugador,
            'categorias' => $categorias,
            'instructores' => $instructores,
            'epsList' => $epsList,
            'tipoDocumento' => $tiposDocumento,
            'metodoPago' => $metodoPago,
            'tipoBecas' => $tipoBeca,
            'responsables' => $responsables,
        ]);

    }

    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/jugadores/gestion');
        }

        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/jugadores/gestion?error=csrf');
        }

        $idJugador = (int) ($_POST['id_jugadores'] ?? 0);
        if ($idJugador <= 0) {
            $this->redirect('/streepsoft/jugadores/gestion?error=jugador_invalido');
        }

        $nombre1 = trim($_POST['nombre1'] ?? '');
        $nombre2 = trim($_POST['nombre2'] ?? '');
        $apellido1 = trim($_POST['apellido1'] ?? '');
        $apellido2 = trim($_POST['apellido2'] ?? '');

        $datos = [
            'nombres'          => trim($nombre1 . ' ' . $nombre2),
            'apellidos'        => trim($apellido1 . ' ' . $apellido2),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'iniciales'        => trim($_POST['iniciales'] ?? ''),
            'id_categorias'    => (int) ($_POST['id_categorias'] ?? 0),
            'id_eps'           => (int) ($_POST['id_eps'] ?? 0),
            'id_instructor'    => (int) ($_POST['id_instructor'] ?? 0),
            'id_responsable'   => (int) ($_POST['id_responsable'] ?? 0),
        ];

        $documentoNumero = trim($_POST['documento'] ?? '');
        $idTipoDocumento = (int) ($_POST['id_tipo_documento'] ?? 0);

        $obligatorios = ['nombres', 'apellidos', 'fecha_nacimiento'];
        foreach ($obligatorios as $campo) {
            if ($datos[$campo] === '') {
                $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=campos_vacios');
            }
        }

        // Formato de nombres e identificaciones (mismo control que al crear)
        if (!empty($this->validarFormatoPersona())) {
            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=formato_invalido');
        }
        // La categoría manda: si tiene un instructor activo asignado, ese es el instructor
        // del jugador (aunque el formulario haya mandado otro o ninguno).
        $idInstructorCategoria = $this->categoriaModel->obtenerInstructorActivo($datos['id_categorias']);
        if ($idInstructorCategoria !== null) {
            $datos['id_instructor'] = $idInstructorCategoria;
        }

        if ($datos['id_categorias'] <= 0 || $datos['id_eps'] <= 0 || $datos['id_instructor'] <= 0) {
            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=campos_vacios');
        }

        // Foto actual del jugador: se necesita para borrarla del disco si se reemplaza por una nueva
        $jugadorActual = $this->jugadorModel->obtenerParaEditar($idJugador);
        $fotoAnterior = $jugadorActual['foto'] ?? null;

        // La foto es opcional al editar: si no se tocó, se conserva la que ya tenía
        try {
            $datos['foto'] = $this->subirFotoJugador($_POST['foto_base64'] ?? null);
        } catch (Exception $e) {
            error_log('Actualizar jugador (foto): ' . $e->getMessage());
            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=' . urlencode($e->getMessage()));
        }

        try {
            $this->pdo->beginTransaction();

            // Guardar/actualizar las tallas opcionales antes de actualizar el jugador.
            $datos['id_uniforme'] = $this->guardarTallasUniforme(
                !empty($jugadorActual['id_uniforme']) ? (int) $jugadorActual['id_uniforme'] : null
            );

            $responsableNombres = trim($_POST['responsable_nombres'] ?? '');
            $responsableApellidos = trim($_POST['responsable_apellidos'] ?? '');
            $responsableIdentificacion = trim($_POST['responsable_identificacion'] ?? '');
            $responsableCelular = trim($_POST['responsable_numero_celular'] ?? '');
            $responsableIdTipoDocumento = (int) ($_POST['responsable_id_tipo_documento'] ?? 0);

            if ($responsableNombres !== '' && $responsableApellidos !== '' && 
                $responsableIdentificacion !== '' && $responsableCelular !== '') {
                
                $responsableModel = new Responsable($this->pdo);
                $idResponsable = (int) ($datos['id_responsable'] ?? 0);

                if ($idResponsable > 0) {
                    $responsableModel->actualizar($idResponsable, [
                        'nombres' => $responsableNombres,
                        'apellidos' => $responsableApellidos,
                        'id_tipo_documento' => $responsableIdTipoDocumento,
                        'identificacion' => $responsableIdentificacion,
                        'numero_celular' => $responsableCelular,
                    ]);
                } else {
                    $idResponsable = $responsableModel->crear([
                        'nombres' => $responsableNombres,
                        'apellidos' => $responsableApellidos,
                        'id_tipo_documento' => $responsableIdTipoDocumento,
                        'identificacion' => $responsableIdentificacion,
                        'numero_celular' => $responsableCelular,
                    ]);

                    if ($idResponsable > 0) {
                        $datos['id_responsable'] = $idResponsable;
                    }
                }
            }

            // Sin responsable el formulario envía 0; la FK fk_jugadores_responsable
            // solo acepta un id existente o NULL, así que 0 se guarda como NULL.
            if (empty($datos['id_responsable'])) {
                $datos['id_responsable'] = null;
            }

            // 2) ACTUALIZAR JUGADOR
            if (!$this->jugadorModel->actualizar($idJugador, $datos)) {
                throw new Exception('No se pudo actualizar el jugador');
            }

            $this->documentoModel->guardarOActualizar($idJugador, $documentoNumero ?: null, $idTipoDocumento ?: null);

            $this->pdo->commit();

            // La BD ya usa la foto nueva: la anterior quedó huérfana, se elimina del disco
            if (!empty($datos['foto']) && !empty($fotoAnterior) && $fotoAnterior !== $datos['foto']) {
                $this->eliminarFotoJugador($fotoAnterior);
            }

            $this->redirect('/streepsoft/jugadores/gestion?success=actualizado');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Actualizar jugador: ' . $e->getMessage());

            // Se revirtió la transacción: la foto nueva (si se subió) quedó sin uso, se elimina
            $this->eliminarFotoJugador($datos['foto'] ?? null);

            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=actualizacion_fallida');
        }
    }

    /**
     * Guardar un nuevo jugador
     * Se ejecuta cuando envías el formulario (POST)
     */
    public function guardar(): void
    {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/jugadores/crear');
        }

        // Validar CSRF (el formulario ahora incluye el campo oculto _token)
        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->volverAGestionConAviso('Token de seguridad inválido, intenta de nuevo.');
        }

        // 1) Recoger y limpiar los datos de texto del formulario
        $nombre1 = trim($_POST['nombre1'] ?? '');
        $nombre2 = trim($_POST['nombre2'] ?? '');
        $apellido1 = trim($_POST['apellido1'] ?? '');
        $apellido2 = trim($_POST['apellido2'] ?? '');

        $datos = [
            'nombres'          => trim($nombre1 . ' ' . $nombre2),
            'apellidos'        => trim($apellido1 . ' ' . $apellido2),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'iniciales'        => trim($_POST['iniciales'] ?? ''),
            'id_categorias'    => (int) ($_POST['id_categorias'] ?? 0),
            'id_eps'           => (int) ($_POST['id_eps'] ?? 0),
            'id_instructor'    => (int) ($_POST['id_instructor'] ?? 0),
            'id_responsable'   => (int) ($_POST['id_responsable'] ?? 0),
        ];

        $documentoNumero = trim($_POST['documento'] ?? '');
        $idTipoDocumento = (int) ($_POST['id_tipo_documento'] ?? 0);


        $matricula = $this->limpiarMonto($_POST['Matricula'] ?? '');
        $mensualidad = $this->limpiarMonto($_POST['Mensualidad'] ?? '');
        $fechaPago = trim($_POST['fecha_pago'] ?? '');
        $idMetodoPago = (int) ($_POST['id_metodo_pago'] ?? 0);
        $idTipoBecas = (int) ($_POST['id_tipo_becas'] ?? 0);
        

        // 2) Validar TODOS los campos obligatorios en una sola pasada para poder avisar
        //    exactamente cuáles faltan (en el orden en que aparecen en el formulario).
        $faltantes = [];
        $mensajes = [];

        // La categoría manda: si tiene un instructor activo asignado, ese es el instructor
        // del jugador (aunque el formulario haya mandado otro o ninguno).
        $idInstructorCategoria = $this->categoriaModel->obtenerInstructorActivo($datos['id_categorias']);
        if ($idInstructorCategoria !== null) {
            $datos['id_instructor'] = $idInstructorCategoria;
        }

        // Paso 1 y 2: datos del jugador
        if ($datos['nombres'] === '') {
            $faltantes[] = 'Primer nombre';
        }
        if ($datos['apellidos'] === '') {
            $faltantes[] = 'Primer apellido';
        }

        $fecha = false;
        if ($datos['fecha_nacimiento'] === '') {
            $faltantes[] = 'Fecha de nacimiento';
        } else {
            // La fecha debe tener formato correcto y no ser futura
            $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_nacimiento']);
            if (!$fecha || $fecha > new DateTime()) {
                $mensajes[] = 'La fecha de nacimiento no es válida.';
            }
        }

        if ($datos['id_eps'] <= 0) {
            $faltantes[] = 'EPS';
        }
        if ($datos['id_instructor'] <= 0) {
            $faltantes[] = 'Instructor';
        }
        if ($datos['id_categorias'] <= 0) {
            $faltantes[] = 'Categoría';
        }

        // Paso 4: datos del responsable (acudiente)
        $responsableNombres = trim($_POST['responsable_nombres'] ?? '');
        $responsableApellidos = trim($_POST['responsable_apellidos'] ?? '');
        $responsableIdentificacion = trim($_POST['responsable_identificacion'] ?? '');
        $responsableCelular = trim($_POST['responsable_numero_celular'] ?? '');
        $responsableIdTipoDocumento = (int) ($_POST['responsable_id_tipo_documento'] ?? 0);

        if ($responsableNombres === '') {
            $faltantes[] = 'Nombres del acudiente';
        }
        if ($responsableApellidos === '') {
            $faltantes[] = 'Apellidos del acudiente';
        }
        if ($responsableIdTipoDocumento <= 0) {
            $faltantes[] = 'Tipo de documento del acudiente';
        }
        if ($responsableIdentificacion === '') {
            $faltantes[] = 'Identificación del acudiente';
        }
        if ($responsableCelular === '') {
            $faltantes[] = 'Celular del acudiente';
        }

        // Paso 5: inscripción y pago inicial
        if ($mensualidad <= 0) {
            $faltantes[] = 'Mensualidad';
        }

        $fechaPagoObj = false;
        if ($fechaPago === '') {
            $faltantes[] = 'Fecha de pago';
        } else {
            $fechaPagoObj = DateTime::createFromFormat('Y-m-d', $fechaPago);
            if (!$fechaPagoObj) {
                $mensajes[] = 'La fecha de pago no es válida.';
            }
        }

        if ($idMetodoPago <= 0) {
            $faltantes[] = 'Método de pago';
        }
        if ($idTipoBecas <= 0) {
            $faltantes[] = 'Tipo de beca';
        }

        // Formato de nombres e identificaciones (letras / números). El servidor siempre
        // valida, porque lo que hace el JavaScript del navegador se puede saltar.
        $mensajes = array_merge($mensajes, $this->validarFormatoPersona());

        if (!empty($faltantes) || !empty($mensajes)) {
            $this->volverAGestionConAviso(
                !empty($faltantes) ? 'Completa los campos obligatorios' : 'Revisa los datos ingresados',
                $faltantes,
                $mensajes
            );
        }

        // ------------------------------------------------------------
        // 3) Subir la foto de forma segura (es opcional en la BD)
        // ------------------------------------------------------------
        try {
            $nombreFoto = $this->subirFotoJugador($_POST['foto_base64'] ?? null);
        } catch (Exception $e) {
            error_log("Guardar jugador (foto): " . $e->getMessage());
            $this->volverAGestionConAviso('No se pudo subir la foto', [], [$e->getMessage()]);
        }
        $datos['foto'] = $nombreFoto;

        // ------------------------------------------------------------
        // 4) Guardar en la base de datos DENTRO de una transacción:
        try {
            $this->pdo->beginTransaction();

            // Crear el registro de tallas solo si se diligenció algún campo.
            $datos['id_uniforme'] = $this->guardarTallasUniforme();

            $responsableModel = new Responsable($this->pdo);
            $idResponsable = $responsableModel->crear([
                'nombres' => $responsableNombres,
                'apellidos' => $responsableApellidos,
                'id_tipo_documento' => $responsableIdTipoDocumento,
                'identificacion' => $responsableIdentificacion,
                'numero_celular' => $responsableCelular,
            ]);

            if ($idResponsable === 0) {
                throw new Exception('No se pudo crear el responsable');
            }
   
            $datos['id_responsable'] = $idResponsable;
            
            $idJugador = $this->jugadorModel->crear($datos);

            if ($idJugador === 0) {
                throw new Exception('No se pudo crear el jugador');
            }

            $this->documentoModel->crear($idJugador, $documentoNumero ?: null, $idTipoDocumento ?: null);

            $mesesEs = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
            ];
            
            $totalInicial = $matricula + $mensualidad;

            $idDeuda = $this->deudaModel->crearInicial([
                'id_jugadores' => $idJugador,
                'matricula' => $matricula,
                'mensualidad' => $mensualidad,
                'mes' => $mesesEs[(int) $fechaPagoObj->format('n')],
                'anio' => $fechaPagoObj->format('Y'),
                'totalidad' => $totalInicial,
                'fecha_pago' => $fechaPago,
                'id_metodo_pago' => $idMetodoPago,
                'id_tipo_becas' => $idTipoBecas,
                'concepto' => 'Matrícula y mensualidad de inscripción',
                'valor_pagado' => $totalInicial,
            ]);
            
            if ($idDeuda === 0) {
                throw new Exception('No se pudo crear la deuda inicial');
            }
            
            $this->pdo->commit();

            $this->redirect('/streepsoft/jugadores/gestion?success=creado');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Guardar jugador: " . $e->getMessage());

            // No se creó el jugador: la foto que se alcanzó a subir queda sin uso, se elimina
            $this->eliminarFotoJugador($nombreFoto);

            $this->volverAGestionConAviso(
                'No se pudo registrar el jugador',
                [],
                ['Ocurrió un error al guardar. Intenta de nuevo.']
            );
        }
    }

    /**
     * Deja un aviso en sesión y vuelve a la tabla de jugadores. gestion() lo recoge y la
     * página abre el modal de registro y muestra el aviso como toast
     * (gestionJugadores/index.php -> avisosFormulario.js). Se usa sesión y no la URL para
     * poder decir exactamente qué campos faltan o qué falló con la foto.
     *
     * @param string[] $faltantes Nombres de los campos obligatorios que faltan
     * @param string[] $mensajes  Otros problemas (fecha inválida, error de foto, etc.)
     */
    /**
     * Valida el FORMATO de los campos de texto y de identificación que llegan por POST.
     *
     *  - Nombres y apellidos (jugador y acudiente): solo letras (con tildes, ñ, ü),
     *    y entre palabras se admite espacio, guion o apóstrofo. Sin números ni símbolos.
     *  - Identificación del jugador, identificación del acudiente y celular: solo dígitos, máximo 10
     *    (el mismo límite que tiene el formulario).
     *
     * Los campos vacíos se ignoran aquí: si son obligatorios, ya los revisa la validación de faltantes.
     * Devuelve una lista de mensajes; si está vacía, todo está en orden.
     *
     * Por qué en el servidor: el JavaScript solo ayuda al usuario, pero cualquiera puede
     * desactivarlo o enviar la petición a mano. Esta es la validación que de verdad protege los datos.
     */
    private function validarFormatoPersona(): array
    {
        $errores = [];

        // Campo del formulario => nombre que se le muestra al usuario
        $camposTexto = [
            'nombre1'               => 'Primer nombre',
            'nombre2'               => 'Segundo nombre',
            'apellido1'             => 'Primer apellido',
            'apellido2'             => 'Segundo apellido',
            'responsable_nombres'   => 'Nombres del acudiente',
            'responsable_apellidos' => 'Apellidos del acudiente',
        ];

        foreach ($camposTexto as $campo => $etiqueta) {
            $valor = $_POST[$campo] ?? '';

            // Si alguien manda nombre1[]=x, llega un arreglo y no un texto: se rechaza
            if (!is_string($valor)) {
                $errores[] = $etiqueta . ': el valor enviado no es válido.';
                continue;
            }

            $valor = trim($valor);
            if ($valor === '') {
                continue;
            }

            // \p{L} = letra, \p{M} = tilde combinada. Palabras separadas por espacio, guion o apóstrofo.
            if (!preg_match('/^[\p{L}\p{M}]+(?:[ \'’-][\p{L}\p{M}]+)*$/u', $valor)) {
                $errores[] = $etiqueta . ': solo se permiten letras (sin números ni caracteres especiales).';
            }
        }

        // Campo del formulario => [nombre para el usuario, máximo de dígitos]
        $camposNumericos = [
            'documento'                  => ['Identificación', 10],
            'responsable_identificacion' => ['Identificación del acudiente', 10],
            'responsable_numero_celular' => ['Celular del acudiente', 10],
        ];

        foreach ($camposNumericos as $campo => [$etiqueta, $maximo]) {
            $valor = $_POST[$campo] ?? '';

            if (!is_string($valor)) {
                $errores[] = $etiqueta . ': el valor enviado no es válido.';
                continue;
            }

            $valor = trim($valor);
            if ($valor === '') {
                continue;
            }

            // ctype_digit: true solo si TODOS los caracteres son 0-9 (a diferencia de is_numeric,
            // no acepta signos, puntos, espacios ni notación científica como "1e5")
            if (!ctype_digit($valor)) {
                $errores[] = $etiqueta . ': solo se permiten números.';
            } elseif (strlen($valor) > $maximo) {
                $errores[] = $etiqueta . ': máximo ' . $maximo . ' dígitos.';
            }
        }

        return $errores;
    }

    private function volverAGestionConAviso(string $titulo, array $faltantes = [], array $mensajes = []): void
    {
        $_SESSION['aviso_registro_jugador'] = [
            'icono' => 'error',
            'titulo' => $titulo,
            'faltantes' => array_values($faltantes),
            'mensajes' => array_values($mensajes),
        ];
        $this->redirect('/streepsoft/jugadores/gestion');
    }

    /**
     * Guarda las tallas del uniforme opcional en su tabla independiente.
     * Devuelve el id del registro o null si todos los campos están vacíos.
     */
    private function guardarTallasUniforme(?int $idUniforme = null): ?int
    {
        $numero = trim((string) ($_POST['numero_camisa'] ?? ''));
        $camisa = trim((string) ($_POST['talla_camisa'] ?? ''));
        $media = trim((string) ($_POST['talla_media'] ?? ''));

        $opcionesMedias = ['Niño', 'Adolescente', 'Adulto'];
        if ($media !== '' && !in_array($media, $opcionesMedias, true)) {
            // Permite conservar un valor histórico que ya estuviera guardado,
            // pero no permite introducir valores nuevos fuera de las opciones.
            $valorHistorico = null;
            if ($idUniforme !== null && $idUniforme > 0) {
                $consultaHistorico = $this->pdo->prepare(
                    'SELECT talla_media FROM uniformes_tallas WHERE id_uniforme = ?'
                );
                $consultaHistorico->execute([$idUniforme]);
                $valorHistorico = $consultaHistorico->fetchColumn();
            }
            if ($valorHistorico === false || $valorHistorico === null || (string) $valorHistorico !== $media) {
                throw new InvalidArgumentException('La talla de medias seleccionada no es válida.');
            }
        }

        $hayDatos = ($numero !== '' || $camisa !== '' || $media !== '');

        if (!$hayDatos) {
            if ($idUniforme !== null && $idUniforme > 0) {
                $stmt = $this->pdo->prepare('DELETE FROM uniformes_tallas WHERE id_uniforme = ?');
                $stmt->execute([$idUniforme]);
            }
            return null;
        }

        if ($idUniforme !== null && $idUniforme > 0) {
            $stmt = $this->pdo->prepare(
                'UPDATE uniformes_tallas
                 SET numero_camisa = ?, talla_camisa = ?, talla_media = ?
                 WHERE id_uniforme = ?'
            );
            $stmt->execute([
                $numero !== '' ? $numero : null,
                $camisa !== '' ? $camisa : null,
                $media !== '' ? $media : null,
                $idUniforme,
            ]);

            $check = $this->pdo->prepare('SELECT id_uniforme FROM uniformes_tallas WHERE id_uniforme = ?');
            $check->execute([$idUniforme]);
            if ($check->fetchColumn()) {
                return $idUniforme;
            }
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO uniformes_tallas (numero_camisa, talla_camisa, talla_media)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $numero !== '' ? $numero : null,
            $camisa !== '' ? $camisa : null,
            $media !== '' ? $media : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Convierte importes escritos en formato colombiano (80.000 o $80.000)
     * a un número entero de pesos. En este formulario COP no usa centavos.
     */
    private function limpiarMonto(string $valor): float
    {
        $limpio = preg_replace('/[^0-9]/', '', $valor);
        return $limpio === '' ? 0.0 : (float) $limpio;
    }


    /**
     * Guardar la foto del jugador de forma segura
     *
     */
    private function subirFotoJugador(?string $dataUrl): ?string
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

        $binario = base64_decode($contenidoBase64, true);
        if ($binario === false) {
            throw new Exception('No se pudo leer la imagen enviada');
        }

        $tamanoMaximo = 3 * 1024 * 1024; // 3 MB
        if (strlen($binario) > $tamanoMaximo) {
            throw new Exception('La foto supera el tamaño máximo permitido');
        }

        // No confiamos solo en lo que dice el data URL: comprobamos el
        // contenido real decodificado, igual que antes se hacía con finfo.
        $info = getimagesizefromstring($binario);
        if ($info === false || !isset($tiposPermitidos[$info['mime']])) {
            throw new Exception('Formato de imagen no permitido (solo JPG o PNG)');
        }

        $carpetaDestino = __DIR__ . '/../../public/Image/jugadores';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        // Nombre de archivo aleatorio y seguro (nunca datos del cliente)
        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$info['mime']];

        if (file_put_contents($carpetaDestino . '/' . $nombreArchivo, $binario) === false) {
            throw new Exception('No se pudo guardar la foto en el servidor');
        }

        return $nombreArchivo;
    }

    /**
     * Elimina del disco una foto de jugador (solo dentro de public/Image/jugadores).
     * No la borra si otro registro (jugadores o jugadores_inactivos) todavía la usa:
     * al inactivar un jugador su foto se copia a jugadores_inactivos.
     */
    private function eliminarFotoJugador(?string $nombreFoto): void
    {
        if (empty($nombreFoto)) {
            return;
        }

        try {
            // basename() evita borrar archivos fuera de la carpeta si la BD trae un valor raro
            $nombreFoto = basename($nombreFoto);

            $stmt = $this->pdo->prepare(
                'SELECT (SELECT COUNT(*) FROM jugadores WHERE foto = ?)
                      + (SELECT COUNT(*) FROM jugadores_inactivos WHERE foto = ?)'
            );
            $stmt->execute([$nombreFoto, $nombreFoto]);

            if ((int) $stmt->fetchColumn() > 0) {
                return;
            }

            $ruta = __DIR__ . '/../../public/Image/jugadores/' . $nombreFoto;

            if (is_file($ruta) && !unlink($ruta)) {
                error_log('JugadorController::eliminarFotoJugador - No se pudo eliminar: ' . $ruta);
            }
        } catch (Throwable $e) {
            // Limpiar una foto nunca debe romper la operación principal (crear, editar o eliminar)
            error_log('JugadorController::eliminarFotoJugador - ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un jugador
     */
    public function eliminar(int $id): void
    {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/streepsoft/jugadores/gestion');
        }

        // Validar CSRF
        if (!$this->validateCSRFToken($_POST['_token'] ?? '')) {
            $this->redirect('/streepsoft/jugadores/gestion?error=csrf');
        }

        // Se lee la foto ANTES de borrar el registro, porque después ya no se podría consultar
        $jugadorAEliminar = $this->jugadorModel->obtenerParaEditar($id);
        $fotoJugador = $jugadorAEliminar['foto'] ?? null;

        // Eliminar el jugador
        if ($this->jugadorModel->eliminar($id)) {
            $this->eliminarFotoJugador($fotoJugador);
            $this->redirect('/streepsoft/jugadores/gestion?success=eliminado');
        } else {
            $this->redirect('/streepsoft/jugadores/gestion?error=eliminacion_fallida');
        }
    }


    public function perfil(int $id): void
    {
        // ANTES: perfil() no recibía ningún $id, así que llamaba a
        // obtenerTodos() (la lista COMPLETA de jugadores) y trataba
        // el resultado como si fuera un solo jugador. Por eso salían
        // los "undefined array key nombres/apellidos/estado": esas
        // claves no existen en una lista, existen en una fila.
        $jugador = $this->jugadorModel->obtenerPorId($id);

        if (!$jugador) {
            $this->redirect('/streepsoft/jugadores/gestion?error=no_encontrado');
            return;
        }
        
        $responsable = null;
        if (!empty($jugador['id_responsable'])) {
            try {
                $responsableModel = new Responsable($this->pdo);
                $responsable = $responsableModel->obtenerPorId((int)$jugador['id_responsable']);
            } catch (Exception $e) {
                error_log("Cargar responsable: " . $e->getMessage());
            }
        }


        $this->view('perfilJugador/index', [
            'titulo' => 'Perfil de Alumnos',
            'jugador' => $jugador,
            'responsable' => $responsable 
        ]);
    }
}
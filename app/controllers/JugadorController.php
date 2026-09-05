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
    }

    /**
     * Mostrar lista de jugadores en gestión
     * 
     * Se ejecuta cuando accedes a /jugadores/gestion
     */
    public function gestion(): void
    {
        // Obtener todos los jugadores del modelo
        try {
            $jugadores = $this->jugadorModel->obtenerTodos();
        } catch (Exception $e) {
            error_log("Gestion jugadiores: " . $e->getMessage());
            $jugadores = [];
        }
        

        // Enviar datos a la vista
        $this->view('jugadores/gestionJugadores/index', [
            'jugadores' => $jugadores,
            'titulo' => 'Gestión de Jugadores'
        ]);
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
            $categorias = $this->categoriaModel->obtenerTodas();
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

        $this->view('jugadores/gestionJugadores/create', [
            'titulo' => 'Crear Jugador',
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
            $categorias = $this->categoriaModel->obtenerTodas();
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
        if ($datos['id_categorias'] <= 0 || $datos['id_eps'] <= 0 || $datos['id_instructor'] <= 0) {
            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=campos_vacios');
        }

        // La foto es opcional al editar: si no se tocó, se conserva la que ya tenía
        try {
            $datos['foto'] = $this->subirFotoJugador($_POST['foto_base64'] ?? null);
        } catch (Exception $e) {
            error_log('Actualizar jugador (foto): ' . $e->getMessage());
            $this->redirect('/streepsoft/jugadores/editar/' . $idJugador . '?error=' . urlencode($e->getMessage()));
        }

        try {
            $this->pdo->beginTransaction();

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

            // 2) ACTUALIZAR JUGADOR
            if (!$this->jugadorModel->actualizar($idJugador, $datos)) {
                throw new Exception('No se pudo actualizar el jugador');
            }

            $this->documentoModel->guardarOActualizar($idJugador, $documentoNumero ?: null, $idTipoDocumento ?: null);

            $this->pdo->commit();
            $this->redirect('/streepsoft/jugadores/gestion?success=actualizado');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Actualizar jugador: ' . $e->getMessage());
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
            $this->redirect('/streepsoft/jugadores/crear?error=csrf');
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
        

        // 2) Validar los campos obligatorios 
        $obligatorios = [
            'nombres', 'apellidos', 'fecha_nacimiento',
            'acudiente', 'numero_acudiente',
        ];

        foreach ($obligatorios as $campo) {
            if ($datos[$campo] === '') {
                $this->redirect('/streepsoft/jugadores/crear?error=campos_vacios');
            }
        }

        if ($datos['id_categorias'] <= 0 || $datos['id_eps'] <= 0 || $datos['id_instructor'] <= 0) {
            $this->redirect('/streepsoft/jugadores/crear?error=campos_vacios');
        }

        if ($mensualidad <= 0 || $fechaPago === '' || $idMetodoPago <= 0 || $idTipoBecas <= 0){
            $this->redirect('/streepsoft/jugadores/crear?error=campos_vacios');
        } 

        // Validar que la fecha de nacimiento tenga formato correcto y no sea futura
        $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_nacimiento']);
        if (!$fecha || $fecha > new DateTime()) {
            $this->redirect('/streepsoft/jugadores/crear?error=fecha_invalida');
        }

        $fechaPagoObj = DateTime::createFromFormat('Y-m-d', $fechaPago);
        if (!$fechaPagoObj) {
            $this->redirect('/streepsoft/jugadores/crear?error=fecha_invalida');
        }

        // Validar datos del responsable
        $responsableNombres = trim($_POST['responsable_nombres'] ?? '');
        $responsableApellidos = trim($_POST['responsable_apellidos'] ?? '');
        $responsableIdentificacion = trim($_POST['responsable_identificacion'] ?? '');
        $responsableCelular = trim($_POST['responsable_numero_celular'] ?? '');
        $responsableIdTipoDocumento = (int) ($_POST['responsable_id_tipo_documento'] ?? 0);

        if ($responsableNombres === '' || $responsableApellidos === '' || 
            $responsableIdentificacion === '' || $responsableCelular === '') {
            $this->redirect('/streepsoft/jugadores/crear?error=datos_responsable_incompletos');
        }

        if ($responsableIdTipoDocumento <= 0) {
            $this->redirect('/streepsoft/jugadores/crear?error=tipo_documento_responsable_invalido');
        }

        // ------------------------------------------------------------
        // 3) Subir la foto de forma segura (es opcional en la BD)
        // ------------------------------------------------------------
        try {
            $nombreFoto = $this->subirFotoJugador($_POST['foto_base64'] ?? null);
        } catch (Exception $e) {
            error_log("Guardar jugador (foto): " . $e->getMessage());
            $this->redirect('/streepsoft/jugadores/crear?error=' . urlencode($e->getMessage()));
        }
        $datos['foto'] = $nombreFoto;

        // ------------------------------------------------------------
        // 4) Guardar en la base de datos DENTRO de una transacción:
        try {
            $this->pdo->beginTransaction();

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
            
            $idDeuda = $this->deudaModel->crearInicial([
                'id_jugadores' => $idJugador,
                'matricula' => $matricula,
                'mes' => $mesesEs[(int) $fechaPagoObj->format('n')],
                'anio' => $fechaPagoObj->format('Y'),
                'totalidad' => $mensualidad,
                'fecha_pago' => $fechaPago,
                'id_metodo_pago' => $idMetodoPago,
                'id_tipo_becas' => $idTipoBecas,
                'concepto' => 'Matrícula y mensualidad de inscripción',
                'valor_pagado' => $matricula + $mensualidad,
            ]);
            
            if ($idDeuda === 0) {
                throw new Exception('No se pudo crear la deuda inicial');
            }
            
            $this->pdo->commit();

            $this->redirect('/streepsoft/jugadores/gestion?success=creado');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Guardar jugador: " . $e->getMessage());
            $this->redirect('/streepsoft/jugadores/gestion?error=creacion_fallida');
        }
    }

    private function limpiarMonto(string $valor): float
    {
        $limpio = preg_replace('/[^0-9\.]/', '', $valor); 
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

        // Eliminar el jugador
        if ($this->jugadorModel->eliminar($id)) {
            $this->redirect('/streepsoft/jugadores/gestion?success=eliminado');
        } else {
            $this->redirect('/streepsoft/jugadores/gestion?error=eliminacion_fallida');
        }
    }


    public function perfil(): void
    {
        $jugadores = $this->jugadorModel->obtenerTodos();

        $this->view('perfilJugador/index', [
            'titulo' => 'Perfil de Alumnos',
            'jugadores' => $jugadores
        ]);
    }
}
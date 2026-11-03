<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/desactivacion.css">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/modaldesactivacion.css">
    <title>Cambiar estado a jugadores</title>
</head>
<body class="iframe-child">
    <main class="iframe-view">
        <input type="hidden" id="csrfTokenDesactivacion" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="vista-encabezado">
            <div>
                <h2>Cambiar estado a varios jugadores</h2>
                <p>Completa los pasos para revisar y confirmar el cambio.</p>
            </div>
        </div>
        <div class="wizard-pasos" id="wizardIndicador">
            <div class="wizard-paso-indicador activo" data-paso-indicador="1"><span class="wizard-paso-numero">1</span></div>
            <div class="wizard-linea"></div>
            <div class="wizard-paso-indicador" data-paso-indicador="2"><span class="wizard-paso-numero">2</span></div>
            <div class="wizard-linea"></div>
            <div class="wizard-paso-indicador" data-paso-indicador="3"><span class="wizard-paso-numero">3</span></div>
        </div>
        <div class="vista-cuerpo">
            <section class="wizard-paso-contenido" data-paso="1">
                <h3 class="wizard-pregunta">¿A qué estado quieres cambiar los jugadores?</h3>
                <p class="wizard-subtexto" id="wizardSubtitulo">Los jugadores seleccionados recibirán el mismo cambio.</p>
                <label class="opcion-estado">
                    <input type="radio" name="nuevoEstado" value="Inactivo" checked>
                    <div class="opcion-estado-texto"><strong>Inactivo</strong><p>El jugador deja de aparecer entre los cobros activos, pero conserva su historial.</p></div>
                </label>
                <div class="campo-filtro campo-ancho">
                    <label for="motivoCambio">Motivo del cambio</label>
                    <select id="motivoCambio">
                        <option value="">Selecciona un motivo</option>
                        <option value="Mora de mensualidad">Meses sin pago de mensualidad</option>
                        <option value="Fin de temporada">Fin de temporada, no renovó matrícula</option>
                        <option value="Reactivación de jugador">Reactivación de jugador</option>
                        <option value="Ajuste administrativo">Ajuste administrativo</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="campo-filtro campo-ancho">
                    <label for="descripcionCambio">Descripción</label>
                    <textarea id="descripcionCambio" rows="3" placeholder="Detalle opcional..."></textarea>
                </div>
            </section>
            <section class="wizard-paso-contenido oculto" data-paso="2">
                <h3 class="wizard-pregunta">Revisa a quiénes se aplicará el cambio</h3>
                <p class="wizard-subtexto">Puedes quitar a algún jugador antes de confirmar.</p>
                <div class="lista-revision" id="listaRevision"></div>
            </section>
            <section class="wizard-paso-contenido oculto" data-paso="3">
                <h3 class="wizard-pregunta">Confirmar cambio</h3>
                <p class="wizard-subtexto">Revisa el resumen antes de continuar.</p>
                <div class="resumen-confirmacion">
                    <div class="resumen-item"><span class="resumen-etiqueta">Jugadores afectados</span><strong id="resumenCantidad">0</strong></div>
                    <div class="resumen-item"><span class="resumen-etiqueta">Nuevo estado</span><strong id="resumenEstado" class="resumen-estado-inactivo">Inactivo</strong></div>
                </div>
                <div class="resumen-item resumen-motivo"><span class="resumen-etiqueta">Motivo</span><strong id="resumenMotivo">—</strong></div>
                <label class="aviso-confirmacion">
                    <input type="checkbox" id="checkEntendido">
                    <span>Entiendo que este cambio se aplicaría a todos los jugadores listados. El cambio se guardará en el historial y actualizará el estado de los jugadores seleccionados.</span>
                </label>
            </section>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secundario" id="btnWizardAtras">Cancelar</button>
            <button type="button" class="btn-primario" id="btnWizardSiguiente">Continuar</button>
        </div>
    </main>
    <script src="/streepsoft/public/js/Jugadores/desactivacion.js"></script> 
</body>
</html>

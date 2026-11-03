<?php
/** @var array|null $cambio */
/** @var array $jugadoresCambio */
$estado = strtolower((string) ($cambio['estado_aplicado'] ?? 'inactivo'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/modaldesactivacion.css">
    <link rel="stylesheet" href="/streepsoft/public/css/jugadores/desactivacion.css">
    <title>Detalle del historial</title>
</head>
<body class="iframe-child vista-detalle-jugadores">
    <main class="iframe-view">
        <input type="hidden" id="csrfTokenReactivacion" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!$cambio): ?>
            <div class="vista-encabezado">
                <div><h2>Historial no encontrado</h2><p>Este registro no existe o ya no está disponible.</p></div>
            </div>
        <?php else: ?>
            <div class="seccion-detalles-sup">
                <p class="detalle-realizado-por">
                    Realizado por <strong id="detalleHistorialRealizadoPor"><?= htmlspecialchars($cambio['realizado_por'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></strong>
                    · <span class="badge-estado badge-<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>" id="detalleHistorialBadge"><?= htmlspecialchars(ucfirst($estado), ENT_QUOTES, 'UTF-8') ?></span>
                </p>
                <h3 class="detalle-subtitulo-1">Motivo</h3>
                <p class="detalle-motivo-texto" id="detalleHistorialMotivo"><?= htmlspecialchars($cambio['motivo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($cambio['descripcion'])): ?>
                    <h3 class="detalle-subtitulo">Descripción</h3>
                    <p><?= nl2br(htmlspecialchars($cambio['descripcion'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                 <h3 class="detalle-subtitulo titulo-jugadores-afectados" id="detalleHistorialContadorTitulo">Jugadores afectados (<?= count($jugadoresCambio) ?>)</h3>
            </div>
            <div class="seccion-jugadores-card">
                <div class="lista-revision" id="detalleHistorialLista">
                    <?php foreach ($jugadoresCambio as $jugador): ?>
                        <div class="item-revision" data-jugador-id="<?= (int) $jugador['id'] ?>">
                            <div class="celda-jugador">
                                <div class="avatar-iniciales"><?= htmlspecialchars($jugador['iniciales'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="celda-jugador-texto">
                                    <h3><?= htmlspecialchars(trim(($jugador['nombres'] ?? '') . ' ' . ($jugador['apellidos'] ?? '')) . ' · ' . ($jugador['categoria'] ?? 'Sin categoría'), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p><?= htmlspecialchars($jugador['documento'] ?? 'Documento no registrado', ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                            <?php $estadoActual = strtolower(trim((string) ($jugador['estado_actual'] ?? ''))); ?>
                            <?php if ($estadoActual === 'inactivo'): ?>
                                <button type="button" class="btn-reactivar" data-jugador-id="<?= (int) $jugador['id'] ?>">Reactivar</button>
                            <?php elseif ($estadoActual === 'activo'): ?>
                                <span class="badge-estado badge-activo">Activo</span>
                            <?php else: ?>
                                <span class="badge-estado badge-retirado">Retirado</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($jugadoresCambio)): ?>
                        <p>No hay jugadores asociados a este cambio.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="modal-footer">
            <button type="button" class="btn-secundario" id="btnVolverMenu">Volver</button>
            <button type="button" class="btn-primario" id="btnCerrarDetalle">Cerrar</button>
        </div>
    </main>
    <script src="/streepsoft/public/js/Jugadores/desactivacion.js"></script>
</body>
</html>

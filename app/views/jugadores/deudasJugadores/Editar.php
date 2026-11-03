<?php
declare(strict_types=1);
/** @var array $pago */
/** @var array $metodos */
/** @var string $csrfToken */

$esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
$nombreJugador = trim(($pago['nombres'] ?? '') . ' ' . ($pago['apellidos'] ?? ''));
$fechaLimite = !empty($pago['fecha_limite_pago']) ? date('Y-m-d', strtotime($pago['fecha_limite_pago'])) : '';
$fechaPago = !empty($pago['fecha_pago']) ? date('Y-m-d', strtotime($pago['fecha_pago'])) : '';
$descuentoActual = (int) ($pago['descuento_porcentaje'] ?? 0);
$errores = [
    'csrf' => 'La sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.',
    'datos' => 'Revisa los importes, las fechas y el descuento. Hay algún valor inválido.',
    'metodo' => 'El método de pago seleccionado no es válido.',
    'guardar' => 'No se pudo actualizar el pago. Inténtalo nuevamente.',
];
?>
<link rel="stylesheet" href="/streepsoft/public/css/nuevo/historialPago.css">
<div class="card-pagos">
    <div class="card-text">
        <div class="text-pagos">
            <h2>Editar pago registrado</h2>
            <p><?= $esc($nombreJugador) ?><?= !empty($pago['categoria']) ? ' - ' . $esc($pago['categoria']) : '' ?></p>
            <p>Mensualidad de <?= $esc($pago['mes']) ?> de <?= (int) $pago['anio'] ?></p>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:#e74c3c;font-weight:bold;padding:0 30px;">
            <?= $esc($errores[$_GET['error']] ?? 'Ocurrió un error al editar el pago.') ?>
        </p>
    <?php endif; ?>

    <form action="/streepsoft/jugadores/deudas/historial/actualizar/<?= (int) $pago['id_historial'] ?>"
          method="POST" id="formEditarHistorial">
        <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">

        <div class="card-detalle-pago">
            <div class="card-seleccion">
                <div class="card-registros">
                    <div class="registro">
                        <p>Matrícula (COP)</p>
                        <div class="card-info">
                            <input class="input-pago" type="number" name="matricula" min="0" step="0.01" required value="<?= $esc(number_format((float) ($pago['matricula'] ?? 0), 2, '.', '')) ?>">
                        </div>
                    </div>
                    <div class="registro">
                        <p>Total del periodo (COP)</p>
                        <div class="card-info">
                            <input class="input-pago" type="number" name="totalidad" min="0" step="0.01" required value="<?= $esc(number_format((float) ($pago['totalidad'] ?? 0), 2, '.', '')) ?>">
                        </div>
                    </div>
                    <div class="registro">
                        <p>Valor realmente pagado (COP)</p>
                        <div class="card-info">
                            <input class="input-pago" type="number" name="valor_pagado" min="0" step="0.01" required value="<?= $esc(number_format((float) ($pago['valor_pagado'] ?? 0), 2, '.', '')) ?>">
                        </div>
                    </div>
                    <div class="registro">
                        <p>Fecha límite de pago</p>
                        <div class="card-info">
                            <input class="input-pago" type="date" name="fecha_limite_pago" required value="<?= $esc($fechaLimite) ?>">
                        </div>
                    </div>
                    <div class="registro">
                        <p>Fecha en que se pagó</p>
                        <div class="card-info">
                            <input class="input-pago" type="date" name="fecha_pago" required value="<?= $esc($fechaPago) ?>">
                        </div>
                    </div>
                    <div class="registro">
                        <p>Método de pago</p>
                        <div class="card-info select-tipo">
                            <select class="custom-select" name="id_metodo_pago">
                                <option value="">Sin método registrado</option>
                                <?php foreach ($metodos as $metodo): ?>
                                    <option value="<?= (int) $metodo['id_metodo_pago'] ?>"
                                        <?= (int) ($pago['id_metodo_pago'] ?? 0) === (int) $metodo['id_metodo_pago'] ? 'selected' : '' ?>>
                                        <?= $esc($metodo['tipo_metodo_pago']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="registro">
                        <p>Descuento aplicado (%)</p>
                        <div class="card-info select-tipo">
                            <select class="custom-select" name="descuento_porcentaje">
                                <?php if (!in_array($descuentoActual, [0, 20, 100], true)): ?>
                                    <option value="<?= $descuentoActual ?>" selected><?= $descuentoActual ?>%</option>
                                <?php endif; ?>
                                <option value="0" <?= $descuentoActual === 0 ? 'selected' : '' ?>>Ninguno 0%</option>
                                <option value="20" <?= $descuentoActual === 20 ? 'selected' : '' ?>>Media beca 20%</option>
                                <option value="100" <?= $descuentoActual === 100 ? 'selected' : '' ?>>Beca 100%</option>
                            </select>
                        </div>
                    </div>
                    <div class="registro">
                        <p>Concepto</p>
                        <div class="card-info">
                            <input class="input-pago" type="text" name="concepto" maxlength="255" value="<?= $esc($pago['concepto'] ?? '') ?>" placeholder="Concepto del pago">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-guardar">
            <div class="card-g-pago">
                <div class="btn-cancelar">
                    <button type="button" onclick="window.parent.postMessage('cerrarModalPago', window.location.origin)">Cancelar</button>
                </div>
                <div class="btn-aceptar">
                    <button type="submit">Guardar </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php if (isset($_GET['actualizado'])): ?>
<script>
    // Se ejecuta dentro del iframe después de que el controlador confirme la actualización.
    if (window.parent !== window) {
        window.parent.postMessage(
            { tipo: 'pago-historial-actualizado' },
            window.location.origin
        );
    }
</script>
<?php endif; ?>

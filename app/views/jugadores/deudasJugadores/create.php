<?php
/** @var array $deuda */
/** @var array $metodos */
/** @var string $csrfToken */

$valor = (float) $deuda['totalidad'];
$fechaLimiteObj = date_create($deuda['fecha_limite_pago'] ?? '');
$fechaLimiteTexto = $fechaLimiteObj ? $fechaLimiteObj->format('d/m/Y') : '-';
?>
<link rel="stylesheet" href="/streepsoft/public/css/nuevo/nuevoPago.css">
<body>
    <div class="card-pagos">
    <div class=" card-text">
        <div class="text-pagos">
            <h2>Registrar Pagos</h2>
            <p><?= htmlspecialchars(trim(($deuda['nombres'] ?? '') . ' ' . ($deuda['apellidos'] ?? ''))) ?> - Mensualidad de <?= htmlspecialchars($deuda['mes'] ?? '') ?></p>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:#e74c3c; font-weight:bold; padding: 0 20px;">
            <?php
                $errores = [
                    'csrf' => 'Token de seguridad inválido, vuelve a intentar.',
                    'campos_vacios' => 'Completa el valor y el método de pago.',
                    'no_guardado' => 'No se pudo registrar el pago.',
                ];
                echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error.');
            ?>
        </p>
    <?php endif; ?>

    <form action="/streepsoft/deudas/registrar-pago" method="POST" target="_top">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <input type="hidden" name="id_deudas" value="<?= (int) $deuda['id_deudas'] ?>">


        <div class="card-detalle-pago">
            <div class="card-registro-pago">
                <div class="detalle-pago">
                    <div class="text-detalle-pago">
                        <h2>Detalle de pago</h2>
                    </div>
                </div>

                <div class="text-info-pagos">
                    <div class="info-pagos">
                        <h3>Valor</h3>
                        <p><?= '$' . number_format($valor, 0, ',', '.') ?> cop</p>
                    </div>

                    <div class="info-pagos">
                        <h3>Fecha</h3>
                        <p><?= htmlspecialchars($fechaLimiteTexto) ?></p>
                    </div>

                    <div class="info-pagos">
                        <h3>Metodo de pago</h3>
                        <p>-</p>
                    </div>

                    <div class="info-pagos">
                        <h3>Concepto</h3>
                        <p>Mensualidad de <?= htmlspecialchars($deuda['mes'] ?? '') ?></p>
                    </div>

                    <div class="info-pagos">
                        <h3>Descuento</h3>
                        <p>Ninguno 0%</p>
                    </div>

                    <div class="text-total">
                        <h1>Total</h1>
                        <p><?= '$' . number_format($valor, 0, ',', '.') ?> cop</p>
                    </div>
                </div>
            </div>

            <div class="card-seleccion">
                <div class="card-registros">
                    <div class="registro">
                        <p>Valor</p>
                        <div class="card-info">
                            <input type="number" class="input-pago" name="valor" value="<?= (int) $valor ?>" required>
                        </div>
                    </div>

                    <div class="registro">
                        <p>Fecha</p>
                        <div class="card-info">
                            <input type="date" class="input-pago" name="fecha_pago" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="registro">
                        <p>Metodo de pago</p>
                        <div class="card-info">
                            <div class="select-tipo">
                                <select class="custom-select" name="id_metodo_pago" required>
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="">Selecciona...</option>
                                    <?php foreach ($metodos as $metodo): ?>
                                        <option value="<?= (int) $metodo['id_metodo_pago'] ?>">
                                            <?= htmlspecialchars($metodo['tipo_metodo_pago']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="registro">
                        <p>Concepto</p>
                        <div class="card-info">
                            <input type="text" class="input-pago" name="concepto" placeholder="Mensualidad">
                        </div>
                    </div>

                    <div class="registro">
                        <p>Descuento</p>
                        <div class="card-info">
                            <div class="select-tipo">
                                <select class="custom-select" name="descuento_porcentaje">
                                    <button>
                                        <selectedcontent></selectedcontent>
                                    </button>
                                    <option value="0">Ninguno 0%</option>
                                    <option value="20">Media Beca 20%</option>
                                    <option value="100">Beca 100%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-guardar">
            <div class="card-g-pago">
                <div class="btn-cancelar">
                    <button type="button" onclick="window.parent.postMessage('cerrarModalPago', '*')">
                        cancelar
                    </button>
                </div>

                <div class="btn-aceptar">
                    <button type="submit">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

<script src="/streepsoft/public/js/nuevo/pago.js"></script>
</body>

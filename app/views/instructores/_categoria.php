<?php
// Casillas de categorías del instructor.
// Una categoría pertenece a un solo instructor; un instructor puede tener varias.
// Variables: $categorias (con id_instructor / instructor_nombre), $idsSeleccionados, $idInstructorActual.
$idsSeleccionados = $idsSeleccionados ?? [];
$idInstructorActual = (int) ($idInstructorActual ?? 0);
?>
<div class="grupo">
    <label>Categorías</label>

    <div class="categorias-checks">
        <?php foreach ($categorias as $cat): ?>
            <?php
            $idCat = (int) $cat['id_categorias'];
            $idDueno = (int) ($cat['id_instructor'] ?? 0);
            $esDeOtro = $idDueno > 0 && $idDueno !== $idInstructorActual;
            ?>
            <label class="categoria-check">
                <input type="checkbox"
                       name="id_categorias[]"
                       value="<?= $idCat ?>"
                       <?= in_array($idCat, $idsSeleccionados, true) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($cat['nombre']) ?></span>
                <?php if ($esDeOtro): ?>
                    <small>(hoy: <?= htmlspecialchars($cat['instructor_nombre'] ?? '') ?>)</small>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div>

    <p class="ayuda-categorias">
        Cada categoría tiene un solo instructor. Si marcas una que ya tiene otro,
        pasa a este instructor y sus jugadores se reasignan a él.
    </p>
</div>

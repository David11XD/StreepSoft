<?php
$textoEstado = ['incompleto'=>'Incompleto', 'verificar'=>'Por verificar', 'completo'=>'Completo'];
$totalDocumentos = 4;
$esc = static fn($valor) => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/documentos/documentos.css">
    <link rel="shortcut icon" href="/streepsoft/public/assets/img/logofavi.ico" type="image/x-icon">
    <title>Documentos | Alumnos</title>
</head>

<body>

    <div id="nav-card"></div>

    <div class="main-content">

        <h1 class="doc-titulo">Bienvenido a Documentos | Alumnos</h1>

        <!-- TARJETAS DE RESUMEN -->
        <section class="doc-resumen">

            <article class="doc-card">
                <span class="doc-card-icono icono-dorado">
                    <i class="fi fi-rr-user-add"></i>
                </span>
                <div class="doc-card-texto">
                    <strong><?= (int)($resumenDocumentos['total'] ?? 0) ?></strong>
                    <span>Total de alumnos</span>
                    <small>esta temporada</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-verde">
                    <i class="fi fi-rr-check"></i>
                </span>
                <div class="doc-card-texto">
                    <strong><?= (int)($resumenDocumentos['completos'] ?? 0) ?></strong>
                    <span>Documentación completa</span>
                    <small>4 de 4 verificados</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-rojo">
                    <i class="fi fi-rr-triangle-warning"></i>
                </span>
                <div class="doc-card-texto">
                    <strong><?= (int)($resumenDocumentos['incompletos'] ?? 0) ?></strong>
                    <span>Con documentos pendientes</span>
                    <small>falta al menos uno</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-dorado">
                    <i class="fi fi-rr-memo-circle-check"></i>
                </span>
                <div class="doc-card-texto">
                    <strong><?= (int)($resumenDocumentos['verificar'] ?? 0) ?></strong>
                    <span>Por verificar</span>
                    <small>archivos subidos sin revisar</small>
                </div>
            </article>

        </section>

        <!--  BARRA DE FILTROS  -->
        <section class="doc-filtros">

            <label class="doc-buscador">
                <i class="fi fi-rr-search"></i>
                <input type="text" id="doc-buscar" placeholder="Buscar por nombre o identificación">
            </label>

            <!-- FILTRO DE CATEGORÍA -->
            <div class="select-tipo doc-select">
                <span class="fi fi-rr-user"></span>

                <select class="custom-select" id="filtro-categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach (array_unique(array_filter(array_column($alumnos, 'categoria'))) as $categoria): ?>
                        <option value="<?= $esc($categoria) ?>"><?= $esc($categoria) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- FILTRO DE DOCUMENTACIÓN -->
            <div class="select-tipo doc-select">
                <span class="fi fi-rr-document"></span>

                <select class="custom-select" id="filtro-documentacion">
                    <option value="">Toda la documentación</option>
                    <option value="completo">Completo</option>
                    <option value="incompleto">Incompleto</option>
                    <option value="verificar">Por verificar</option>
                </select>
            </div>
        </section>

        <!--  TABLA DE ALUMNOS  -->
        <section class="doc-tabla-contenedor">

            <table class="doc-tabla">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Categoria</th>
                        <th>Entrega</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alumnos)): ?>
                        <tr><td colspan="4">No hay jugadores activos registrados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr data-nombre="<?= $esc($alumno['nombre']) ?>" data-documento="<?= $esc($alumno['documento']) ?>" data-categoria="<?= $esc($alumno['categoria']) ?>" data-estado="<?= $esc($alumno['estado']) ?>">
                            <td>
                                <div class="doc-alumno">
                                    <span class="doc-avatar">
                                        <?php if (!empty($alumno['foto'])): ?>
                                            <img src="/streepsoft/public/Image/jugadores/<?= rawurlencode(basename((string)$alumno['foto'])) ?>"
                                                 alt="Foto de <?= $esc($alumno['nombre']) ?>"
                                                 loading="lazy"
                                                 onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                                        <?php endif; ?>
                                        <span <?= !empty($alumno['foto']) ? 'hidden' : '' ?>><?= $esc(mb_substr($alumno['iniciales'] ?? '', 0, 3)) ?></span>
                                    </span>
                                    <div class="doc-alumno-info">
                                        <p class="doc-alumno-nombre"><?= $esc($alumno['nombre']) ?></p>
                                        <small class="doc-alumno-doc"><?= $esc($alumno['documento']) ?></small>
                                    </div>
                                </div>
                            </td>

                            <td class="doc-categoria"><?= $esc($alumno['categoria']) ?></td>

                            <td>
                                <div class="doc-entrega">
                                    <div class="doc-progreso-fila">
                                        <div class="doc-progreso estado-<?= $alumno['estado'] ?>"
                                            data-entregados="<?= $alumno['entregados'] ?>"
                                            data-total="<?= $totalDocumentos ?>">
                                            <div class="doc-progreso-relleno"></div>
                                        </div>
                                        <span class="doc-progreso-numero"><?= $alumno['entregados'] ?>/<?= $totalDocumentos ?></span>
                                    </div>
                                    <span class="doc-estado estado-<?= $alumno['estado'] ?>">
                                        <?= $textoEstado[$alumno['estado']] ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <a href="/streepsoft/documentos/gestionar/<?= $alumno['id'] ?>" class="doc-btn-gestionar">Gestionar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="doc-paginacion" id="doc-paginacion">
                <span id="doc-info-registros">Mostrando 0 de 0 alumnos</span>
                <nav class="doc-paginas" id="doc-paginas"></nav>
                <label class="doc-registros">Mostrar
                    <select id="doc-cantidad"><option>5</option><option>10</option><option>20</option></select>
                    registros
                </label>
            </div>

        </section>

    </div>
    
    <script src="/streepsoft/public/js/dashboard/dashboard.js"></script>
    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/documentos/documentos.js"></script>
</body>

</html>

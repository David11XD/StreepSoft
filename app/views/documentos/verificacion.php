<?php

/**
 * Vista: Documentos | Alumnos
 * Por ahora usa datos quemados (de prueba). Mas adelante este arreglo
 * llegara desde el controlador con informacion real de la base de datos.
 */

$alumnos = [
    ['id' => 1, 'nombre' => 'Carlos Tomas Gomez Perez', 'iniciales' => 'CTP', 'documento' => 'C.C 1087637289', 'categoria' => 'Sub-20', 'entregados' => 0, 'estado' => 'incompleto'],
    ['id' => 2, 'nombre' => 'Carlos Tomas Gomez Perez', 'iniciales' => 'CTP', 'documento' => 'C.C 1087637289', 'categoria' => 'Sub-20', 'entregados' => 2, 'estado' => 'incompleto'],
    ['id' => 3, 'nombre' => 'Carlos Tomas Gomez Perez', 'iniciales' => 'CTP', 'documento' => 'C.C 1087637289', 'categoria' => 'Sub-20', 'entregados' => 4, 'estado' => 'verificar'],
    ['id' => 4, 'nombre' => 'Carlos Tomas Gomez Perez', 'iniciales' => 'CTP', 'documento' => 'C.C 1087637289', 'categoria' => 'Sub-20', 'entregados' => 4, 'estado' => 'completo'],
];

// Texto que se muestra debajo de la barra segun el estado
$textoEstado = [
    'incompleto' => 'Incompleto',
    'verificar'  => 'Por verificar',
    'completo'   => 'Completo',
];

$totalDocumentos = 4;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="/streepsoft/public/css/documentos/documentos.css">
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
                    <strong>6</strong>
                    <span>Total de alumnos</span>
                    <small>esta temporada</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-verde">
                    <i class="fi fi-rr-check"></i>
                </span>
                <div class="doc-card-texto">
                    <strong>1</strong>
                    <span>Documentación completa</span>
                    <small>4 de 4 verificados</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-rojo">
                    <i class="fi fi-rr-triangle-warning"></i>
                </span>
                <div class="doc-card-texto">
                    <strong>4</strong>
                    <span>Con documentos pendientes</span>
                    <small>falta al menos uno</small>
                </div>
            </article>

            <article class="doc-card">
                <span class="doc-card-icono icono-dorado">
                    <i class="fi fi-rr-memo-circle-check"></i>
                </span>
                <div class="doc-card-texto">
                    <strong>2</strong>
                    <span>Por verificar</span>
                    <small>archivos subidos sin revisar</small>
                </div>
            </article>

        </section>

        <!--  BARRA DE FILTROS  -->
        <section class="doc-filtros">

            <label class="doc-buscador">
                <i class="fi fi-rr-search"></i>
                <input type="text" placeholder="Buscar por nombre o identificacion">
            </label>

            <label class="doc-select">
                <i class="fi fi-rr-user"></i>
                <select>
                    <option>Todas las categorias</option>
                    <option>Sub-12</option>
                    <option>Sub-14</option>
                    <option>Sub-20</option>
                </select>
            </label>

            <label class="doc-select">
                <i class="fi fi-rr-document"></i>
                <select>
                    <option>Toda la documentacion</option>
                    <option>Completo</option>
                    <option>Incompleto</option>
                    <option>Por verificar</option>
                </select>
            </label>

            <button type="button" class="doc-btn-filtro" title="Filtrar">
                <i class="fi fi-rr-filter"></i>
            </button>

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
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td>
                                <div class="doc-alumno">
                                    <span class="doc-avatar"><?= htmlspecialchars(mb_substr($alumno['iniciales'] ?? '', 0, 3)) ?></span>
                                    <div class="doc-alumno-info">
                                        <p class="doc-alumno-nombre"><?= htmlspecialchars($alumno['nombre']) ?></p>
                                        <small class="doc-alumno-doc"><?= htmlspecialchars($alumno['documento']) ?></small>
                                    </div>
                                </div>
                            </td>

                            <td class="doc-categoria"><?= htmlspecialchars($alumno['categoria']) ?></td>

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

            <!-- Paginacion simulada -->
            <div class="doc-paginacion">
                <span>Mostrando 1 - 125 de alumnos</span>

                <nav class="doc-paginas">
                    <a href="#">Anterior</a>
                    <a href="#" class="activa">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#">Despues</a>
                </nav>

                <label class="doc-registros">
                    Mostrar
                    <select>
                        <option>5</option>
                        <option>10</option>
                        <option>25</option>
                    </select>
                    registros
                </label>
            </div>

        </section>

    </div>

    <script type="module" src="/streepsoft/public/js/nav/export.js"></script>
    <script src="/streepsoft/public/js/documentos/documentos.js"></script>
</body>

</html>

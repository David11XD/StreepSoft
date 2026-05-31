<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Alumnos</title>
    <link rel="stylesheet" href="css/gestion.css" />
</head>

<body>
    <main class="app-shell">
        <section class="app-header">
            <div>
                <p class="eyebrow">Gestión De Alumnos</p>
            </div>
            <button id="new-student-button" class="button button-primary">+ Nuevo alumno</button>
        </section>

        <section class="panel-card">
            <div class="panel-top">
                <div class="tab-list">
                    <button class="tab active" data-view="registros">Registros</button>
                    <button class="tab" data-view="deudas">Deudas alumnos</button>
                </div>
                <div class="search-wrapper">
                    <input type="search" placeholder="Buscar alumno" />
                </div>
            </div>

            <div class="panel-body">
                <div class="panel-view active" data-view="registros">
                    <div class="controls-row controls-row--compact">
                        <div class="dropdown-wrapper">
                            <select id="categoria">
                                <option value="">Todos</option>
                                <option>2000</option>
                                <option>2001</option>
                                <option>2002</option>
                                <option>2003</option>
                                <option>2004</option>
                                <option>2005</option>
                                <option>2006</option>
                                <option>2007</option>
                                <option>2008</option>
                                <option>2009</option>
                                <option>2010</option>
                                <option>2011</option>
                                <option>2012</option>
                                <option>2013</option>
                                <option>2014</option>
                                <option>2015</option>
                                <option>2016</option>
                                <option>2017</option>
                                <option>2018</option>
                                <option>2019</option>
                            </select>
                        </div>
                        <div class="dropdown-wrapper">
                            <select id="sort-select">
                                <option value="">Ordenar</option>
                                <option value="name-asc">A-Z</option>
                                <option value="name-desc">Z-A</option>
                                <option value="date-asc">Inicio ↑</option>
                                <option value="date-desc">Inicio ↓</option>
                            </select>
                        </div>
                        <div id="category-badge" class="category-badge">Año</div>
                    </div>

                    <div class="table-scroll">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Primer Apellido</th>
                                    <th>Segundo Apel</th>
                                    <th>Nombres</th>
                                    <th>Fecha Nac</th>
                                    <th>Edad</th>
                                    <th>Acudiente</th>
                                    <th>Numero Acud</th>
                                    <th>Estado</th>
                                    <th>Fecha límite</th>
                                    <th>Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="registros-tbody">
                                <tr>
                                    <td>Mora</td>
                                    <td>Castillo</td>
                                    <td>Juan Luis</td>
                                    <td>12/07/2014</td>
                                    <td>11</td>
                                    <td>Pablo Mora</td>
                                    <td>+57 300001128</td>
                                    <td><span class="status status-active">Activo</span></td>
                                    <td>12/04/26</td>
                                    <td><span class="pill pill-paid">Pago</span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                                <tr>
                                    <td>Blanco</td>
                                    <td>n/a</td>
                                    <td>Lucas</td>
                                    <td>12/07/2015</td>
                                    <td>12</td>
                                    <td>Maria Blanco</td>
                                    <td>+57 300001128</td>
                                    <td><span class="status status-inactive">Inactivo</span></td>
                                    <td>02/03/26</td>
                                    <td><span class="pill pill-late">Mora</span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                                <tr>
                                    <td>Rodriguez</td>
                                    <td>Castillo</td>
                                    <td>Juan Luis</td>
                                    <td>12/07/2014</td>
                                    <td>11</td>
                                    <td>Pablo Mora</td>
                                    <td>+57 300001128</td>
                                    <td><span class="status status-active">Activo</span></td>
                                    <td>12/04/26</td>
                                    <td><span class="pill pill-paid">Pago</span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                                <tr>
                                    <td>Blanco</td>
                                    <td>n/a</td>
                                    <td>Lucas</td>
                                    <td>12/07/2015</td>
                                    <td>12</td>
                                    <td>Maria Blanco</td>
                                    <td>+57 300001128</td>
                                    <td><span class="status status-inactive">Inactivo</span></td>
                                    <td>02/03/26</td>
                                    <td><span class="pill pill-late">Mora</span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="pagination">
                            <span class="page active">1</span>
                            <span class="page">2</span>
                            <span class="page">3</span>
                            <span class="page">4</span>
                            <span class="page">5</span>
                            <span class="page ellipsis">6..</span>
                        </div>
                    </div>
                </div>

                <div class="panel-view" data-view="deudas">
                    <div class="controls-row controls-row--compact">
                        <div class="dropdown-wrapper">
                            <select id="categoria-deudas">
                                <option value="">Todos</option>
                                <option>2023</option>
                                <option>2024</option>
                                <option>2025</option>
                                <option>2026</option>
                            </select>
                        </div>
                        <div class="dropdown-wrapper">
                            <select id="sort-select-deudas">
                                <option value="">Ordenar</option>
                                <option value="name-asc">A-Z</option>
                                <option value="name-desc">Z-A</option>
                                <option value="date-asc">Matrícula ↑</option>
                                <option value="date-desc">Matrícula ↓</option>
                            </select>
                        </div>
                        <div id="category-badge-deudas" class="category-badge">Año</div>
                    </div>

                    <div class="table-scroll">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Nombres y apellidos</th>
                                    <th>Matrícula<br><span class="subtext">Fecha matrícula</span></th>
                                    <th>Ene<br><span class="subtext">Fecha pago</span></th>
                                    <th>Feb<br><span class="subtext">Fecha pago</span></th>
                                    <th>Mar<br><span class="subtext">Fecha pago</span></th>
                                    <th>Abr<br><span class="subtext">Fecha pago</span></th>
                                    <th>May<br><span class="subtext">Fecha pago</span></th>
                                    <th>Jun<br><span class="subtext">Fecha pago</span></th>
                                    <th>Jul<br><span class="subtext">Fecha pago</span></th>
                                    <th>Ago<br><span class="subtext">Fecha pago</span></th>
                                    <th>Sep<br><span class="subtext">Fecha pago</span></th>
                                    <th>Oct<br><span class="subtext">Fecha pago</span></th>
                                    <th>Nov<br><span class="subtext">Fecha pago</span></th>
                                    <th>Dic<br><span class="subtext">Fecha pago</span></th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="deudas-tbody">
                                <tr>
                                    <td>Juan Luis Mora Castillo</td>
                                    <td>$90.000 cop<br><span class="subtext">20/04/2023</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/01/2026</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/02/2026</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/02/2026</span></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><span class="pill pill-paid">$255.000 cop<br><span class="subtext">al día</span></span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                                <tr>
                                    <td>Lucas Blanco</td>
                                    <td>$90.000 cop<br><span class="subtext">20/04/2023</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/01/2026</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/02/2026</span></td>
                                    <td>$80.000 cop<br><span class="subtext">20/02/2026</span></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><span class="pill pill-late">$166.000 cop<br><span class="subtext">debe</span></span></td>
                                    <td><button type="button" class="button button-secondary button-small delete-row-button">Eliminar</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal-overlay hidden" id="student-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h2>Agregar nuevo alumno</h2>
                <button id="close-modal" class="icon-button">×</button>
            </div>
            <form id="student-form" class="student-form">
                <div class="form-grid">
                    <label>
                        Primer Apellido
                        <input name="apellido1" required />
                    </label>
                    <label>
                        Segundo Apellido
                        <input name="apellido2" />
                    </label>
                    <label>
                        Nombres
                        <input name="nombres" required />
                    </label>
                    <label>
                        Fecha Nacimiento
                        <input name="fechaNac" type="date" required />
                    </label>
                    <label>
                        Edad
                        <input name="edad" type="number" min="1" required />
                    </label>
                    <label>
                        Acudiente
                        <input name="acudiente" required />
                    </label>
                    <label>
                        Número Acudiente
                        <input name="numeroAcud" required />
                    </label>
                    <label>
                        Estado
                        <select name="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </label>
                    <label>
                        Fecha límite
                        <input name="fechaLimite" type="date" required />
                    </label>
                    <label>
                        Pago
                        <select name="pago">
                            <option value="Pago">Pago</option>
                            <option value="Mora">Mora</option>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" id="cancel-button" class="button button-secondary">Cancelar</button>
                    <button type="submit" class="button button-primary">Agregar alumno</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/gestion.js"></script>
</body>

</html>
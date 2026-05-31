const openModal = document.getElementById('new-student-button');
const modal = document.getElementById('student-modal');
const closeModal = document.getElementById('close-modal');
const cancelButton = document.getElementById('cancel-button');
const studentForm = document.getElementById('student-form');
const registrosTableBody = document.getElementById('registros-tbody');
const deudasTableBody = document.getElementById('deudas-tbody');
const yearSelect = document.getElementById('categoria');
const categoryBadge = document.getElementById('category-badge');
const debtYearSelect = document.getElementById('categoria-deudas');
const debtCategoryBadge = document.getElementById('category-badge-deudas');
const debtSortSelect = document.getElementById('sort-select-deudas');
const sortSelect = document.getElementById('sort-select');
const tabs = document.querySelectorAll('.tab');
const panelViews = document.querySelectorAll('.panel-view');
const searchInput = document.querySelector('.search-wrapper input');
const paginationContainer = document.querySelector('.pagination');

const rowsPerPage = 5;
let currentPage = 1;

function toggleModal(show) {
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

function getActiveView() {
    const activePanel = Array.from(panelViews).find(panel => panel.classList.contains('active'));
    return activePanel ? activePanel.dataset.view : 'registros';
}

function getStudentRows() {
    return Array.from(registrosTableBody.querySelectorAll('tr'));
}

function getDebtRows() {
    return Array.from(deudasTableBody.querySelectorAll('tr'));
}

function getSelectedYear() {
    return yearSelect ? yearSelect.value : '';
}

function getSelectedDebtYear() {
    return debtYearSelect ? debtYearSelect.value : '';
}

function getSearchTerm() {
    return searchInput ? searchInput.value.trim().toLowerCase() : '';
}

function getRowBirthYear(row) {
    const fechaNacCell = row.querySelector('td:nth-child(4)');
    if (!fechaNacCell) return '';
    const value = fechaNacCell.textContent.trim();
    // Try to extract a 4-digit year from multiple possible date formats
    const yearMatch = value.match(/(\d{4})/);
    if (yearMatch) return yearMatch[1];
    const parts = value.split('/');
    return parts.length === 3 ? parts[2] : '';
}

function getDebtRowYear(row) {
    const matriculaCell = row.querySelector('td:nth-child(2)');
    if (!matriculaCell) return '';
    const value = matriculaCell.textContent.trim();
    const match = value.match(/(\d{4})/);
    return match ? match[1] : '';
}

function registrosRowMatchesFilters(row) {
    const selectedYear = getSelectedYear();
    const searchTerm = getSearchTerm();
    const birthYear = getRowBirthYear(row);

    const yearMatch = !selectedYear || birthYear === selectedYear;
    if (!yearMatch) {
        return false;
    }

    if (!searchTerm) {
        return true;
    }

    return row.textContent.toLowerCase().includes(searchTerm);
}

function deudasRowMatchesFilters(row) {
    const selectedYear = getSelectedDebtYear();
    const searchTerm = getSearchTerm();
    const year = getDebtRowYear(row);

    const yearMatch = !selectedYear || year === selectedYear;
    if (!yearMatch) {
        return false;
    }

    if (!searchTerm) {
        return true;
    }

    return row.textContent.toLowerCase().includes(searchTerm);
}

function rowMatchesFilters(row) {
    return getActiveView() === 'deudas' ? deudasRowMatchesFilters(row) : registrosRowMatchesFilters(row);
}

function parseDateFromString(value) {
    if (!value) return null;
    value = value.trim();
    // ISO format YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return new Date(value);
    }
    // dd/mm/yyyy or dd/mm/yy
    const parts = value.split('/');
    if (parts.length === 3) {
        let day = parseInt(parts[0], 10);
        let month = parseInt(parts[1], 10) - 1;
        let year = parseInt(parts[2], 10);
        if (year < 100) year += 2000;
        return new Date(year, month, day);
    }
    // try to find a 4-digit year and build a date roughly
    const match = value.match(/(\d{4})/);
    if (match) {
        const y = parseInt(match[1], 10);
        return new Date(y, 0, 1);
    }
    return null;
}

function sortStudentRows(mode) {
    const rows = getStudentRows();
    const sorted = rows.slice();

    const getNameKey = (row) => {
        const cols = row.querySelectorAll('td');
        const a = (cols[0] ? cols[0].textContent.trim() : '');
        const b = (cols[1] ? cols[1].textContent.trim() : '');
        const c = (cols[2] ? cols[2].textContent.trim() : '');
        return `${a} ${b} ${c}`.toLowerCase();
    };

    const getFechaInicio = (row) => {
        const cols = row.querySelectorAll('td');
        // Fecha límite is column 9 (index 8)
    if (debtSortSelect) {
        debtSortSelect.addEventListener('change', handleDebtSortChange);
    }
        const val = cols[8] ? cols[8].textContent.trim() : '';
        return parseDateFromString(val) || new Date(0);
    };

    if (mode === 'name-asc') {
        sorted.sort((a, b) => getNameKey(a).localeCompare(getNameKey(b)));
    } else if (mode === 'name-desc') {
        sorted.sort((a, b) => getNameKey(b).localeCompare(getNameKey(a)));
    } else if (mode === 'date-asc') {
        sorted.sort((a, b) => getFechaInicio(a) - getFechaInicio(b));
    } else if (mode === 'date-desc') {
        sorted.sort((a, b) => getFechaInicio(b) - getFechaInicio(a));
    }

    // Re-append rows in sorted order
    sorted.forEach(r => registrosTableBody.appendChild(r));
    // Refresh visible page
    showPage(1);
}

function handleSortChange() {
    if (!sortSelect) return;
    const mode = sortSelect.value;
    if (!mode) return;
    sortStudentRows(mode);
}

function sortDebtRows(mode) {
    const rows = getDebtRows();
    const sorted = rows.slice();

    const getNameKey = (row) => {
        const cols = row.querySelectorAll('td');
        return (cols[0] ? cols[0].textContent.trim().toLowerCase() : '');
    };

    const getMatriculaDate = (row) => {
        const cols = row.querySelectorAll('td');
        const val = cols[1] ? cols[1].textContent.trim() : '';
        return parseDateFromString(val) || new Date(0);
    };

    if (mode === 'name-asc') {
        sorted.sort((a, b) => getNameKey(a).localeCompare(getNameKey(b)));
    } else if (mode === 'name-desc') {
        sorted.sort((a, b) => getNameKey(b).localeCompare(getNameKey(a)));
    } else if (mode === 'date-asc') {
        sorted.sort((a, b) => getMatriculaDate(a) - getMatriculaDate(b));
    } else if (mode === 'date-desc') {
        sorted.sort((a, b) => getMatriculaDate(b) - getMatriculaDate(a));
    }

    sorted.forEach(r => deudasTableBody.appendChild(r));
    showDebtRows();
}

function handleDebtSortChange() {
    if (!debtSortSelect) return;
    const mode = debtSortSelect.value;
    if (!mode) return;
    sortDebtRows(mode);
}

function renderPagination(totalPages) {
    paginationContainer.innerHTML = '';

    for (let page = 1; page <= totalPages; page++) {
        const pageButton = document.createElement('span');
        pageButton.className = `page${page === currentPage ? ' active' : ''}`;
        pageButton.textContent = page;
        pageButton.dataset.page = String(page);
        paginationContainer.appendChild(pageButton);
    }
}

function showPage(pageNumber) {
    if (getActiveView() !== 'registros') {
        return;
    }

    const rows = getStudentRows();
    const filteredRows = rows.filter(rowMatchesFilters);
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));
    currentPage = Math.min(Math.max(pageNumber, 1), totalPages);

    const start = (currentPage - 1) * rowsPerPage;
    const end = currentPage * rowsPerPage;

    rows.forEach(row => {
        const visible = filteredRows.includes(row) && filteredRows.indexOf(row) >= start && filteredRows.indexOf(row) < end;
        row.style.display = visible ? '' : 'none';
    });

    renderPagination(totalPages);
}

function handlePaginationClick(event) {
    const target = event.target;
    if (target.classList.contains('page') && target.dataset.page) {
        showPage(Number(target.dataset.page));
    }
}

function switchPanel(viewName) {
    tabs.forEach(tab => {
        const isActive = tab.dataset.view === viewName;
        tab.classList.toggle('active', isActive);
    });

    panelViews.forEach(panel => {
        panel.classList.toggle('active', panel.dataset.view === viewName);
    });

    if (viewName === 'registros') {
        showPage(1);
    } else if (viewName === 'deudas') {
        if (debtSortSelect && debtSortSelect.value) {
            sortDebtRows(debtSortSelect.value);
        }
        showDebtRows();
    }
}

function getTabViewName(tab) {
    return tab.dataset.view || tab.textContent.trim().toLowerCase();
}

function isRegistrosActive() {
    const currentView = Array.from(panelViews).find(panel => panel.classList.contains('active'));
    return currentView && currentView.dataset.view === 'registros';
}

function handleFilterChange() {
    if (getActiveView() === 'registros' && yearSelect && categoryBadge) {
        categoryBadge.textContent = yearSelect.value || 'Año';
        showPage(1);
    }

    if (getActiveView() === 'deudas' && debtYearSelect && debtCategoryBadge) {
        debtCategoryBadge.textContent = debtYearSelect.value || 'Año';
        showDebtRows();
    }
}

function showDebtRows() {
    const rows = getDebtRows();
    rows.forEach(row => {
        row.style.display = deudasRowMatchesFilters(row) ? '' : 'none';
    });
}

function createDeleteButtonCell() {
    const cell = document.createElement('td');
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button button-secondary button-small delete-row-button';
    button.textContent = 'Eliminar';
    cell.appendChild(button);
    return cell;
}

function addDebtRow(studentId, nombreCompleto, fechaMatricula) {
    const debtRow = document.createElement('tr');
    debtRow.dataset.studentId = studentId;
    debtRow.innerHTML = `
        <td>${nombreCompleto}</td>
        <td>$0 cop<br><span class="subtext">${fechaMatricula}</span></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><span class="pill pill-late">$0 cop<br><span class="subtext">debe</span></span></td>
    `;
    debtRow.appendChild(createDeleteButtonCell());
    deudasTableBody.appendChild(debtRow);
    if (debtSortSelect && debtSortSelect.value) {
        sortDebtRows(debtSortSelect.value);
    }
}

function removeStudentById(studentId) {
    if (!studentId) {
        return;
    }

    const registroRow = registrosTableBody.querySelector(`tr[data-student-id="${studentId}"]`);
    const deudaRow = deudasTableBody.querySelector(`tr[data-student-id="${studentId}"]`);

    if (registroRow) {
        registroRow.remove();
    }
    if (deudaRow) {
        deudaRow.remove();
    }
}

openModal.addEventListener('click', () => {
    studentForm.reset();
    toggleModal(true);
});

if (yearSelect && categoryBadge) {
    categoryBadge.textContent = yearSelect.value || 'Año';
    yearSelect.addEventListener('change', handleFilterChange);
}

if (debtYearSelect && debtCategoryBadge) {
    debtCategoryBadge.textContent = debtYearSelect.value || 'Año';
    debtYearSelect.addEventListener('change', handleFilterChange);
}

if (searchInput) {
    searchInput.addEventListener('input', handleFilterChange);
}

if (sortSelect) {
    sortSelect.addEventListener('change', handleSortChange);
}

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        switchPanel(getTabViewName(tab));
    });
});

closeModal.addEventListener('click', () => toggleModal(false));
cancelButton.addEventListener('click', () => toggleModal(false));

paginationContainer.addEventListener('click', handlePaginationClick);

document.addEventListener('click', (event) => {
    const button = event.target.closest('.delete-row-button');
    if (!button) {
        return;
    }

    const row = button.closest('tr');
    if (!row) {
        return;
    }

    const studentId = row.dataset.studentId;
    if (studentId) {
        removeStudentById(studentId);
    } else {
        row.remove();
    }

    if (getActiveView() === 'registros') {
        showPage(currentPage);
    } else {
        showDebtRows();
    }
});

studentForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const formData = new FormData(studentForm);
    const estado = formData.get('estado');
    const pago = formData.get('pago');
    const studentId = `student-${Date.now()}`;
    const nombreCompleto = `${formData.get('nombres')} ${formData.get('apellido1')} ${formData.get('apellido2') || ''}`.trim();

    const newRow = document.createElement('tr');
    newRow.dataset.studentId = studentId;
    newRow.innerHTML = `
    <td>${formData.get('apellido1')}</td>
    <td>${formData.get('apellido2') || 'n/a'}</td>
    <td>${formData.get('nombres')}</td>
    <td>${formData.get('fechaNac')}</td>
    <td>${formData.get('edad')}</td>
    <td>${formData.get('acudiente')}</td>
    <td>${formData.get('numeroAcud')}</td>
    <td><span class="status ${estado === 'Activo' ? 'status-active' : 'status-inactive'}">${estado}</span></td>
    <td>${formData.get('fechaLimite')}</td>
    <td><span class="pill ${pago === 'Pago' ? 'pill-paid' : 'pill-late'}">${pago}</span></td>
  `;
    newRow.appendChild(createDeleteButtonCell());
    registrosTableBody.appendChild(newRow);

    if (pago === 'Mora') {
        addDebtRow(studentId, nombreCompleto, formData.get('fechaLimite'));
    }

    if (getActiveView() === 'registros') {
        showPage(Math.ceil(getStudentRows().filter(rowMatchesFilters).length / rowsPerPage));
    } else {
        showDebtRows();
    }

    toggleModal(false);
});

showPage(1);

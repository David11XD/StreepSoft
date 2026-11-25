/**
 * Notificaciones de Streepsoft.
 *
 * Es un modal que se abre con la campana de la barra superior y muestra los
 * avisos del centro de notificaciones. Lo arranca export.js cuando ya cargó la
 * navegación (initStreepNotifications).
 */
window.initStreepNotifications = function initStreepNotifications() {
    if (window.__streepNotifInitialized) return;
    window.__streepNotifInitialized = true;

    const BASE = '/streepsoft/notificaciones';
    const REFRESCO_MS = 2 * 60 * 1000;

    const PRIORIDADES = { alta: 'Alta', media: 'Media', baja: 'No importante' };
    const ESTADOS = { nueva: 'Nuevo', revisada: 'Revisado', resuelta: 'Resuelto' };
    const TIPOS = {
        mora: 'Mora',
        proximo_vencer: 'Por vencer',
        documento_pendiente: 'Doc. pendiente',
        documento_rechazado: 'Doc. rechazado',
        pago_registrado: 'Pago',
        inactivo: 'Inactivo'
    };

    const drawer = document.getElementById('ntf-drawer');
    const backdrop = document.getElementById('ntf-drawer-backdrop');
    const bell = document.getElementById('ntf-bell');
    const list = document.getElementById('ntf-drawer-list');
    const bellCount = document.getElementById('ntf-bell-count');
    const configModal = document.getElementById('modal-config');
    const configForm = document.getElementById('form-config');
    const statButtons = document.querySelectorAll('.ntf-drawer-stats [data-filtro]');

    // Estado en memoria: último listado recibido, filtro activo y configuración.
    let items = [];
    let filtro = null; // null | 'alta' | 'seguimiento'
    let configuracion = null;

    /* ---------- utilidades ---------- */

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);

    const pad = (n) => String(n).padStart(2, '0');

    // "2026-11-11 15:57:00" -> "11/11/2026 15:57" (sin hora si el evento solo tiene fecha)
    const formatDate = (value) => {
        if (!value) return '';
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '';
        const day = `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
        const hasTime = d.getHours() !== 0 || d.getMinutes() !== 0;
        return hasTime ? `${day} ${pad(d.getHours())}:${pad(d.getMinutes())}` : day;
    };

    const token = () => window.NOTIFICACIONES_CONFIG?.token || drawer?.dataset.token || '';

    const setToken = (value) => {
        if (!value) return;
        window.NOTIFICACIONES_CONFIG = window.NOTIFICACIONES_CONFIG || {};
        window.NOTIFICACIONES_CONFIG.token = value;
    };

    async function postJson(url, params) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(params)
        });
        let data = {};
        try { data = await response.json(); } catch (_) { /* respuesta sin JSON */ }
        if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No se pudo completar la acción.');
        return data;
    }

    /* ---------- contadores: número de la campana y cajas del modal ---------- */

    const updateCounters = (resumen = {}) => {
        const alta = Number(resumen.alta || 0);
        const seguimiento = Number(resumen.seguimiento ?? (Number(resumen.media || 0) + Number(resumen.baja || 0)));
        const total = alta + seguimiento;

        // La campana muestra cuántos avisos siguen pendientes (nuevos + revisados sin resolver).
        if (bellCount) {
            bellCount.textContent = total > 99 ? '99+' : String(total);
            bellCount.hidden = total === 0;
        }
        if (bell) {
            bell.setAttribute('aria-label', total > 0
                ? `Abrir notificaciones (${total} pendientes)`
                : 'Abrir notificaciones');
        }

        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = String(value);
        };
        set('ntf-drawer-alta', alta);
        set('ntf-drawer-seguimiento', seguimiento);
    };

    /* ---------- tarjetas de aviso ---------- */

    const cardHtml = (item) => {
        const prioridad = PRIORIDADES[item.prioridad] ? item.prioridad : 'media';
        const jugador = String(item.jugador || '').trim();
        const titulo = `${item.titulo || ''}${jugador ? ` · ${jugador}` : ''}`;
        const date = formatDate(item.fecha_evento);

        const link = item.url ? `<a class="ntf-link" href="${escapeHtml(item.url)}">Ver detalle</a>` : '';
        const reviewButton = item.estado === 'nueva'
            ? '<button type="button" class="ntf-btn" data-estado="revisada">Marcar revisada</button>'
            : '';

        return `
            <article class="ntf-card es-${prioridad}" data-id="${Number(item.id)}">
                <div class="ntf-card-top">
                    <div class="ntf-tags">
                        <span class="ntf-tag tag-prioridad">${escapeHtml(PRIORIDADES[prioridad])}</span>
                        <span class="ntf-tag">${escapeHtml(TIPOS[item.tipo] || item.tipo)}</span>
                        <span class="ntf-tag tag-estado">${escapeHtml(ESTADOS[item.estado] || item.estado)}</span>
                    </div>
                    ${link}
                </div>
                <h3 class="ntf-card-title">${escapeHtml(titulo)}</h3>
                <p class="ntf-card-text">${escapeHtml(item.mensaje)}</p>
                <div class="ntf-card-bottom">
                    <small class="ntf-date">${escapeHtml(date)}</small>
                    <div class="ntf-actions">
                        ${reviewButton}
                        <button type="button" class="ntf-btn ntf-btn-main" data-estado="resuelta">Resolver</button>
                    </div>
                </div>
            </article>`;
    };

    // "Seguimiento" agrupa las prioridades media y baja (igual que la caja del encabezado).
    const coincideFiltro = (item) => {
        if (!filtro) return true;
        return filtro === 'alta' ? item.prioridad === 'alta' : item.prioridad !== 'alta';
    };

    const renderNotifications = () => {
        if (!list) return;

        statButtons.forEach((button) => {
            const activo = button.dataset.filtro === filtro;
            button.classList.toggle('activo', activo);
            button.setAttribute('aria-pressed', activo ? 'true' : 'false');
        });

        const visibles = items.filter(coincideFiltro);

        if (visibles.length) {
            list.innerHTML = visibles.map(cardHtml).join('');
        } else {
            list.innerHTML = filtro
                ? '<div class="ntf-drawer-empty"><strong>Sin avisos</strong>No hay avisos en esta categoría.</div>'
                : '<div class="ntf-drawer-empty"><strong>¡Todo al día!</strong>No tienes avisos pendientes.</div>';
        }
    };

    statButtons.forEach((button) => {
        button.addEventListener('click', () => {
            filtro = filtro === button.dataset.filtro ? null : button.dataset.filtro;
            renderNotifications();
        });
    });

    /* ---------- carga de datos ---------- */

    async function loadNotifications({ silent = false } = {}) {
        if (!silent && list) list.innerHTML = '<p class="ntf-drawer-loading">Cargando notificaciones...</p>';

        try {
            const response = await fetch(`${BASE}/panel`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No se pudieron cargar los avisos.');

            setToken(data.token);
            items = data.notificaciones || [];
            configuracion = data.configuracion || configuracion;
            updateCounters(data.resumen || {});

            // Si el modal está cerrado solo se actualiza el número de la campana.
            if (!silent || drawer?.classList.contains('abierto')) renderNotifications();
        } catch (error) {
            if (!silent && list) list.innerHTML = `<p class="ntf-drawer-loading">${escapeHtml(error.message)}</p>`;
        }
    }

    /* ---------- abrir y cerrar el modal ---------- */

    const closeDrawer = () => {
        drawer?.classList.remove('abierto');
        backdrop?.classList.remove('abierto');
        drawer?.setAttribute('aria-hidden', 'true');
        bell?.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('ntf-drawer-open');
    };

    const openDrawer = async () => {
        if (!drawer || !backdrop) return;
        filtro = null;
        drawer.classList.add('abierto');
        backdrop.classList.add('abierto');
        drawer.setAttribute('aria-hidden', 'false');
        bell?.setAttribute('aria-expanded', 'true');
        document.body.classList.add('ntf-drawer-open');
        await loadNotifications();
    };

    bell?.addEventListener('click', () => (drawer?.classList.contains('abierto') ? closeDrawer() : openDrawer()));

    // El fondo cubre toda la pantalla: tocar en cualquier parte fuera del modal lo cierra.
    backdrop?.addEventListener('click', closeDrawer);

    /* ---------- marcar revisada / resolver ---------- */

    list?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-estado]');
        if (!button) return;

        const card = button.closest('.ntf-card');
        if (!card) return;

        button.disabled = true;
        try {
            const data = await postJson(`${BASE}/estado`, {
                _token: token(),
                id: card.dataset.id,
                estado: button.dataset.estado
            });
            updateCounters(data.resumen || {});
            await loadNotifications({ silent: true });
        } catch (error) {
            button.disabled = false;
            alert(error.message);
        }
    });

    /* ---------- configuración (engranaje del modal) ---------- */

    const fillConfigForm = () => {
        if (!configForm || !configuracion) return;
        configForm.querySelectorAll('input[name]').forEach((input) => {
            const valor = configuracion[input.name];
            if (valor === undefined) return;
            if (input.type === 'checkbox') input.checked = String(valor) === '1';
            else input.value = valor;
        });
    };

    const openConfig = async () => {
        if (!configuracion) await loadNotifications({ silent: true });
        fillConfigForm();
        const feedback = document.getElementById('config-feedback');
        if (feedback) feedback.textContent = '';
        configModal?.classList.add('abierto');
        configModal?.setAttribute('aria-hidden', 'false');
    };

    const closeConfig = () => {
        configModal?.classList.remove('abierto');
        configModal?.setAttribute('aria-hidden', 'true');
    };

    document.getElementById('abrir-config')?.addEventListener('click', openConfig);
    document.getElementById('cerrar-config')?.addEventListener('click', closeConfig);
    configModal?.addEventListener('click', (event) => {
        if (event.target === configModal) closeConfig();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        // Escape cierra primero la configuración y, si no está abierta, el modal.
        if (configModal?.classList.contains('abierto')) closeConfig();
        else closeDrawer();
    });

    configForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const feedback = document.getElementById('config-feedback');
        const data = new FormData(configForm);
        data.set('_token', token());

        // Un checkbox sin marcar no se envía: se manda explícitamente en 0.
        configForm.querySelectorAll('input[type="checkbox"]').forEach((box) => {
            if (!data.has(box.name)) data.set(box.name, '0');
        });

        const submit = configForm.querySelector('.ntf-save');
        if (submit) submit.disabled = true;

        try {
            const result = await postJson(`${BASE}/configuracion`, data);
            if (feedback) feedback.textContent = result.mensaje || 'Configuración guardada.';
            configuracion = null; // se vuelve a leer del servidor
            await loadNotifications({ silent: true });
            setTimeout(closeConfig, 700);
        } catch (error) {
            if (feedback) feedback.textContent = error.message;
        } finally {
            if (submit) submit.disabled = false;
        }
    });

    /* ---------- número de la campana siempre al día ---------- */

    if (bell) {
        loadNotifications({ silent: true });
        setInterval(() => {
            if (!document.hidden) loadNotifications({ silent: true });
        }, REFRESCO_MS);
        // Al volver a la pestaña se actualiza de inmediato.
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) loadNotifications({ silent: true });
        });
    }
};

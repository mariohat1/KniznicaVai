document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#reservation-search-form');
    if (!form) return;

    const listSelector = '#reservation-list';
    const pageInput = form.querySelector('#reservation-page');
    const searchInput = form.querySelector('#reservation-search-input');

    // ====== helper: nastav vizuálne aktívny status ======
    function updateStatusButtons(status) {
        const buttons = form.querySelectorAll('[data-status]');
        buttons.forEach(btn => {
            const isActive = btn.dataset.status === (status || 'all');
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }
    async function load() {
        const params = new URLSearchParams(new FormData(form));

        let urlObj = new URL(form.action, window.location.href);

        urlObj.search = params.toString();
        const url = urlObj.toString();

        let res;
        try {
            res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
        } catch (err) {
            console.error('Fetch failed', err);
            return;
        }

        if (!res.ok) {
            console.error('Server returned', res.status);
            return;
        }

        const html = await res.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');

        // ====== nahradenie zoznamu ======
        const newList = doc.querySelector(listSelector);
        const existingList = document.querySelector(listSelector);
        if (newList && existingList) {
            existingList.outerHTML = newList.outerHTML;
        } else if (newList && !existingList) {
            document.body.insertAdjacentHTML('afterbegin', newList.outerHTML);
        }

        // ====== nahradenie stránkovania ======
        const newPagination = doc.querySelector('.pagination');
        const oldPagination = document.querySelector('.pagination');
        if (newPagination && oldPagination) oldPagination.outerHTML = newPagination.outerHTML;
        else if (newPagination && !oldPagination) {
            const listEl = document.querySelector(listSelector);
            if (listEl) listEl.insertAdjacentElement('afterend', newPagination.cloneNode(true));
        } else if (!newPagination && oldPagination) oldPagination.remove();

        // ====== nastav aktívny filter po načítaní ======
        const currentStatus = (new URLSearchParams(urlObj.search)).get('status') || form.querySelector('[name="status"]')?.value || 'all';
        updateStatusButtons(currentStatus);
        history.replaceState(null, '', url);
    }

    // ====== eventy ======
    searchInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (pageInput) pageInput.value = 1;
            load();
        }
    });

    form.querySelector('#reservation-search-button')?.addEventListener('click', () => {
        if (pageInput) pageInput.value = 1;
        load();
    });

    form.addEventListener('click', e => {
        const target = e.target.closest('[data-status]');
        if (!target) return;
        e.preventDefault();
        const statusInput = form.querySelector('[name="status"]');
        if (statusInput) statusInput.value = target.dataset.status;
        if (pageInput) pageInput.value = 1;
        updateStatusButtons(target.dataset.status);
        load();
    });

    // ===== handle reservation action buttons (cancel/restore) =====
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.reservation-action');
        if (!btn) return;
        e.preventDefault();

        const id = btn.dataset.id;
        const action = btn.dataset.action;
        if (!id || !action) return;

        const updateUrl = form.getAttribute('data-update-url') || form.dataset.updateUrl;
        if (!updateUrl) {
            console.error('No update URL configured on form');
            return;
        }

        const prevDisabled = btn.disabled;
        btn.disabled = true;

        try {
            const payload = new URLSearchParams({ id: String(id), action: String(action) });
            const res = await fetch(updateUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload.toString(),
                credentials: 'same-origin'
            });


            // expect JSON { success: true }
            let json = null;
            try { json = await res.json(); } catch (e) { /* ignore */ }

            if (json && json.success) {
                // refresh list via AJAX to reflect new state
                if (typeof load === 'function') {
                    // keep current page
                    await load();
                } else {
                    location.reload();
                }
            } else {
                // fallback: full reload
                location.reload();
            }
        } catch (err) {
            console.error('Error updating reservation', err);
        } finally {
            btn.disabled = prevDisabled;
        }
    });

    document.addEventListener('click', e => {
        const a = e.target.closest('.pagination a[data-page]');
        if (!a) return;
        e.preventDefault();
        if (pageInput) pageInput.value = a.dataset.page;
        load();
    });


});

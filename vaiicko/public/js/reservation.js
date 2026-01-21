document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#reservation-search-form');
    if (!form) return;

    const listSelector = '#reservation-list';
    const pageInput = form.querySelector('[name="page"]');
    const searchBtn = form.querySelector('#reservation-search-button');
    if (searchBtn) {
        searchBtn.addEventListener('click', e => {
            e.preventDefault();
            if (pageInput) pageInput.value = 1;
            load();
        });
    }

    async function load() {
        const params = new URLSearchParams(new FormData(form));
        const urlObj = new URL(form.action);
        urlObj.search = params.toString();
        const url = urlObj.toString();
        try {
            const res = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newList = doc.querySelector(listSelector);
            const oldList = document.querySelector(listSelector);
            if (newList && oldList) {
                oldList.replaceWith(newList);
            }
            const oldNav = document.querySelector('nav[aria-label="pagination"]');
            if (oldNav) {
                oldNav.remove();
            }
            const newNav = doc.querySelector('nav[aria-label="pagination"]');
            if (newNav && document.querySelector(listSelector)) {
                document.querySelector(listSelector).after(newNav);
            }
            const status = params.get('status') || 'all';
            document.querySelectorAll('[data-status]').forEach(btn =>
                btn.classList.toggle('active', btn.dataset.status === status)
            );
            history.replaceState(null, '', url);
            const resultingList = document.querySelector(listSelector);
            if (resultingList) {
                const items = resultingList.querySelectorAll('.list-group-item');
                return { hasItems: items.length > 0 };
            }
            return { hasItems: false };
        } catch (err) {
            return { hasItems: false };
        }
    }

    document.addEventListener('click', async e => {
        const statusBtn = e.target.closest('[data-status]');
        if (statusBtn) {
            e.preventDefault();
            const statusVal = statusBtn.dataset.status;
            const statusInput = form.querySelector('[name="status"]');
            if (statusInput) statusInput.value = statusVal;
            if (pageInput) pageInput.value = 1;
            load();
            return;
        }

        const pageBtn = e.target.closest('[data-page]');
        if (pageBtn && pageBtn.classList.contains('page-link')) {
            e.preventDefault();
            const pageVal = pageBtn.dataset.page;
            if (pageVal && pageVal.trim()) {
                if (pageInput) pageInput.value = pageVal;
                load();
            }
            return;
        }

        const actionBtn = e.target.closest('.reservation-action');
        if (actionBtn) {
            e.preventDefault();
            const updateUrl = form.dataset.updateUrl;

            try {
                const res = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id: actionBtn.dataset.id,
                        action: actionBtn.dataset.action
                    }),
                    credentials: 'same-origin'
                });

                try {
                    await res.json();
                } catch (e) {
                }

            } catch (err) {
            }

            await load();
        }
    });

});

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
        const urlObj = new URL(form.action, window.location.href);
        urlObj.search = params.toString();
        const url = urlObj.toString();

        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const json = await res.json();
            let listHtml = '<div class="list-group" id="reservation-list">';
            if (json.items && json.items.length) {
                json.items.forEach(it => {
                    const title = it.book?.title || 'Neznáma kniha';
                    const username = it.user?.username || it.user?.id || '—';
                    const expDate = it.expDate ? `Expiruje: ${it.expDate}` : '';
                    const daysLeft = it.daysLeft ? ` · Zostáva: ${it.daysLeft}` : '';
                    const isReserved = parseInt(it.reservation?.is_reserved) === 1;
                    const btnText = isReserved ? 'Zrušiť' : 'Obnoviť';
                    const btnClass = isReserved ? 'btn-outline-danger' : 'btn-outline-primary';
                    const btnAction = isReserved ? 'cancel' : 'restore';
                    listHtml += `
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">${title}</div>
                                <div class="small text-muted">
                                    Používateľ: ${username}<br>
                                    ${expDate}${daysLeft ? daysLeft : ''}
                                </div>
                            </div>
                            <div class="text-end ms-2 flex-shrink-0">
                                <button type="button"
                                    class="btn btn-sm ${btnClass} reservation-action"
                                    data-id="${it.reservation?.id}"
                                    data-action="${btnAction}">
                                    ${btnText}
                                </button>
                            </div>
                        </div>`;
                });
            } else {
                listHtml += '<div class="alert alert-info">Žiadne rezervácie.</div>';
            }
            listHtml += '</div>';
            const temp = document.createElement('div');
            temp.innerHTML = listHtml;
            document.querySelector(listSelector)?.replaceWith(temp.firstElementChild);
            // ================= PAGINATION =================
            document.querySelector('.pagination')?.remove();
            if (json.pagination?.pages > 1) {
                let pag = '<ul class="pagination">';
                for (let i = 1; i <= json.pagination.pages; i++) {
                    pag += `
                        <li class="page-item ${i === json.pagination.page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
                }
                pag += '</ul>';
                const p = document.createElement('div');
                p.innerHTML = pag;
                document.querySelector(listSelector).after(p.firstElementChild);
            }
            // ================= ACTIVE STATUS =================
            const status = params.get('status') || 'all';
            document.querySelectorAll('[data-status]').forEach(btn =>
                btn.classList.toggle('active', btn.dataset.status === status)
            );
            history.replaceState(null, '', url);
        } catch (err) {
            console.error('AJAX error:', err);
        }
    }



    // ================= CLICK HANDLER =================
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

        // Accept both <button data-page> and <a data-page> generated by server templates
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

            await res.json().catch(() => null);
            load();
        }
    });


});

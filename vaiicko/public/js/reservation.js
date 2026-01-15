document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#reservation-search-form');
    if (!form) return;

    const load = async () => {
        const params = new URLSearchParams(new FormData(form));
        const url = form.action + '?' + params.toString();

        const json = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(res => res.json()).catch(() => ({ items: [], pagination: {} }));

        let listHtml = '<div class="list-group" id="reservation-list">';
        if (json.items && json.items.length > 0) {
            json.items.forEach(it => {
                const title = it.book?.title || 'Neznáma kniha';
                const safeTitle = title.replace(/[<>"&]/g, m => ({'<':'&lt;','>':'&gt;','"':'&quot;','&':'&amp;'}[m]));
                const username = it.user?.username || it.user?.id || '—';
                const safeName = username.toString().replace(/[<>&]/g, m => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[m]));
                const expDate = it.expDate ? `Expiruje: ${it.expDate}` : '';
                const daysLeft = it.daysLeft ? ` · Zostáva: ${it.daysLeft}` : '';
                const timeStr = expDate + daysLeft;
                const btnAction = it.reservation.is_reserved == 1 ? 'cancel' : 'restore';
                const btnText = it.reservation.is_reserved == 1 ? 'Zrušiť rezerváciu' : 'Obnoviť rezerváciu';
                const btnClass = it.reservation.is_reserved == 1 ? 'btn-warning' : 'btn-success';

                listHtml += `<div class="list-group-item d-flex justify-content-between align-items-start reservation-item" data-title="${safeTitle}" data-reservation-id="${it.reservation.id}">
                    <div>
                        <div class="fw-bold">${safeTitle}</div>
                        <div class="small text-muted">Používateľ: ${safeName}<br>Čas: ${timeStr}</div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-sm ${btnClass} reservation-action" data-action="${btnAction}" data-id="${it.reservation.id}">${btnText}</button>
                    </div>
                </div>`;
            });
        } else {
            listHtml += '<div class="alert alert-info">Žiadne rezervácie.</div>';
        }
        listHtml += '</div>';

        let paginationHtml = '';
        if (json.pagination && json.pagination.pages > 1) {
            const page = json.pagination.page;
            const pages = json.pagination.pages;
            paginationHtml = '<ul class="pagination"><li class="page-item ' + (page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (page > 1 ? page - 1 : '') + '">&laquo; Predošlá</a></li>';
            for (let i = 1; i <= pages; i++) {
                paginationHtml += '<li class="page-item ' + (i === page ? 'active' : '') + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            paginationHtml += '<li class="page-item ' + (page >= pages ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (page < pages ? page + 1 : '') + '">Nasledujúca &raquo;</a></li></ul>';
        }

        const temp = document.createElement('div');
        temp.innerHTML = listHtml;
        document.querySelector('#reservation-list')?.replaceWith(temp.firstElementChild);

        const oldPag = document.querySelector('.pagination');
        if (paginationHtml) {
            const tempPag = document.createElement('div');
            tempPag.innerHTML = paginationHtml;
            if (oldPag) oldPag.replaceWith(tempPag.firstElementChild);
            else document.querySelector('#reservation-list')?.after(tempPag.firstElementChild);
        } else if (oldPag) oldPag.remove();

        const status = new URLSearchParams(params).get('status') || 'all';
        document.querySelectorAll('[data-status]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === status);
        });

        history.replaceState(null, '', url);
    };

    document.addEventListener('click', e => {
        const statusBtn = e.target.closest('[data-status]');
        if (statusBtn) {
            e.preventDefault();
            form.querySelector('[name="status"]').value = statusBtn.dataset.status;
            form.querySelector('[name="page"]').value = 1;
            load();
            return;
        }

        const pageLink = e.target.closest('.pagination a[data-page]');
        if (pageLink?.dataset.page) {
            e.preventDefault();
            form.querySelector('[name="page"]').value = pageLink.dataset.page;
            load();
            return;
        }

        const actionBtn = e.target.closest('.reservation-action');
        if (actionBtn) {
            e.preventDefault();
            const { id, action } = actionBtn.dataset;
            if (!id || !action) return;
            const updateUrl = form.dataset.updateUrl;
            if (!updateUrl) return;
            fetch(updateUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id, action }).toString(),
                credentials: 'same-origin'
            })
                .then(res => res.json())
                .then(json => { if (json?.success) load(); })
                .catch(() => {});
            return;
        }
    });

    document.querySelector('#reservation-search-button')?.addEventListener('click', e => {
        e.preventDefault();
        form.querySelector('[name="page"]').value = 1;
        load();
    });

    document.querySelector('#reservation-search-input')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            form.querySelector('[name="page"]').value = 1;
            load();
        }
    });

    const status = form.querySelector('[name="status"]')?.value || 'all';
    document.querySelectorAll('[data-status]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });
});


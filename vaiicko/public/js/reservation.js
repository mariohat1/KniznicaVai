(function () {
    // reservation.js - spracovanie AJAX vyhľadávania a akcií pre spravu rezervácií
    document.addEventListener('DOMContentLoaded', function () {
        try {
            var form = document.getElementById('reservation-search-form');
            var input = document.getElementById('reservation-search-input');
            var button = document.getElementById('reservation-search-button');
            var pageInput = document.getElementById('reservation-page');

            if (!form) return; // nič nerobíme ak formulár nie je na stránke

            // Read URLs from data attributes on the form - no inline JS globals required
            var manageAction = form.getAttribute('action') || window.location.pathname;
            var updateUrl = form.getAttribute('data-update-url') || null;

            // replaceContainerWithHtml: spracuje HTML odpoveď zo servera a nahradí
            // obsah zoznamu rezervácií (#reservation-list) + stránkovanie.
            // - html: celý HTML dokument alebo fragment vrátený serverom
            function replaceContainerWithHtml(html) {
                if (!html) return;
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newList = doc.querySelector('#reservation-list');
                let container = document.getElementById('reservation-list');
                if (!container) {
                    let sibling = form.nextElementSibling;
                    while (sibling && sibling.nodeType !== 1) sibling = sibling.nextElementSibling;
                    container = sibling || null;
                }

                // Ak server poslal nový zoznam, nahradíme ho.
                if (newList) {
                    if (container) {
                        container.outerHTML = newList.outerHTML;
                    } else {
                        let sibling = form.nextElementSibling;
                        while (sibling && sibling.nodeType !== 1) sibling = sibling.nextElementSibling;
                        if (sibling && sibling.parentNode) {
                            sibling.parentNode.insertBefore(newList.cloneNode(true), sibling);
                        } else if (form.parentNode) {
                            form.parentNode.appendChild(newList.cloneNode(true));
                        }
                    }
                }

                // Aktualizujeme stránkovanie (pagination) ak je v odpovedi.
                const newPagination = doc.querySelector('nav[aria-label="Stránkovanie"]');
                const oldPagination = document.querySelector('nav[aria-label="Stránkovanie"]');
                if (newPagination) {
                    if (oldPagination) {
                        oldPagination.outerHTML = newPagination.outerHTML;
                    } else {
                        const list = document.querySelector('#reservation-list');
                        if (list) {
                            list.insertAdjacentHTML('afterend', newPagination.outerHTML);
                        }
                    }
                } else {
                    if (oldPagination && oldPagination.parentNode) oldPagination.parentNode.removeChild(oldPagination);
                }
            }

            // setActive: nastaví vizuálnu aktívnu triedu pre filter buttony podľa statusu
            function setActive(status) {
                var buttons = form.querySelectorAll('.btn-group a');
                buttons.forEach(function (btn) {
                    var href = btn.getAttribute('href') || '';
                    var isActive = (href.indexOf('status=' + status) !== -1);
                    btn.classList.toggle('active', isActive);
                });
            }

            // ENTER v inpute: spustí AJAX vyhľadávanie (resetuje stránku na 1)
            if (input) {
                input.addEventListener('keydown', async function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        if (pageInput) pageInput.value = 1;
                        await fetchAndReplace();
                    }
                });
            }

            // Klik na tlačidlo 'Hľadať': spustí AJAX vyhľadávanie (resetuje stránku na 1)
            if (button) {
                button.addEventListener('click', async function () {
                    if (pageInput) pageInput.value = 1;
                    await fetchAndReplace();
                });
            }

            // Status linky (Všetky / Aktívne / Skončené): prepíšu hidden status a spustia fetch
            var statusLinks = form.querySelectorAll('.btn-group a[data-status]');
            statusLinks.forEach(function (a) {
                a.addEventListener('click', async function (e) {
                    e.preventDefault();
                    var s = a.getAttribute('data-status') || '';
                    var qVal = input && input.value ? input.value.trim() : '';
                    if (input) input.value = qVal;
                    var statusInput = form.querySelector('input[name="status"]');
                    if (statusInput) statusInput.value = s;
                    if (pageInput) pageInput.value = 1;
                    setActive(s);
                    await fetchAndReplace();
                });
            });

            // Intercept pre kliky na pagination linky. Linky musia mať data-page aby boli ajax-ované.
            document.addEventListener('click', async function (ev) {
                var a = ev.target.closest('.pagination a.page-link');
                if (!a) return;
                var p = a.getAttribute('data-page');
                if (!p) return; // necháme normálny link fungovať ak nie je data-page
                ev.preventDefault();
                if (pageInput) pageInput.value = p;
                await fetchAndReplace();
            });

            // fetchAndReplace: vytvorí GET parametre zo formulára, zavolá server cez fetch
            // a potom volá replaceContainerWithHtml na aktualizáciu zoznamu.
            async function fetchAndReplace() {
                var fd = new FormData(form);
                if (input) fd.set('q', input.value.trim());
                var params = new URLSearchParams(fd);
                var action = manageAction || window.location.pathname;
                var paramStr = params.toString();
                var urlUsed = action + (paramStr ? ((action.indexOf('?') === -1 ? '?' : '&') + paramStr) : '');
                var displayUrl = (urlUsed.charAt(0) === '?' ? window.location.pathname + urlUsed : urlUsed);

                try {
                    var resp = await fetch(urlUsed, {
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        credentials: 'same-origin'
                    });
                    if (resp.redirected) {
                        // server chce presmerovať (napr. na login) -> prejsť tam
                        window.location.href = resp.url;
                        return;
                    }
                    if (!resp.ok) {
                        // ne-2xx odpoveď -> fallback na normálne GET
                        window.location.href = urlUsed;
                        return;
                    }
                    var text = await resp.text();
                    // nahradíme obsah stránky fragmentom
                    replaceContainerWithHtml(text);
                    try { setActive(fd.get('status') || ''); } catch (e) {}
                    try { history.replaceState(null, '', displayUrl); } catch (e) {}
                } catch (err) {
                    // sieťová chyba -> fallback na normálnu navigáciu
                    console.error('AJAX reservation fetch failed', err);
                    window.location.href = urlUsed;
                }
            }

            // Delegácia pre akcie (cancel/restore) - odchytené z tlačidiel .reservation-action
            // Po úspešnom JSON odpovedi sa refreshne zoznam cez fetchAndReplace().
            document.addEventListener('click', async function (ev) {
                var btn = ev.target.closest('.reservation-action');
                if (!btn) return;
                ev.preventDefault();
                var id = btn.getAttribute('data-id');
                var action = btn.getAttribute('data-action');
                if (!id || !action) return;
                var payload = {id: id, action: action};
                var url = updateUrl;
                if (!url) {
                    console.error('reservation.js: missing data-update-url on #reservation-search-form');
                    return;
                }
                if (typeof url === 'string' && url.charAt(0) === '?') {
                    url = window.location.pathname + url;
                }

                btn.disabled = true;
                try {
                    var formBody = new FormData();
                    formBody.append('id', payload.id);
                    formBody.append('action', payload.action);
                    var resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: formBody
                    });
                    var json = await resp.json();
                    if (json && json.success) {
                        // ak server potvrdí úspech, refreshneme zoznam
                        await fetchAndReplace();
                    }
                } catch (err) {
                    console.error('Update failed', err);
                    // pri chybe fallback na normálnu navigáciu so zachovaním filtrov
                    window.location.href = window.location.pathname + '?status=' + (form.querySelector('input[name="status"]') ? encodeURIComponent(form.querySelector('input[name="status"]').value) : 'all') + '&q=' + encodeURIComponent(input ? input.value.trim() : '');
                } finally {
                    btn.disabled = false;
                }
            });

        } catch (e) {
            console.error('reservations init error', e);
        }
    });
})();

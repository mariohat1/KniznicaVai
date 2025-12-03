<?php
/** @var array $items */
/** @var \Framework\Support\LinkGenerator $link */
/** @var string $q */
/** @var string|null $status */
/** @var array|null $users */
?>
<div class="container">
    <h1 class="mb-4">Správa rezervácií</h1>
    <form id="reservation-search-form" class="row g-2 mb-3" method="get"
          action="<?= $link->url('reservation.manage') ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status ?? 'all') ?>">
        <input type="hidden" name="page" id="reservation-page"
               value="<?= htmlspecialchars(isset($pagination) ? ($pagination['page'] ?? 1) : 1) ?>">
        <div class="col-auto">
            <label for="reservation-search-by" class="visually-hidden">Režim hľadania</label>
            <select id="reservation-search-by" name="searchBy" class="form-select" aria-label="Režim hľadania">
                <option value="book" <?= (isset($searchBy) && $searchBy === 'book') || !isset($searchBy) ? 'selected' : '' ?>>
                    Podľa knihy
                </option>
                <option value="user" <?= isset($searchBy) && $searchBy === 'user' ? 'selected' : '' ?>>Podľa
                    používateľa
                </option>
            </select>
        </div>
        <div class="col-auto">
            <input id="reservation-search-input" aria-label="Hľadať" type="search" name="q"
                   class="form-control" placeholder="Hľadať podľa názvu knihy alebo používateľa"
                   value="<?= htmlspecialchars($q ?? '') ?>">
        </div>
        <div class="col-auto">
            <button id="reservation-search-button" type="button" class="btn btn-primary">Hľadať</button>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group" aria-label="Status filter">
                <a class="btn btn-outline-secondary <?= ($status === null || $status === 'all') ? 'active' : '' ?>"
                   data-status="all"
                   href="<?= $link->url('reservation.manage', ['status' => 'all', 'q' => $q ?? '', 'user' => $selectedUser ?? '', 'searchBy' => $searchBy ?? '']) ?>">Všetky
                </a>
                <a class="btn btn-outline-success <?= ($status === 'active') ? 'active' : '' ?>"
                   data-status="active"
                   href="<?= $link->url('reservation.manage', ['status' => 'active', 'q' => $q ?? '', 'user' => $selectedUser ?? '', 'searchBy' => $searchBy ?? '']) ?>">Aktívne</a>

                <a class="btn btn-outline-dark <?= ($status === 'finished') ? 'active' : '' ?>"
                   data-status="finished"
                   href="<?= $link->url('reservation.manage', ['status' => 'finished', 'q' => $q ?? '', 'user' => $selectedUser ?? '', 'searchBy' => $searchBy ?? '']) ?>">Skončené</a>
            </div>
        </div>
    </form>
    <?php if (empty($items)): ?>
        <div class="alert alert-info">Žiadne rezervácie.</div>
    <?php else: ?>
        <div class="list-group" id="reservation-list">
            <?php foreach ($items as $it):
                $r = $it['reservation'];
                $book = $it['book'];
                $copy = $it['copy'];
                $user = $it['user'];
                $safeTitle = $book ? htmlspecialchars($book->getTitle(), ENT_QUOTES, 'UTF-8') : '';
                ?>
                <div class="list-group-item d-flex justify-content-between align-items-start reservation-item"
                     data-title="<?= $safeTitle ?>" data-reservation-id="<?= htmlspecialchars((string)$r->getId()) ?>">
                    <div>
                        <div class="fw-bold"><?= $book ? htmlspecialchars($book->getTitle()) : 'Neznáma kniha' ?></div>
                        <div class="small text-muted">
                            Kópia: <?= $copy ? htmlspecialchars((string)$copy->getId()) : '—' ?>
                            Používateľ: <?= $user ? htmlspecialchars($user->getUsername() ?? $user->getId()) : '—' ?>
                            Rezervované: <?= $r->getIsReserved() ? 'Áno' : 'Nie' ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if ($r->getIsReserved()): ?>
                            <button type="button" class="btn btn-sm btn-warning  reservation-action"
                                    data-action="cancel" data-id="<?= htmlspecialchars((string)$r->getId()) ?>">Zrušiť
                                rezerváciu
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn  btn-sm btn-success reservation-action"
                                    data-action="restore" data-id="<?= htmlspecialchars((string)$r->getId()) ?>">Obnoviť
                                rezerváciu
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($pagination)): ?>
            <?php
            $page = $pagination['page'] ?? 1;
            $pages = $pagination['pages'] ?? 1;
            ?>
            <nav aria-label="Stránkovanie" class="mt-3">
                <ul class="pagination">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= ($page > 1) ? htmlspecialchars($link->url('reservation.manage',
                                ['page' => $page - 1])) : '#' ?>" data-page="<?= ($page > 1) ? ($page - 1) : '' ?>">
                            &laquo; Predošlá
                        </a>
                    </li>

                    <?php
                    for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($link->url('reservation.manage',
                                    ['page' => $i])) ?>" data-page="<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $pages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= ($page < $pages) ? htmlspecialchars($link->url('reservation.manage', ['page' => $page + 1])) : '#' ?>"
                           data-page="<?= ($page < $pages) ? ($page + 1) : '' ?>">Nasledujúca &raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                var form = document.getElementById('reservation-search-form');
                var input = document.getElementById('reservation-search-input');
                var button = document.getElementById('reservation-search-button');
                var pageInput = document.getElementById('reservation-page');


                function replaceContainerWithHtml(html) {
                    if (!html) return;
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var newList = doc.querySelector('#reservation-list');
                    var container = document.getElementById('reservation-list');
                    if (!container) {
                        var n = form.nextElementSibling;
                        while (n && n.nodeType !== 1) n = n.nextElementSibling;
                        container = n || null;
                    }
                    container.innerHTML = newList.innerHTML;
                    container.outerHTML = newList.outerHTML;
                    var newPagination = doc.querySelector('nav[aria-label="Stránkovanie"]');

                    var oldPagination = document.querySelector('nav[aria-label="Stránkovanie"]');
                    if (newPagination) {
                        if (oldPagination) {
                            // existuje → nahradíme
                            oldPagination.outerHTML = newPagination.outerHTML;
                        } else {
                            // neexistuje → vložíme ju za #reservation-list
                            var list = document.querySelector('#reservation-list');
                            if (list) {
                                list.insertAdjacentHTML('afterend', newPagination.outerHTML);
                            }
                        }
                    }
                }

                function setActive(status) {
                    var buttons = form.querySelectorAll('.btn-group a');
                    buttons.forEach(function (btn) {
                        var isActive = (btn.getAttribute('href').indexOf('status=' + status) !== -1);
                        btn.classList.toggle('active', isActive);
                    });
                }

                input.addEventListener('keydown', async function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        // new search -> reset to first page
                        if (pageInput) pageInput.value = 1;
                        await fetchAndReplace()
                    }
                });
                button.addEventListener('click', async function () {
                    // new search via button -> reset to first page
                    if (pageInput) pageInput.value = 1;
                    await fetchAndReplace();
                });


                var statusLinks = form.querySelectorAll('.btn-group a[data-status]');
                statusLinks.forEach(function (a) {
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        var s = a.getAttribute('data-status') || '';
                        var qVal = input && input.value ? input.value.trim() : '';
                        if (input) input.value = qVal;
                        var statusInput = form.querySelector('input[name="status"]');
                        if (statusInput) statusInput.value = s;
                        if (pageInput) pageInput.value = 1;
                        setActive(s);
                        fetchAndReplace();
                    });
                });

                // intercept clicks on pager links (data-page) and perform AJAX while preserving filters
                document.addEventListener('click', function (ev) {
                    var a = ev.target.closest('.pagination a.page-link');
                    if (!a) return;
                    var p = a.getAttribute('data-page');
                    if (!p) return; // let links without data-page act normally
                    ev.preventDefault();
                    if (pageInput) pageInput.value = p;
                    fetchAndReplace();
                });

                async function fetchAndReplace() {
                    var fd = new FormData(form);
                    if (input) fd.set('q', input.value.trim());
                    var params = new URLSearchParams(fd);
                    console.log(params.toString());
                    var action = form.getAttribute('action') || window.location.pathname;
                    var paramStr = params.toString();
                    var urlUsed = action + (paramStr ? ((action.indexOf('?') === -1 ? '?' : '&') + paramStr) : '');
                    var displayUrl = (urlUsed.charAt(0) === '?' ? window.location.pathname + urlUsed : urlUsed);

                    try {
                        var resp = await fetch(urlUsed, {
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            credentials: 'same-origin'
                        });
                        if (resp.redirected) {
                            window.location.href = resp.url;
                            return;
                        }
                        if (!resp.ok) {
                            window.location.href = urlUsed;
                            return;
                        }
                        var text = await resp.text();
                        replaceContainerWithHtml(text);
                        setActive(fd.get('status') || '');
                        try {
                            history.replaceState(null, '', displayUrl);
                        } catch (e) {
                        }
                    } catch (err) {
                        console.error('AJAX reservation fetch failed', err);
                        window.location.href = urlUsed;
                    }
                }

                // delegation for action buttons
                document.addEventListener('click', async function (ev) {
                    var btn = ev.target.closest('.reservation-action');
                    if (!btn) return;
                    ev.preventDefault();
                    var id = btn.getAttribute('data-id');
                    var action = btn.getAttribute('data-action');
                    if (!id || !action) return;
                    var payload = {id: id, action: action};
                    var url = <?= json_encode($link->url('reservation.update')) ?>;
                    if (typeof url === 'string' && url.charAt(0) === '?') {
                        url = window.location.pathname + url;
                    }

                    btn.disabled = true;
                    try {
                        // send as FormData (browser sets Content-Type multipart/form-data) so PHP populates $_POST
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
                            // Always refresh the list after a successful change so server-state and filters apply
                            await fetchAndReplace();
                            return;
                        }
                        alert((json && json.message) ? json.message : 'Operácia zlyhala');
                    } catch (err) {
                        console.error('Update failed', err);
                        window.location.href = window.location.pathname + '?status=<?= htmlspecialchars($status ?? 'all') ?>&q=' + encodeURIComponent(input ? input.value.trim() : '');
                    } finally {
                        btn.disabled = false;
                    }
                });

            } catch (e) {
                console.error('reservations init error', e);
            }
        });
    </script>
</div>

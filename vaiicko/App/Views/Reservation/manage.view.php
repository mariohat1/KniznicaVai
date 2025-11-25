<?php
/** @var array $items */
/** @var \Framework\Support\LinkGenerator $link */
/** @var string $q */
/** @var string|null $status */
?>
<div class="container">
    <h1 class="mb-4">Správa rezervácií</h1>

    <?php $baseManageUrl = '?' . http_build_query(['c' => 'reservation', 'a' => 'manage']); ?>

    <!-- use simple relative query so URLs are like /?c=reservation&a=manage (no index.php) -->
    <form id="reservation-search-form" class="row g-2 mb-3" method="get" action="<?= $baseManageUrl ?>">
        <!-- preserve current status when searching (default to 'all' so initial searches use all) -->
        <input type="hidden" name="status" value="<?= htmlspecialchars($status ?? 'all') ?>">
        <div class="col-auto">
            <input id="reservation-search-input" aria-label="Hľadať podľa názvu knihy" type="search" name="q"
                   class="form-control" placeholder="Hľadať podľa názvu knihy"
                   value="<?= htmlspecialchars($q ?? '') ?>">
        </div>
        <div class="col-auto">
            <button id="reservation-search-button" type="button" class="btn btn-primary">Hľadať</button>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group" aria-label="Status filter">
                <a class="btn btn-outline-secondary <?= ($status === null || $status === 'all') ? 'active' : '' ?>"
                   data-status="all"
                   href="<?= $baseManageUrl . '&' . http_build_query(['status' => 'all', 'q' => $q ?? '']) ?>">Všetky</a>
                <a class="btn btn-outline-success <?= ($status === 'active') ? 'active' : '' ?>" data-status="active"
                   href="<?= $baseManageUrl . '&' . http_build_query(['status' => 'active', 'q' => $q ?? '']) ?>">Aktívne</a>
                <a class="btn btn-outline-dark <?= ($status === 'finished') ? 'active' : '' ?>" data-status="finished"
                   href="<?= $baseManageUrl . '&' . http_build_query(['status' => 'finished', 'q' => $q ?? '']) ?>">Skončené</a>
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
                     data-title="<?= $safeTitle ?>">
                    <div>
                        <div class="fw-bold"><?php echo $book ? htmlspecialchars($book->getTitle()) : 'Neznáma kniha'; ?></div>
                        <div class="small text-muted">
                            Kópia: <?= $copy ? htmlspecialchars((string)$copy->getId()) : '—' ?>
                            Používateľ: <?= $user ? htmlspecialchars($user->getUsername() ?? $user->getId()) : '—' ?>
                            Rezervované: <?= htmlspecialchars((string)$r->getCreatedAt()) ?>
                            Aktívne: <?= $r->getIsActive() ? 'Áno' : 'Nie' ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <!-- future: add quick actions (deactivate/cancel) -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <script>
        (function () {
            var form = document.getElementById('reservation-search-form');
            var input = document.getElementById('reservation-search-input');
            var button = document.getElementById('reservation-search-button');


            function replaceContainerWithHtml(html) {
                if (!html) return;

                // parse returned HTML (works whether server returns fragment or full page)
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                var newList = doc.querySelector('#reservation-list');
                var newAlert = doc.querySelector('.alert');

                // determine current container on the page
                var currentList = document.getElementById('reservation-list');
                var currentAlert = document.getElementById('reservation-no-results');
                var container = currentList || currentAlert;
                if (!container) {
                    // fallback to element after form
                    var n = form.nextElementSibling;
                    while (n && n.nodeType !== 1) n = n.nextElementSibling;
                    container = n || null;
                }

                if (newList) {
                    if (container && container.id === 'reservation-list') {
                        // update inner HTML to preserve the wrapper
                        container.innerHTML = newList.innerHTML;
                    } else if (container) {
                        // replace whole container
                        container.outerHTML = newList.outerHTML;
                    } else {
                        // insert after form as last resort
                        form.insertAdjacentHTML('afterend', newList.outerHTML);
                    }

                    // no-results alert handling removed as requested
                    return;
                }

                if (newAlert) {
                    if (container) {
                        container.outerHTML = newAlert.outerHTML;
                    } else {
                        form.insertAdjacentHTML('afterend', newAlert.outerHTML);
                    }
                    return;
                }
                if (form) form.insertAdjacentHTML('afterend', html);
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
                    await fetchAndReplace()
                }
            });
            button.addEventListener('click', async function () {
                await fetchAndReplace();
            });


            var statusLinks = form.querySelectorAll('.btn-group a[data-status]');
            statusLinks.forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    var s = a.getAttribute('data-status') || '';
                    // keep q from the input (do not override if user typed something)
                    var qVal = input && input.value ? input.value.trim() : '';
                    if (input) input.value = qVal;
                    var statusInput = form.querySelector('input[name="status"]');
                    if (statusInput) statusInput.value = s;
                    setActive(s);
                    fetchAndReplace();
                });
            });

            async function fetchAndReplace() {

                var fd = new FormData(form);
                if (input) fd.set('q', input.value.trim());
                var params = new URLSearchParams(fd);
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
        })();
    </script>
</div>

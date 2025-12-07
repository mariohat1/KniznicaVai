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
          action="<?= $link->url('reservation.manage') ?>" data-update-url="<?= htmlspecialchars($link->url('reservation.update')) ?>">
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
    <script src="<?= $link->asset('js/reservation.js') ?>"></script>
</div>

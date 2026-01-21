<?php
/** @var array $items */
/** @var string $status */
/** @var array $pagination */
/** @var \Framework\Support\LinkGenerator $link */

?>
<div class="container">
    <h1 class="mb-4 section-title">Moje rezervácie</h1>

    <div class="mb-3">
        <div class="btn-group" role="group" aria-label="Status filter">
            <a href="<?= $link->url('reservation.index', ['status' => 'all']) ?>" class="btn btn-sm <?= ($status ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">Všetky</a>
            <a href="<?= $link->url('reservation.index', ['status' => 'active']) ?>" class="btn btn-sm <?= ($status ?? '') === 'active' ? 'btn-primary' : 'btn-outline-secondary' ?>">Aktívne</a>
            <a href="<?= $link->url('reservation.index', ['status' => 'finished']) ?>" class="btn btn-sm <?= ($status ?? '') === 'finished' ? 'btn-primary' : 'btn-outline-secondary' ?>">Skončené</a>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">Nemáte žiadne rezervácie.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($items as $it):
                $r = $it['reservation'];
                $book = $it['book'];
                $copy = $it['copy'];
                ?>
                <div class="list-group-item card highlight-reservation d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold book-title">
                            <?php if ($book): ?>
                                <a href="<?= $link->url(['book', 'view', 'id' => $book->getId()]) ?>"
                                   class="text-decoration-none author-link"><?= htmlspecialchars((string)$book->getTitle()) ?></a>
                            <?php else: ?>
                                Neznáma kniha
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted">
                            Rezervované do: <?= htmlspecialchars((new DateTime($r->getReservedUntil()))->format('d.m.Y')) ?>
                        </div>
                    </div>
                    <div class="text-end ms-3">
                        <?php if ($r->getIsReserved()): ?>
                            <form method="post" action="<?= $link->url('reservation.cancel') ?>" class="d-inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$r->getId()) ?>">
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status ?? 'all') ?>">
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Naozaj zrušiť rezerváciu?');">Zrušiť</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination: simple full pages loop using provided $pagination -->
        <?php if (!empty($pagination) && isset($pagination['pages']) && $pagination['pages'] > 1):
            $page = (int)$pagination['page'];
            $pages = (int)$pagination['pages'];
            $limit = (int)($pagination['limit'] ?? 10);
            $total = (int)($pagination['total'] ?? 0);
            ?>
            <div class="d-flex flex-column align-items-center mt-3">
                <nav aria-label="Stránkovanie">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('reservation.index', ['page' => max(1, $page - 1), 'status' => $status ?? 'all']) ?>" aria-label="Predchádzajúca">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $link->url('reservation.index', ['page' => $p, 'status' => $status ?? 'all']) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('reservation.index', ['page' => min($pages, $page + 1), 'status' => $status ?? 'all']) ?>" aria-label="Ďalšia">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="small text-muted">
                    Zobrazené: <?= $total > 0 ? (($page - 1) * $limit + 1) : 0 ?> - <?= $total > 0 ? min($total, $page * $limit) : 0 ?> z <?= $total ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

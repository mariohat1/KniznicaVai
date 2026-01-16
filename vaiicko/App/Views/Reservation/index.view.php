<?php
/** @var array $items */
/** @var string $status */
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
                            Kópia: <?= $copy ? htmlspecialchars((string)$copy->getId()) : '—' ?>
                            <br>
                            Rezervované: <?= htmlspecialchars((string)$r->getCreatedAt()) ?>
                        </div>
                    </div>
                    <div class="text-end ms-3">
                        <?php if ($r->getIsReserved()): ?>
                            <form method="post" action="<?= $link->url('reservation.cancel') ?>" class="d-inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$r->getId()) ?>">
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Naozaj zrušiť rezerváciu?');">Zrušiť</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

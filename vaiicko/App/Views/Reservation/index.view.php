<?php
/** @var array $items */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */

?>
<div class="container">
    <h1 class="mb-4 section-title">Moje rezervácie</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">Nemáte žiadne rezervácie.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($items as $it):
                $r = $it['reservation'];
                $book = $it['book'];
                $copy = $it['copy'];
                ?>
                <div class="list-group-item d-flex justify-content-between align-items-start">
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
                            Rezervované: <?= htmlspecialchars((string)$r->getCreatedAt()) ?>
                            Rezervované (flag): <?= $r->getIsReserved() ? 'Áno' : 'Nie' ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if ($r->getIsReserved()): ?>
                            <form method="post" action="<?= $link->url('reservation.update') ?>" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$r->getId()) ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn btn-sm btn-outline-danger">Zrušiť</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

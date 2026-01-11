<?php
/** @var array $genres */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1 class="mb-4 section-title">Žánre</h1>

    <?php if (empty($genres)): ?>
        <div class="alert alert-info">Žiadne žánre.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($genres as $g): ?>
                <div class="list-group-item border-bottom py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <?php $url = $link->url('book.index') . (strpos($link->url('book.index'), '?') === false ? '?' : '&') . 'genre=' . (int)$g->getId(); ?>
                            <h5 class="mb-0"><a href="<?= htmlspecialchars($url) ?>" class="text-decoration-none"><?= htmlspecialchars($g->getName()) ?></a></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


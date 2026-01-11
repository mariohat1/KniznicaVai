<?php
/** @var array $categories */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1 class="mb-4 section-title">Kategórie</h1>

    <?php if (empty($categories)): ?>
        <div class="alert alert-info">Žiadne kategórie.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($categories as $c): ?>
                <div class="list-group-item border-bottom py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <?php $url = $link->url('book.index') . (strpos($link->url('book.index'), '?') === false ? '?' : '&') . 'category=' . (int)$c->getId(); ?>
                            <h5 class="mb-0"><a href="<?= htmlspecialchars($url) ?>" class="text-decoration-none"><?= htmlspecialchars($c->getName()) ?></a></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


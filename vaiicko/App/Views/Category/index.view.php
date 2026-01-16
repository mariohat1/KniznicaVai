<?php
/** @var array $categories */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1 class="mb-4 section-title">Kategórie</h1>

    <!-- Search form -->
    <form method="get" action="<?= $link->url('category.index') ?>" class="row g-2 mb-4">
        <input type="hidden" name="c" value="category">
        <input type="hidden" name="a" value="index">
        <div class="col-12 col-md-6">
            <input type="search" name="q" class="form-control" placeholder="Hľadať kategóriu..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary w-100 w-md-auto">Hľadať</button>
        </div>
    </form>

    <?php if (empty($categories)): ?>
        <div class="alert alert-info">Žiadne kategórie.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($categories as $c): ?>
                <div class="list-group-item border-bottom py-3 card list-item-hover">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <?php $url = $link->url('book.index') . (strpos($link->url('book.index'), '?') === false ? '?' : '&') . 'category=' . (int)$c->getId(); ?>
                            <h5 class="mb-0"><a href="<?= htmlspecialchars($url) ?>" class="author-link"><?= htmlspecialchars($c->getName()) ?></a></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


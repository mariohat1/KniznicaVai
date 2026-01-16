<?php
/** @var array $genres */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1 class="mb-4 section-title">Žánre</h1>

    <!-- Search form -->
    <form method="get" action="<?= $link->url('genre.index') ?>" class="row g-2 mb-4">
        <input type="hidden" name="c" value="genre">
        <div class="col-12 col-md-6">
            <input type="search" name="q" class="form-control" placeholder="Hľadať žáner..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary w-100 w-md-auto">Hľadať</button>
        </div>
    </form>

    <?php if (empty($genres)): ?>
        <div class="alert alert-info">Žiadne žánre.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($genres as $g): ?>
                <div class="list-group-item border-bottom py-3 card list-item-hover">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <?php $url = $link->url('book.index') . (strpos($link->url('book.index'), '?') === false ? '?' : '&') . 'genre=' . (int)$g->getId(); ?>
                            <h5 class="mb-0"><a href="<?= htmlspecialchars($url) ?>" class="author-link"><?= htmlspecialchars($g->getName()) ?></a></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


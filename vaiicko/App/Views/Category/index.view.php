<?php
/** @var array $categories */
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $filters */
/** @var array $pagination */
?>

<div class="container">
    <h1 class="mb-4 section-title">Kategórie</h1>
    <form method="get" action="<?= $link->url('category.index') ?>" class="row g-2 mb-4">
        <input type="hidden" name="c" value="category">
        <div class="col-12 col-md-6">
            <label for="categorySearchInput" class="visually-hidden">Hľadať kategóriu</label>
            <input id="categorySearchInput" type="search" name="q" class="form-control" placeholder="Hľadať kategóriu..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary w-100 w-md-auto">Hľadať</button>
        </div>
    </form>
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">Žiadne kategórie.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php foreach ($categories as $c): ?>
                <?php $url = $link->url('book.index') . (strpos($link->url('book.index'), '?') === false ? '?' : '&') . 'category=' . (int)$c->getId(); ?>
                <div class="col">
                    <div class="card h-100 list-item-hover">
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="<?= htmlspecialchars($url) ?>" class="author-link"><?= htmlspecialchars($c->getName()) ?></a>
                            </h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($pagination) && isset($pagination['pages']) && $pagination['pages'] > 1):
            $page = (int)$pagination['page'];
            $pages = (int)$pagination['pages'];
            $perPage = (int)($pagination['perPage'] ?? 10);
            $total = (int)($pagination['total'] ?? 0);
            $qParam = $filters['q'] ?? '';
            ?>
            <div class="d-flex flex-column align-items-center mt-3">
                <nav aria-label="Stránkovanie kategórií">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('category.index', ['page' => max(1, $page - 1), 'q' => $qParam]) ?>" aria-label="Predchádzajúca">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $link->url('category.index', ['page' => $p, 'q' => $qParam]) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('category.index', ['page' => min($pages, $page + 1), 'q' => $qParam]) ?>" aria-label="Ďalšia">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="small text-muted">
                    Zobrazené: <?= $total > 0 ? (($page - 1) * $perPage + 1) : 0 ?> - <?= $total > 0 ? min($total, $page * $perPage) : 0 ?> z <?= $total ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>


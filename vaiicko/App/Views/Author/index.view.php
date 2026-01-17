<?php
/** @var array $authors */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $filters */
/** @var array $pagination */
?>

<div class="container">
    <h1 class="mb-4 section-title">Autori</h1>

    <!-- Search form -->
    <form method="get" action="<?= $link->url('author.index') ?>" class="row g-2 mb-4">
        <div class="col-12 col-md-6">
            <label for="authorSearchInput" class="visually-hidden">Hľadať autora</label>
            <input id="authorSearchInput" type="search" name="q" class="form-control" placeholder="Hľadať autora..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary w-100 w-md-auto">Hľadať</button>
        </div>
    </form>

    <?php if (empty($authors)): ?>
        <div class="alert alert-info">Žiadni autori.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($authors as $a): ?>
                <div class="list-group-item border-bottom py-3 card list-item-hover">
                    <div class="row g-3 align-items-center">
                        <!-- Avatar column -->
                        <div class="col-auto text-center">
                            <?php $photo = $a->getPhoto(); ?>
                            <div class="author-avatar-wrapper author-avatar--medium">
                                <?php if (!empty($photo)): ?>
                                    <img src="<?= htmlspecialchars($photo) ?>"
                                         alt="Fotka - <?= htmlspecialchars($a->getFirstName() . ' ' . $a->getLastName()) ?>"
                                         class="avatar-author">
                                <?php else: ?>
                                    <?php
                                        $fn = trim((string)$a->getFirstName());
                                        $ln = trim((string)$a->getLastName());

                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-1">
                                <a href="<?= $link->url('author.view', ['id' => $a->getId()]) ?>" class="author-link">
                                    <?= htmlspecialchars(trim($a->getFirstName() . ' ' . $a->getLastName()) ?: 'Bez mena') ?>
                                </a>
                            </h5>
                            <p class="mb-0 text-muted"><small>
                                <strong>Rok narodenia:</strong> <?= htmlspecialchars($a->getBirthYear() ?: 'Neznáme') ?>
                            </small></p>
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
                <nav aria-label="Stránkovanie autorov">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('author.index', ['page' => max(1, $page - 1), 'q' => $qParam]) ?>" aria-label="Predchádzajúca">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $link->url('author.index', ['page' => $p, 'q' => $qParam]) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $link->url('author.index', ['page' => min($pages, $page + 1), 'q' => $qParam]) ?>" aria-label="Ďalšia">
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

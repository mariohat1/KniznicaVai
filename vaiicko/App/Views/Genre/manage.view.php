<?php
if (isset($view) && method_exists($view, 'setLayout')) {
    $view->setLayout('admin');
}
/** @var array $genres */
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $filters */
/** @var array $pagination */
?>

<div class="container">
    <h1>Správa žánrov</h1>

    <form method="get" action="<?= $link->url('genre.manage') ?>" class="mb-3">
        <input type="hidden" name="c" value="genre">
        <input type="hidden" name="a" value="manage">
        <input type="hidden" name="page" value="<?= htmlspecialchars($filters['page'] ?? 1) ?>">

        <div class="row g-2 align-items-start">
            <div class="col-12 col-md-auto mb-2 mb-md-0">
                <label for="genreManageSearch" class="visually-hidden">Hľadať názov</label>
                <input id="genreManageSearch" type="search" name="q" class="form-control" placeholder="Hľadať názov..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-auto mb-2 mb-md-0">
                <button class="btn btn-primary w-100 w-md-auto" type="submit">Hľadať</button>
            </div>
        </div>
    </form>

    <div class="mb-3 row">
        <div class="col-12 col-md-auto">
            <a class="btn btn-primary btn-sm w-100" href="<?= $link->url('genre.add') ?>">Pridať žáner</a>
        </div>
    </div>

    <?php if (empty($genres)): ?>
        <p>Žiadne žánre.</p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table ">
            <thead>
                <tr>
                    <th>Názov</th>
                    <th class="d-none d-md-table-cell">Popis</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($genres as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$g->getName()) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars((string)$g->getDescription()) ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('genre.add', ['id' => $g->getId()]) ?>">Upraviť</a>
                                <form method="post" action="<?= $link->url('genre.delete') ?>" class="d-inline-block genre-delete-form"
                                      onsubmit="return confirm('Naozaj chcete zmazať tento žáner?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($g->getId()) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if (!empty($pagination) && isset($pagination['pages']) && $pagination['pages'] > 1): ?>
            <nav aria-label="Stránkovanie" class="mt-3">
                <ul class="pagination">
                    <?php $current = (int)($pagination['page'] ?? 1); $pages = (int)$pagination['pages']; ?>
                    <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                        <?php $p = max(1, $current - 1); $url = $link->url('genre.manage', array_merge($filters ?? [], ['page' => $p])); ?>
                        <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>" aria-label="Predchádzajúca">&laquo;</a>
                    </li>

                    <?php for ($i = 1; $i <= $pages; $i++):
                        $url = $link->url('genre.manage', array_merge($filters ?? [], ['page' => $i]));
                        ?>
                        <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                        <?php $p = min($pages, $current + 1); $url = $link->url('genre.manage', array_merge($filters ?? [], ['page' => $p])); ?>
                        <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>" aria-label="Nasledujúca">&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>


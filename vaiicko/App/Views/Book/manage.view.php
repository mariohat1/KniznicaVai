<?php
/** @var array $books */
/** @var array $copies */
/** @var array $categories */
/** @var array $genres */
/** @var array $filters */
/** @var array $pagination */
/** @var \Framework\Support\LinkGenerator $link */
/** @var object $view */
$view->setLayout('admin');

?>
<div class="container">
    <h1>Správa kníh</h1>
    <form id="bookSearchForm" method="get" action="<?= $link->url('book.manage') ?>" class="mb-3">
        <input type="hidden" name="c" value="book">
        <input type="hidden" name="a" value="manage">

        <div class="row g-2 align-items-start">
            <div class="col-12 col-sm-6 col-md-2">
                <label for="bookManageSearch" class="visually-hidden">Hľadať podľa názvu knihy</label>
                <input id="bookManageSearch" type="search" name="q" class="form-control" placeholder="Hľadať podľa názvu knihy" value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>

            <div class="col-12 col-sm-6 col-md-2">
                <label for="bookCategorySelect" class="visually-hidden">Kategória</label>
                <select id="bookCategorySelect" name="category" class="form-select">
                    <option value="">Všetky kategórie</option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= isset($filters['category']) && $filters['category'] == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-2">
                <label for="bookGenreSelect" class="visually-hidden">Žáner</label>
                <select id="bookGenreSelect" name="genre" class="form-select">
                    <option value="">Všetky žánre</option>
                    <?php foreach ($genres as $id => $name): ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= isset($filters['genre']) && $filters['genre'] == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" id="bookPageInput" name="page" value="<?= htmlspecialchars($filters['page'] ?? 1) ?>">

            <div class="col-12 col-sm-6 col-md-auto">
                <button type="submit" class="btn btn-primary w-100 w-md-auto"><i class="bi bi-search" aria-hidden="true"></i> Hľadať</button>
            </div>
        </div>
    </form>

    <div class="mb-3 row">
        <div class="col-12 col-md-auto">
            <a class="btn btn-primary btn-sm w-100" href="<?= $link->url('book.add') ?>">Pridať knihu</a>
        </div>
    </div>

    <div id="manageFeedback" class="alert d-none"></div>

    <?php if (empty($books)): ?>
        <p>Žiadne knihy.</p>
    <?php else: ?>

    <div id="booksListContainer">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th class="visually-hidden">ID</th>
                    <th>Názov</th>
                    <th class="d-none d-md-table-cell">ISBN</th>
                    <th class="d-none d-md-table-cell">Kópie (celk./dost.)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($books as $b): ?>
                    <?php $cid = (int)$b->getId();
                    $meta = $copies[$cid] ?? ['total' => 0, 'available' => 0]; ?>
                    <tr>
                        <td class="visually-hidden"><?= htmlspecialchars((string)$cid) ?></td>
                        <td title="<?= htmlspecialchars($b->getTitle()) ?>"><?= htmlspecialchars($b->getTitle()) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($b->getIsbn()) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars((string)$meta['total']) ?> / <?= htmlspecialchars((string)$meta['available']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="pt-1 pb-1">
                            <div class="d-grid gap-2 d-sm-flex flex-sm-row">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('book.add', ['id' => $cid]) ?>">Upraviť</a>
                                <a class="btn btn-sm btn-outline-info" href="<?= $link->url('bookcopy.index', ['book_id' => $cid]) ?>">Spravovať kópie</a>
                                <form method="post" class="d-grid" action="<?= $link->url('book.delete', ['id' => $cid]) ?>" onsubmit="return confirm('Naozaj chcete zmazať túto knihu?');">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Zmazať</button>
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
                <ul class="pagination pagination-sm justify-content-center">
                    <?php $current = (int)($pagination['page'] ?? 1); $pages = (int)$pagination['pages']; ?>
                    <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                        <?php $p = max(1, $current - 1); $url = $link->url('book.manage', array_merge($filters ?? [], ['page' => $p])); ?>
                        <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>" aria-label="Predchádzajúca">&laquo;</a>
                    </li>

                    <?php for ($i = 1; $i <= $pages; $i++):
                        $url = $link->url('book.manage', array_merge($filters ?? [], ['page' => $i]));
                        ?>
                        <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                        <?php $p = min($pages, $current + 1); $url = $link->url('book.manage', array_merge($filters ?? [], ['page' => $p])); ?>
                        <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>" aria-label="Nasledujúca">&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>


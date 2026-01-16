<?php
/** @var array $books */
/** @var array $copies */
/** @var array $categories */
/** @var array $genres */
/** @var array $filters */
/** @var array $pagination */
/** @var \Framework\Support\LinkGenerator $link */
/** @var TYPE_NAME $view */
$view->setLayout('admin');

?>
<div class="container py-3">
    <h1 class="mb-3">Správa kníh</h1>

    <!-- Plain GET form: submit to front controller so router always receives c=book&a=manage -->
    <form id="bookSearchForm" method="get" action="<?= $link->url('book.manage') ?>" class="mb-3">
        <input type="hidden" name="c" value="book">
        <input type="hidden" name="a" value="manage">
        <div class="row g-2">
            <div class="col-auto">
                <input type="search" name="q" class="form-control" placeholder="Hľadať podľa názvu knihy" value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <select name="category" class="form-select">
                    <option value="">Všetky kategórie</option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= isset($filters['category']) && $filters['category'] == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="genre" class="form-select">
                    <option value="">Všetky žánre</option>
                    <?php foreach ($genres as $id => $name): ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= isset($filters['genre']) && $filters['genre'] == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" id="bookPageInput" name="page" value="<?= htmlspecialchars($filters['page'] ?? 1) ?>">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Hľadať</button>
            </div>
        </div>
    </form>

    <div class="mb-2">
        <a class="btn btn-primary" href="<?= $link->url('book.add') ?>">
            <i class="bi bi-plus-circle"></i> Pridať knihu
        </a>
    </div>
    <div id="manageFeedback" class="alert d-none"></div>

    <?php if (empty($books)): ?>
        <p>Žiadne knihy.</p>
    <?php else: ?>

    <div id="booksListContainer">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
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
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('book.add', ['id' => $cid]) ?>">Upraviť</a>
                                <a class="btn btn-sm btn-outline-info" href="<?= $link->url('bookcopy.index', ['book_id' => $cid]) ?>">Spravovať kópie</a>
                                <form method="post" action="<?= $link->url('book.delete', ['id' => $cid]) ?>" class="m-0" onsubmit="return confirm('Naozaj chcete zmazať túto knihu?');">
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


<?php
if (isset($view) && method_exists($view, 'setLayout')) {
    $view->setLayout('admin');
}
/** @var array $authors */
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $filters */
?>

<div class="container">
    <h1>Správa autorov</h1>

    <!-- Search form -->
    <form method="get" action="<?= $link->url('author.manage') ?>" class="row g-2 mb-3">
        <input type="hidden" name="c" value="author">
        <input type="hidden" name="a" value="manage">
        <div class="col-12 col-md-6">
            <label for="authorManageSearch" class="visually-hidden">Hľadať meno alebo priezvisko</label>
            <input id="authorManageSearch" type="search" name="q" class="form-control"
                   placeholder="Hľadať meno alebo priezvisko" value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-primary w-100 w-md-auto">Hľadať</button>
        </div>
    </form>

    <div class="mb-3">
        <a class="btn btn-primary" href="<?= $link->url('author.add') ?>">Pridať autora</a>
    </div>

    <?php if (empty($authors)): ?>
        <p>Žiadni autori.</p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="d-none d-sm-table-cell">ID</th>
                    <th>Meno</th>
                    <th>Priezvisko</th>
                    <th class="d-none d-md-table-cell">Rok narodenia</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $a): ?>
                    <tr>
                        <td class="d-none d-sm-table-cell"><?= htmlspecialchars((string)$a->getId()) ?></td>
                        <td><?= htmlspecialchars($a->getFirstName()) ?></td>
                        <td><?= htmlspecialchars($a->getLastName()) ?></td>
                        <?php $by = $a->getBirthYear(); ?>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($by ?: 'Neznáme') ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('author.index') ?>">Zobraziť</a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('author.add', ['id' => $a->getId()]) ?>">Upraviť</a>

                                <form method="post" action="<?= $link->url('author.delete') ?>"
                                      onsubmit="return confirm('Naozaj chcete zmazať tohto autora?');"
                                    class="d-inline-block author-delete-form">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($a->getId()) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>


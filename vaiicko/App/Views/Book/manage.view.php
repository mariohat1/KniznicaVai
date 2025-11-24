<?php
/** @var array $books */
/** @var array $copies */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="container">
    <h1>Správa kníh</h1>
    <div class="mb-3">
        <a class="btn btn-primary" href="<?= $link->url('book.add') ?>">Pridať knihu</a>
    </div>
    <?php if (empty($books)): ?>
        <p>Žiadne knihy.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Názov</th>
                    <th>ISBN</th>
                    <th>Rok</th>
                    <th>Kópie (celk./dost.)</th>
                    <th>Upraviť počet kópií</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <?php $cid = (int)$b->getId(); $meta = $copies[$cid] ?? ['total'=>0,'available'=>0]; ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$b->getId()) ?></td>
                        <td><?= htmlspecialchars($b->getTitle()) ?></td>
                        <td><?= htmlspecialchars($b->getIsbn()) ?></td>
                        <td><?= htmlspecialchars($b->getYearPublished()) ?></td>
                        <td>
                            <?= htmlspecialchars((string)$meta['total']) ?> / <?= htmlspecialchars((string)$meta['available']) ?>
                         </td>
                        <td>
                            <form method="post" action="<?= $link->url('bookcopy.updateCopies') ?>" class="d-flex">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$cid) ?>">
                                <input type="number" name="copies" aria-label="Počet kópií" min="0" value="<?= htmlspecialchars((string)$meta['total']) ?>" class="form-control form-control-sm me-2" style="width:96px">
                                <button class="btn btn-sm btn-success" type="submit">Uložiť</button>
                            </form>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('book.view', ['id' => $b->getId()]) ?>">Zobraziť</a>
                            <a class="btn btn-sm btn-outline-secondary" href="#">Upraviť</a>
                            <a class="btn btn-sm btn-outline-danger" href="#">Zmazať</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

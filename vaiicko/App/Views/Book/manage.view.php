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
    <div id="manageFeedback" style="display:none;" class="alert"></div>
    <?php if (empty($books)): ?>
        <p>Žiadne knihy.</p>
    <?php else: ?>
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
                        <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($b->getTitle()) ?>"><?= htmlspecialchars($b->getTitle()) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($b->getIsbn()) ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars((string)$meta['total']) ?> / <?= htmlspecialchars((string)$meta['available']) ?></td>
                    </tr>
                    <tr class="table-action-row">
                        <td colspan="4" class="pt-1 pb-1">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <form method="post" action="<?= $link->url('bookcopy.updateCopies') ?>" class="d-flex align-items-center m-0">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$cid) ?>">
                                    <input type="number" name="copies" min="0" class="form-control form-control-sm me-2"
                                           style="width:60px" value="<?= htmlspecialchars((string)$meta['total']) ?>"
                                           aria-label="Počet kópií">
                                    <button class="btn btn-sm btn-success me-2" type="submit">Pridať kpie</button>
                                </form>
                                <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('book.view', ['id' => $cid]) ?>">Zobraziť</a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('book.add', ['id' => $cid]) ?>">Upraviť</a>
                                <form method="post" action="<?= $link->url('book.delete', ['id' => $cid]) ?>" class="m-0 ajax-delete-book">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Zmazať</button>
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
<script src="<?= $link->asset('js/bookManage.js') ?>"></script>

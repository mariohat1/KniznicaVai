<?php
if (isset($view) && method_exists($view, 'setLayout')) {
    $view->setLayout('admin');
}
/** @var array $copies */
/** @var \App\Models\Book $book */
/** @var array $reservations */
/** @var array $users */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="container">
    <h1>Kópie knihy: <?= htmlspecialchars((string)$book->getTitle()) ?></h1>
    <div class="mb-3">
        <a class="btn btn-outline-secondary" href="<?= $link->url('book.manage') ?>">Späť na správu kníh</a>
    </div>

    <!-- Add copies form -->
    <div class="mb-3">
        <form method="post" action="<?= $link->url('bookcopy.updateCopies') ?>" class="d-inline-block">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$book->getId()) ?>">
            <div class="input-group" style="max-width:220px;">
                <input type="number" name="copies" min="1" class="form-control form-control-sm" value="1" aria-label="Pridať kópie">
                <button class="btn btn-sm btn-success" type="submit">Pridať kópie</button>
            </div>
        </form>
    </div>

    <?php if (empty($copies)): ?>
        <p>Žiadne kópie.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Stav</th>
                        <th>Akcie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($copies as $c): ?>
                        <?php $id = (int)$c->getId(); $avail = $c->getAvailable() ? 1 : 0; $res = $reservations[$id] ?? null; ?>
                        <tr>
                            <td>
                                <?php if ($res !== null && $res->getIsReserved()): ?>
                                    <?php $uid = $res->getUserId(); $user = $users[$uid] ?? null; ?>
                                    <div><span class="badge bg-warning text-dark">Rezervovaná</span></div>
                                    <div class="small text-black">Pre používateľa:<?= htmlspecialchars($user->getUsername()); ?></div>
                                    <div class="small text-muted">Do: <?php echo htmlspecialchars((string)date('Y-m-d', strtotime(($res->getCreatedAt() ?? '') . ' +7 days'))); ?></div>
                                <?php else: ?>
                                    <?= $avail ? '<span class="badge bg-success">Dostupná</span>' : '<span class="badge bg-secondary">Nedostupná</span>' ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="<?= $link->url('bookcopy.setAvailability') ?>" class="d-inline-block me-2">
                                    <input type="hidden" name="copy_id" value="<?= htmlspecialchars((string)$id) ?>">
                                    <input type="hidden" name="book_id" value="<?= htmlspecialchars((string)$book->getId()) ?>">
                                    <input type="hidden" name="available" value="<?= $avail ? '0' : '1' ?>">
                                    <button class="btn btn-sm <?= $avail ? 'btn-outline-warning' : 'btn-outline-success' ?>" type="submit"><?php echo $avail ? 'Označiť nedostupné' : 'Označiť dostupné'; ?></button>
                                </form>
                                <form method="post" action="<?= $link->url('bookcopy.delete') ?>" class="d-inline-block">
                                    <input type="hidden" name="copy_id" value="<?= htmlspecialchars((string)$id) ?>">
                                    <input type="hidden" name="book_id" value="<?= htmlspecialchars((string)$book->getId()) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Zmazať túto kópiu?')">Zmazať kópiu</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>


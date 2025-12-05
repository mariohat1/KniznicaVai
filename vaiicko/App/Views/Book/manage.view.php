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
                <?php $cid = (int)$b->getId();
                $meta = $copies[$cid] ?? ['total' => 0, 'available' => 0]; ?>
                <tr>
                    <td><?= htmlspecialchars((string)$b->getId()) ?></td>
                    <td><?= htmlspecialchars($b->getTitle()) ?></td>
                    <td><?= htmlspecialchars($b->getIsbn()) ?></td>
                    <td><?= htmlspecialchars($b->getYearPublished()) ?></td>
                    <td>
                        <?= htmlspecialchars((string)$meta['total']) ?>
                        / <?= htmlspecialchars((string)$meta['available']) ?>
                    </td>
                    <td>
                        <form method="post" action="<?= $link->url('bookcopy.updateCopies') ?>" class="d-flex">
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$cid) ?>">
                            <input type="number" name="copies" aria-label="Počet kópií" min="0"
                                   value="<?= htmlspecialchars((string)$meta['total']) ?>"
                                   class="form-control form-control-sm me-2" style="width:96px">
                            <button class="btn btn-sm btn-success" type="submit">Uložiť</button>
                        </form>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary"
                           href="<?= $link->url('book.view', ['id' => $b->getId()]) ?>">Zobraziť</a>
                        <a class="btn btn-sm btn-outline-secondary"
                           href="<?= $link->url('book.add', ['id' => $b->getId()]) ?>">Upraviť</a>
                        <form method="post" action="<?= $link->url('book.delete', ['id' => $b->getId()]) ?>"
                              style="display:inline;" class="ajax-delete-book">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Zmazať</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
    (function () {
        const feedback = document.getElementById('manageFeedback');

        function showFeedback(msg, type) {
            if (!feedback) return;
            feedback.textContent = msg || '';
            feedback.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-danger');
            feedback.style.display = 'block';
            if (type === 'success') {
                setTimeout(hideFeedback, 2500);
            }
        }

        function hideFeedback() {
            if (!feedback) return;
            feedback.style.display = 'none';
            feedback.textContent = '';
        }

        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (!form || !form.classList || !form.classList.contains('ajax-delete-book')) return;
            e.preventDefault();

            // confirm delete
            if (!confirm('Naozaj chcete zmazať túto knihu?')) return;
            hideFeedback();

            const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
            if (submitBtn) submitBtn.disabled = true;

            try {
                // use FormData to keep compatibility with backend
                const fd = new FormData(form);
                const resp = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });

                const contentType = resp.headers.get('content-type') || '';
                let data = null;
                if (contentType.includes('application/json')) {
                    data = await resp.json();
                } else {
                    // non-json (redirect/login page) -> treat as failure
                    const text = await resp.text();
                    showFeedback('Server odpovedal nečakaným obsahom.', 'error');
                    console.error('Delete unexpected response', text);
                    return;
                }

                if (data && data.success) {
                    const tr = form.closest('tr');
                    if (tr) tr.parentNode.removeChild(tr);
                    showFeedback('Kniha bola úspešne vymazaná.', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                } else {
                    showFeedback((data && (data.message || data.error)) || 'Vymazanie zlyhalo.', 'error');
                }
            } catch (err) {
                console.error('Delete failed', err);
                showFeedback('Chyba spojenia: ' + (err.message || err), 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    })();
</script>

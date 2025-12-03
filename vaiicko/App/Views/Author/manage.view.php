<?php
/** @var array $authors */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1>Správa autorov</h1>
    <div class="mb-3">
        <a class="btn btn-primary" href="<?= $link->url('author.add') ?>">Pridať autora</a>
    </div>

    <?php if (empty($authors)): ?>
        <p>Žiadni autori.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Meno</th>
                    <th>Priezvisko</th>
                    <th>Národnosť</th>
                    <th>Dátum narodenia</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$a->getId()) ?></td>
                        <td><?= htmlspecialchars($a->getFirstName()) ?></td>
                        <td><?= htmlspecialchars($a->getLastName()) ?></td>
                        <td><?= htmlspecialchars($a->getNationality()) ?></td>
                        <td><?= htmlspecialchars($a->getBirthDate()) ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('author.index') ?>">Zobraziť</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('author.add', ['id' => $a->getId()]) ?>">Upraviť</a>

                            <form method="post" action="<?= $link->url('author.delete') ?>" class="d-inline-block author-delete-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($a->getId()) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Zmazať</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.author-delete-form').forEach(function(f){
    f.addEventListener('submit', function(e){
      if (!confirm('Naozaj chcete zmazať tohto autora?')) {
        e.preventDefault();
      }
    });
  });
});
</script>

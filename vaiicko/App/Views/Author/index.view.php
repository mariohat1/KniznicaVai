<?php
/** @var array $authors */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Core\IAuthenticator $auth */
?>

<div class="container">
    <h1>Authors</h1>
    <?php
    // Determine whether the current user may add authors: require explicit role 'admin'
    $canAddAuthor = false;
    try {
        if ($auth?->isLogged()) {
            $u = $auth->getUser();
            if (is_object($u) && method_exists($u, 'getRole')) {
                $canAddAuthor = (strtolower((string)$u->getRole()) === 'admin');
            }
        }
    } catch (\Throwable $e) {
        $canAddAuthor = false;
    }
    ?>

    <?php if ($canAddAuthor): ?>
        <div class="mb-3">
            <a class="btn btn-primary" href="<?= $link->url('author.add') ?>">Add author</a>
        </div>
    <?php endif; ?>

    <?php if (empty($authors)): ?>
        <p>No authors found.</p>
    <?php else: ?>
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First name</th>
                    <th>Last name</th>
                    <th>Nationality</th>
                    <th>Birth date</th>
                    <?php if ($canAddAuthor): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a->getId()) ?></td>
                        <td><?= htmlspecialchars($a->getFirstName()) ?></td>
                        <td><?= htmlspecialchars($a->getLastName()) ?></td>
                        <td><?= htmlspecialchars($a->getNationality()) ?></td>
                        <td><?= htmlspecialchars($a->getBirthDate()) ?></td>
                        <?php if ($canAddAuthor): ?>
                        <td>
                            <!-- Edit link navigates to the add page with id param so it can prefill -->
                            <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('author.add', ['id' => $a->getId()]) ?>">Edit</a>

                            <form method="post" action="<?= $link->url('author.delete') ?>" class="d-inline-block author-delete-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($a->getId()) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
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
      if (!confirm('Are you sure you want to delete this author?')) {
        e.preventDefault();
      }
    });
  });
});
</script>

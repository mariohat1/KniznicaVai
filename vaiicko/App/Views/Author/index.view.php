<?php
/** @var array $authors */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Core\IAuthenticator $auth */

use App\Support\AuthView;
?>

<div class="container">
    <h1>Authors</h1>
    <?php
    // Determine whether the current user may add authors: require explicit role 'admin'
    $canAddAuthor = AuthView::canAddAuthor($auth);
    ?>

    <?php if ($canAddAuthor): ?>
        <div class="mb-3">
            <a class="btn btn-primary" href="<?= $link->url('author.add') ?>">Add author</a>
        </div>
    <?php endif; ?>

    <?php if (empty($authors)): ?>
        <p>No authors found.</p>
    <?php else: ?>
        <div class="table-responsive"> <!-- allows horizontal scroll on small screens -->
        <table class="table table-striped table-sm mt-3 mb-3 authors-table">
            <thead>
                <tr>
                    <th scope="col">First name</th>
                    <th scope="col">Last name</th>
                    <th scope="col">Nationality</th>
                    <th scope="col">Birth date</th>
                    <?php if ($canAddAuthor): ?><th scope="col" class="d-none d-sm-table-cell">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $a): ?>
                    <tr>
                        <td data-label="First name"><?= htmlspecialchars($a->getFirstName()) ?></td>
                        <td data-label="Last name"><?= htmlspecialchars($a->getLastName()) ?></td>
                        <td data-label="Nationality"><?= htmlspecialchars($a->getNationality()) ?></td>
                        <td data-label="Birth date"><?= htmlspecialchars($a->getBirthDate()) ?></td>
                        <?php if ($canAddAuthor): ?>
                        <td class="d-none d-sm-table-cell">
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
        </div>
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

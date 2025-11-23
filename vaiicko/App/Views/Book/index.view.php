<?php
/** @var array $books */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */

use App\Support\AuthView;

?>
<div class="container">
    <h1>Books</h1>
    <?php
    $canAddAuthor = AuthView::canAddAuthor($auth);
    ?>
    <?php if ($canAddAuthor): ?>
        <div class="mb-3">
            <a class="btn btn-primary" href="<?= $link->url('book.add') ?>">Add book</a>
        </div>
    <?php endif; ?>


    <?php if (empty($books)): ?>
        <p>No books found</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>ISBN</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b->getTitle()) ?></td>
                        <td><?= htmlspecialchars($b->getIsbn()) ?></td>
                        <td><?= htmlspecialchars($b->getYearPublished()) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

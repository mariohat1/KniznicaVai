<?php
/** @var array $authors */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Core\IAuthenticator $auth */
?>

<div class="container">
    <h1>Authors</h1>
    <?php if ($auth?->isLogged()): ?>
        <div class="mb-3">
            <a class="btn btn-primary" href="<?= $link->url('author.add') ?>">Add author</a>
        </div>
    <?php endif; ?>

    <?php if (empty($authors)): ?>
        <p>No authors found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First name</th>
                    <th>Last name</th>
                    <th>Nationality</th>
                    <th>Birth date</th>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

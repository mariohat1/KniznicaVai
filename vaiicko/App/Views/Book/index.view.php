<?php
/** @var array $books */
?>
<div class="container">
    <h1>Books</h1>
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
                        <td><?= htmlspecialchars($b->id) ?></td>
                        <td><?= htmlspecialchars($b->title) ?></td>
                        <td><?= htmlspecialchars($b->isbn) ?></td>
                        <td><?= htmlspecialchars($b->year_published) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


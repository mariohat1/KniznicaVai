<?php
/** @var \App\Models\Book $book */
/** @var \App\Models\Author|null $author */
/** @var \App\Models\Category|null $category */
/** @var \App\Models\Genre|null $genre */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <div class="row">
        <div class="col-md-3 text-center">
            <img src="<?= $link->asset('images/vaiicko_logo.png') ?>" alt="cover" class="img-fluid img-thumbnail mb-3" style="max-height:300px; object-fit:cover;">
            <div class="d-grid gap-2">
                <form method="post" action="<?= $link->url('reservation.create', ['id' => $book->getId()]) ?>">
                    <button class="btn btn-primary" type="submit">Získať</button>
                </form>
                <a class="btn btn-outline-secondary" href="<?= $link->url('book.index') ?>">Späť na zoznam</a>
            </div>
        </div>
        <div class="col-md-9">
            <h1><?= htmlspecialchars($book->getTitle()) ?></h1>
            <p class="text-muted mb-1">Autor: <?= $author ? htmlspecialchars($author->getFirstName() . ' ' . $author->getLastName()) : 'Neznámy' ?></p>
            <p class="text-muted small mb-2">
                <?= $category ? 'Kategória: ' . htmlspecialchars($category->getName()) . ' | ' : '' ?>
                <?= $genre ? 'Žáner: ' . htmlspecialchars($genre->getName()) . ' | ' : '' ?>
                <strong>ISBN:</strong> <?= htmlspecialchars((string)$book->getIsbn()) ?>
                <span class="mx-1">|</span>
                <strong>Rok:</strong> <?= htmlspecialchars((string)$book->getYearPublished()) ?>
            </p>

            <hr>

            <div class="book-description">
                <p><?= nl2br(htmlspecialchars((string)$book->getDescription())) ?></p>
            </div>
        </div>
    </div>
</div>

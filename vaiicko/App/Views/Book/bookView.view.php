<?php
/** @var \App\Models\Book $book */
/** @var \App\Models\Author|null $author */
/** @var \App\Models\Category|null $category */
/** @var \App\Models\Genre|null $genre */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var mixed $reserved */
/** @var mixed $reservedCopy */
/** @var mixed $must_login */
?>

<div class="container">

    <?php if (isset($reserved) && $reserved == 1): ?>
        <div class="alert alert-success">Rezervácia prebehla úspešne.</div>
    <?php endif; ?>


    <div class="row">
        <div class="col-md-3 text-center">
            <img src="<?= $link->asset('images/vaiicko_logo.png') ?>" alt="cover" class="img-fluid img-thumbnail mb-3" style="max-height:300px; object-fit:cover;">
            <div class="d-grid gap-2">
                <?php if ($auth?->isLogged()): ?>
                    <form method="post" action="<?= $link->url('reservation.create', ['id' => $book->getId()]) ?>">
                        <button class="btn btn-primary" type="submit">Rezervovať</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Získať</button>
                    <div class="small text-muted mt-2">Pre rezerváciu sa musíš najprv prihlásiť.</div>
                <?php endif; ?>
                <a class="btn btn-outline-secondary" href="<?= $link->url('book.index') ?>">Späť na zoznam</a>
            </div>
        </div>
        <div class="col-md-9">
            <h1 class="book-title"><?= htmlspecialchars($book->getTitle()) ?></h1>
            <p class="text-muted mb-1">Autor: <span class="author-link"><?= $author ? htmlspecialchars($author->getFirstName() . ' ' . $author->getLastName()) : 'Neznámy' ?></span></p>
            <p class="text-muted small mb-2">
                <?php if ($category): ?><span class="category-label">Kategória: <?= htmlspecialchars($category->getName()) ?></span><?php endif; ?>
                <?php if ($genre): ?><span class="category-label">Žáner: <?= htmlspecialchars($genre->getName()) ?></span><?php endif; ?>
                <span class="meta-label"><strong>ISBN:</strong> <?= htmlspecialchars((string)$book->getIsbn()) ?></span>
                <span class="mx-1">|</span>
                <span class="meta-label"><strong>Rok:</strong> <?= htmlspecialchars((string)$book->getYearPublished()) ?></span>
            </p>

            <hr>

            <div class="book-description">
                <p><?= nl2br(htmlspecialchars((string)$book->getDescription())) ?></p>
            </div>
        </div>
    </div>
</div>

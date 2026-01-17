<?php
/** @var \App\Models\Book $book */
/** @var \App\Models\Author|null $author */
/** @var \App\Models\Category|null $category */
/** @var \App\Models\Genre|null $genre */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var mixed $reservedSuccess */
/** @var mixed $available */
/** @var mixed $reserved */
/** @var mixed $copies */
?>

<div class="container">

    <?php if (($reservedSuccess ?? 0) === 1): ?>
        <div class="alert alert-success">Rezervácia prebehla úspešne.</div>
    <?php endif; ?>


    <div class="row">
        <div class="col-md-3 text-center">
            <div class="mb-3">
                <?php
                $available = isset($available) ? (int)$available : 0;
                $reserved = isset($reserved) ? (int)$reserved : 0;
                $photo = $book->getPhoto();
                if ($photo && trim((string)$photo) !== ''): ?>
                    <div class="book-cover-box">
                        <img src="<?= htmlspecialchars($photo) ?>" alt="cover" class="book-cover-img">
                    </div>
                <?php else: ?>
                    <div class="book-cover-box">
                        <svg xmlns="http://www.w3.org/2000/svg" class="book-placeholder-svg" viewBox="0 0 16 16"
                             fill="currentColor" aria-hidden="true">
                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-grid gap-2">
                <a class="btn btn-outline-secondary" href="<?= $link->url('book.index') ?>">Späť na zoznam</a>
                <?php if ($auth?->isLogged()): ?>
                    <form method="post" action="<?= $link->url('reservation.create', ['id' => $book->getId()]) ?>">
                        <button class="btn btn-success w-100" type="submit" <?= $available <= 0 ? 'disabled' : '' ?>>
                            Získať
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-success w-100"
                            type="button" <?= $available <= 0 ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' ?>>
                        Získať
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-9">
            <h1 class="book-title"><?= htmlspecialchars($book->getTitle()) ?></h1>

            <p class="text-muted mb-1">Autor:
                <?php if ($author): ?>
                    <a href="<?= $link->url('author.view', ['id' => $author->getId()]) ?>" class="author-link">
                        <?= htmlspecialchars($author->getFirstName() . ' ' . $author->getLastName()) ?>
                    </a>
                <?php else: ?>
                    <span class="author-link">Neznámy</span>
                <?php endif; ?>
            </p>
            <p class="text-muted small mb-2">
                <?php if ($category): ?><span class="category-label">
                    Kategória: <?= htmlspecialchars($category->getName()) ?></span><?php endif; ?>
                <?php if ($genre): ?><span class="category-label">
                    Žáner: <?= htmlspecialchars($genre->getName()) ?></span><?php endif; ?>

                <span>
                    <?php if ((($available + $reserved) ?? 0) <= 0): ?>
                        <span class="badge bg-secondary">Bez kópií</span>
                    <?php elseif ((($available ?? 0) <= 0)): ?>
                        <span class="badge bg-danger">Všetky požičané</span>
                    <?php else: ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success category-label">
                            <i class="bi bi-check-circle me-1"></i> Dostupné
                            <span class="ms-2 small text-dark"><?= htmlspecialchars((string)($available ?? 0)) ?> / <?= htmlspecialchars((string)($available + $reserved)) ?></span>
                        </span>
                    <?php endif; ?>
                </span>

                <span class="meta-label">
                    <strong>ISBN:</strong> <?= htmlspecialchars((string)$book->getIsbn()) ?>
                </span>
                <span class="mx-1">|</span>
                <span class="meta-label"><strong>Rok:</strong> <?= htmlspecialchars((string)$book->getYearPublished()) ?></span>
                <?php $publisher = trim((string)$book->getPublisher()); if (!empty($publisher)): ?>
                    <span class="mx-1">|</span>
                    <span class="meta-label"><strong>Vydavateľ:</strong> <?= htmlspecialchars($publisher) ?></span>
                <?php endif; ?>
            </p>

            <hr>

            <div>
                <p><?= nl2br(htmlspecialchars((string)$book->getDescription())) ?></p>
            </div>
        </div>
    </div>
</div>

<script src="<?= $link->asset('js/bookView.js') ?>"></script>

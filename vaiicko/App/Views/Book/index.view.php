<?php
/** @var array $books */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $copies */
?>

<div class="container">
    <h1 class="mb-4">Knihy</h1>

    <?php if (empty($books)): ?>
        <div class="alert alert-info">Žiadne knihy nenájdené.</div>
    <?php else: ?>

        <div class="list-group list-group-flush">
            <?php foreach ($books as $b): ?>
                <?php $bid = (int)$b->getId();
                $meta = $copies[$bid] ?? ['total' => 0, 'available' => 0]; ?>
                <div class="list-group-item border-bottom py-3">
                    <div class="row g-3 align-items-stretch">

                        <div class="col-12 col-md-2 col-lg-1 text-center">
                            <div class="bg-light d-flex align-items-center justify-content-center border rounded"
                                 style="height: 100px; width: 100%; min-width: 80px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                     class="text-secondary bi bi-book" viewBox="0 0 16 16">
                                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8">
                            <h4 class="mb-1">
                                <a href="<?= $link->url('book.view', ['id' => $b->getId()]) ?>"
                                   class="text-decoration-none text-primary">
                                    <?= htmlspecialchars((string)$b->getTitle() ?: 'Bez názvu') ?>
                                </a>
                            </h4>

                            <p class="mb-1 text-muted">
                                <small>
                                    <strong>ISBN:</strong> <?= htmlspecialchars((string)$b->getIsbn()) ?>
                                    <span class="mx-1">|</span>
                                    <strong>Rok:</strong> <?= htmlspecialchars((string)$b->getYearPublished()) ?>
                                </small>
                            </p>

                            <p class="mb-2 text-secondary">
                                <?= htmlspecialchars(strlen((string)$b->getDescription()) > 200 ? substr((string)$b->getDescription(), 0, 200) . '...' : (string)$b->getDescription()) ?>
                            </p>

                            <div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    <i class="bi bi-check-circle me-1"></i> Dostupné
                                    <span class="ms-2 small text-dark">(<?= htmlspecialchars((string)$meta['available']) ?> /
                                        <?= htmlspecialchars((string)$meta['total']) ?>)</span>
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-2 d-flex flex-md-column">
                            <!-- show POST form only for logged-in users; guests get login modal button -->
                            <?php if ($auth?->isLogged()): ?>
                                <form method="post" action="<?= $link->url('reservation.create') ?>" class="d-flex w-100 justify-content-end">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$b->getId()) ?>">
                                    <button class="btn btn-primary mt-auto align-self-end me-3" style="padding:0.5rem 1rem;" type="submit">Získať</button>
                                </form>
                            <?php else: ?>
                                <div class="d-flex w-100 justify-content-end">
                                    <button class="btn btn-primary mt-auto align-self-end me-3" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Získať</button>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
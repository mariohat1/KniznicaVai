<?php
/** @var \App\Models\Author $author */
/** @var array $books */
/** @var array $copies */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="container py-4">
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-white shadow-sm rounded p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-md-4 col-lg-3 text-center">
                        <?php if (method_exists($author, 'getPhoto') && $author->getPhoto()): ?>
                            <div class="author-avatar-wrapper">
                                <img src="<?= htmlspecialchars($author->getPhoto()) ?>"
                                     alt="<?= htmlspecialchars(trim($author->getFirstName() . ' ' . $author->getLastName())) ?>"
                                     class="avatar-author">
                            </div>
                        <?php else: ?>
                            <div class="author-avatar-wrapper author-avatar-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" fill="currentColor"
                                     class="text-secondary bi bi-person" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                    <path fill-rule="evenodd" d="M14 14s-1-4-6-4-6 4-6 4 1 0 6 0 6 0 6 0z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Author Info Column -->
                    <div class="col-12 col-md-8 col-lg-9">
                        <h1 class="mb-3 fw-bold" style="line-height:1.2; font-size:clamp(1.5rem, 5vw, 2.2rem);">
                            <?= htmlspecialchars(trim($author->getFirstName() . ' ' . $author->getLastName())) ?>
                        </h1>

                        <div class="mb-3">
                            <?php $by = $author->getBirthYear(); $dy = $author->getDeathYear(); ?>
                            <p class="mb-1 text-muted">
                                <strong>Narodenie / Úmrtie:</strong>
                                <span><?= htmlspecialchars(($by ?: 'Neznáme') . ' — ' . ($dy ?: 'Neznáme')) ?></span>
                            </p>
                        </div>

                        <!-- Description -->
                        <?php if (method_exists($author, 'getDescription') && $author->getDescription()): ?>
                            <div class="mt-4 p-3 bg-light border-start border-4 border-success rounded" style="text-align:justify; line-height:1.6; font-size:0.95rem;">
                                <?= nl2br(htmlspecialchars($author->getDescription())) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary"><em>Biografia nie je dostupná.</em></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Books Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="section-title">Knihy od tohto autora</h2>
        </div>
    </div>

    <?php $totalBooks = count($books); ?>
    <div class="row mb-3">
        <div class="col-12">
            <p class="text-muted mb-0">
                <small><strong>Celkom kníh:</strong> <span class="badge bg-success"><?= htmlspecialchars((string)$totalBooks) ?></span></small>
            </p>
        </div>
    </div>

    <?php if (empty($books)): ?>
        <div class="alert alert-info" role="alert">Tento autor nemá žiadne knihy v tejto knižnici.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($books as $b): ?>
                <?php $bid = (int)$b->getId();
                $meta = $copies[$bid] ?? ['total' => 0, 'available' => 0]; ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 position-relative border-0 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <?php $avail = (int)$meta['available'];
                            $tot = (int)$meta['total'];
                            $isAvail = $avail > 0; ?>

                            <!-- Title + badge -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2 mb-3">
                                <h5 class="mb-0 fw-bold" style="font-size:clamp(0.95rem, 3vw, 1.1rem); flex: 1;">
                                    <?= htmlspecialchars($b->getTitle()) ?>
                                </h5>
                                <div class="flex-shrink-0">
                                    <?php if ($isAvail): ?>
                                        <span class="badge border border-success text-success bg-white" style="padding:.35rem .6rem; white-space:nowrap;">
                                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
                                            <strong>Dostupné</strong>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge border border-secondary text-secondary bg-white" style="padding:.35rem .6rem; white-space:nowrap;">
                                            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
                                            <strong>Nedostupné</strong>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p class="mb-3 text-muted small">
                                <strong><?= htmlspecialchars((string)$avail) ?> / <?= htmlspecialchars((string)$tot) ?></strong> kópií dostupných
                            </p>

                            <a href="<?= $link->url('book.view', ['id' => $b->getId()]) ?>" class="stretched-link btn btn-sm btn-outline-primary mt-auto"
                               aria-label="Zobraziť <?= htmlspecialchars($b->getTitle()) ?>">
                                Zobraziť detaily
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
/** @var \App\Models\Author $author */
/** @var array $books */
/** @var array $copies */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="container author-page">
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-white shadow-sm rounded p-4 author-header">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <?php if (method_exists($author, 'getPhoto') && $author->getPhoto()): ?>
                            <img src="<?= htmlspecialchars($author->getPhoto()) ?>"
                                 alt="<?= htmlspecialchars(trim($author->getFirstName() . ' ' . $author->getLastName())) ?>"
                                 class="rounded" style="width:140px; height:140px; object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="width:140px; height:140px; margin:0 auto;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="currentColor"
                                     class="text-secondary bi bi-person" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                    <path fill-rule="evenodd" d="M14 14s-1-4-6-4-6 4-6 4 1 0 6 0 6 0 6 0z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-10">
                        <h2 class="mb-1 author-name fw-bold" style="line-height:1.1; font-size:1.6rem;">
                            <?= htmlspecialchars(trim($author->getFirstName() . ' ' . $author->getLastName())) ?>
                        </h2>

                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <?php $by = $author->getBirthYear(); ?>
                            <?php $dy = $author->getDeathYear(); ?>
                            <span>
                                Narodenie / Úmrtie: <?= htmlspecialchars(($by ?: 'Neznáme') . ' - ' . ($dy ?: 'Neznáme')) ?>
                            </span>
                        </div>

                        <?php if (method_exists($author, 'getDescription') && $author->getDescription()): ?>
                            <div class="mt-2 p-3 bg-light border rounded text-muted" style="text-align:justify; line-height:1.5;">
                                <?= nl2br(htmlspecialchars($author->getDescription())) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary mb-0">Biografia nie je dostupná.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Knihy od tohto autora</h3>
        </div>
    </div>

    <?php
    $totalBooks = count($books);
    ?>
    <div class="row mb-3">
        <div class="col-12">
            <small class="text-muted">Celkom kníh: <strong><?= htmlspecialchars((string)$totalBooks) ?></strong></small>
        </div>
    </div>

    <?php if (empty($books)): ?>
        <div class="alert alert-info">Tento autor nemá žiadne knihy.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($books as $b): ?>
                <?php $bid = (int)$b->getId();
                $meta = $copies[$bid] ?? ['total' => 0, 'available' => 0]; ?>
                <div class="col-12">
                    <div class="card h-100 position-relative">
                        <?php if (method_exists($b, 'getPhoto') && $b->getPhoto()): ?>
                            <img src="<?= htmlspecialchars($b->getPhoto()) ?>" class="card-img-top"
                                 alt="<?= htmlspecialchars($b->getTitle()) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <?php $avail = (int)$meta['available'];
                            $tot = (int)$meta['total'];
                            $isAvail = $avail > 0; ?>

                            <!-- Responsive container: column on small, row (title left / badge right) on md+ -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-2">
                                <h5 class="mb-2 mb-md-0 fw-bold" style="font-size:1.125rem;">
                                    <?= htmlspecialchars($b->getTitle()) ?>
                                </h5>
                                <div class="mt-2 mt-md-0 ms-md-3">
                                    <?php if ($isAvail): ?>
                                        <span class="badge border border-success text-success bg-white" style="padding:.35rem .6rem;">
                                            <i class="bi bi-check-circle me-1"></i>
                                            <strong>Dostupné</strong>
                                            <span class="ms-2 small text-dark"><?= htmlspecialchars((string)$avail) ?> / <?= htmlspecialchars((string)$tot) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge border border-secondary text-secondary bg-white" style="padding:.35rem .6rem;">
                                            <i class="bi bi-x-circle me-1"></i>
                                            <strong>Nedostupné</strong>
                                            <span class="ms-2 small text-dark"><?= htmlspecialchars((string)$avail) ?> / <?= htmlspecialchars((string)$tot) ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a href="<?= $link->url('book.view', ['id' => $b->getId()]) ?>" class="stretched-link" aria-label="Zobraziť <?= htmlspecialchars($b->getTitle()) ?>"></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

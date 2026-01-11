<?php
/** @var array $authors */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1 class="mb-4 section-title">Autori</h1>

    <?php if (empty($authors)): ?>
        <div class="alert alert-info">Žiadni autori.</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($authors as $a): ?>
                <div class="list-group-item border-bottom py-3">
                    <div class="row g-3 align-items-stretch">

                        <div class="col-12 col-md-2 col-lg-1 text-center">
                            <?php $photo = $a->getPhoto(); ?>
                            <?php if (!empty($photo)): ?>
                                <div class="bg-light d-flex align-items-center justify-content-center border rounded" style="height:100px; width:100%; min-width:80px; overflow:hidden;">
                                    <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($a->getFirstName() . ' ' . $a->getLastName()) ?>" class="avatar-author" style="width:100%; height:100%; object-fit:cover; object-position:center; display:block;">
                                </div>
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center border rounded" style="height:100px; width:100%; min-width:80px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="text-secondary bi bi-person" viewBox="0 0 16 16" style="flex:0 0 auto;">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                        <path fill-rule="evenodd" d="M14 14s-1-4-6-4-6 4-6 4 1 0 6 0 6 0 6 0z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-lg-8">
                            <h4 class="mb-1">
                                <a href="<?= $link->url('author.view', ['id' => $a->getId()]) ?>" class="text-decoration-none text-primary author-link">
                                    <?= htmlspecialchars(trim($a->getFirstName() . ' ' . $a->getLastName()) ?: 'Bez mena') ?>
                                </a>
                            </h4>

                            <p class="mb-1 text-muted"><small>
                                <?php if (method_exists($a, 'getBirthDate') && $a->getBirthDate()): ?>
                                    <strong>Dátum narodenia:</strong> <?= htmlspecialchars((string)$a->getBirthDate()) ?>
                                <?php endif; ?>
                            </small></p>

                            <?php // no description field on author model; show placeholder or nothing ?>
                            <p class="mb-2 text-secondary">&nbsp;</p>

                        </div>


                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

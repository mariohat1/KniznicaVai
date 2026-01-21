<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Author|null $author */
/** @var \Framework\Support\View $view */
$view->setLayout('admin');
?>

<div class="container">
    <h1><?= isset($author) ? 'Upraviť autora' : 'Pridať autora' ?></h1>
    <div id="authorFormFeedback" aria-live="polite"></div>
    <form id="authorForm" method="post" action="<?= $link->url('author.store') ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= isset($author) ? htmlspecialchars((string)$author->getId()) : '' ?>">
        <div class="mb-3">
            <label for="first_name" class="form-label">Krstné meno</label>
            <input id="first_name" type="text" name="first_name" class="form-control" required aria-required="true"
                   value="<?= isset($author) ? htmlspecialchars((string)$author->getFirstName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Priezvisko</label>
            <input id="last_name" type="text" name="last_name" class="form-control" required aria-required="true"
                   value="<?= isset($author) ? htmlspecialchars((string)$author->getLastName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Biografia</label>
            <textarea id="description" name="description" class="form-control"
                      rows="5"><?= isset($author) ? htmlspecialchars((string)$author->getDescription()) : '' ?></textarea>
        </div>
        <div class="mb-3">
            <label for="birth_year" class="form-label">Rok narodenia</label>
            <input id="birth_year" name="birth_year" type="number" class="form-control"
                   max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars(isset($author) ? ($author->getBirthYear() ?? '') : '') ?>">
        </div>
        <div class="mb-3">
            <label for="death_year" class="form-label">Rok úmrtia</label>
            <input id="death_year" name="death_year" type="number" class="form-control"
                    max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars(isset($author) ? ($author->getDeathYear() ?? '') : '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Fotka autora (PNG alebo JPEG)</label>
            <input type="file" name="photo" accept="image/png,image/jpeg" class="form-control">
            <small class="form-text text-muted">Max 5 MB. Voliteľné.</small>
        </div>
        <button class="btn btn-primary"><?= isset($author) ? 'Upraviť' : 'Uložiť' ?></button>
        <a href="<?= $link->url('author.manage') ?>" class="btn btn-link">Zrušiť</a>
    </form>
</div>

<script src="<?= $link->asset('js/authorAdd.js') ?>"></script>

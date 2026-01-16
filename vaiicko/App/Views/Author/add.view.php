<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Author|null $author */
/** @var \Framework\Support\View $view */
$view->setLayout('admin');
?>

<div class="container">
    <h1><?= isset($author) ? 'Edit author' : 'Add author' ?></h1>
    <div id="authorFormFeedback" aria-live="polite"></div>
    <form id="authorForm" method="post" action="<?= $link->url('author.store') ?>" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?= isset($author) ? htmlspecialchars((string)$author->getId()) : '' ?>">
        <div class="mb-3">
            <label for="first_name" class="form-label">First name</label>
            <input id="first_name" type="text" name="first_name" class="form-control" required
                   value="<?= isset($author) ? htmlspecialchars((string)$author->getFirstName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last name</label>
            <input id="last_name" type="text" name="last_name" class="form-control" required
                   value="<?= isset($author) ? htmlspecialchars((string)$author->getLastName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Biografia</label>
            <textarea id="description" name="description" class="form-control"
                      rows="5"><?= isset($author) ? htmlspecialchars((string)$author->getDescription()) : '' ?></textarea>
        </div>
        <div class="mb-3">
            <label for="birth_year" class="form-label">Birth year</label>
            <input id="birth_year" name="birth_year" type="number" class="form-control"
                   min="1000" max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars(isset($author) ? ($author->getBirthYear() ?? '') : '') ?>">
        </div>
        <div class="mb-3">
            <label for="death_year" class="form-label">Death year</label>
            <input id="death_year" name="death_year" type="number" class="form-control"
                   min="1000" max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars(isset($author) ? ($author->getDeathYear() ?? '') : '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Fotka autora (PNG)</label>
            <input type="file" name="photo" accept="image/png" class="form-control">
            <small class="form-text text-muted">Max 5 MB. Voliteľný.</small>
        </div>
        <button class="btn btn-primary"><?= isset($author) ? 'Update' : 'Save' ?></button>
    </form>
</div>


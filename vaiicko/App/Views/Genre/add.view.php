<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Genre|null $genre */
if (!isset($genre)) $genre = null;
?>

<div class="container">
    <h1><?= isset($genre) ? 'Upraviť žáner' : 'Pridať žáner' ?></h1>
    <form method="post" action="<?= $link->url('genre.store') ?>">
        <input type="hidden" name="id" value="<?= isset($genre) ? htmlspecialchars((string)$genre->getId()) : '' ?>">
        <div class="mb-3">
            <label for="genre-name" class="form-label">Názov</label>
            <input id="genre-name" type="text" name="name" class="form-control" required value="<?= isset($genre) ? htmlspecialchars((string)$genre->getName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="genre-description" class="form-label">Popis</label>
            <textarea id="genre-description" name="description" class="form-control" rows="4"><?= isset($genre) ? htmlspecialchars((string)$genre->getDescription()) : '' ?></textarea>
        </div>
        <button class="btn btn-primary"><?= isset($genre) ? 'Upraviť' : 'Uložiť' ?></button>
    </form>
</div>


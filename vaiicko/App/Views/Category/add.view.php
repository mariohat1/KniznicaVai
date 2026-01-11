<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Category|null $category */
if (!isset($category)) $category = null;
?>

<div class="container">
    <h1><?= isset($category) ? 'Upraviť kategóriu' : 'Pridať kategóriu' ?></h1>
    <form method="post" action="<?= $link->url('category.store') ?>">
        <input type="hidden" name="id" value="<?= isset($category) ? htmlspecialchars((string)$category->getId()) : '' ?>">
        <div class="mb-3">
            <label for="category-name" class="form-label">Názov</label>
            <input id="category-name" type="text" name="name" class="form-control" required value="<?= isset($category) ? htmlspecialchars((string)$category->getName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="category-description" class="form-label">Popis</label>
            <textarea id="category-description" name="description" class="form-control" rows="4"><?= isset($category) ? htmlspecialchars((string)$category->getDescription()) : '' ?></textarea>
        </div>
        <button class="btn btn-primary"><?= isset($category) ? 'Upraviť' : 'Uložiť' ?></button>
    </form>
</div>


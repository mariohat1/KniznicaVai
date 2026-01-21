<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Category|null $category */
/** @var \Framework\Support\View $view */
$view->setLayout('admin');
?>
<div class="container">
    <h1><?= isset($category) ? 'Upraviť kategóriu' : 'Pridať kategóriu' ?></h1>
    <div id="categoryClientFeedback" aria-live="polite"></div>
    <form id="categoryForm" method="post" action="<?= $link->url('category.store') ?>">
        <input type="hidden" name="id" value="<?= isset($category) ? htmlspecialchars((string)$category->getId()) : '' ?>">
        <div class="mb-3">
            <label for="category-name" class="form-label">Názov</label>
            <input id="category-name" type="text" name="name" class="form-control" required value="<?= isset($category) ? htmlspecialchars((string)$category->getName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="category-description" class="form-label">Popis</label>
            <textarea id="category-description" name="description" class="form-control" rows="4"><?= isset($category) ? htmlspecialchars((string)$category->getDescription()) : '' ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= isset($category) ? 'Upraviť' : 'Uložiť' ?></button>
        <a href="<?= $link->url('category.manage') ?>" class="btn btn-link">Zrušiť</a>
    </form>
</div>

<script src="<?= $link->asset('js/categoryAdd.js') ?>"></script>

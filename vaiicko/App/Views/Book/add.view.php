<?php
/** @var \App\Models\Author[] $authors */
/** @var \App\Models\Category[] $categories */
/** @var \App\Models\Genre[] $genres */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="container">
    <h1>Add book</h1>
    <div id="bookAddRoot"
         data-category-url="<?= htmlspecialchars($link->url('category.store')) ?>"
         data-genre-url="<?= htmlspecialchars($link->url('genre.store')) ?>">
    </div>
    <div id="bookFormContainer">
        <form id="bookForm" method="post" action="<?= $link->url('book.store') ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input id="title" type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="isbn">ISBN</label>
                <input id="isbn" type="text" name="isbn" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label" for="year_published">Year published</label>
                <input id="year_published" type="date" name="year_published" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="author_id">Author</label>
                <select id="author_id" name="author_id" class="form-select">
                    <option value="">-- choose author --</option>
                    <?php foreach ($authors ?? [] as $a): ?>
                        <option value="<?= htmlspecialchars($a->getId()) ?>"><?= htmlspecialchars($a->getFirstName() . ' ' . $a->getLastName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">-- choose category --</option>
                    <?php foreach ($categories ?? [] as $c): ?>
                        <option value="<?= htmlspecialchars($c->getId()) ?>"><?= htmlspecialchars($c->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Add new category below.</small>
                <div class="input-group mt-2">
                    <input type="text" id="new_category_name" aria-label="New category name" class="form-control" placeholder="New category name">
                    <button type="button" id="createCategoryBtn" class="btn btn-outline-secondary">Add</button>
                </div>
                <div id="categoryFeedback" class="form-text text-danger mt-1" style="display:none"></div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="genre_id">Genre</label>
                <select id="genre_id" name="genre_id" class="form-select">
                    <option value="">-- choose genre --</option>
                    <?php foreach ($genres ?? [] as $g): ?>
                        <option value="<?= htmlspecialchars($g->getId()) ?>"><?= htmlspecialchars($g->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Add new genre below.</small>
                <div class="input-group mt-2">
                    <input type="text" id="new_genre_name" aria-label="New genre name" class="form-control" placeholder="New genre name">
                    <button type="button" id="createGenreBtn" class="btn btn-outline-secondary">Add</button>
                </div>
                <div id="genreFeedback" class="form-text text-danger mt-1" style="display:none"></div>
            </div>

            <div class="mb-3 text-end">
                <a class="btn btn-secondary me-2" href="<?= $link->url('book.index') ?>">Cancel</a>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>

        <div id="bookFormResult" style="display:none" class="mt-3"></div>
    </div>
</div>

<script src="<?= $link->asset('js/bookAdd.js') ?>"></script>

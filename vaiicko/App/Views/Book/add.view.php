<?php
if (isset($view) && method_exists($view, 'setLayout')) {
    $view->setLayout('admin');
}
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Book|null $book */
/** @var array $authors */
/** @var array $categories */
/** @var array $genres */
?>

<div class="container">
    <h1 class="mb-4"><?= isset($book) ? 'Editovať knihu' : 'Pridať knihu' ?></h1>

    <div id="bookAddRoot" data-category-url="<?= $link->url('category.store') ?>" data-genre-url="<?= $link->url('genre.store') ?>" data-redirect-url="<?= $link->url('book.manage') ?>">
    <div id="bookFormFeedback" class="ajax-error"></div>
    <form method="post" action="<?= $link->url('book.store') ?>" enctype="multipart/form-data">
        <?php if (isset($book)): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars(isset($book) ? ($book->getId() ?? '') : '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="title" class="form-label">Názov</label>
            <input id="title" name="title" type="text" class="form-control" required
                   value="<?= htmlspecialchars(isset($book) ? ($book->getTitle() ?? '') : '') ?>">
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="isbn" class="form-label">ISBN</label>
                <input id="isbn" name="isbn" type="text" class="form-control"  required value="<?= htmlspecialchars(isset($book) ? ($book->getIsbn() ?? '') : '') ?>">
            </div>
            <div class="col-md-6">
                <label for="year_published" class="form-label">Rok vydania</label>
                <input id="year_published" name="year_published" type="number" class="form-control" required min="1000" max="<?= date('Y') ?>" step="1" value="<?= htmlspecialchars(isset($book) ? ($book->getYearPublished() ?? '') : '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="publisher" class="form-label">Vydavateľ</label>
            <input id="publisher" name="publisher" type="text" class="form-control" required maxlength="255" value="<?= htmlspecialchars(isset($book) ? ($book->getPublisher() ?? '') : '') ?>">
        </div>

        <div class="mb-3">
            <label for="author_id" class="form-label">Autor</label>
            <select id="author_id" name="author_id" class="form-select" required>
                <option value="">-- vybrať --</option>
                <?php foreach ($authors as $a): ?>
                    <?php $aid = $a->getId(); $selected = (isset($book) && ($book->getAuthorId() ?? '') == $aid) ? 'selected' : '';?>
                    <option value="<?= htmlspecialchars($aid) ?>" <?= $selected ?>><?= htmlspecialchars($a->getFirstName() . ' ' . $a->getLastName()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Kategória</label>
                <div class="input-group">
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">-- vybrať --</option>
                        <?php foreach ($categories as $c): ?>
                            <?php $cid = $c->getId(); $selected = (isset($book) && ($book->getCategoryId() ?? '') == $cid) ? 'selected' : '';?>
                            <option value="<?= htmlspecialchars($cid) ?>" <?= $selected ?>><?= htmlspecialchars($c->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-success" id="showCategoryAdd" data-bs-toggle="modal" data-bs-target="#categoryModal">Nová</button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="genre_id" class="form-label">Žáner</label>
                <div class="input-group">
                    <select id="genre_id" name="genre_id" class="form-select" required>
                        <option value="">-- vybrať --</option>
                        <?php foreach ($genres as $g): ?>
                            <?php $gid = $g->getId(); $selected = (isset($book) && ($book->getGenreId() ?? '') == $gid) ? 'selected' : '';?>
                            <option value="<?= htmlspecialchars($gid) ?>" <?= $selected ?>><?= htmlspecialchars($g->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-success" id="showGenreAdd" data-bs-toggle="modal" data-bs-target="#genreModal">Nový</button>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Popis</label>
            <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars(isset($book) ? ($book->getDescription() ?? '') : '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Obal knihy (PNG)</label>
            <input type="file" name="photo" accept="image/png" class="form-control">
            <small class="form-text text-muted">Max 5 MB. Voliteľný.</small>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary" type="submit"><?= isset($book) ? 'Uložiť zmeny' : 'Pridať knihu' ?></button>
            <a href="<?= $link->url('book.manage') ?>" class="btn btn-link">Zrušiť</a>
        </div>
    </form>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nová kategória</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryModalForm" action="<?= $link->url('category.store') ?>" method="post" novalidate>
                    <div class="modal-body">
                        <div id="categoryFeedback"></div>
                        <div class="mb-3">
                            <label for="new_category_name" class="form-label">Názov</label>
                            <input type="text" id="new_category_name" name="name" class="form-control" placeholder="Názov kategórie" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_category_description" class="form-label">Popis</label>
                            <textarea id="new_category_description" name="description" class="form-control" rows="3" placeholder="Popis kategórie"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                        <button type="submit" class="btn btn-primary">Uložiť</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Genre Modal -->
    <div id="genreModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nový žáner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="genreModalForm" action="<?= $link->url('genre.store') ?>" method="post" novalidate>
                    <div class="modal-body">
                        <div id="genreFeedback"></div>
                        <div class="mb-3">
                            <label for="new_genre_name" class="form-label">Názov</label>
                            <input type="text" id="new_genre_name" name="name" class="form-control" placeholder="Názov žánru" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_genre_description" class="form-label">Popis</label>
                            <textarea id="new_genre_description" name="description" class="form-control" rows="3" placeholder="Popis žánru"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                        <button type="submit" class="btn btn-primary">Uložiť</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= $link->asset('js/modalCategory.js') ?>"></script>
    <script src="<?= $link->asset('js/modalGenre.js') ?>"></script>
    <script src="<?= $link->asset('js/bookAdd.js') ?>"></script>
</div>

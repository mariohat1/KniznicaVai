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
    <div id="bookFormFeedback" class="ajax-error custom-error-box" style="display:none;"></div>
    <form method="post" action="<?= $link->url('book.store') ?>">
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
                <input id="year_published" name="year_published" type="date" class="form-control" required value="<?= htmlspecialchars(isset($book) ? ($book->getYearPublished() ?? '') : '') ?>">
            </div>
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
                    <button type="button" class="btn btn-outline-success" id="showCategoryAdd">Nová</button>
                </div>
                <div id="categoryAddContainer" style="display:none; margin-top:0.5em;">
                    <input type="text" id="new_category_name" class="form-control form-control-sm mb-1" placeholder="Názov novej kategórie">
                    <button type="button" id="createCategoryBtn" class="btn btn-sm btn-success">Pridať</button>
                    <div id="categoryFeedback" class="form-text text-danger" style="display:none;"></div>
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
                    <button type="button" class="btn btn-outline-success" id="showGenreAdd">Nový</button>
                </div>
                <div id="genreAddContainer" style="display:none; margin-top:0.5em;">
                    <input type="text" id="new_genre_name" class="form-control form-control-sm mb-1" placeholder="Názov nového žánru">
                    <button type="button" id="createGenreBtn" class="btn btn-sm btn-success">Pridať</button>
                    <div id="genreFeedback" class="form-text text-danger" style="display:none;"></div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Popis</label>
            <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars(isset($book) ? ($book->getDescription() ?? '') : '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Obal knihy (PNG) — potiahni sem alebo klikni</label>
            <div id="book-photo-drop" class="border rounded p-3 text-center" style="min-height:120px; display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <div id="book-photo-placeholder">Potiahni sem PNG alebo klikni pre výber súboru</div>
                <img id="book-photo-preview" src="<?= htmlspecialchars(isset($book) ? ($book->getPhoto() ?? '') : '') ?>" alt="" style="max-height:120px; display:none; margin:auto;">
            </div>
            <input id="book-photo-input" type="file" accept="image/png" style="display:none">
            <input type="hidden" name="photo_path" id="photo_path" value="<?= htmlspecialchars(isset($book) ? ($book->getPhoto() ?? '') : '') ?>">
            <div class="form-text">Max 5 MB. Použiť PNG pre najlepšiu kvalitu.</div>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary" type="submit"><?= isset($book) ? 'Uložiť zmeny' : 'Pridať knihu' ?></button>
            <a href="<?= $link->url('book.manage') ?>" class="btn btn-link">Zrušiť</a>
        </div>
    </form>
    </div>

    <script>
        (function(){
            var drop = document.getElementById('book-photo-drop');
            var input = document.getElementById('book-photo-input');
            var placeholder = document.getElementById('book-photo-placeholder');
            var preview = document.getElementById('book-photo-preview');
            var hidden = document.getElementById('photo_path');

            function showPreviewUrl(url){
                if(!url) return;
                preview.src = url;
                preview.style.display = '';
                placeholder.style.display = 'none';
            }

            if(hidden.value) showPreviewUrl(hidden.value);

            drop.addEventListener('click', function(){ input.click(); });
            drop.addEventListener('dragover', function(e){ e.preventDefault(); drop.classList.add('bg-light'); });
            drop.addEventListener('dragleave', function(){ drop.classList.remove('bg-light'); });
            drop.addEventListener('drop', function(e){ e.preventDefault(); drop.classList.remove('bg-light'); var f = (e.dataTransfer.files && e.dataTransfer.files[0]) || null; if(f) handleFile(f); });
            input.addEventListener('change', function(e){ var f = e.target.files && e.target.files[0]; if(f) handleFile(f); });

            async function handleFile(file){
                if(!file) return;
                if(file.type !== 'image/png') { alert('Only PNG allowed'); return; }
                if(file.size > 5*1024*1024) { alert('File too large'); return; }

                var fd = new FormData(); fd.append('photo', file);
                try{
                    var resp = await fetch('<?= $link->url('book.uploadPhoto') ?>', { method: 'POST', body: fd, headers: {'X-Requested-With':'XMLHttpRequest'}, credentials: 'same-origin' });
                    if(!resp.ok) { alert('Upload failed'); return; }
                    var json = await resp.json();
                    if(json && json.success && json.path){
                        hidden.value = json.path;
                        showPreviewUrl(json.path);
                    } else {
                        alert(json && json.message ? json.message : 'Upload failed');
                    }
                }catch(err){ console.error(err); alert('Upload error'); }
            }
        })();
    </script>
    <script src="<?= $link->asset('js/bookAdd.js') ?>"></script>
</div>

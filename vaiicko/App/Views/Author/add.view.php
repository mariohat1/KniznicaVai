<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Author|null $author */
/** @var \Framework\Support\View $view */
$view->setLayout('admin');
?>

<div class="container">
    <h1><?= isset($author) ? 'Edit author' : 'Add author' ?></h1>
    <div id="authorFormFeedback" aria-live="polite"></div>
    <form id="authorForm" method="post" action="<?= $link->url('author.store') ?>" novalidate>
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
            <label class="form-label">Fotka autora (PNG) — potiahni sem</label>
            <div id="author-photo-drop" class="border rounded p-3 text-center"
                 style="min-height:120px; display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <div id="author-photo-placeholder">Potiahni sem PNG alebo klikni pre vybratie súboru</div>
                <img id="author-photo-preview"
                     src="<?= isset($author) && method_exists($author, 'getPhoto') ? htmlspecialchars((string)$author->getPhoto()) : '' ?>"
                     alt="" style="max-height:100px; display:none; margin:auto;">
            </div>
            <input id="author-photo-input" type="file" accept="image/png" style="display:none">
            <input type="hidden" name="photo_path" id="photo_path"
                   value="<?= isset($author) && method_exists($author, 'getPhoto') ? htmlspecialchars((string)$author->getPhoto()) : '' ?>">
            <small class="form-text text-muted">Max 5 MB. Len PNG.</small>
        </div>
        <button class="btn btn-primary"><?= isset($author) ? 'Update' : 'Save' ?></button>
    </form>

    <script>
        (function () {
            var drop = document.getElementById('author-photo-drop');
            var input = document.getElementById('author-photo-input');
            var placeholder = document.getElementById('author-photo-placeholder');
            var preview = document.getElementById('author-photo-preview');
            var hidden = document.getElementById('photo_path');

            function showPreviewUrl(url) {
                if (!url) return;
                preview.src = url;
                preview.style.display = '';
                placeholder.style.display = 'none';
            }

            if (hidden && hidden.value) showPreviewUrl(hidden.value);

            drop.addEventListener('click', function () {
                input.click();
            });
            drop.addEventListener('dragover', function (e) {
                e.preventDefault();
                drop.classList.add('bg-light');
            });
            drop.addEventListener('dragleave', function () {
                drop.classList.remove('bg-light');
            });
            drop.addEventListener('drop', function (e) {
                e.preventDefault();
                drop.classList.remove('bg-light');
                var f = (e.dataTransfer.files && e.dataTransfer.files[0]) || null;
                if (f) handleFile(f);
            });
            input.addEventListener('change', function (e) {
                var f = e.target.files && e.target.files[0];
                if (f) handleFile(f);
            });

            async function handleFile(file) {
                if (!file) return;
                if (file.type !== 'image/png') {
                    alert('Only PNG allowed');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File too large');
                    return;
                }

                var fd = new FormData();
                fd.append('photo', file);
                try {
                    var resp = await fetch('<?= $link->url('author.uploadPhoto') ?>', {
                        method: 'POST',
                        body: fd,
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) {
                        alert('Upload failed');
                        return;
                    }
                    var json = await resp.json();
                    if (json && json.success && json.path) {
                        hidden.value = json.path;
                        showPreviewUrl(json.path);
                    } else {
                        alert(json && json.message ? json.message : 'Upload failed');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Upload error');
                }
            }
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var feedback = document.getElementById('authorFormFeedback');
            var form = document.getElementById('authorForm');

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept' : 'application/json'
                        },
                        body: params
                    });
                    var result = await response.json();
                    if(!result.success) {
                        feedback.innerHTML = '<div class="alert alert-danger" role="alert">' + (result.errors || 'Error saving author') + '</div>';
                    } else {
                        window.location.href = result.redirect;
                    }
                } catch (err) {
                    feedback.innerHTML = '<div class="alert alert-danger" role="alert">Chyba spojenia</div>';
                }
            });
        });
     </script>
</div>


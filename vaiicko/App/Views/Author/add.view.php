<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Author|null $author */
?>

<div class="container">
    <h1><?= isset($author) ? 'Edit author' : 'Add author' ?></h1>
    <form method="post" action="<?= $link->url('author.store') ?>">
        <?php if (isset($author)) { ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($author->getId()) ?>">
        <?php } ?>
        <div class="mb-3">
            <label class="form-label">First name</label>
            <input type="text" name="first_name" class="form-control" required value="<?= isset($author) ? htmlspecialchars($author->getFirstName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" class="form-control" required value="<?= isset($author) ? htmlspecialchars($author->getLastName()) : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control" value="<?= isset($author) ? htmlspecialchars($author->getNationality()) : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Birth date</label>
            <?php
            // Ensure the date input gets a YYYY-MM-DD value when editing.
            $birthValue = '';
            if (isset($author) && $author->getBirthDate()) {
                try {
                    $dt = new \DateTime($author->getBirthDate());
                    $birthValue = $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    // fallback: leave empty if date parsing fails
                    $birthValue = '';
                }
            }
            ?>
            <input type="date" name="birth_date" class="form-control" value="<?= htmlspecialchars($birthValue) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Fotka autora (PNG) — potiahni sem</label>
            <div id="author-photo-drop" class="border rounded p-3 text-center" style="min-height:120px; display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <div id="author-photo-placeholder">Potiahni sem PNG alebo klikni pre vybratie súboru</div>
                <img id="author-photo-preview" src="<?= isset($author) && method_exists($author, 'getPhoto') ? htmlspecialchars($author->getPhoto()) : '' ?>" alt="" style="max-height:100px; display:none; margin:auto;">
            </div>
            <input id="author-photo-input" type="file" accept="image/png" style="display:none">
            <input type="hidden" name="photo_path" id="photo_path" value="<?= isset($author) && method_exists($author, 'getPhoto') ? htmlspecialchars($author->getPhoto()) : '' ?>">
            <small class="form-text text-muted">Max 5 MB. Len PNG.</small>
        </div>
        <button class="btn btn-primary"><?= isset($author) ? 'Update' : 'Save' ?></button>
    </form>
    <script>
        (function(){
            var drop = document.getElementById('author-photo-drop');
            var input = document.getElementById('author-photo-input');
            var placeholder = document.getElementById('author-photo-placeholder');
            var preview = document.getElementById('author-photo-preview');
            var hidden = document.getElementById('photo_path');

            function showPreviewUrl(url){
                if(!url) return;
                preview.src = url;
                preview.style.display = '';
                placeholder.style.display = 'none';
            }

            // init preview if value present
            if(hidden.value) showPreviewUrl(hidden.value);

            drop.addEventListener('click', function(){ input.click(); });
            drop.addEventListener('dragover', function(e){ e.preventDefault(); drop.classList.add('bg-light'); });
            drop.addEventListener('dragleave', function(e){ drop.classList.remove('bg-light'); });
            drop.addEventListener('drop', function(e){ e.preventDefault(); drop.classList.remove('bg-light'); var f = (e.dataTransfer.files && e.dataTransfer.files[0]) || null; if(f) handleFile(f); });
            input.addEventListener('change', function(e){ var f = e.target.files && e.target.files[0]; if(f) handleFile(f); });

            async function handleFile(file){
                if(!file) return;
                if(file.type !== 'image/png') { alert('Only PNG allowed'); return; }
                if(file.size > 5*1024*1024) { alert('File too large'); return; }

                var fd = new FormData(); fd.append('photo', file);
                try{
                    var resp = await fetch('<?= $link->url('author.uploadPhoto') ?>', { method: 'POST', body: fd, headers: {'X-Requested-With':'XMLHttpRequest'}, credentials: 'same-origin' });
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
</div>

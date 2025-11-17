<?php
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container">
    <h1>Add author</h1>
    <form method="post" action="<?= $link->url('author.store') ?>">
        <div class="mb-3">
            <label class="form-label">First name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Birth date</label>
            <input type="date" name="birth_date" class="form-control">
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>


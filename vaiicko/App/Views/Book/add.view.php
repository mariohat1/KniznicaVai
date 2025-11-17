<?php
/** @var \Framework\Http\Request $request */
/** @var \Framework\Support\LinkGenerator $link */
?>
</div>
    </form>
        <button class="btn btn-primary">Save</button>
        </div>
            <textarea name="description" class="form-control"></textarea>
            <label class="form-label">Description</label>
        <div class="mb-3">
        </div>
            <input type="date" name="year_published" class="form-control">
            <label class="form-label">Year published</label>
        <div class="mb-3">
        </div>
            <input type="text" name="isbn" class="form-control">
            <label class="form-label">ISBN</label>
        <div class="mb-3">
        </div>
            <input type="text" name="title" class="form-control" required>
            <label class="form-label">Title</label>
        <div class="mb-3">
    <form method="post" action="<?= $link->url('book.store') ?>">
    <h1>Add book</h1>
<div class="container">

?>


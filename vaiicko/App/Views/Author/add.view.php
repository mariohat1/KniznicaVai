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
        <button class="btn btn-primary"><?= isset($author) ? 'Update' : 'Save' ?></button>
    </form>
</div>

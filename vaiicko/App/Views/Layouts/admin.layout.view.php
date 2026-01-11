<?php

/** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var LinkGenerator $link */

use App\Support\AuthView;
use Framework\Support\LinkGenerator;

$displayNameEsc = AuthView::displayNameEsc($auth);
?>
<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Configuration::APP_NAME ?> — Správa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="admin-layout">
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="btn btn-outline-light me-2 d-md-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#adminOffcanvas" aria-controls="adminOffcanvas" aria-label="Otvoriť menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand d-flex align-items-center ms-1" href="<?= $link->url('home.index') ?>">Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#admNav"
                aria-controls="admNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="admNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><span class="nav-link text-white">Prihlásený: <strong><?= $displayNameEsc ?></strong></span></li>
                <li class="nav-item"><a class="nav-link" href="<?= $link->url('auth.logout') ?>">Odhlásiť</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Offcanvas menu for small screens -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="adminOffcanvas" aria-labelledby="adminOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="adminOffcanvasLabel">Správa</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Zavrieť"></button>
  </div>
  <div class="offcanvas-body">
    <nav class="nav flex-column">
        <a class="nav-link text-black" href="<?= $link->url('home.index') ?>">Domov</a>
        <a class="nav-link text-black" href="<?= $link->url('category.manage') ?>">Kategórie</a>
        <a class="nav-link text-black" href="<?= $link->url('genre.manage') ?>">Žánre</a>
        <a class="nav-link text-black" href="<?= $link->url('author.manage') ?>">Autori</a>
        <a class="nav-link text-black" href="<?= $link->url('book.manage') ?>">Knihy</a>
        <a class="nav-link text-black" href="<?= $link->url('reservation.manage') ?>">Rezervácie</a>
    </nav>
  </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Left white sidebar (hidden on small screens) -->
        <aside class="d-none d-md-block col-md-2 p-0 admin-sidebar">
            <div class="position-sticky top-0 vh-100 p-3 d-flex flex-column">
                <h6 class="mb-3">Správa</h6>
                <div class="d-grid mb-3">
                    <a class="btn btn-sm btn-secondary" href="<?= $link->url('home.index') ?>">Domov</a>
                </div>
                <nav class="nav flex-column small">
                    <a href="<?= $link->url('category.manage') ?>" class="nav-link text-black ">Kategórie</a>
                    <a href="<?= $link->url('genre.manage') ?>" class="nav-link text-black">Žánre</a>
                    <a href="<?= $link->url('author.manage') ?>" class="nav-link text-black">Autori</a>
                    <a href="<?= $link->url('book.manage') ?>" class="nav-link text-black">Knihy</a>
                    <a href="<?= $link->url('reservation.manage') ?>" class="nav-link text-black">Rezervácie</a>
                </nav>
                <div class="mt-auto pt-3 small text-muted">&copy; <?= date('Y') ?></div>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="col-12 col-md-10 p-3 admin-content" role="main">
            <?= $contentHTML ?>
        </main>
    </div>
</div>

</body>
</html>


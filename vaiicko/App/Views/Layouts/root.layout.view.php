<?php

/** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>">
    <!-- No local page behavior scripts required for classic header/main/footer layout -->
</head>
<body>

<header class="site-header">
    <nav class="navbar navbar-expand-sm bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $link->url('home.index') ?>">
                <img src="<?= $link->asset('images/vaiicko_logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>"
                     alt="Framework Logo">
            </a>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $link->url('home.contact') ?>">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $link->url('author.index') ?>">Autori</a>
                </li>
            </ul>
            <?php if ($auth?->isLogged()) { ?>
                <?php
                // Determine if current user is admin. Support different identity shapes (role property or username methods/properties)
                $user = null;
                try {
                    $user = $auth->getUser();
                } catch (\Throwable $e) {
                    $user = $auth?->user ?? null;
                }

                $isAdmin = false;
                if ($user) {
                    if (isset($user->role)) {
                        $isAdmin = ($user->role === 'admin');
                    } elseif (method_exists($user, 'getUsername')) {
                        $isAdmin = ($user->getUsername() === 'admin');
                    } elseif (isset($user->username)) {
                        $isAdmin = ($user->username === 'admin');
                    }
                }
                ?>

                <div class="d-flex align-items-center ms-auto">
                    <?php if ($isAdmin): ?>
                        <div class="dropdown me-3">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="adminMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                Správa
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                                <li><a class="dropdown-item" href="<?= $link->url('author.index') ?>">Správa autorov</a></li>
                                <li><a class="dropdown-item" href="<?= $link->url('book.index') ?>">Správa kníh</a></li>
                                <li><a class="dropdown-item" href="<?= $link->url('admin.index') ?>">Nastavenia</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <span class="navbar-text me-3">Prihlásený: <b><?= htmlspecialchars($user?->name ?? ($user?->getUsername() ?? ($user?->username ?? ''))) ?></b></span>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $link->url('auth.logout') ?>">Odhlásiť sa</a>
                        </li>
                    </ul>
                </div>
            <?php } else { ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= App\Configuration::LOGIN_URL ?>">Log in</a>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </nav>
</header>

<!-- Main content -->
<main id="main" class="container mt-4 mb-5">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</main>
<!-- Footer -->
<footer class="site-footer bg-light border-top py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>O knižnici</h5>
                <p class="small mb-0">Poskytujeme široký výber kníh, prístup k počítačom a priestor na štúdium.</p>
            </div>
            <div class="col-md-4">
                <h5>Otváracie hodiny</h5>
                <ul class="opening-hours list-unstyled small mb-0">
                    <li>Pondelok — Piatok: 9:00 — 18:00</li>
                    <li>Sobota: 10:00 — 13:00</li>
                    <li>Nedeľa: Zatvorené</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Kontakt</h5>
                <p class="small mb-0">E-mail: info@kniznica.example<br>Tel: +421 2 123 4567</p>
            </div>
        </div>
        <div class="text-center small text-muted mt-3">© <?= date('Y') ?> <?= App\Configuration::APP_NAME ?></div>
    </div>

</footer>
</body>
</html>

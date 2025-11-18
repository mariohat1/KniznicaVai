<?php

/** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */

// --- Logic: compute display variables once to keep template markup clean ---
$user = null;
try {
    $user = $auth->getUser();
} catch (\Throwable $e) {
    $user = $auth?->user ?? null;
}
// Determine admin flag in a safe way (methods first, then public props)
$isAdmin = false;
// Only consider admin when authenticator reports logged-in and the user's role is explicitly 'admin'
try {
    if ($auth?->isLogged()) {
        try {
            // require the user to be the application's User model
            if (is_object($user) && ($user instanceof \App\Models\User)) {
                $isAdmin = (strtolower((string)$user->getRole()) === 'admin');
            } else {
                $isAdmin = false;
            }
        } catch (\Throwable $e) {
            $isAdmin = false;
        }
    }
} catch (\Throwable $e) {
    $isAdmin = false;
}

// Friendly display name for navbar
$displayName = '';
if ($user) {
    if (is_object($user) && method_exists($user, 'getName')) {
        $displayName = $user->getName();
    } elseif (is_object($user) && method_exists($user, 'getUsername')) {
        $displayName = $user->getUsername();
    } else {
        $vars = is_object($user) ? get_object_vars($user) : [];
        $displayName = $vars['name'] ?? $vars['username'] ?? '';
    }
}

$displayNameEsc = htmlspecialchars($displayName);

// Read transient auth modal flags from session (if any)
$openAuthModal = $_SESSION['open_auth_modal'] ?? false;
$authModalMode = $_SESSION['auth_modal_mode'] ?? 'login';
$authModalMessage = $_SESSION['auth_modal_message'] ?? null;
$authModalUsername = $_SESSION['auth_modal_username'] ?? '';
// Clear them so they appear only once
if (isset($_SESSION['open_auth_modal'])) unset($_SESSION['open_auth_modal']);
if (isset($_SESSION['auth_modal_mode'])) unset($_SESSION['auth_modal_mode']);
if (isset($_SESSION['auth_modal_message'])) unset($_SESSION['auth_modal_message']);
if (isset($_SESSION['auth_modal_username'])) unset($_SESSION['auth_modal_username']);

?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
</head>
<body>

<header class="site-header">
    <nav class="navbar navbar-expand-sm bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $link->url('home.index') ?>">
                <img src="<?= $link->asset('images/vaiicko_logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>" alt="Framework Logo">
            </a>

            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $link->url('home.contact') ?>">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $link->url('author.index') ?>">Autori</a></li>
            </ul>

            <?php if ($auth?->isLogged()): ?>
                <div class="d-flex align-items-center ms-auto">
                    <?php if ($isAdmin): ?>
                        <div class="dropdown me-3">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="adminMenu" data-bs-toggle="dropdown" aria-expanded="false">Správa</button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                                <li><a class="dropdown-item" href="<?= $link->url('author.index') ?>">Správa autorov</a></li>
                                <li><a class="dropdown-item" href="<?= $link->url('book.index') ?>">Správa kníh</a></li>
                                <li><a class="dropdown-item" href="<?= $link->url('admin.index') ?>">Nastavenia</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <span class="navbar-text me-3">Prihlásený: <b><?= $displayNameEsc ?></b></span>
                    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="<?= $link->url('auth.logout') ?>">Odhlásiť sa</a></li></ul>
                </div>
            <?php else: ?>
                <div class="ms-auto">
                    <button id="loginToggle" class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Prihlásiť sa</button>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- Login modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Prihlásenie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavrieť"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <h6 class="mb-0">Prihlásenie / Registrácia</h6>
                <div>
                    <button id="showLogin" type="button" class="btn btn-sm btn-link">Prihlásiť</button>
                    <button id="showRegister" type="button" class="btn btn-sm btn-link">Registrovať</button>
                </div>
            </div>
        </div>

        <!-- Login form -->
        <div id="modalLoginForm">
            <?php if (!empty($authModalMessage) && $authModalMode === 'login'): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($authModalMessage) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= $link->url('auth.login') ?>">
              <input type="hidden" name="auth_form" value="login">
               <div class="mb-3">
                 <label for="modal_username" class="form-label">Používateľ</label>
                <input id="modal_username" name="username" type="text" class="form-control" value="<?= htmlspecialchars($authModalUsername) ?>">
               </div>
               <div class="mb-3">
                 <label for="modal_password" class="form-label">Heslo</label>
                 <input id="modal_password" name="password" type="password" class="form-control" required>
               </div>
               <div class="text-end">
                 <button class="btn btn-secondary me-2" type="button" data-bs-dismiss="modal">Zrušiť</button>
                 <button class="btn btn-primary" type="submit" name="submit">Prihlásiť</button>
               </div>
             </form>
         </div>

         <!-- Register form (hidden by default) -->
         <div id="modalRegisterForm" style="display:none;">
            <form method="post" action="<?= $link->url('user.add') ?>">
                <input type="hidden" name="auth_form" value="register">
                 <div class="mb-3">
                     <label for="reg_username" class="form-label">Používateľ</label>
                     <input id="reg_username" name="username" type="text" class="form-control" required>
                 </div>
                 <div class="mb-3">
                     <label for="reg_email" class="form-label">Email</label>
                     <input id="reg_email" name="email" type="email" class="form-control">
                 </div>
                 <div class="mb-3">
                     <label for="reg_password" class="form-label">Heslo</label>
                     <input id="reg_password" name="password" type="password" class="form-control" required>
                 </div>
                 <div class="mb-3">
                     <label for="reg_password2" class="form-label">Heslo znova</label>
                     <input id="reg_password2" name="password2" type="password" class="form-control" required>
                 </div>
                 <div class="text-end">
                     <button class="btn btn-secondary me-2" type="button" data-bs-dismiss="modal">Zrušiť</button>
                     <button class="btn btn-success" type="submit" name="submit">Registrovať</button>
                 </div>
             </form>
         </div>
      </div>
    </div>
  </div>
</div>

<main id="main" class="container mt-4 mb-5">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</main>

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

<script>


    // Server-driven auto-open disabled. Modal will open only when user clicks the button.

    // No custom fetch submit — rely on normal form POST so cookies/session are sent by the browser.
</script>

<!-- Persist server modal message into sessionStorage and show it only when user opens the modal -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var loginForm = document.getElementById('modalLoginForm');
        var registerForm = document.getElementById('modalRegisterForm');
        var loginModalEl = document.getElementById('loginModal');

        // Toggle login/register forms
        document.getElementById('showLogin').addEventListener('click', function() {
            if(loginForm) loginForm.style.display = 'block';
            if(registerForm) registerForm.style.display = 'none';
        });

        document.getElementById('showRegister').addEventListener('click', function() {
            if(loginForm) loginForm.style.display = 'none';
            if(registerForm) registerForm.style.display = 'block';
        });

        // Open modal automatically if server flagged it
        var open = <?= $openAuthModal ? 'true' : 'false' ?>;
        var mode = '<?= htmlspecialchars($authModalMode) ?>';

        if (open && loginModalEl) {
            if(mode === 'login') {
                if(loginForm) loginForm.style.display = 'block';
                if(registerForm) registerForm.style.display = 'none';
            } else if (mode === 'register') {
                if(loginForm) loginForm.style.display = 'none';
                if(registerForm) registerForm.style.display = 'block';
            }

            var bootstrapModal = new bootstrap.Modal(loginModalEl);
            bootstrapModal.show();
        }
    });
</script>

</body>
</html>

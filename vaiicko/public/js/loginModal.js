document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('modalLoginForm');
    var registerForm = document.getElementById('modalRegisterForm');

    var showLoginBtn = document.getElementById('showLogin');
    var showRegisterBtn = document.getElementById('showRegister');


    if (showLoginBtn) {
        showLoginBtn.addEventListener('click', function () {
            if (loginForm) loginForm.style.display = 'block';
            if (registerForm) registerForm.style.display = 'none';
            var fb = document.getElementById('modalRegisterFeedback');
            if (fb) fb.style.display = 'none';
        });
    }

    if (showRegisterBtn) {
        showRegisterBtn.addEventListener('click', function () {
            if (loginForm) loginForm.style.display = 'none';
            if (registerForm) registerForm.style.display = 'block';
        });
    }

});
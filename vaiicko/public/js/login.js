document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        var loginFeedback = document.createElement('div');
        loginFeedback.id = 'loginFormFeedback';
        loginFeedback.className = 'alert alert-danger';
        loginFeedback.style.display = 'none';
        loginForm.insertBefore(loginFeedback, loginForm.firstChild);

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            loginFeedback.style.display = 'none';
            loginFeedback.innerHTML = '';

            var submitBtn = loginForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            var url = loginForm.action;
            var formData = new FormData(loginForm);
            try {
                var resp = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                var json = await resp.json();
                if (json && json.success) {
                    window.location.href = json.redirect || window.location.href;
                    return;
                }
                loginFeedback.innerHTML = json && json.message ? json.message : 'Neplatné meno alebo heslo';
                loginFeedback.style.display = 'block';
            } catch (err) {
                console.error('Login failed', err);
                loginFeedback.innerHTML = 'Chyba spojenia';
                loginFeedback.style.display = 'block';
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
    var registerForm = document.querySelector('#modalRegisterForm form');
    if (registerForm) {
        var registerFeedback = document.createElement('div');
        registerFeedback.id = 'registerFormFeedback';
        registerFeedback.className = 'alert alert-danger';
        registerFeedback.style.display = 'none';
        registerForm.insertBefore(registerFeedback, registerForm.firstChild);

        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            registerFeedback.style.display = 'none';
            registerFeedback.innerHTML = '';

            var submitBtn = registerForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            var url = registerForm.action || window.location.href;
            var formData = new FormData(registerForm);
            try {
                var resp = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                var json = await resp.json();
                if (json && json.success) {
                    window.location.href = json.redirect || window.location.href;
                    return;
                }

                registerFeedback.innerHTML = json && json.message ? json.message : 'Registrácia zlyhala';
                registerFeedback.style.display = 'block';
            } catch (err) {
                console.error('Register failed', err);
                registerFeedback.innerHTML = 'Chyba spojenia';
                registerFeedback.style.display = 'block';
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});

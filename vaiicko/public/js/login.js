document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    function showError(msg) {
        var box = loginForm.querySelector('.ajax-error');
        if (!box) {
            box = document.createElement('div');
            box.className = 'ajax-error alert alert-danger mt-2';
            loginForm.insertBefore(box, loginForm.firstChild);
        }
        box.textContent = msg || 'Chyba';
        box.style.display = 'block';
    }

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault(); // prevent default form submission
        var url = loginForm.action || window.location.href;
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

            if (resp.redirected) {
                window.location.href = resp.url;
                return;
            }

            if (!resp.ok) {
                var textErr = await resp.text().catch(function () { return ''; });
                try {
                    var parsedErr = textErr ? JSON.parse(textErr) : null;
                    if (parsedErr && parsedErr.message) {
                        showError(parsedErr.message);
                        return;
                    }
                } catch (e) {
                }
                showError('Chyba servera (' + resp.status + ').');
                return;
            }

            var text = await resp.text();
            var json = null;
            try {
                json = text ? JSON.parse(text) : null;
            } catch (e) {
                showError('Nesprávna odpoveď zo servera.');
                console.error('Invalid JSON response:', text);
                return;
            }

            if (json && json.success) {
                window.location.href = json.redirect || window.location.href;
                return;
            }

            showError((json && json.message) ? json.message : 'Neplatné meno alebo heslo');

        } catch (err) {
            console.error('Login fetch failed', err);
            showError('Chyba pri sieti. Skús to neskôr.');
        }
    });

    // ----- registration via AJAX -----
    var registerContainer = document.getElementById('modalRegisterForm');
    var registerForm = registerContainer ? registerContainer.querySelector('form') : null;

    function ensureRegisterFeedback() {
        if (!registerForm) return null;
        var existing = document.getElementById('modalRegisterFeedback');
        if (existing) return existing;
        var div = document.createElement('div');
        div.id = 'modalRegisterFeedback';
        div.className = 'alert alert-danger mb-2';
        div.style.display = 'none';
        registerForm.insertBefore(div, registerForm.firstChild);
        return div;
    }

    function showRegisterError(msg) {
        var box = document.getElementById('modalRegisterFeedback') || ensureRegisterFeedback();
        if (!box) return;
        box.textContent = msg || 'Chyba';
        box.style.display = 'block';
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
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

                if (resp.redirected) {
                    window.location.href = resp.url;
                    return;
                }

                var text = await resp.text();
                var json = null;
                try {
                    json = text ? JSON.parse(text) : null;
                } catch (e) {
                    showRegisterError('Nesprávna odpoveď zo servera.');
                    console.error('Invalid JSON response:', text);
                    return;
                }

                if (resp.ok && json && json.success) {
                    window.location.href = json.redirect || window.location.href;
                    return;
                }

                // show server message or generic
                showRegisterError((json && json.message) ? json.message : 'Registrácia zlyhala.');

            } catch (err) {
                console.error('Register fetch failed', err);
                showRegisterError('Chyba pri sieti. Skús to neskôr.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});


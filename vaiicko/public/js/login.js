document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    // Simple helper to show an inline error message inside the form
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

    loginForm.addEventListener('submit', function (event) {
        event.preventDefault(); // stop the normal form submit

        // create XHR
        var xhr = new XMLHttpRequest();

        // Use the form action so it works for your routing (don't hardcode '/login')
        var url = loginForm.action || window.location.href;
        xhr.open('POST', url, true);

        // Tell the server this is an AJAX request
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader('Accept', 'application/json');

        // Prepare form data (includes any hidden CSRF input)
        var formData = new FormData(loginForm);

        // Handle response
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== XMLHttpRequest.DONE) return;

            // 2xx success
            if (xhr.status >= 200 && xhr.status < 300) {
                var text = xhr.responseText;
                try {
                    var json = JSON.parse(text);
                } catch (e) {
                    showError('Nesprávna odpoveď zo servera.');
                    console.error('Invalid JSON response:', text);
                    return;
                }

                if (json && json.success) {
                    // redirect if server provided a URL, otherwise reload
                    window.location.href = json.redirect || window.location.href;
                    return;
                }

                // show server-provided message or generic message
                showError((json && json.message) ? json.message : 'Neplatné meno alebo heslo');
                return;
            }

                       // non-2xx
            showError('Chyba servera (' + xhr.status + ').');
        };

        xhr.onerror = function () {
            showError('Chyba pri sieti. Skús to neskôr.');
        };

        // send it
        xhr.send(formData);
    });
});

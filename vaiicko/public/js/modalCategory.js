(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('categoryModalForm');
        var feedback = document.getElementById('categoryFeedback');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            var params = new URLSearchParams(new FormData(form));

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: params,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(json => {
                if (json && json.success) {
                    var select = document.getElementById('category_id');
                    if (select) {
                        var opt = document.createElement('option');
                        opt.value = json.id;
                        opt.textContent = json.name;
                        select.appendChild(opt);
                        select.value = json.id;
                        form.reset();
                        var modalEl = document.getElementById('categoryModal');
                        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                        return;
                    }

                    // No related select on page: if server sent redirect, navigate
                    if (json && json.redirect) {
                        window.location.href = json.redirect;
                        return;
                    }

                    // Fallback: just reset and hide modal if present
                    form.reset();
                    var modalEl = document.getElementById('categoryModal');
                    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    return;
                }
                if (feedback) {
                    feedback.innerHTML = '<div class="alert alert-danger mb-0" role="alert">' + ((json && (json.error || json.message)) || 'Chyba') + '</div>';
                }
            })
            .catch(() => {
                if (feedback) feedback.innerHTML = '<div class="alert alert-danger mb-0" role="alert">Chyba spojenia</div>';
            });
        });
    });
})();

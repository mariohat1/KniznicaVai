(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('genreModalForm');
        var feedback = document.getElementById('genreFeedback');

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
                    var select = document.getElementById('genre_id');
                    if (select) {
                        var opt = document.createElement('option');
                        opt.value = json.id;
                        opt.textContent = json.name;
                        select.appendChild(opt);
                        select.value = json.id;
                        form.reset();
                        var modalEl = document.getElementById('genreModal');
                        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                        return;
                    }

                    if (json && json.redirect) {
                        window.location.href = json.redirect;
                        return;
                    }

                    form.reset();
                    var modalEl = document.getElementById('genreModal');
                    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    return;
                }
                if (feedback) {
                    let errors = json.errors.join('<br>');
                    feedback.innerHTML =
                        '<div class="alert alert-danger" role="alert">' +
                        errors +
                        '</div>';
                }
            })
            .catch(() => {
                if (feedback) feedback.innerHTML = '<div class="alert alert-danger mb-0" role="alert">Chyba spojenia</div>';
            });
        });
    });
})();

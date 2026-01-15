(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('genreForm');
        var feedback = document.getElementById('genreClientFeedback');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (feedback) feedback.innerHTML = '';
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
                        if (json.redirect) {
                            window.location.href = json.redirect;
                            return;
                        }
                        return;
                    }
                    let msg = json.errors.join('<br>');

                    feedback.innerHTML = '<div class="alert alert-danger mb-0" role="alert">' + msg + '</div>';
                })
                .catch(function () {
                    if (feedback) feedback.innerHTML = '<div class="alert alert-danger mb-0" role="alert">Chyba spojenia</div>';
                });
        });
    });
})();

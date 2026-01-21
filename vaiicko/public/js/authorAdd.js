(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('authorForm');
        var feedback = document.getElementById('authorClientFeedback') || document.getElementById('authorFormFeedback');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                let result = await response.json();
                if (!result.success) {
                    let errors = result.errors.join('<br>');
                    feedback.innerHTML =
                        '<div class="alert alert-danger" role="alert">' +
                        errors +
                        '</div>';
                } else {
                    window.location.href = result.redirect;
                }
            } catch (err) {
                feedback.innerHTML = '<div class="alert alert-danger" role="alert">Chyba spojenia</div>';
            }
        });
    });
})();

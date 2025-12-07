(function () {
    const feedback = document.getElementById('manageFeedback');

    function showFeedback(msg, type) {
        if (!feedback) return;
        feedback.textContent = msg || '';
        feedback.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-danger');
        feedback.style.display = 'block';
        if (type === 'success') {
            setTimeout(hideFeedback, 2500);
        }
    }

    function hideFeedback() {
        if (!feedback) return;
        feedback.style.display = 'none';
        feedback.textContent = '';
    }

    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form || !form.classList || !form.classList.contains('ajax-delete-book')) return;
        e.preventDefault();

        // confirm delete
        if (!confirm('Naozaj chcete zmazať túto knihu?')) return;
        hideFeedback();

        const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
        if (submitBtn) submitBtn.disabled = true;

        try {
            // use FormData to keep compatibility with backend
            const fd = new FormData(form);
            const resp = await fetch(form.action, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            const contentType = resp.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await resp.json();
            } else {
                // non-json (redirect/login page) -> treat as failure
                const text = await resp.text();
                showFeedback('Server odpovedal nečakaným obsahom.', 'error');
                console.error('Delete unexpected response', text);
                return;
            }

            if (data && data.success) {
                // remove both the data row and the action row
                const actionRow = form.closest('tr');
                if (actionRow) {
                    const dataRow = actionRow.previousElementSibling;
                    actionRow.remove();
                    if (dataRow && dataRow.tagName === 'TR') dataRow.remove();
                }
                showFeedback('Kniha bola úspešne vymazaná.', 'success');
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
            } else {
                showFeedback((data && (data.message || data.error)) || 'Vymazanie zlyhalo.', 'error');
            }
        } catch (err) {
            console.error('Delete failed', err);
            showFeedback('Chyba spojenia: ' + (err.message || err), 'error');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
})();

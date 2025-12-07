(function () {
    const root = document.getElementById('bookAddRoot');
    if (!root) return;

    const categoryUrl = root.dataset.categoryUrl;
    const genreUrl = root.dataset.genreUrl;

    // zobrazenie feedbacku podla spravy
    function showFeedback(el, msg, ok) {
        if (!el) return;
        el.style.display = 'block';
        el.className = ok ? 'form-text text-success mt-1' : 'form-text text-danger mt-1';
        el.textContent = msg;
    }

    async function postJson(url, payload) {
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include',
                body: JSON.stringify(payload)
            });

            const contentType = res.headers.get('content-type') || '';
            let data = null;
            let text = null;
            if (contentType.includes('application/json')) {
                try {
                    data = await res.json();
                } catch (e) {
                    text = await res.text().catch(() => null);
                }
            } else {
                text = await res.text().catch(() => null);
            }

            return {status: res.status, ok: res.ok, data, text};
        } catch (e) {
            return {status: 0, ok: false, data: null, text: e.message || String(e)};
        }
    }

    function toggleContainer(btnId, containerId, inputId) {
        const trigger = document.getElementById(btnId);
        if (!trigger) return;
        trigger.addEventListener('click', () => {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
            if (container.style.display === 'block') {
                const input = document.getElementById(inputId);
                if (input) input.focus();
            }
        });
    }

    toggleContainer('showCategoryAdd', 'categoryAddContainer', 'new_category_name');
    toggleContainer('showGenreAdd', 'genreAddContainer', 'new_genre_name');

    async function handleCreateCategory() {
        const nameInput = document.getElementById('new_category_name');
        const fb = document.getElementById('categoryFeedback');
        const btn = document.getElementById('createCategoryBtn');
        if (!nameInput || !fb || !btn) return;

        const name = nameInput.value.trim();
        if (!name) {
            showFeedback(fb, 'Zadaj názov kategórie', false);
            return;
        }
        btn.disabled = true;
        try {
            const res = await postJson(categoryUrl, {name});
            console.log('create category response', res);
            if (res.ok && res.status === 201 && res.data && res.data.id) {
                const categorySelect = document.getElementById('category_id');
                const newOption = document.createElement('option');
                newOption.value = res.data.id;
                newOption.textContent = res.data.name;
                categorySelect.appendChild(newOption);
                categorySelect.value = res.data.id;
                nameInput.value = '';
                showFeedback(fb, 'Kategória pridaná', true);
            } else {
                const msg = (res.data && (res.data.error || res.data.message)) || res.text || ('Server error: ' + res.status);
                showFeedback(fb, msg, false);
            }
        } catch (e) {
            console.error('handleCreateCategory error', e);
            showFeedback(fb, 'Chyba spojenia: ' + (e.message || e), false);
        } finally {
            btn.disabled = false;
        }
    }

    async function handleCreateGenre() {
        const nameInput = document.getElementById('new_genre_name');
        const feedBack = document.getElementById('genreFeedback');
        const button = document.getElementById('createGenreBtn');
        if (!nameInput || !feedBack || !button) return;

        const name = nameInput.value.trim();
        if (!name) {
            showFeedback(feedBack, 'Zadaj názov žánru', false);
            return;
        }
        button.disabled = true;
        try {
            const resposnse = await postJson(genreUrl, {name});
            console.log('create genre response', resposnse);
            if (resposnse.ok && resposnse.status === 201 && resposnse.data && resposnse.data.id) {
                //select form
                const genreSelect = document.getElementById('genre_id');
                // nova option
                const newOption = document.createElement('option');
                newOption.value = resposnse.data.id;
                newOption.textContent = resposnse.data.name;
                genreSelect.appendChild(newOption);
                genreSelect.value = resposnse.data.id;
                nameInput.value = '';
                showFeedback(feedBack, 'Žáner pridaný', true);
            } else {
                const msg = (resposnse.data && (resposnse.data.error || resposnse.data.message)) || resposnse.text || ('Server error: ' + resposnse.status);
                showFeedback(feedBack, msg, false);
            }
        } catch (e) {
            console.error('handleCreateGenre error', e);
            showFeedback(feedBack, 'Chyba spojenia: ' + (e.message || e), false);
        } finally {
            button.disabled = false;
        }
    }

    const catBtn = document.getElementById('createCategoryBtn');
    if (catBtn) catBtn.addEventListener('click', handleCreateCategory);

    const genBtn = document.getElementById('createGenreBtn');
    if (genBtn) genBtn.addEventListener('click', handleCreateGenre);

    // Delegation fallback
    document.addEventListener('click', function (e) {
        const eventTarget = e.target;
        if (!eventTarget) return;
        if (eventTarget.matches && eventTarget.matches('#createCategoryBtn')) {
            handleCreateCategory();
        }
        if (eventTarget.matches && eventTarget.matches('#createGenreBtn')) {
            handleCreateGenre();
        }
    });

    (function attachBookFormHandler() {
        try {
            const bookForm = document.querySelector('#bookAddRoot form');
            const rootEl = document.getElementById('bookAddRoot');
            const feedbackEl = document.getElementById('bookFormFeedback');
            const redirectUrl = rootEl ? rootEl.dataset.redirectUrl : null;
            if (!bookForm || !feedbackEl) return;

            bookForm.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                feedbackEl.style.display = 'none';
                feedbackEl.className = '';

                const submitBtn = bookForm.querySelector('button[type="submit"]') || bookForm.querySelector('button');
                if (submitBtn) submitBtn.disabled = true;

                const fd = new FormData(bookForm);
                try {
                    const resp = await fetch(bookForm.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'include',
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });

                    let data = await resp.json();

                    if (data) {
                        if (!data.success) {
                            feedbackEl.style.display = 'block';
                            feedbackEl.className = 'ajax-error alert alert-danger mt-2';
                            feedbackEl.textContent = data.message || data.error || ('Chyba: ' + (resp.status || ''));
                        } else if (data.success && redirectUrl) {
                            window.location.href = redirectUrl;
                        }
                    } else {
                        feedbackEl.style.display = 'block';
                        feedbackEl.className = 'ajax-error alert alert-danger mt-2';
                        feedbackEl.textContent = text || ('Server error: ' + resp.status);
                    }
                } catch (err) {
                    console.error('Book save failed', err);
                    feedbackEl.style.display = 'block';
                    feedbackEl.className = 'ajax-error alert alert-danger mt-2';
                    feedbackEl.textContent = 'Chyba spojenia: ' + (err.message || err);
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        } catch (e) {
            console.error('attachBookFormHandler error', e);
        }
    })();

})();

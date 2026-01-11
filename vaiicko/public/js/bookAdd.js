(function () {
    const root = document.getElementById('bookAddRoot');
    if (!root) return;

    const categoryUrl = root.dataset.categoryUrl;
    const genreUrl = root.dataset.genreUrl;

    // prevent duplicate submissions
    let creatingCategory = false;
    let creatingGenre = false;

    // zobrazenie feedbacku podla spravy
    function showFeedback(el, msg, ok) {
        if (!el) return;
        el.style.display = 'block';
        el.className = ok ? 'form-text text-success mt-1' : 'form-text text-danger mt-1';
        el.textContent = msg;
    }


    async function postJson(url, payload) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include', // important for session auth (admin)
                body: JSON.stringify(payload)
            });
            const data = await response.json()
            return {status: response.status, ok: response.ok, data};
        } catch (err) {
            return {status: 0, ok: false, data: null, error: err.message || String(err)};
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
        if (creatingCategory) return; // guard against double calls
        const nameInput = document.getElementById('new_category_name');
        const fb = document.getElementById('categoryFeedback');
        const btn = document.getElementById('createCategoryBtn');
        if (!nameInput || !fb || !btn) return;

        const name = nameInput.value.trim();
        if (!name) {
            showFeedback(fb, 'Zadaj názov kategórie', false);
            return;
        }

        creatingCategory = true;
        btn.disabled = true;
        try {
            const res = await postJson(categoryUrl, {name});
            console.log('create category response', res);
            if (res.ok) {
                const categorySelect = document.getElementById('category_id');
                const newOption = document.createElement('option');
                newOption.value = res.data.id;
                newOption.textContent = res.data.name;
                categorySelect.appendChild(newOption);
                categorySelect.value = res.data.id;
                nameInput.value = '';
                showFeedback(fb, 'Kategória pridaná', true);
            } else {
                const msg = (res.data && (res.data.error || res.data.message)) || (res.error || 'Server error');
                showFeedback(fb, msg, false);
            }
        } catch (e) {
            console.error('handleCreateCategory error', e);
            showFeedback(fb, 'Chyba spojenia', false);
        } finally {
            creatingCategory = false;
            btn.disabled = false;
        }
    }

    async function handleCreateGenre() {
        if (creatingGenre) return; // guard
        const nameInput = document.getElementById('new_genre_name');
        const feedBack = document.getElementById('genreFeedback');
        const button = document.getElementById('createGenreBtn');
        if (!nameInput || !feedBack || !button) return;

        const name = nameInput.value.trim();
        if (!name) {
            showFeedback(feedBack, 'Zadaj názov žánru', false);
            return;
        }

        creatingGenre = true;
        button.disabled = true;
        try {
            const response = await postJson(genreUrl, {name});
            console.log('create genre response', response);
            if (response.ok) {
                const genreSelect = document.getElementById('genre_id');
                const newOption = document.createElement('option');
                newOption.value = response.data.id;
                newOption.textContent = response.data.name;
                genreSelect.appendChild(newOption);
                genreSelect.value = response.data.id;
                nameInput.value = '';
                showFeedback(feedBack, 'Žáner pridaný', true);
            } else {
                const msg = (response.data && (response.data.error || response.data.message)) || (response.error || 'Server error');
                showFeedback(feedBack, msg, false);
            }
        } catch (e) {
            console.error('handleCreateGenre error', e);
            showFeedback(feedBack, 'Chyba spojenia', false);
        } finally {
            creatingGenre = false;
            button.disabled = false;
        }
    }

    const catBtn = document.getElementById('createCategoryBtn');
    if (catBtn) catBtn.addEventListener('click', handleCreateCategory);

    const genBtn = document.getElementById('createGenreBtn');
    if (genBtn) genBtn.addEventListener('click', handleCreateGenre);

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
                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
                    });

                    const contentType = resp.headers.get('content-type') || '';
                    let data = null;
                    if (contentType.includes('application/json')) {
                        data = await resp.json().catch(() => null);
                    }

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
                        feedbackEl.textContent = 'Server error: ' + resp.status;
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

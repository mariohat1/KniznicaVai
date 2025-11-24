(function () {
    const root = document.getElementById('bookAddRoot');
    if (!root) return;

    const categoryUrl = root.dataset.categoryUrl;
    const genreUrl = root.dataset.genreUrl;

    function showFeedback(el, msg, ok) {
        if (!el) return;
        el.style.display = 'block';
        el.className = ok ? 'form-text text-success mt-1' : 'form-text text-danger mt-1';
        el.textContent = msg;
    }

    async function postJson(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        return {status: res.status, data};
    }

    // Toggle show/hide for the 'Iné' add controls
    const showCatSpan = document.getElementById('showCategoryAdd');
    if (showCatSpan) {
        showCatSpan.addEventListener('click', () => {
            const container = document.getElementById('categoryAddContainer');
            if (!container) return;
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
            if (container.style.display === 'block') {
                const input = document.getElementById('new_category_name');
                if (input) input.focus();
            }
        });
    }

    const showGenSpan = document.getElementById('showGenreAdd');
    if (showGenSpan) {
        showGenSpan.addEventListener('click', () => {
            const container = document.getElementById('genreAddContainer');
            if (!container) return;
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
            if (container.style.display === 'block') {
                const input = document.getElementById('new_genre_name');
                if (input) input.focus();
            }
        });
    }

    const catBtn = document.getElementById('createCategoryBtn');
    if (catBtn) {
        catBtn.addEventListener('click', async () => {
            const nameInput = document.getElementById('new_category_name');
            const fb = document.getElementById('categoryFeedback');
            if (!nameInput || !fb) return;

            const name = nameInput.value.trim();
            if (!name) {
                showFeedback(fb, 'Zadaj názov kategórie', false);
                return;
            }

            catBtn.disabled = true;
            try {
                const res = await postJson(categoryUrl, {name});
                if (res.status === 201 && res.data.id) {
                    const categorySelect = document.getElementById('category_id');
                    const newOption = document.createElement('option');
                    newOption.value = res.data.id;
                    newOption.textContent = res.data.name;
                    categorySelect.appendChild(newOption);
                    categorySelect.value = res.data.id;
                    nameInput.value = '';
                    showFeedback(fb, 'Kategória pridaná', true);
                } else {
                    showFeedback(fb, res.data.error || 'Chyba pri pridávaní', false);
                }
            } catch (e) {
                showFeedback(fb, 'Chyba spojenia', false);
            } finally {
                catBtn.disabled = false;
            }
        });
    }

    const genBtn = document.getElementById('createGenreBtn');
    if (genBtn) {
        genBtn.addEventListener('click', async () => {
            const nameInput = document.getElementById('new_genre_name');
            const fb = document.getElementById('genreFeedback');
            if (!nameInput || !fb) return;

            const name = nameInput.value.trim();
            if (!name) {
                showFeedback(fb, 'Zadaj názov žánru', false);
                return;
            }

            genBtn.disabled = true;
            try {
                const res = await postJson(genreUrl, {name});
                if (res.status === 201 && res.data.id) {
                    const genreSelect = document.getElementById('genre_id');
                    const newOption = document.createElement('option');
                    newOption.value = res.data.id;
                    newOption.textContent = res.data.name;
                    genreSelect.appendChild(newOption);
                    genreSelect.value = res.data.id;
                    nameInput.value = '';
                    showFeedback(fb, 'Žáner pridaný', true);
                } else {
                    showFeedback(fb, res.data.error || 'Chyba pri pridávaní', false);
                }
            } catch (e) {
                showFeedback(fb, 'Chyba spojenia', false);
            } finally {
                genBtn.disabled = false;
            }
        });
    }

})();

(function () {
    const root = document.getElementById('bookAddRoot');
    if (!root) return;

    document.addEventListener('DOMContentLoaded', function () {

         // book form submit
         const bookForm = document.querySelector('#bookAddRoot form');
         const feedbackEl = document.getElementById('bookFormFeedback');
         if (bookForm && feedbackEl) {
             bookForm.addEventListener('submit', function (ev) {
                 ev.preventDefault();
                 feedbackEl.innerHTML = '';

                 const submitBtn = bookForm.querySelector('button[type="submit"]');
                 if (submitBtn) submitBtn.disabled = true;

                 const fd = new FormData(bookForm);
                 fetch(bookForm.action, {
                     method: 'POST',
                     body: fd,
                     credentials: 'same-origin',
                     headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                 })
                     .then(r => r.json())
                     .then(data => {
                         if (data && data.success) {
                             const cancelHref = document.querySelector('a.btn.btn-link[href]')?.getAttribute('href');
                             window.location.href = data.redirect || cancelHref || window.location.href;
                             return;
                         }
                         feedbackEl.innerHTML = '<div class="alert alert-danger">' + ((data && (data.message || data.error)) || 'Chyba') + '</div>';
                     })
                     .catch(() => {
                         feedbackEl.innerHTML = '<div class="alert alert-danger">Chyba spojenia</div>';
                     })
                     .finally(() => {
                         if (submitBtn) submitBtn.disabled = false;
                     });
             });
         }
     });
 })();

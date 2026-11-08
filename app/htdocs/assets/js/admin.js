(() => {
    'use strict';
    document.querySelectorAll('[data-admin-form]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.pending === 'true') { event.preventDefault(); return; }
            form.dataset.pending = 'true';
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.dataset.originalLabel = button.textContent;
                button.textContent = button.dataset.pendingLabel || 'Saving…';
                button.disabled = true;
            });
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-admin-form]').forEach(form => {
            delete form.dataset.pending;
            form.querySelectorAll('button[data-original-label]').forEach(button => {
                button.textContent = button.dataset.originalLabel;
                delete button.dataset.originalLabel;
                button.disabled = false;
            });
        });
    });
})();

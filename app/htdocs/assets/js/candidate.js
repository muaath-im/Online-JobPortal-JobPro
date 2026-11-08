(() => {
    'use strict';
    document.querySelectorAll('[data-letter-count]').forEach(input => {
        const output = document.getElementById(input.dataset.letterCount);
        if (!output) return;
        const update = () => { output.textContent = `${Array.from(input.value).length.toLocaleString()} / 10,000 characters`; };
        input.addEventListener('input', update); update();
    });
    document.querySelectorAll('form[data-candidate-form]').forEach(form => {
        form.addEventListener('submit', () => {
            if (!form.checkValidity()) return;
            form.setAttribute('aria-busy', 'true');
            const button = form.querySelector('[data-pending-label]');
            if (button) { button.disabled = true; button.textContent = button.dataset.pendingLabel; }
        });
    });
    // A history-restored page must not retain disabled controls from submission.
    window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
})();

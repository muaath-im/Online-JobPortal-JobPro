(() => {
    'use strict';
    document.querySelectorAll('[data-employer-form]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.pending === 'true') { event.preventDefault(); return; }
            form.dataset.pending = 'true';
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.dataset.originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = button.dataset.pendingLabel || 'Saving…';
            });
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-employer-form]').forEach(form => {
            delete form.dataset.pending;
            form.querySelectorAll('button[data-original-label]').forEach(button => {
                button.disabled = false;
                button.textContent = button.dataset.originalLabel;
                delete button.dataset.originalLabel;
            });
        });
    });
    const editor = document.querySelector('[data-job-editor]');
    if (!editor) return;
    const title = editor.querySelector('[name="job_title"]');
    const location = editor.querySelector('[name="job_location"]');
    const type = editor.querySelector('[name="job_type"]');
    const description = editor.querySelector('[name="job_description"]');
    const minimum = editor.querySelector('[name="min_salary"]');
    const maximum = editor.querySelector('[name="max_salary"]');
    const update = () => {
        editor.querySelector('[data-preview-title]').textContent = title.value.trim() || 'Your opportunity title';
        editor.querySelector('[data-preview-location]').textContent = location.value.trim() || 'Location';
        editor.querySelector('[data-preview-type]').textContent = type.value.trim() || 'Employment type';
        editor.querySelector('[data-description-count]').textContent = `${Array.from(description.value).length.toLocaleString()} / 10,000 characters`;
        maximum.setCustomValidity(minimum.value !== '' && maximum.value !== '' && Number(maximum.value) < Number(minimum.value)
            ? 'Maximum salary must be at least the minimum.' : '');
    };
    editor.addEventListener('input', update);
    update();
})();

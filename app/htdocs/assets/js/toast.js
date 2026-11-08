(() => {
    'use strict';

    let container = null;

    const getContainer = () => {
        if (!container || !document.body.contains(container)) {
            container = document.createElement('div');
            container.className = 'jp-toast-container';
            container.id = 'jp-toast-container';
            container.setAttribute('role', 'region');
            container.setAttribute('aria-label', 'Notifications');
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        return container;
    };

    const icons = {
        success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
        error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></svg>',
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>'
    };

    const show = (message, type = 'success', duration = 4000) => {
        if (!message) return;
        const validTypes = ['success', 'error', 'warning', 'info'];
        const toastType = validTypes.includes(type) ? type : 'info';

        const toast = document.createElement('div');
        toast.className = `jp-toast jp-toast--${toastType}`;
        toast.setAttribute('role', toastType === 'error' ? 'alert' : 'status');

        const iconWrap = document.createElement('div');
        iconWrap.className = 'jp-toast__icon';
        iconWrap.innerHTML = icons[toastType] || icons.info;

        const body = document.createElement('div');
        body.className = 'jp-toast__body';

        const text = document.createElement('p');
        text.className = 'jp-toast__message';
        text.textContent = String(message);
        body.appendChild(text);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'jp-toast__close';
        closeBtn.setAttribute('aria-label', 'Dismiss notification');
        closeBtn.innerHTML = '&times;';

        toast.appendChild(iconWrap);
        toast.appendChild(body);
        toast.appendChild(closeBtn);

        const parent = getContainer();
        parent.appendChild(toast);

        // Trigger entrance animation
        requestAnimationFrame(() => {
            toast.classList.add('is-visible');
        });

        let timer = null;
        let remaining = duration;
        let start = Date.now();

        const dismiss = () => {
            if (timer) clearTimeout(timer);
            toast.classList.remove('is-visible');
            toast.classList.add('is-leaving');
            toast.addEventListener('transitionend', () => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, { once: true });
        };

        const startTimer = () => {
            if (duration > 0) {
                start = Date.now();
                timer = setTimeout(dismiss, remaining);
            }
        };

        const pauseTimer = () => {
            if (timer) {
                clearTimeout(timer);
                remaining -= Date.now() - start;
            }
        };

        toast.addEventListener('mouseenter', pauseTimer);
        toast.addEventListener('mouseleave', startTimer);
        closeBtn.addEventListener('click', dismiss);

        startTimer();
        return { dismiss };
    };

    const JobProToast = Object.freeze({
        show,
        success: (msg, duration) => show(msg, 'success', duration),
        error: (msg, duration) => show(msg, 'error', duration),
        warning: (msg, duration) => show(msg, 'warning', duration),
        info: (msg, duration) => show(msg, 'info', duration)
    });

    globalThis.JobProToast = JobProToast;

    // Auto-detect and display flash messages from page load
    document.addEventListener('DOMContentLoaded', () => {
        const flashEl = document.querySelector('[data-page-flash]');
        if (flashEl) {
            const msg = flashEl.dataset.pageFlash;
            const type = flashEl.dataset.pageFlashType || 'success';
            if (msg) JobProToast.show(msg, type);
        }
    });
})();

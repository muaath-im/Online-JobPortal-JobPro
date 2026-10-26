// AJAX function for CRUD operations
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            callback(null, xhr.responseText);
        } else {
            callback(xhr.status);
        }
    };
    xhr.send(data);
}

// Form validation
function validateForm(formId) {
    // TODO
}

// Progressive enhancement: all navigation links remain visible without JavaScript.
(() => {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;
    const toggle = header.querySelector('.jp-menu-toggle');
    const navigation = header.querySelector('#primary-navigation');
    if (!toggle || !navigation) return;

    const mobileViewport = window.matchMedia('(max-width: 900px)');
    const setOpen = (open, restoreFocus = false) => {
        header.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (restoreFocus) toggle.focus();
    };
    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    header.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false, true);
        }
    });
    navigation.addEventListener('click', (event) => {
        if (mobileViewport.matches && event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('click', (event) => {
        if (mobileViewport.matches && !header.contains(event.target)) {
            setOpen(false, navigation.contains(document.activeElement));
        }
    });
    mobileViewport.addEventListener('change', () => {
        setOpen(false, mobileViewport.matches && navigation.contains(document.activeElement));
    });
    toggle.hidden = false;
    header.setAttribute('data-menu-ready', '');
})();



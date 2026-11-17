import './cookies.js';

// Forms: after the first press, a [data-submit-once] button says "Sending…" and can't be
// pressed again, so a slow connection doesn't send the same request twice.
document.querySelectorAll('button[data-submit-once]').forEach((button) => {
    const label = button.textContent;

    button.form?.addEventListener('submit', (event) => {
        if (button.disabled) {
            event.preventDefault();
            return;
        }
        // Wait a moment so the browser sends the form before the button is disabled.
        setTimeout(() => {
            button.disabled = true;
            button.textContent = button.dataset.sendingText || label;
        }, 0);
    });

    // Coming back with the browser's Back button: make the button usable again.
    window.addEventListener('pageshow', () => {
        button.disabled = false;
        button.textContent = label;
    });
});

// Mobile menu: open/close with the menu button, close with Escape.
const toggle = document.querySelector('[data-menu-toggle]');
const menu = document.getElementById('mobile-menu');

if (toggle && menu) {
    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        menu.hidden = !open;
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
}

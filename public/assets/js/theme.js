(function () {
    const storageKey = 'finance-tracker-theme';
    const root = document.documentElement;
    const initialTheme = localStorage.getItem(storageKey) === 'light' ? 'light' : 'dark';

    root.dataset.theme = initialTheme;

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('[data-theme-toggle]');

        if (!toggle) {
            return;
        }

        function updateToggle() {
            const isLight = root.dataset.theme === 'light';
            const nextTheme = isLight ? 'dark' : 'light';
            const label = nextTheme === 'light' ? 'Light theme' : 'Dark theme';

            toggle.textContent = label;
            toggle.setAttribute('aria-label', 'Switch to ' + nextTheme + ' theme');
        }

        toggle.addEventListener('click', function () {
            const nextTheme = root.dataset.theme === 'light' ? 'dark' : 'light';
            root.dataset.theme = nextTheme;
            updateToggle();
            document.dispatchEvent(new CustomEvent('finance-theme-change'));
            localStorage.setItem(storageKey, nextTheme);
        });

        updateToggle();
    });
})();

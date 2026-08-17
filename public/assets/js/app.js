document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('#sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
        document.addEventListener('click', (event) => {
            if (window.innerWidth > 820 || !sidebar.classList.contains('is-open')) return;
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) sidebar.classList.remove('is-open');
        });
    }
});

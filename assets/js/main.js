/*
 * CaraTemple main script.
 * Handles responsive sidebar interactions for the landing page layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const openButton = document.querySelector('[data-menu-toggle]');
    const closeButton = document.querySelector('[data-menu-close]');

    if (sidebar && openButton) {
        openButton.addEventListener('click', () => {
            sidebar.classList.add('is-open');
        });
    }

    if (sidebar && closeButton) {
        closeButton.addEventListener('click', () => {
            sidebar.classList.remove('is-open');
        });
    }

    document.addEventListener('click', (event) => {
        if (!sidebar?.classList.contains('is-open')) {
            return;
        }

        if (event.target instanceof Node && sidebar.contains(event.target)) {
            return;
        }

        if (event.target === openButton) {
            return;
        }

        sidebar.classList.remove('is-open');
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            sidebar.classList.remove('is-open');
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar?.classList.remove('is-open');
        }
    });
});

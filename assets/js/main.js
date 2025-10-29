/*
 * CaraTemple main script.
 * Handles simple UI interactions for the starter template.
 */

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const menuLinks = document.querySelector('[data-menu-links]');

    if (menuToggle && menuLinks) {
        menuToggle.addEventListener('click', () => {
            menuLinks.classList.toggle('is-open');
        });
    }
});

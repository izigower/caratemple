<?php
/**
 * Global navigation component.
 *
 * Provides the main menu required by the CaraTemple design system. The menu will be included
 * on every public page.
 *
 * @package CaraTemple\Includes
 */
?>
<nav class="primary-nav" aria-label="Navigation principale">
    <div class="brand-area">
        <a class="brand" href="<?= BASE_URL; ?>/index.php" aria-label="CaraTemple - accueil">
            <img src="<?= BASE_URL; ?>/assets/images/logo-caratemple.svg" alt="Logo CaraTemple" class="brand-logo" />
            <span class="brand-name">CaraTemple</span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" data-menu-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    <ul class="nav-links" data-menu-links>
        <li><a href="#discussions" class="nav-link">Discussions</a></li>
        <li><a href="#new-question" class="nav-link">Nouvelle question</a></li>
        <li><a href="#about" class="nav-link">À propos</a></li>
        <li><a href="#contact" class="nav-link">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <a class="btn secondary" href="#login">Connexion</a>
        <a class="btn primary" href="#register">Rejoindre le Temple</a>
    </div>
</nav>

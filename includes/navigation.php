<?php
/**
 * Global navigation component.
 *
 * Provides the primary application bar consistent with the CaraTemple design system.
 *
 * @package CaraTemple\Includes
 */
?>
<nav class="app-bar" aria-label="Barre principale">
    <div class="app-bar__section app-bar__section--left">
        <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" data-menu-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a class="brand" href="<?= BASE_URL; ?>/index.php" aria-label="CaraTemple - accueil">
            <img src="<?= BASE_URL; ?>/assets/images/logo-caratemple.svg" alt="Logo CaraTemple" class="brand-logo" />
            <span class="brand-name">CaraTemple</span>
        </a>
    </div>
    <div class="app-bar__section app-bar__section--center">
        <h1 class="app-bar__title">Nouvelle Question</h1>
    </div>
    <div class="app-bar__section app-bar__section--right">
        <a class="btn ghost" href="#join" aria-label="Rejoindre CaraTemple">Rejoindre le Temple</a>
        <a class="btn secondary" href="#login" aria-label="Se connecter">Connexion</a>
        <a class="btn primary" href="#new-post" aria-label="Créer un nouveau post">
            <span aria-hidden="true">＋</span>
            <span>Créer un post</span>
        </a>
    </div>
</nav>

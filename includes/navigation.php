<?php
/**
 * Global navigation component.
 *
 * Provides the primary application bar consistent with the CaraTemple design system.
 *
 * @package CaraTemple\Includes
 */
require_once __DIR__ . '/auth.php';

$appBarTitle = $app_bar_title ?? 'Nouvelle Question';
$currentUser = current_user();
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
        <h1 class="app-bar__title"><?= htmlspecialchars($appBarTitle); ?></h1>
    </div>
    <div class="app-bar__section app-bar__section--right">
        <?php if ($currentUser !== null) : ?>
            <span class="app-bar__welcome" aria-live="polite">
                Bonjour, <strong><?= htmlspecialchars($currentUser['username']); ?></strong>
            </span>
            <a class="btn secondary" href="<?= BASE_URL; ?>/logout.php" aria-label="Se déconnecter">Déconnexion</a>
            <a class="btn primary" href="<?= BASE_URL; ?>/views/discussion_create.php" aria-label="Créer un nouveau post">
                <span aria-hidden="true">＋</span>
                <span>Créer un post</span>
            </a>
        <?php else : ?>
            <a class="btn ghost" href="<?= BASE_URL; ?>/views/register.php" aria-label="Rejoindre CaraTemple">Rejoindre le Temple</a>
            <a class="btn secondary" href="<?= BASE_URL; ?>/views/login.php" aria-label="Se connecter">Connexion</a>
            <a class="btn primary" href="<?= BASE_URL; ?>/views/login.php" aria-label="Créer un nouveau post">
                <span aria-hidden="true">＋</span>
                <span>Créer un post</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

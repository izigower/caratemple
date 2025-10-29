<?php
/**
 * Global footer component.
 *
 * Contains base footer content and the back-to-top anchor required by the specification.
 *
 * @package CaraTemple\Includes
 */
?>
<footer class="footer" id="contact">
    <div class="footer-content">
        <p>&copy; <?= date('Y'); ?> CaraTemple. Tous droits réservés.</p>
        <nav class="footer-nav" aria-label="Liens internes CaraTemple">
            <ul>
                <li><a href="<?= BASE_URL; ?>/index.php#discussions">Discussions</a></li>
                <li><a href="<?= BASE_URL; ?>/views/register.php#register">Inscription</a></li>
                <li><a href="<?= BASE_URL; ?>/views/login.php#login">Connexion</a></li>
                <li><a href="<?= BASE_URL; ?>/views/discussion_create.php#new-discussion">Nouvelle discussion</a></li>
            </ul>
        </nav>
        <a class="back-to-top" href="#top" aria-label="Revenir en haut de la page">↑ Retour en haut</a>
    </div>
</footer>
<script src="<?= BASE_URL; ?>/assets/js/main.js" defer></script>
</body>
</html>

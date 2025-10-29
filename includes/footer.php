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
        <p>
            <a href="#discussions">Discussions</a> ·
            <a href="#about">À propos</a> ·
            <a href="#contact">Contact</a>
        </p>
        <a class="back-to-top" href="#top" aria-label="Revenir en haut de la page">↑ Retour en haut</a>
    </div>
</footer>
<script src="<?= BASE_URL; ?>/assets/js/main.js" defer></script>
</body>
</html>

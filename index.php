<?php
/**
 * CaraTemple landing page.
 *
 * Displays the initial marketing content and placeholder sections for the forum.
 *
 * @package CaraTemple
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero" id="discussions">
        <div class="hero-content">
            <h1>Le Temple de Carapuce t'attend</h1>
            <p>
                Plus de 150 nouveaux messages de disciples CaraTemple. Prépare ton équipe et viens partager
                tes meilleures stratégies aquatiques avec une communauté bienveillante.
            </p>
            <div class="cta-group">
                <a class="btn primary" href="#register">Rejoindre le Temple</a>
                <a class="btn secondary" href="#login">Se connecter</a>
            </div>
        </div>
        <img src="<?= BASE_URL; ?>/assets/images/hero-illustration.svg" alt="Illustration aquatique CaraTemple" class="hero-illustration" />
    </section>

    <h2 class="section-title" id="about">CaraTemple en un coup d'œil</h2>
    <section class="cards-grid" aria-label="Avantages CaraTemple">
        <article class="card">
            <h3>Discussions immersives</h3>
            <p>Organise tes sujets par tags, vote pour les meilleures réponses et inspire la prochaine génération de dresseurs.</p>
        </article>
        <article class="card">
            <h3>Mur social</h3>
            <p>Retrouve les publications de tes amis, découvre leurs captures et reste informé des événements du Temple.</p>
        </article>
        <article class="card">
            <h3>Espace membre sécurisé</h3>
            <p>Connexion protégée, mots de passe hachés et modération active pour garantir une expérience sereine.</p>
        </article>
    </section>

    <h2 class="section-title" id="new-question">Prochaine étape</h2>
    <section class="cards-grid" aria-label="Étapes de mise en œuvre">
        <article class="card">
            <h3>Base de données</h3>
            <p>Structure MySQL dédiée aux utilisateurs, messages et interactions, prête à être déployée.</p>
        </article>
        <article class="card">
            <h3>Interface forum</h3>
            <p>Maquettes détaillées pour les discussions, profils et administration afin de guider le développement.</p>
        </article>
        <article class="card">
            <h3>Administration</h3>
            <p>Espace de gestion des utilisateurs et des contenus avec actions sécurisées.</p>
        </article>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php';

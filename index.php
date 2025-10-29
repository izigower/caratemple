<?php
/**
 * CaraTemple public landing page following the "Main - Non Connecté" mockup.
 *
 * Displays the community feed preview with sidebar navigation and right rail highlights.
 *
 * @package CaraTemple
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
?>
<main class="page" id="discussions">
    <div class="dashboard-layout">
        <aside class="sidebar" data-sidebar>
            <button class="sidebar__close" type="button" aria-label="Fermer le menu" data-menu-close>
                <span aria-hidden="true">×</span>
            </button>
            <form class="search" role="search">
                <label class="search__label" for="search-input">Rechercher</label>
                <div class="search__field">
                    <span class="search__icon" aria-hidden="true">🔍</span>
                    <input id="search-input" type="search" name="query" placeholder="Chercher un sujet" />
                </div>
            </form>

            <section class="sidebar__section" aria-labelledby="menu-title">
                <h2 class="sidebar__title" id="menu-title">Menu</h2>
                <ul class="sidebar__links">
                    <li class="is-active">
                        <a href="#discussions" class="sidebar__link" aria-current="page">Discussions</a>
                    </li>
                    <li><a href="#tags" class="sidebar__link">Tags</a></li>
                    <li><a href="#news" class="sidebar__link">Actualités du Temple</a></li>
                </ul>
            </section>

            <section class="sidebar__section" aria-labelledby="personal-title">
                <h2 class="sidebar__title" id="personal-title">Espace personnel</h2>
                <ul class="sidebar__links">
                    <li class="is-disabled"><span>Mes contributions</span></li>
                    <li class="is-disabled"><span>Mes commentaires</span></li>
                    <li class="is-disabled"><span>Mes favoris</span></li>
                </ul>
                <p class="sidebar__hint">Connecte-toi pour débloquer ces fonctionnalités.</p>
            </section>
        </aside>

        <section class="feed" aria-label="Discussions de la communauté">
            <div class="feed__header">
                <h2 class="feed__title">Discussions en cours</h2>
                <ul class="pill-nav" role="tablist">
                    <li role="presentation">
                        <button class="pill is-active" type="button" role="tab" aria-selected="true">Nouveaux</button>
                    </li>
                    <li role="presentation">
                        <button class="pill" type="button" role="tab">Populaire</button>
                    </li>
                    <li role="presentation">
                        <button class="pill" type="button" role="tab">Sans réponse</button>
                    </li>
                    <li role="presentation">
                        <button class="pill" type="button" role="tab">Fermé</button>
                    </li>
                </ul>
            </div>

            <article class="discussion-card" aria-labelledby="discussion-1-title">
                <header class="discussion-card__header">
                    <div class="discussion-card__author">
                        <img src="<?= BASE_URL; ?>/assets/images/avatar-meliora.svg" alt="Avatar de Meliora" />
                        <div>
                            <h3 id="discussion-1-title">Quel est le meilleur jeu Pokémon avec Carapuce ?</h3>
                            <p class="discussion-card__meta">Meliora · il y a 2 heures</p>
                        </div>
                    </div>
                    <span class="status-badge">Nouveau</span>
                </header>
                <p class="discussion-card__excerpt">Je cherche une intro de Carapuce avec vraiment mise en avant. Avez-vous des recommandations ?</p>
                <footer class="discussion-card__footer" aria-label="Statistiques de la discussion">
                    <span aria-label="15 commentaires">💬 15</span>
                    <span aria-label="155 vues">👁️ 155</span>
                    <button class="tag" type="button">Stratégie</button>
                </footer>
            </article>

            <article class="discussion-card" aria-labelledby="discussion-2-title">
                <header class="discussion-card__header">
                    <div class="discussion-card__author">
                        <img src="<?= BASE_URL; ?>/assets/images/avatar-pokaflow.svg" alt="Avatar de Pokaflow" />
                        <div>
                            <h3 id="discussion-2-title">Carapuce vs Salamèche : qui gagne vraiment ?</h3>
                            <p class="discussion-card__meta">Pokaflow · il y a 5 heures</p>
                        </div>
                    </div>
                    <span class="status-badge status-badge--popular">Populaire</span>
                </header>
                <p class="discussion-card__excerpt">Je veux vos arguments, stratégie par stratégie. Débattons !</p>
                <footer class="discussion-card__footer" aria-label="Statistiques de la discussion">
                    <span aria-label="32 commentaires">💬 32</span>
                    <span aria-label="512 vues">👁️ 512</span>
                    <button class="tag" type="button">Versus</button>
                </footer>
            </article>

            <article class="discussion-card" aria-labelledby="discussion-3-title">
                <header class="discussion-card__header">
                    <div class="discussion-card__author">
                        <img src="<?= BASE_URL; ?>/assets/images/avatar-hugocho.svg" alt="Avatar de Hugocho" />
                        <div>
                            <h3 id="discussion-3-title">Collection de cartes Carapuce : vos plus belles trouvailles ?</h3>
                            <p class="discussion-card__meta">Hugocho · hier</p>
                        </div>
                    </div>
                    <span class="status-badge status-badge--open">Ouvert</span>
                </header>
                <p class="discussion-card__excerpt">Partagez vos cartes les plus rares ou vos éditions préférées.</p>
                <footer class="discussion-card__footer" aria-label="Statistiques de la discussion">
                    <span aria-label="18 commentaires">💬 18</span>
                    <span aria-label="245 vues">👁️ 245</span>
                    <button class="tag" type="button">Collection</button>
                </footer>
            </article>

            <article class="discussion-card" aria-labelledby="discussion-4-title">
                <header class="discussion-card__header">
                    <div class="discussion-card__author">
                        <img src="<?= BASE_URL; ?>/assets/images/avatar-lola.svg" alt="Avatar de Lola" />
                        <div>
                            <h3 id="discussion-4-title">Équipe aquatique : vos indispensables ?</h3>
                            <p class="discussion-card__meta">Lola · il y a 3 jours</p>
                        </div>
                    </div>
                    <span class="status-badge status-badge--archived">Archivé</span>
                </header>
                <p class="discussion-card__excerpt">Vos movesets préférés pour dominer les arènes aquatiques ?</p>
                <footer class="discussion-card__footer" aria-label="Statistiques de la discussion">
                    <span aria-label="9 commentaires">💬 9</span>
                    <span aria-label="120 vues">👁️ 120</span>
                    <button class="tag" type="button">Compétitif</button>
                </footer>
            </article>
        </section>

        <aside class="right-rail" aria-labelledby="spotlight-title">
            <div class="right-rail__section">
                <h2 class="right-rail__title" id="spotlight-title">À lire absolument</h2>
                <ul class="right-rail__list">
                    <li><a href="#" class="right-rail__link">Guide du Temple Carapuce</a></li>
                    <li><a href="#" class="right-rail__link">Top 10 des équipes aquatiques</a></li>
                    <li><a href="#" class="right-rail__link">Événements IRL Carapuce</a></li>
                </ul>
            </div>
            <div class="right-rail__section">
                <h2 class="right-rail__title">Liens utiles</h2>
                <ul class="right-rail__list">
                    <li><a href="#" class="right-rail__link">Wiki Carapuce</a></li>
                    <li><a href="#" class="right-rail__link">Règles du Temple</a></li>
                    <li><a href="#" class="right-rail__link">Support &amp; sécurité</a></li>
                </ul>
            </div>
        </aside>
    </div>
    <div id="join" class="sr-only" aria-hidden="true"></div>
    <div id="login" class="sr-only" aria-hidden="true"></div>
    <div id="new-post" class="sr-only" aria-hidden="true"></div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';

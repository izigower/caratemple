<?php
/**
 * CaraTemple community discussions listing.
 *
 * Displays the main forum feed with dynamic statistics pulled from the database.
 *
 * @package CaraTemple
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/discussions.php';

$page_title = 'Discussions · CaraTemple';
$page_description = 'Explore les discussions CaraTemple : stratégies Carapuce, affrontements et collections rares partagés par la communauté.';
$page_url = BASE_URL . '/index.php';
$sidebar_target_id = 'sidebar-navigation';
$app_bar_title = 'Discussions';
$current_user = current_user();
$discussions = fetch_latest_discussions();
$spotlightDiscussions = array_slice($discussions, 0, 3);
$createDiscussionUrl = $current_user !== null ? BASE_URL . '/views/discussion_create.php' : BASE_URL . '/views/login.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/flash.php';
?>
<main class="page" id="main-content">
    <div class="dashboard-layout" id="discussions">
        <aside class="sidebar" id="<?= htmlspecialchars($sidebar_target_id); ?>" data-sidebar>
            <button class="sidebar__close" type="button" aria-label="Fermer le menu" data-menu-close>
                <span aria-hidden="true">×</span>
            </button>
            <div class="search" role="search">
                <label class="search__label" for="search-input">Rechercher</label>
                <div class="search__field">
                    <span class="search__icon" aria-hidden="true">🔍</span>
                    <input 
                        id="search-input" 
                        type="search" 
                        name="query" 
                        placeholder="Chercher un sujet" 
                        data-instant-search
                        autocomplete="off" />
                </div>
                <div class="search-results" data-search-results style="display: none;"></div>
            </div>

            <section class="sidebar__section" aria-labelledby="menu-title">
                <h2 class="sidebar__title" id="menu-title">Menu</h2>
                <ul class="sidebar__links">
                    <li class="is-active">
                        <a href="<?= BASE_URL; ?>/index.php" class="sidebar__link" aria-current="page">Discussions</a>
                    </li>
                    <li class="is-disabled"><span>Tags</span></li>
                    <li class="is-disabled"><span>Actualités du Temple</span></li>
                </ul>
            </section>

            <section class="sidebar__section" aria-labelledby="personal-title">
                <h2 class="sidebar__title" id="personal-title">Espace personnel</h2>
                <ul class="sidebar__links">
                    <li class="is-disabled"><span>Mes contributions</span></li>
                    <li class="is-disabled"><span>Mes commentaires</span></li>
                    <li class="is-disabled"><span>Mes favoris</span></li>
                </ul>
                <?php if ($current_user === null) : ?>
                    <p class="sidebar__hint">Connecte-toi pour débloquer ces fonctionnalités.</p>
                <?php else : ?>
                    <p class="sidebar__hint">Bienvenue dans ton espace CaraTemple.</p>
                <?php endif; ?>
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
                        <button class="pill" type="button" role="tab" aria-disabled="true">Populaire</button>
                    </li>
                    <li role="presentation">
                        <button class="pill" type="button" role="tab" aria-disabled="true">Sans réponse</button>
                    </li>
                    <li role="presentation">
                        <button class="pill" type="button" role="tab" aria-disabled="true">Fermé</button>
                    </li>
                </ul>
            </div>

            <?php if ($current_user !== null) : ?>
                <div class="feed__cta">
                    <p>Une nouvelle idée ? Publie ta question et reçois l'avis des maîtres Carapuce.</p>
                    <a class="btn primary" href="<?= htmlspecialchars($createDiscussionUrl); ?>">Créer une discussion</a>
                </div>
            <?php else : ?>
                <div class="feed__cta feed__cta--guest">
                    <p>Inscris-toi pour poser ta première question et interagir avec la communauté.</p>
                    <a class="btn secondary" href="<?= BASE_URL; ?>/views/register.php">Rejoindre le Temple</a>
                </div>
            <?php endif; ?>

            <?php if ($discussions === []) : ?>
                <div class="empty-state" role="status">
                    <h3>Aucune discussion pour le moment</h3>
                    <p>Soyez le premier à lancer un sujet autour de Carapuce !</p>
                </div>
            <?php else : ?>
                <?php foreach ($discussions as $discussion) : ?>
                    <?php
                    $discussionLink = BASE_URL . '/views/discussion.php?id=' . (int) $discussion['id'];
                    $createdAt = $discussion['created_at'] ?? '';
                    $relativeTime = $createdAt ? format_relative_time($createdAt) : '';
                    $repliesCount = (int) $discussion['replies_count'];
                    $viewsCount = (int) $discussion['views_count'];
                    $statusLabel = null;
                    $createdTimestamp = $createdAt ? strtotime($createdAt) : false;
                    if ($createdTimestamp !== false && (time() - $createdTimestamp) < 86400) {
                        $statusLabel = 'Nouveau';
                    } elseif ($repliesCount >= 10) {
                        $statusLabel = 'Populaire';
                    }
                    $excerpt = $discussion['tag_line'] !== null && $discussion['tag_line'] !== ''
                        ? $discussion['tag_line']
                        : create_excerpt($discussion['body']);
                    ?>
                    <article class="discussion-card" aria-labelledby="discussion-<?= (int) $discussion['id']; ?>-title">
                        <header class="discussion-card__header">
                            <div class="discussion-card__author">
                                <div class="avatar avatar--small" aria-hidden="true">🐢</div>
                                <div>
                                    <h3 id="discussion-<?= (int) $discussion['id']; ?>-title">
                                        <a href="<?= htmlspecialchars($discussionLink); ?>">
                                            <?= htmlspecialchars($discussion['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="discussion-card__meta">
                                        <?= htmlspecialchars($discussion['username']); ?> · <?= htmlspecialchars($relativeTime); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($statusLabel !== null) : ?>
                                <span class="status-badge<?= $statusLabel === 'Populaire' ? ' status-badge--popular' : ''; ?>">
                                    <?= htmlspecialchars($statusLabel); ?>
                                </span>
                            <?php endif; ?>
                        </header>
                        <p class="discussion-card__excerpt">
                            <?= htmlspecialchars($excerpt); ?>
                        </p>
                        <footer class="discussion-card__footer" aria-label="Statistiques de la discussion">
                            <span aria-label="<?= $repliesCount; ?> réponses">💬 <?= $repliesCount; ?></span>
                            <span aria-label="<?= $viewsCount; ?> vues">👁️ <?= $viewsCount; ?></span>
                            <a class="tag" href="<?= htmlspecialchars($discussionLink); ?>"><?= htmlspecialchars($discussion['category']); ?></a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="right-rail" aria-labelledby="spotlight-title">
            <div class="right-rail__section">
                <h2 class="right-rail__title" id="spotlight-title">À lire absolument</h2>
                <ul class="right-rail__list">
                    <?php if ($spotlightDiscussions === []) : ?>
                        <li><span class="right-rail__link right-rail__link--disabled">Aucune discussion à mettre en avant pour le moment.</span></li>
                    <?php else : ?>
                        <?php foreach ($spotlightDiscussions as $highlight) : ?>
                            <?php
                            $highlightLink = BASE_URL . '/views/discussion.php?id=' . (int) $highlight['id'];
                            ?>
                            <li>
                                <a
                                    class="right-rail__link"
                                    href="<?= htmlspecialchars($highlightLink); ?>"
                                    aria-label="Lire la discussion : <?= htmlspecialchars($highlight['title']); ?>"
                                >
                                    <?= htmlspecialchars($highlight['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="right-rail__section">
                <h2 class="right-rail__title">Liens utiles</h2>
                <ul class="right-rail__list">
                    <li><a class="right-rail__link" href="<?= BASE_URL; ?>/views/register.php">Créer un compte CaraTemple</a></li>
                    <li><a class="right-rail__link" href="<?= BASE_URL; ?>/views/login.php">Se connecter</a></li>
                    <li><a class="right-rail__link" href="<?= htmlspecialchars($createDiscussionUrl); ?>">Publier une discussion</a></li>
                </ul>
            </div>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

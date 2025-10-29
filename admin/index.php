<?php
/**
 * CaraTemple administration dashboard.
 *
 * Provides user and message moderation capabilities restricted to administrators.
 *
 * @package CaraTemple\Admin
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

$adminUser = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfKey = $_POST['csrf_key'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if ($csrfKey === '' || !validate_csrf_token($csrfKey, $csrfToken)) {
        set_flash_message('error', 'La requête a expiré, merci de réessayer.');
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }

    switch ($action) {
        case 'delete_user':
            $userId = (int) ($_POST['user_id'] ?? 0);
            $targetUser = admin_find_user($userId);

            if ($userId < 1 || $targetUser === null) {
                set_flash_message('error', 'Utilisateur introuvable.');
                break;
            }

            if ($userId === $adminUser['id']) {
                set_flash_message('error', 'Tu ne peux pas supprimer ton propre compte administrateur.');
                break;
            }

            if ((int) $targetUser['is_admin'] === 1 && admin_count_admins() <= 1) {
                set_flash_message('error', 'Impossible de supprimer le dernier administrateur.');
                break;
            }

            if (admin_delete_user($userId)) {
                set_flash_message('success', 'Utilisateur supprimé avec succès.');
            } else {
                set_flash_message('error', 'La suppression de l’utilisateur a échoué.');
            }
            break;

        case 'delete_discussion':
            $discussionId = (int) ($_POST['discussion_id'] ?? 0);

            if ($discussionId < 1) {
                set_flash_message('error', 'Discussion introuvable.');
                break;
            }

            if (admin_delete_discussion($discussionId)) {
                set_flash_message('success', 'Discussion supprimée.');
            } else {
                set_flash_message('error', 'Suppression impossible, réessaie plus tard.');
            }
            break;

        case 'delete_post':
            $postId = (int) ($_POST['post_id'] ?? 0);
            $post = admin_find_post($postId);

            if ($postId < 1 || $post === null) {
                set_flash_message('error', 'Message introuvable.');
                break;
            }

            if ((int) $post['is_root'] === 1) {
                if (admin_delete_discussion((int) $post['discussion_id'])) {
                    set_flash_message('success', 'Discussion supprimée (message racine).');
                } else {
                    set_flash_message('error', 'Suppression de la discussion impossible.');
                }
                break;
            }

            if ((int) $post['is_deleted'] === 1) {
                set_flash_message('info', 'Ce message est déjà supprimé.');
                break;
            }

            if (admin_delete_post($postId)) {
                set_flash_message('success', 'Message supprimé.');
            } else {
                set_flash_message('error', 'La suppression du message a échoué.');
            }
            break;

        default:
            set_flash_message('error', 'Action non reconnue.');
            break;
    }

    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$page_title = 'CaraTemple · Administration';
$page_description = 'Gère les utilisateurs et les discussions CaraTemple depuis le tableau de bord administrateur.';
$page_url = BASE_URL . '/admin/index.php';
$sidebar_target_id = null;
$meta_robots = 'noindex,nofollow';
$app_bar_title = 'Administration';
$body_class = 'admin-page';
$stats = fetch_admin_dashboard_stats();
$recentUsers = fetch_admin_recent_users();
$recentDiscussions = fetch_admin_recent_discussions();
$recentPosts = fetch_admin_recent_posts();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/flash.php';
?>
<main class="page admin-page" id="main-content">
    <div class="admin-layout">
        <section class="admin-section">
            <h2 class="admin-section__title">Tableau de bord</h2>
            <div class="admin-stats">
                <article class="admin-card" aria-label="Nombre total d'utilisateurs">
                    <h3 class="admin-card__label">Utilisateurs</h3>
                    <p class="admin-card__value"><?= (int) $stats['users']; ?></p>
                </article>
                <article class="admin-card" aria-label="Nombre total de discussions">
                    <h3 class="admin-card__label">Discussions</h3>
                    <p class="admin-card__value"><?= (int) $stats['discussions']; ?></p>
                </article>
                <article class="admin-card" aria-label="Nombre total de messages">
                    <h3 class="admin-card__label">Messages</h3>
                    <p class="admin-card__value"><?= (int) $stats['posts']; ?></p>
                </article>
                <article class="admin-card" aria-label="Nombre total de likes">
                    <h3 class="admin-card__label">Likes</h3>
                    <p class="admin-card__value"><?= (int) $stats['likes']; ?></p>
                </article>
            </div>
        </section>

        <section class="admin-section" aria-labelledby="users-title">
            <div class="admin-section__header">
                <h2 class="admin-section__title" id="users-title">Gestion des utilisateurs</h2>
                <p class="admin-section__subtitle">Supprime les comptes problématiques et surveille les nouvelles inscriptions.</p>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Pseudo</th>
                            <th scope="col">Email</th>
                            <th scope="col">Rôle</th>
                            <th scope="col">Discussions</th>
                            <th scope="col">Messages</th>
                            <th scope="col" class="admin-table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentUsers === []) : ?>
                            <tr>
                                <td colspan="6">Aucun utilisateur enregistré pour le moment.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recentUsers as $user) : ?>
                                <?php
                                $userId = (int) $user['id'];
                                $tokenKey = 'admin_delete_user_' . $userId;
                                $csrfToken = generate_csrf_token($tokenKey);
                                ?>
                                <tr>
                                    <th scope="row">
                                        <span class="admin-identifier">#<?= $userId; ?></span>
                                        <?= htmlspecialchars($user['username']); ?>
                                    </th>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ((int) $user['is_admin'] === 1) : ?>
                                            <span class="admin-badge admin-badge--admin">Admin</span>
                                        <?php else : ?>
                                            <span class="admin-badge">Membre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $user['discussions_count']; ?></td>
                                    <td><?= (int) $user['posts_count']; ?></td>
                                    <td class="admin-table__actions">
                                        <?php if ($userId === $adminUser['id']) : ?>
                                            <span class="admin-hint">Compte actuel</span>
                                        <?php else : ?>
                                            <form method="post" data-confirm="Supprimer définitivement ce compte ?" class="admin-inline-form">
                                                <input type="hidden" name="action" value="delete_user" />
                                                <input type="hidden" name="user_id" value="<?= $userId; ?>" />
                                                <input type="hidden" name="csrf_key" value="<?= htmlspecialchars($tokenKey); ?>" />
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                                                <button type="submit" class="btn danger">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section" aria-labelledby="discussions-title">
            <div class="admin-section__header">
                <h2 class="admin-section__title" id="discussions-title">Discussions récentes</h2>
                <p class="admin-section__subtitle">Modère les fils qui ne respectent pas le code du Temple.</p>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Titre</th>
                            <th scope="col">Auteur</th>
                            <th scope="col">Catégorie</th>
                            <th scope="col">Messages</th>
                            <th scope="col">Vues</th>
                            <th scope="col" class="admin-table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentDiscussions === []) : ?>
                            <tr>
                                <td colspan="6">Aucune discussion enregistrée.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recentDiscussions as $discussion) : ?>
                                <?php
                                $discussionId = (int) $discussion['id'];
                                $tokenKey = 'admin_delete_discussion_' . $discussionId;
                                $csrfToken = generate_csrf_token($tokenKey);
                                $discussionLink = BASE_URL . '/views/discussion.php?id=' . $discussionId;
                                ?>
                                <tr>
                                    <th scope="row">
                                        <a href="<?= htmlspecialchars($discussionLink); ?>" class="admin-link"><?= htmlspecialchars($discussion['title']); ?></a>
                                    </th>
                                    <td><?= htmlspecialchars($discussion['username']); ?></td>
                                    <td><span class="admin-badge admin-badge--category"><?= htmlspecialchars($discussion['category']); ?></span></td>
                                    <td><?= (int) $discussion['posts_count']; ?></td>
                                    <td><?= (int) $discussion['views_count']; ?></td>
                                    <td class="admin-table__actions">
                                        <form method="post" data-confirm="Supprimer toute la discussion ?" class="admin-inline-form">
                                            <input type="hidden" name="action" value="delete_discussion" />
                                            <input type="hidden" name="discussion_id" value="<?= $discussionId; ?>" />
                                            <input type="hidden" name="csrf_key" value="<?= htmlspecialchars($tokenKey); ?>" />
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                                            <button type="submit" class="btn danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section" aria-labelledby="posts-title">
            <div class="admin-section__header">
                <h2 class="admin-section__title" id="posts-title">Messages récents</h2>
                <p class="admin-section__subtitle">Supprime les messages signalés pour conserver une communauté saine.</p>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Message</th>
                            <th scope="col">Auteur</th>
                            <th scope="col">Discussion</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="admin-table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentPosts === []) : ?>
                            <tr>
                                <td colspan="5">Aucun message posté pour l’instant.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recentPosts as $post) : ?>
                                <?php
                                $postId = (int) $post['id'];
                                $tokenKey = 'admin_delete_post_' . $postId;
                                $csrfToken = generate_csrf_token($tokenKey);
                                $discussionLink = BASE_URL . '/views/discussion.php?id=' . (int) $post['discussion_id'];
                                $statusLabel = ((int) $post['is_deleted'] === 1) ? 'Supprimé' : (((int) $post['is_root'] === 1) ? 'Message initial' : 'Actif');
                                ?>
                                <tr>
                                    <th scope="row">
                                        <span class="admin-message"><?= htmlspecialchars(create_excerpt($post['body'], 80)); ?></span>
                                    </th>
                                    <td><?= htmlspecialchars($post['username']); ?></td>
                                    <td><a class="admin-link" href="<?= htmlspecialchars($discussionLink); ?>"><?= htmlspecialchars($post['title']); ?></a></td>
                                    <td>
                                        <span class="admin-badge <?= (int) $post['is_deleted'] === 1 ? 'admin-badge--muted' : 'admin-badge--active'; ?>"><?= htmlspecialchars($statusLabel); ?></span>
                                    </td>
                                    <td class="admin-table__actions">
                                        <?php if ((int) $post['is_deleted'] === 1) : ?>
                                            <span class="admin-hint">Déjà supprimé</span>
                                        <?php else : ?>
                                            <form method="post" data-confirm="Supprimer ce message ?" class="admin-inline-form">
                                                <input type="hidden" name="action" value="delete_post" />
                                                <input type="hidden" name="post_id" value="<?= $postId; ?>" />
                                                <input type="hidden" name="csrf_key" value="<?= htmlspecialchars($tokenKey); ?>" />
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                                                <button type="submit" class="btn danger">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

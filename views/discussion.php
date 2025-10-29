<?php
/**
 * Detailed discussion page following the "Discussion" mockup.
 *
 * Provides the full thread view with replies, likes and moderation actions.
 *
 * @package CaraTemple\Views
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/discussions.php';

$discussionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($discussionId < 1) {
    set_flash_message('error', 'Discussion introuvable.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$discussion = fetch_discussion($discussionId);

if ($discussion === null) {
    set_flash_message('error', 'Ce sujet n\'existe plus.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$current_user = current_user();
$currentUserId = $current_user['id'] ?? null;
$is_owner = $current_user !== null && $current_user['id'] === (int) $discussion['user_id'];
$baseRedirect = BASE_URL . '/views/discussion.php?id=' . $discussionId;

$edit_mode = $is_owner && ($_GET['edit'] ?? '') === '1';
$editErrors = [];
$editForm = [
    'title' => $discussion['title'],
    'category' => $discussion['category'],
    'tag_line' => $discussion['tag_line'] ?? '',
    'body' => $discussion['body'],
];

$replyError = null;
$replyDraft = '';
$availableCategories = ['Général', 'Stratégie', 'Collection', 'Compétitif', 'Événement'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'reply':
            if (!validate_csrf_token('discussion_reply_' . $discussionId, $_POST['_token'] ?? null)) {
                set_flash_message('error', 'Ta session a expiré. Merci de réessayer.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            if ($current_user === null) {
                set_flash_message('error', 'Connecte-toi pour répondre.');
                header('Location: ' . BASE_URL . '/views/login.php');
                exit;
            }

            $replyDraft = trim($_POST['message'] ?? '');
            $result = create_post($discussionId, $current_user['id'], $replyDraft);
            if ($result['success']) {
                set_flash_message('success', 'Réponse publiée !');
                header('Location: ' . $baseRedirect . '#reponses');
                exit;
            }

            $replyError = $result['error'] ?? 'Impossible de publier ta réponse.';
            break;

        case 'delete_post':
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            if ($postId < 1 || !validate_csrf_token('delete_post_' . $postId, $_POST['_token'] ?? null)) {
                set_flash_message('error', 'Action expirée, merci de réessayer.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            if ($current_user === null) {
                set_flash_message('error', 'Connecte-toi pour gérer tes messages.');
                header('Location: ' . BASE_URL . '/views/login.php');
                exit;
            }

            if (delete_post($postId, $current_user['id'])) {
                set_flash_message('success', 'Message supprimé.');
            } else {
                set_flash_message('error', 'Impossible de supprimer ce message.');
            }

            header('Location: ' . $baseRedirect . '#reponses');
            exit;

        case 'toggle_like':
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            if ($postId < 1 || !validate_csrf_token('toggle_like_' . $postId, $_POST['_token'] ?? null)) {
                set_flash_message('error', 'Action expirée.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            if ($current_user === null) {
                set_flash_message('error', 'Connecte-toi pour aimer un message.');
                header('Location: ' . BASE_URL . '/views/login.php');
                exit;
            }

            toggle_post_like($postId, $current_user['id']);
            header('Location: ' . $baseRedirect . '#post-' . $postId);
            exit;

        case 'delete_discussion':
            if (!validate_csrf_token('delete_discussion_' . $discussionId, $_POST['_token'] ?? null)) {
                set_flash_message('error', 'Action expirée, merci de réessayer.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            if (!$is_owner || $current_user === null) {
                set_flash_message('error', 'Tu ne peux pas supprimer cette discussion.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            if (delete_discussion($discussionId, $current_user['id'])) {
                set_flash_message('success', 'Discussion supprimée.');
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }

            set_flash_message('error', 'Suppression impossible pour le moment.');
            header('Location: ' . $baseRedirect);
            exit;

        case 'update_discussion':
            if (!validate_csrf_token('update_discussion_' . $discussionId, $_POST['_token'] ?? null)) {
                set_flash_message('error', 'Ta session a expiré.');
                header('Location: ' . $baseRedirect . '&edit=1');
                exit;
            }

            if (!$is_owner || $current_user === null) {
                set_flash_message('error', 'Tu ne peux pas modifier cette discussion.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            $edit_mode = true;
            $editForm = [
                'title' => trim($_POST['title'] ?? ''),
                'category' => trim($_POST['category'] ?? 'Général'),
                'tag_line' => trim($_POST['tag_line'] ?? ''),
                'body' => trim($_POST['body'] ?? ''),
            ];

            $result = update_discussion($discussionId, $current_user['id'], $editForm);
            if ($result['success']) {
                set_flash_message('success', 'Discussion mise à jour.');
                header('Location: ' . $baseRedirect);
                exit;
            }

            $editErrors = $result['errors'];
            break;

        default:
            set_flash_message('error', 'Action inconnue.');
            header('Location: ' . $baseRedirect);
            exit;
    }
}

register_discussion_view($discussionId);
$discussion = fetch_discussion($discussionId) ?? $discussion;
$posts = fetch_discussion_posts($discussionId, $currentUserId);

$rootPost = null;
$replies = [];
$participants = [];

foreach ($posts as $post) {
    $participants[$post['user_id']] = $post['username'];

    if ((int) $post['is_root'] === 1) {
        $rootPost = $post;
        continue;
    }

    $replies[] = $post;
}

$totalReplies = count($replies);
$likesOnRoot = $rootPost['likes_count'] ?? 0;
$rootLikedByUser = ($rootPost['liked_by_user'] ?? 0) > 0;

$rootLikeToken = $rootPost ? generate_csrf_token('toggle_like_' . (int) $rootPost['id']) : null;

$page_title = 'Discussion · ' . $discussion['title'];
$app_bar_title = 'Discussion';
$body_class = 'thread-page';

$replyToken = generate_csrf_token('discussion_reply_' . $discussionId);
$updateToken = generate_csrf_token('update_discussion_' . $discussionId);
$deleteToken = generate_csrf_token('delete_discussion_' . $discussionId);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/flash.php';
?>
<main class="page" id="discussion">
    <div class="dashboard-layout">
        <aside class="sidebar" data-sidebar>
            <button class="sidebar__close" type="button" aria-label="Fermer le menu" data-menu-close>
                <span aria-hidden="true">×</span>
            </button>
            <nav class="sidebar__nav" aria-label="Navigation secondaire">
                <section class="sidebar__section" aria-labelledby="menu-title">
                    <h2 class="sidebar__title" id="menu-title">Menu</h2>
                    <ul class="sidebar__links">
                        <li><a class="sidebar__link" href="<?= BASE_URL; ?>/index.php">← Retour aux discussions</a></li>
                        <li class="is-active"><span>Discussion en cours</span></li>
                        <li class="is-disabled"><span>Tags</span></li>
                    </ul>
                </section>
            </nav>
        </aside>

        <section class="thread" aria-labelledby="thread-title">
            <article class="thread-card" id="thread">
                <header class="thread-card__header">
                    <div>
                        <p class="thread-card__category"><?= htmlspecialchars($discussion['category']); ?></p>
                        <h1 class="thread-card__title" id="thread-title"><?= htmlspecialchars($discussion['title']); ?></h1>
                        <?php if (!empty($discussion['tag_line'])) : ?>
                            <p class="thread-card__tagline"><?= htmlspecialchars($discussion['tag_line']); ?></p>
                        <?php endif; ?>
                        <div class="thread-card__meta">
                            <span><?= htmlspecialchars($discussion['username']); ?></span>
                            <span><?= htmlspecialchars(format_relative_time($discussion['created_at'] ?? '')); ?></span>
                            <span aria-label="<?= (int) $discussion['views_count']; ?> vues">👁️ <?= (int) $discussion['views_count']; ?></span>
                            <span aria-label="<?= $totalReplies; ?> réponses">💬 <?= $totalReplies; ?></span>
                            <span aria-label="<?= (int) $likesOnRoot; ?> likes">❤️ <?= (int) $likesOnRoot; ?></span>
                        </div>
                    </div>
                    <?php if ($is_owner) : ?>
                        <div class="thread-card__actions">
                            <a class="btn secondary" href="<?= htmlspecialchars($baseRedirect); ?>&edit=1">Modifier</a>
                            <form method="post">
                                <input type="hidden" name="action" value="delete_discussion" />
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($deleteToken); ?>" />
                                <button class="btn ghost" type="submit" onclick="return confirm('Supprimer cette discussion ?');">Supprimer</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if ($edit_mode) : ?>
                    <?php if (isset($editErrors['general'])) : ?>
                        <div class="form-alert" role="alert"><?= htmlspecialchars($editErrors['general']); ?></div>
                    <?php endif; ?>
                    <form class="discussion-form" method="post" data-validate="discussion" data-form-type="update">
                        <input type="hidden" name="action" value="update_discussion" />
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($updateToken); ?>" />
                        <div class="form-field<?= isset($editErrors['title']) ? ' is-invalid' : ''; ?>" data-field>
                            <label for="title">Titre</label>
                            <div class="input-wrapper">
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    value="<?= htmlspecialchars($editForm['title']); ?>"
                                    required
                                    minlength="6"
                                    maxlength="180"
                                    data-validate-field="title"
                                />
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="title" data-default="Décris clairement ton sujet.">
                                <?= htmlspecialchars($editErrors['title'] ?? 'Décris clairement ton sujet.'); ?>
                            </p>
                        </div>
                        <div class="form-field" data-field>
                            <label for="category">Catégorie</label>
                            <div class="input-wrapper select-wrapper">
                                <select id="category" name="category" data-validate-field="category">
                                    <?php foreach ($availableCategories as $categoryOption) : ?>
                                        <option value="<?= htmlspecialchars($categoryOption); ?>"<?= $editForm['category'] === $categoryOption ? ' selected' : ''; ?>>
                                            <?= htmlspecialchars($categoryOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="category" data-default="Choisis la thématique la plus adaptée.">
                                Choisis la thématique la plus adaptée.
                            </p>
                        </div>
                        <div class="form-field<?= isset($editErrors['tag_line']) ? ' is-invalid' : ''; ?>" data-field>
                            <label for="tag_line">Résumé</label>
                            <div class="input-wrapper">
                                <input
                                    type="text"
                                    id="tag_line"
                                    name="tag_line"
                                    value="<?= htmlspecialchars($editForm['tag_line']); ?>"
                                    maxlength="120"
                                    placeholder="Optionnel : résume ta question"
                                    data-validate-field="tag_line"
                                />
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="tag_line" data-default="120 caractères maximum.">
                                <?= htmlspecialchars($editErrors['tag_line'] ?? '120 caractères maximum.'); ?>
                            </p>
                        </div>
                        <div class="form-field<?= isset($editErrors['body']) ? ' is-invalid' : ''; ?>" data-field>
                            <label for="body">Message</label>
                            <div class="input-wrapper">
                                <textarea
                                    id="body"
                                    name="body"
                                    rows="6"
                                    required
                                    data-validate-field="body"
                                ><?= htmlspecialchars($editForm['body']); ?></textarea>
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="body" data-default="Minimum 20 caractères.">
                                <?= htmlspecialchars($editErrors['body'] ?? 'Minimum 20 caractères.'); ?>
                            </p>
                        </div>
                        <div class="form-actions">
                            <a class="btn ghost" href="<?= $baseRedirect; ?>">Annuler</a>
                            <button class="btn primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="thread-card__content">
                        <?= nl2br(htmlspecialchars($discussion['body'])); ?>
                    </div>
                    <?php if ($rootPost !== null) : ?>
                        <div class="thread-card__footer">
                            <form method="post">
                                <input type="hidden" name="action" value="toggle_like" />
                                <input type="hidden" name="post_id" value="<?= (int) $rootPost['id']; ?>" />
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($rootLikeToken ?? ''); ?>" />
                                <button class="btn secondary" type="submit"<?= $current_user === null ? ' disabled' : ''; ?>>
                                    <?= $rootLikedByUser ? 'Je n\'aime plus' : 'J\'aime'; ?> · <?= (int) $likesOnRoot; ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </article>

            <section class="thread-replies" id="reponses" aria-labelledby="replies-title">
                <div class="thread-replies__header">
                    <h2 id="replies-title">Réponses</h2>
                    <span><?= $totalReplies; ?> message<?= $totalReplies > 1 ? 's' : ''; ?></span>
                </div>

                <?php if ($replies === []) : ?>
                    <div class="empty-state" role="status">
                        <p>Personne n'a encore répondu. Lance la conversation !</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($replies as $reply) : ?>
                        <?php
                        $replyId = (int) $reply['id'];
                        $replyLiked = ((int) $reply['liked_by_user']) > 0;
                        $replyToken = generate_csrf_token('toggle_like_' . $replyId);
                        $deletePostToken = generate_csrf_token('delete_post_' . $replyId);
                        ?>
                        <article class="reply-card" id="post-<?= $replyId; ?>">
                            <header class="reply-card__header">
                                <div class="reply-card__author">
                                    <div class="avatar" aria-hidden="true">🐢</div>
                                    <div>
                                        <h3><?= htmlspecialchars($reply['username']); ?></h3>
                                        <p><?= htmlspecialchars(format_relative_time($reply['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="reply-card__stats">
                                    <span aria-label="<?= (int) $reply['likes_count']; ?> likes">❤️ <?= (int) $reply['likes_count']; ?></span>
                                </div>
                            </header>
                            <div class="reply-card__content">
                                <?= nl2br(htmlspecialchars($reply['body'])); ?>
                            </div>
                            <footer class="reply-card__footer">
                                <form method="post">
                                    <input type="hidden" name="action" value="toggle_like" />
                                    <input type="hidden" name="post_id" value="<?= $replyId; ?>" />
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($replyToken); ?>" />
                                    <button class="btn ghost" type="submit"<?= $current_user === null ? ' disabled' : ''; ?>>
                                        <?= $replyLiked ? 'Retirer le like' : 'J\'aime'; ?>
                                    </button>
                                </form>
                                <?php if ($current_user !== null && $current_user['id'] === (int) $reply['user_id']) : ?>
                                    <form method="post" onsubmit="return confirm('Supprimer ce message ?');">
                                        <input type="hidden" name="action" value="delete_post" />
                                        <input type="hidden" name="post_id" value="<?= $replyId; ?>" />
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($deletePostToken); ?>" />
                                        <button class="btn ghost" type="submit">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="reply-form" aria-labelledby="reply-title">
                <h2 id="reply-title">Répondre</h2>
                <?php if ($current_user === null) : ?>
                    <p class="reply-form__hint">
                        <a href="<?= BASE_URL; ?>/views/login.php">Connecte-toi</a> ou
                        <a href="<?= BASE_URL; ?>/views/register.php">inscris-toi</a> pour participer à la discussion.
                    </p>
                <?php else : ?>
                    <form method="post" class="discussion-form" data-validate="discussion" data-form-type="reply">
                        <input type="hidden" name="action" value="reply" />
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($replyToken); ?>" />
                        <div class="form-field<?= $replyError !== null ? ' is-invalid' : ''; ?>" data-field>
                            <label for="message">Ton message</label>
                            <div class="input-wrapper">
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="5"
                                    placeholder="Partage ton avis sur Carapuce..."
                                    required
                                    data-validate-field="message"
                                ><?= htmlspecialchars($replyDraft); ?></textarea>
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="message" data-default="Minimum 3 caractères.">
                                <?= htmlspecialchars($replyError ?? 'Minimum 3 caractères.'); ?>
                            </p>
                        </div>
                        <div class="form-actions">
                            <button class="btn primary" type="submit">Publier la réponse</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </section>

        <aside class="thread-rail" aria-label="Informations sur la discussion">
            <div class="thread-rail__section">
                <h2>Statistiques</h2>
                <dl class="thread-stats">
                    <div>
                        <dt>Vues</dt>
                        <dd><?= (int) $discussion['views_count']; ?></dd>
                    </div>
                    <div>
                        <dt>Réponses</dt>
                        <dd><?= $totalReplies; ?></dd>
                    </div>
                    <div>
                        <dt>Participants</dt>
                        <dd><?= count($participants); ?></dd>
                    </div>
                </dl>
            </div>
            <div class="thread-rail__section">
                <h2>Auteur</h2>
                <div class="author-card">
                    <div class="avatar" aria-hidden="true">🐢</div>
                    <div>
                        <p class="author-card__name"><?= htmlspecialchars($discussion['username']); ?></p>
                        <p class="author-card__role">Initiateur de la discussion</p>
                    </div>
                </div>
            </div>
            <div class="thread-rail__section">
                <h2>Participants</h2>
                <?php if ($participants === []) : ?>
                    <p class="thread-rail__empty">Aucun participant pour le moment.</p>
                <?php else : ?>
                    <ul class="participant-list">
                        <?php foreach ($participants as $participant) : ?>
                            <li><?= htmlspecialchars($participant); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Discussion creation page inspired by the mockup.
 *
 * Allows authenticated users to start a new topic with validation and CSRF protection.
 *
 * @package CaraTemple\Views
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/discussions.php';

$current_user = current_user();

if ($current_user === null) {
    set_flash_message('error', 'Connecte-toi pour créer une discussion.');
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$availableCategories = ['Général', 'Stratégie', 'Collection', 'Compétitif', 'Événement'];

$formData = [
    'title' => '',
    'category' => 'Général',
    'tag_line' => '',
    'body' => '',
];
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token('discussion_create', $_POST['_token'] ?? null)) {
        set_flash_message('error', 'Ta session a expiré. Merci de réessayer.');
        header('Location: ' . BASE_URL . '/views/discussion_create.php');
        exit;
    }

    $formData = [
        'title' => trim($_POST['title'] ?? ''),
        'category' => trim($_POST['category'] ?? 'Général'),
        'tag_line' => trim($_POST['tag_line'] ?? ''),
        'body' => trim($_POST['body'] ?? ''),
    ];

    $result = create_discussion($current_user['id'], $formData);
    if ($result['success']) {
        set_flash_message('success', 'Ta discussion est en ligne !');
        header('Location: ' . BASE_URL . '/views/discussion.php?id=' . (int) $result['discussion_id']);
        exit;
    }

    $formErrors = $result['errors'];
    if (isset($formErrors['general'])) {
        set_flash_message('error', $formErrors['general']);
    }
}

$page_title = 'Nouvelle discussion';
$app_bar_title = 'Nouvelle question';
$body_class = 'thread-page';
$csrfToken = generate_csrf_token('discussion_create');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/flash.php';
?>
<main class="page" id="new-discussion">
    <div class="dashboard-layout">
        <aside class="sidebar" data-sidebar>
            <button class="sidebar__close" type="button" aria-label="Fermer le menu" data-menu-close>
                <span aria-hidden="true">×</span>
            </button>
            <section class="sidebar__section" aria-labelledby="create-menu">
                <h2 class="sidebar__title" id="create-menu">Navigation</h2>
                <ul class="sidebar__links">
                    <li><a class="sidebar__link" href="<?= BASE_URL; ?>/index.php">← Retour aux discussions</a></li>
                    <li class="is-active"><span>Nouvelle discussion</span></li>
                </ul>
            </section>
        </aside>

        <section class="thread" aria-labelledby="create-title">
            <article class="thread-card">
                <header class="thread-card__header">
                    <div>
                        <p class="thread-card__category">Préparer une nouvelle question</p>
                        <h1 class="thread-card__title" id="create-title">Partage ton sujet avec le Temple</h1>
                        <p class="thread-card__tagline">Décris clairement ta question pour obtenir des réponses pertinentes.</p>
                    </div>
                </header>

                <form class="discussion-form" method="post" data-validate="discussion" data-form-type="create">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                    <div class="form-field<?= isset($formErrors['title']) ? ' is-invalid' : ''; ?>" data-field>
                        <label for="title">Titre</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="Écris un titre percutant"
                                required
                                minlength="6"
                                maxlength="180"
                                value="<?= htmlspecialchars($formData['title']); ?>"
                                data-validate-field="title"
                            />
                            <span class="input-status" aria-hidden="true"></span>
                        </div>
                        <p class="input-feedback" data-feedback="title" data-default="6 caractères minimum.">
                            <?= htmlspecialchars($formErrors['title'] ?? '6 caractères minimum.'); ?>
                        </p>
                    </div>
                    <div class="form-grid">
                        <div class="form-field" data-field>
                            <label for="category">Catégorie</label>
                            <div class="input-wrapper select-wrapper">
                                <select id="category" name="category" data-validate-field="category">
                                    <?php foreach ($availableCategories as $categoryOption) : ?>
                                        <option value="<?= htmlspecialchars($categoryOption); ?>"<?= $formData['category'] === $categoryOption ? ' selected' : ''; ?>>
                                            <?= htmlspecialchars($categoryOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="category" data-default="Choisis la thématique principale.">
                                Choisis la thématique principale.
                            </p>
                        </div>
                        <div class="form-field<?= isset($formErrors['tag_line']) ? ' is-invalid' : ''; ?>" data-field>
                            <label for="tag_line">Résumé</label>
                            <div class="input-wrapper">
                                <input
                                    type="text"
                                    id="tag_line"
                                    name="tag_line"
                                    placeholder="Optionnel : résume ta question"
                                    maxlength="120"
                                    value="<?= htmlspecialchars($formData['tag_line']); ?>"
                                    data-validate-field="tag_line"
                                />
                                <span class="input-status" aria-hidden="true"></span>
                            </div>
                            <p class="input-feedback" data-feedback="tag_line" data-default="120 caractères maximum.">
                                <?= htmlspecialchars($formErrors['tag_line'] ?? '120 caractères maximum.'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="form-field<?= isset($formErrors['body']) ? ' is-invalid' : ''; ?>" data-field>
                        <label for="body">Message</label>
                        <div class="input-wrapper">
                            <textarea
                                id="body"
                                name="body"
                                rows="8"
                                placeholder="Décris ton sujet, partage du contexte et pose tes questions..."
                                required
                                data-validate-field="body"
                            ><?= htmlspecialchars($formData['body']); ?></textarea>
                            <span class="input-status" aria-hidden="true"></span>
                        </div>
                        <p class="input-feedback" data-feedback="body" data-default="Minimum 20 caractères.">
                            <?= htmlspecialchars($formErrors['body'] ?? 'Minimum 20 caractères.'); ?>
                        </p>
                    </div>
                    <div class="form-toolbar" aria-label="Actions supplémentaires">
                        <button class="btn ghost" type="button" disabled>Ajouter des images</button>
                        <button class="btn ghost" type="button" disabled>Enregistrer comme brouillon</button>
                    </div>
                    <div class="form-actions">
                        <a class="btn ghost" href="<?= BASE_URL; ?>/index.php">Annuler</a>
                        <button class="btn primary" type="submit">Publier</button>
                    </div>
                </form>
            </article>
        </section>

        <aside class="thread-rail" aria-label="Conseils de publication">
            <div class="thread-rail__section">
                <h2>Conseils</h2>
                <ul class="participant-list">
                    <li>Explique clairement ton contexte.</li>
                    <li>Ajoute les informations nécessaires pour répondre.</li>
                    <li>Sois respectueux envers les autres membres.</li>
                </ul>
            </div>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

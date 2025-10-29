<?php
/**
 * Registration page for CaraTemple.
 *
 * Mirrors the "Inscription" mockup with secure form handling and inline validation.
 *
 * @package CaraTemple\Views
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'Rejoindre CaraTemple';
$page_description = 'Inscris-toi sur CaraTemple pour créer un profil Carapuce, poster des discussions et rejoindre la communauté.';
$page_url = BASE_URL . '/views/register.php';
$sidebar_target_id = null;
$meta_robots = 'noindex,nofollow';
$body_class = 'auth-page';
$app_bar_title = 'Inscription';

$formData = [
    'username' => '',
    'email' => '',
    'password' => '',
    'password_confirm' => '',
];
$formErrors = [];
$fieldMessages = [
    'username' => '3 à 20 caractères, lettres/chiffres/underscore.',
    'email' => 'Nous n\'afficherons jamais votre email publiquement.',
    'password' => 'Inclure au moins 8 caractères, avec lettres et chiffres.',
    'password_confirm' => 'Doit correspondre au mot de passe précédent.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
    ];

    if (!validate_csrf_token('register', $_POST['_token'] ?? null)) {
        $formErrors['general'] = 'Ta session a expiré. Merci de réessayer.';
    } else {
        $result = register_user($formData);
        if ($result['success']) {
            set_flash_message('success', 'Ton compte est créé ! Connecte-toi pour accéder au Temple.');
            header('Location: ' . BASE_URL . '/views/login.php');
            exit;
        }

        $formErrors = $result['errors'];
    }
}

$csrfToken = generate_csrf_token('register');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/flash.php';
?>
<main class="auth-layout" id="main-content">
    <section class="auth-card" id="register" aria-labelledby="register-title">
        <div class="auth-card__content">
            <h1 class="auth-card__title" id="register-title">Rejoins CaraTemple</h1>
            <p class="auth-card__subtitle">Accède à une communauté passionnée autour de Carapuce.</p>

            <?php if (isset($formErrors['general'])) : ?>
                <div class="form-alert" role="alert">
                    <?= htmlspecialchars($formErrors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate data-validate="auth" data-auth-type="register">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                <div class="form-field<?= isset($formErrors['username']) ? ' is-invalid' : ''; ?>" data-field>
                    <label for="username">Pseudo</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Anselou"
                            required
                            minlength="3"
                            maxlength="20"
                            pattern="[A-Za-z0-9_]{3,20}"
                            value="<?= htmlspecialchars($formData['username']); ?>"
                            data-validate-field="username"
                            aria-describedby="username-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="username-feedback" data-feedback="username" data-default="<?= htmlspecialchars($fieldMessages['username']); ?>">
                        <?= htmlspecialchars($formErrors['username'] ?? $fieldMessages['username']); ?>
                    </p>
                </div>

                <div class="form-field<?= isset($formErrors['email']) ? ' is-invalid' : ''; ?>" data-field>
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="anselou.temple@gmail.com"
                            required
                            value="<?= htmlspecialchars($formData['email']); ?>"
                            data-validate-field="email"
                            aria-describedby="email-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="email-feedback" data-feedback="email" data-default="<?= htmlspecialchars($fieldMessages['email']); ?>">
                        <?= htmlspecialchars($formErrors['email'] ?? $fieldMessages['email']); ?>
                    </p>
                </div>

                <div class="form-field<?= isset($formErrors['password']) ? ' is-invalid' : ''; ?>" data-field>
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimum 8 caractères"
                            required
                            minlength="8"
                            data-validate-field="password"
                            aria-describedby="password-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="password-feedback" data-feedback="password" data-default="<?= htmlspecialchars($fieldMessages['password']); ?>">
                        <?= htmlspecialchars($formErrors['password'] ?? $fieldMessages['password']); ?>
                    </p>
                </div>

                <div class="form-field<?= isset($formErrors['password_confirm']) ? ' is-invalid' : ''; ?>" data-field>
                    <label for="password_confirm">Confirme ton mot de passe</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            placeholder="Confirme ton mot de passe"
                            required
                            data-validate-field="password_confirm"
                            aria-describedby="password-confirm-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="password-confirm-feedback" data-feedback="password_confirm" data-default="<?= htmlspecialchars($fieldMessages['password_confirm']); ?>">
                        <?= htmlspecialchars($formErrors['password_confirm'] ?? $fieldMessages['password_confirm']); ?>
                    </p>
                </div>

                <button class="btn primary btn--wide" type="submit">Rejoindre le Temple</button>
                <p class="form-footnote">
                    Déjà membre ? <a href="<?= BASE_URL; ?>/views/login.php">Connecte-toi ici</a>.
                </p>
            </form>
        </div>
    </section>
    <aside class="auth-illustration" aria-hidden="true">
        <img src="<?= BASE_URL; ?>/assets/images/register-illustration.svg" alt="Illustration d'inscription CaraTemple avec Carapuce" />
    </aside>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

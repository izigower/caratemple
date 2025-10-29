<?php
/**
 * Login page for CaraTemple.
 *
 * Presents the authentication form with live validation feedback.
 *
 * @package CaraTemple\Views
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (current_user() !== null) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$page_title = 'Connexion CaraTemple';
$body_class = 'auth-page';
$app_bar_title = 'Connexion';

$formData = [
    'email' => '',
    'password' => '',
];
$formErrors = [];
$fieldMessages = [
    'email' => 'Utilise l\'adresse utilisée lors de ton inscription.',
    'password' => '8 caractères minimum.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    if (!validate_csrf_token('login', $_POST['_token'] ?? null)) {
        $formErrors['general'] = 'Ta session a expiré. Merci de réessayer.';
    } else {
        $result = authenticate_user($formData);
        if ($result['success']) {
            set_flash_message('success', 'Connexion réussie. Bienvenue au Temple !');
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $formErrors = $result['errors'];
    }
}

$csrfToken = generate_csrf_token('login');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/flash.php';
?>
<main class="auth-layout" id="login">
    <section class="auth-card" aria-labelledby="login-title">
        <div class="auth-card__content">
            <h1 class="auth-card__title" id="login-title">Entre dans le Temple</h1>
            <p class="auth-card__subtitle">Plus de 150 nouveaux messages t'attendent.</p>

            <?php if (isset($formErrors['general']) && $formErrors['general'] !== 'Identifiants invalides.') : ?>
                <div class="form-alert" role="alert">
                    <?= htmlspecialchars($formErrors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate data-validate="auth" data-auth-type="login">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken); ?>" />
                <?php $loginGeneralError = $formErrors['general'] ?? null; ?>
                <div class="form-field<?= (isset($formErrors['email']) || $loginGeneralError === 'Identifiants invalides.') ? ' is-invalid' : ''; ?>" data-field>
                    <label for="login-email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="login-email"
                            name="email"
                            placeholder="anselou.temple@gmail.com"
                            required
                            value="<?= htmlspecialchars($formData['email']); ?>"
                            data-validate-field="email"
                            aria-describedby="login-email-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="login-email-feedback" data-feedback="email" data-default="<?= htmlspecialchars($fieldMessages['email']); ?>">
                        <?= htmlspecialchars($formErrors['email'] ?? $fieldMessages['email']); ?>
                    </p>
                </div>

                <div class="form-field<?= (isset($formErrors['password']) || $loginGeneralError === 'Identifiants invalides.') ? ' is-invalid' : ''; ?>" data-field>
                    <label for="login-password">Mot de passe</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            placeholder="Ton mot de passe sécurisé"
                            required
                            minlength="8"
                            data-validate-field="password"
                            aria-describedby="login-password-feedback"
                        />
                        <span class="input-status" aria-hidden="true"></span>
                    </div>
                    <p class="input-feedback" id="login-password-feedback" data-feedback="password" data-default="<?= htmlspecialchars($fieldMessages['password']); ?>">
                        <?= htmlspecialchars($formErrors['password'] ?? $fieldMessages['password']); ?>
                    </p>
                </div>

                <?php if (isset($formErrors['general']) && $formErrors['general'] === 'Identifiants invalides.') : ?>
                    <div class="form-alert form-alert--inline" role="alert">
                        <?= htmlspecialchars($formErrors['general']); ?>
                    </div>
                <?php endif; ?>

                <button class="btn primary btn--wide" type="submit">Entrer dans le Temple</button>
                <p class="form-footnote">
                    Pas encore inscrit ? <a href="<?= BASE_URL; ?>/views/register.php">Crée ton compte</a>.
                </p>
            </form>
        </div>
    </section>
    <aside class="auth-illustration" aria-hidden="true">
        <img src="<?= BASE_URL; ?>/assets/images/login-illustration.svg" alt="" />
    </aside>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

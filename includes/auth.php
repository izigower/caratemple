<?php
/**
 * Authentication helper functions for CaraTemple.
 *
 * Handles registration and login logic with secure database interactions.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Attempt to register a new user with the provided data.
 *
 * @param array<string, string> $input
 *
 * @return array{success: bool, errors: array<string, string>}
 */
function register_user(array $input): array
{
    $errors = [];

    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $passwordConfirm = $input['password_confirm'] ?? '';

    if ($username === '') {
        $errors['username'] = 'Le pseudo est requis.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        $errors['username'] = 'Utilise 3 à 20 caractères alphanumériques ou underscores.';
    }

    if ($email === '') {
        $errors['email'] = 'L\'adresse e-mail est requise.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adresse e-mail invalide.';
    }

    if ($password === '') {
        $errors['password'] = 'Le mot de passe est requis.';
    } else {
        $lengthValid = strlen($password) >= 8;
        $containsNumber = (bool) preg_match('/\d/', $password);
        $containsLetter = (bool) preg_match('/[A-Za-z]/', $password);

        if (!$lengthValid || !$containsNumber || !$containsLetter) {
            $errors['password'] = '8 caractères minimum avec lettres et chiffres.';
        }
    }

    if ($passwordConfirm === '') {
        $errors['password_confirm'] = 'La confirmation est requise.';
    } elseif ($passwordConfirm !== $password) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = getDatabaseConnection();

    $checkStatement = $pdo->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
    $checkStatement->bindValue(':username', $username, PDO::PARAM_STR);
    $checkStatement->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStatement->execute();
    $existingUser = $checkStatement->fetch();

    if ($existingUser) {
        if (strcasecmp($existingUser['username'], $username) === 0) {
            $errors['username'] = 'Ce pseudo est déjà utilisé.';
        }
        if (strcasecmp($existingUser['email'], $email) === 0) {
            $errors['email'] = 'Cette adresse e-mail est déjà enregistrée.';
        }

        return ['success' => false, 'errors' => $errors];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertStatement = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_admin) VALUES (:username, :email, :password_hash, 0)');
    $insertStatement->bindValue(':username', $username, PDO::PARAM_STR);
    $insertStatement->bindValue(':email', strtolower($email), PDO::PARAM_STR);
    $insertStatement->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
    $insertStatement->execute();

    return ['success' => true, 'errors' => []];
}

/**
 * Attempt to authenticate a user using email and password.
 *
 * @param array<string, string> $input
 *
 * @return array{success: bool, errors: array<string, string>}
 */
function authenticate_user(array $input): array
{
    $errors = [];

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if ($email === '') {
        $errors['email'] = 'L\'adresse e-mail est requise.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adresse e-mail invalide.';
    }

    if ($password === '') {
        $errors['password'] = 'Le mot de passe est requis.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT id, username, email, password_hash, is_admin FROM users WHERE email = :email LIMIT 1');
    $statement->bindValue(':email', strtolower($email), PDO::PARAM_STR);
    $statement->execute();
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors['general'] = 'Identifiants invalides.';
        return ['success' => false, 'errors' => $errors];
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'is_admin' => (int) $user['is_admin'] === 1,
    ];

    return ['success' => true, 'errors' => []];
}

/**
 * Disconnect the current user.
 */
function logout_user(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/**
 * Retrieve the authenticated user information.
 *
 * @return array{id: int, username: string, email: string}|null
 */
function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    if ($user === null) {
        return null;
    }

    return [
        'id' => (int) $user['id'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'is_admin' => (bool) $user['is_admin'],
    ];
}

/**
 * Determine if the current session user has administrator rights.
 */
function current_user_is_admin(): bool
{
    $user = current_user();

    return $user !== null && $user['is_admin'] === true;
}

/**
 * Ensure the current user is authenticated as administrator.
 * Redirects to the home page with an error flash otherwise.
 *
 * @return array{id: int, username: string, email: string, is_admin: bool}
 */
function require_admin(): array
{
    $user = current_user();

    if ($user === null || $user['is_admin'] !== true) {
        set_flash_message('error', 'Accès administrateur requis.');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    return $user;
}

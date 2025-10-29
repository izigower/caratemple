<?php
/**
 * Global helper functions for CaraTemple.
 *
 * Provides CSRF token utilities and flash messaging helpers shared across views.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

/**
 * Generate a CSRF token for the provided form key.
 */
function generate_csrf_token(string $formKey): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$formKey] = $token;

    return $token;
}

/**
 * Validate a CSRF token against the stored session token.
 */
function validate_csrf_token(string $formKey, ?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sessionToken = $_SESSION['csrf_tokens'][$formKey] ?? null;
    if ($sessionToken !== null && $token !== null && hash_equals($sessionToken, $token)) {
        unset($_SESSION['csrf_tokens'][$formKey]);
        return true;
    }

    return false;
}

/**
 * Store a flash message in the session.
 */
function set_flash_message(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Retrieve flash messages and remove them from the session.
 *
 * @return array<string, array<int, string>>
 */
function get_flash_messages(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);

    return $messages;
}

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

/**
 * Format a timestamp into a short French relative time string.
 */
function format_relative_time(string $datetime): string
{
    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return '';
    }

    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'à l\'instant';
    }

    if ($diff < 3600) {
        $minutes = max(1, (int) floor($diff / 60));
        return 'il y a ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }

    if ($diff < 86400) {
        $hours = max(1, (int) floor($diff / 3600));
        return 'il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
    }

    if ($diff < 604800) {
        $days = max(1, (int) floor($diff / 86400));
        return 'il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
    }

    return date('d/m/Y', $timestamp);
}

/**
 * Create an excerpt limited to the provided number of characters.
 */
function create_excerpt(string $text, int $limit = 160): string
{
    $clean = trim(strip_tags($text));
    if (mb_strlen($clean) <= $limit) {
        return $clean;
    }

    $truncated = mb_substr($clean, 0, $limit);
    return rtrim($truncated) . '…';
}

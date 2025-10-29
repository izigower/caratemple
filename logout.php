<?php
/**
 * Logout endpoint for CaraTemple.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash_message('success', 'Tu es déconnecté. À très vite au Temple !');

header('Location: ' . BASE_URL . '/index.php');
exit;

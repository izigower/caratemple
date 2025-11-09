<?php
/**
 * PHPUnit bootstrap file for CaraTemple tests.
 *
 * @package CaraTemple\Tests
 */

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Set test environment
define('TESTING', true);

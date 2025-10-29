<?php
/**
 * Global configuration for CaraTemple project.
 *
 * This file initializes database credentials and application-wide settings.
 * Replace the placeholder values by environment-specific data before deployment.
 *
 * @package CaraTemple\Config
 */

declare(strict_types=1);

// Database configuration constants.
const DB_HOST = 'localhost';
const DB_NAME = 'caratemple';
const DB_USER = 'root';
const DB_PASSWORD = '';
const DB_CHARSET = 'utf8mb4';

// Base URL of the application. Update depending on server configuration.
const BASE_URL = 'http://localhost/caratemple';

// Toggle detailed error reporting. Keep false in production environment.
const DISPLAY_ERRORS = true;

if (DISPLAY_ERRORS) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Helper function to create a PDO instance when needed.
function getDatabaseConnection(): PDO
{
    static $connection = null;

    if ($connection === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $connection = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $exception) {
            // In production, log the error instead of echoing it.
            if (DISPLAY_ERRORS) {
                echo 'Database connection failed: ' . htmlspecialchars($exception->getMessage());
            }
            throw $exception;
        }
    }

    return $connection;
}

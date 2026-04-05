<?php
/**
 * Application Configuration
 * 
 * SECURITY NOTE: In production, move this file outside the web root
 * or use environment variables instead of hardcoded values.
 * 
 * For a real application, use:
 * - Environment variables: getenv('DB_HOST')
 * - A .env file with a library like vlucas/phpdotenv
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'oscilloscope_catalog');
define('DB_USER', 'remote_user');
define('DB_PASS', 'Q<@|NxQ1K');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'ElectroStore');
define('APP_DEBUG', false); // Set to false in production

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'electrostore_session');

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
?>

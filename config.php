<?php
/**
 * Plus International School — main configuration.
 * Values can be overridden with environment variables or a .env file at the project root.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', __DIR__);

/** Loads simple KEY=value pairs from a .env file into the environment. */
function pis_load_env(string $file): void
{
    if (!is_readable($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

pis_load_env(BASE_PATH . '/.env');

/** Reads a configuration value from the environment with a fallback. */
function env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

// Database ------------------------------------------------------------------
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'plus_international_school'));
define('DB_USER', env('DB_USER', 'plus'));
define('DB_PASS', env('DB_PASS', 'pluspass'));

// School identity -----------------------------------------------------------
define('SCHOOL_NAME', 'Plus International School');
define('SCHOOL_ADDRESS', 'Tunga, Minna, Niger State, Nigeria');
define('SCHOOL_PHONE', '+234 800 000 0000');
define('SCHOOL_EMAIL', 'info@plusinternationalschool.ng');
define('SCHOOL_MOTTO', 'Knowledge · Character · Excellence');

// Payments ------------------------------------------------------------------
define('PAYSTACK_PUBLIC_KEY', env('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'));
define('PAYSTACK_SECRET_KEY', env('PAYSTACK_SECRET_KEY', ''));
define('REMITA_MERCHANT_ID', env('REMITA_MERCHANT_ID', ''));
define('REMITA_SERVICE_TYPE_ID', env('REMITA_SERVICE_TYPE_ID', ''));
define('REMITA_API_KEY', env('REMITA_API_KEY', ''));
define('REMITA_DEMO_MODE', env('REMITA_DEMO_MODE', 'true') === 'true');

// Mail ----------------------------------------------------------------------
define('MAIL_FROM', env('MAIL_FROM', 'no-reply@plusinternationalschool.ng'));
define('MAIL_ENABLED', env('MAIL_ENABLED', 'false') === 'true');

// URLs ----------------------------------------------------------------------
define('APP_URL', rtrim(env('APP_URL', ''), '/'));

/** Builds an absolute-from-root URL for a project path. */
function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

date_default_timezone_set('Africa/Lagos');

foreach ([
    'Database', 'Helpers', 'AuditLogger', 'Permissions', 'Auth',
    'NotificationSystem', 'ResultCalculator', 'PaymentProcessor',
    'TimetableManager', 'ChatSystem', 'PasswordReset',
] as $include) {
    require_once BASE_PATH . '/backend/includes/' . $include . '.php';
}

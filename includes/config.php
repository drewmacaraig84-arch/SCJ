<?php
/**
 * Application Configuration & Environment Loader
 * School of Criminal Justice Education (SCJE) Information System
 */

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Simple .env loader
 */
function load_env($filePath) {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Strip enclosing quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env from root
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    load_env($envPath);
}

/**
 * Get an environment variable with optional fallback
 */
function env($key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    if ($val === 'true' || $val === '(true)') return true;
    if ($val === 'false' || $val === '(false)') return false;
    if ($val === 'null' || $val === '(null)') return null;
    return $val;
}

// Global App Constants
define('APP_NAME', env('APP_NAME', 'School of Criminal Justice Information System'));
define('APP_URL', rtrim(env('APP_URL', 'http://localhost:8000'), '/'));
define('APP_ROOT', dirname(__DIR__));

define('APP_VERSION', '1.3.' . (@filemtime(dirname(__DIR__) . '/assets/css/style.css') ?: time()));

// Initialize Real-Time Crash Logger & Telemetry Engine
require_once __DIR__ . '/CrashLogger.php';
CrashLogger::register();

/**
 * Helper to generate relative or absolute URLs compatible with localhost, LAN IPs, and tunnels
 */
function base_url($path = '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = dirname($scriptName);
    if (str_ends_with($scriptDir, '/admin') || str_ends_with($scriptDir, '/api')) {
        $scriptDir = dirname($scriptDir);
    }
    $base = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.' || $scriptDir === '') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');

    if (empty($path)) {
        return empty($base) ? '/' : $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * Helper to generate versioned asset URLs for cache busting
 */
function asset_url($path) {
    return base_url($path) . '?v=' . APP_VERSION;
}


/**
 * Helper to escape HTML output safely
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

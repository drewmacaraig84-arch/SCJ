<?php
/**
 * Authentication Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once dirname(__DIR__) . '/config.php';

class AuthMiddleware {
    /**
     * Determine if the current visitor is authenticated
     */
    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['scj_user']) && is_array($_SESSION['scj_user']);
    }

    /**
     * Retrieve the currently authenticated user
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return $_SESSION['scj_user'];
    }

    /**
     * Store authenticated user into session
     */
    public static function login(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Regenerate session ID to prevent session fixation if headers not sent
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        unset($user['password']); // Never hold password hash in session
        $_SESSION['scj_user'] = $user;
        $_SESSION['scj_logged_in_at'] = time();
    }

    /**
     * Destroy user session
     */
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['scj_user']);
        unset($_SESSION['scj_logged_in_at']);
        session_destroy();
    }

    /**
     * Require authentication for a route
     */
    public static function handle(bool $isApi = false): void {
        if (!self::check()) {
            if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Authentication required. Please log in with your ID Number.'
                ]);
                exit;
            } else {
                $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
                header("Location: " . base_url("login.php?redirect={$returnUrl}"));
                exit;
            }
        }
    }
}

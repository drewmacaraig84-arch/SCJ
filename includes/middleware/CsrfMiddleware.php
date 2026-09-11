<?php
/**
 * CSRF Protection Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

class CsrfMiddleware {
    /**
     * Generate or retrieve the current CSRF token
     */
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Render an HTML hidden input containing the token
     */
    public static function field(): string {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify whether a provided token matches the session token
     */
    public static function verify(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Middleware handler for incoming state-changing requests
     */
    public static function handle(bool $isApi = false): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $submittedToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            // Also check raw json input if applicable
            if (!$submittedToken) {
                $rawInput = file_get_contents('php://input');
                if ($rawInput) {
                    $json = json_decode($rawInput, true);
                    if (isset($json['_csrf_token'])) {
                        $submittedToken = $json['_csrf_token'];
                    }
                }
            }

            if (!self::verify($submittedToken)) {
                http_response_code(403);
                if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'CSRF verification failed. Please refresh the page and try again.'
                    ]);
                } else {
                    die('<h1>403 Forbidden</h1><p>Invalid or expired CSRF security token. Please go back, refresh the page, and try again.</p>');
                }
                exit;
            }
        }
    }
}

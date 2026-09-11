<?php
/**
 * Role-Based Access Control (RBAC) Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/AuthMiddleware.php';

class RoleMiddleware {
    /**
     * Check if user has an authorized role
     */
    public static function hasRole($roles): bool {
        if (!AuthMiddleware::check()) {
            return false;
        }

        $user = AuthMiddleware::user();
        $userRole = strtolower($user['role'] ?? 'guest');

        if (is_string($roles)) {
            $roles = [$roles];
        }

        $roles = array_map('strtolower', $roles);
        return in_array($userRole, $roles);
    }

    /**
     * Enforce role restrictions on a route or endpoint
     */
    public static function handle($roles, bool $isApi = false): void {
        AuthMiddleware::handle($isApi);

        if (!self::hasRole($roles)) {
            http_response_code(403);
            if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Access denied. You do not have permission to access this resource.'
                ]);
            } else {
                echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{font-family:sans-serif;background:#0A192F;color:#fff;text-align:center;padding:50px;}a{color:#38BDF8;}</style></head><body>';
                echo '<h1>403 Forbidden</h1>';
                echo '<p>Your account role does not permit access to this section.</p>';
                echo '<p><a href="' . base_url() . '">Return to Home</a></p>';
                echo '</body></html>';
            }
            exit;
        }
    }
}

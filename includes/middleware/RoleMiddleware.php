<?php
/**
 * Role-Based Access Control (RBAC) Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/AuthMiddleware.php';

class RoleMiddleware {
    /**
     * Check if the currently authenticated user is a Super Administrator
     */
    public static function isSuperAdmin(): bool {
        if (!AuthMiddleware::check()) {
            return false;
        }
        $user = AuthMiddleware::user();
        $userRole = strtolower($user['role'] ?? 'guest');
        return in_array($userRole, ['super_admin', 'superadmin']);
    }

    /**
     * Check if user has an authorized role with Super Admin privilege inheritance
     */
    public static function hasRole($roles): bool {
        if (!AuthMiddleware::check()) {
            return false;
        }

        $user = AuthMiddleware::user();
        $userRole = strtolower(trim($user['role'] ?? 'guest'));
        if ($userRole === 'superadmin') {
            $userRole = 'super_admin';
        }

        // Super Admin has unrestricted access to all admin and lower privileged sections
        if (self::isSuperAdmin()) {
            return true;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function($r) {
            $r = strtolower(trim($r));
            return $r === 'superadmin' ? 'super_admin' : $r;
        }, $roles);

        // Admin and Faculty share the exact same permissions across all modules
        if ($userRole === 'faculty' && in_array('admin', $roles, true)) {
            return true;
        }
        if ($userRole === 'admin' && in_array('faculty', $roles, true)) {
            return true;
        }

        return in_array($userRole, $roles, true);
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
                $user = AuthMiddleware::user();
                $fallbackUrl = base_url();
                if ($user) {
                    $role = strtolower($user['role'] ?? '');
                    if ($role === 'super_admin' || $role === 'superadmin') {
                        $fallbackUrl = base_url('admin/index.php');
                    } elseif ($role === 'admin' || $role === 'faculty') {
                        $fallbackUrl = base_url('admin/researches.php');
                    } elseif ($role === 'student') {
                        $fallbackUrl = base_url('student/index.php');
                    }
                }

                $referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallbackUrl;
                if (isset($_SERVER['REQUEST_URI']) && str_contains($referer, $_SERVER['REQUEST_URI'])) {
                    $referer = $fallbackUrl;
                }
                $safeReferer = htmlspecialchars($referer, ENT_QUOTES, 'UTF-8');

                $isSuper = self::isSuperAdmin();
                $dashboardBtn = $isSuper ? "<a href=\"" . base_url('admin/index.php') . "\" class=\"btn\"><i class=\"fa-solid fa-gauge\"></i> System Dashboard</a>" : "";
                echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden | SCJE System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; background: #071324; color: #E2E8F0; font-family: 'Segoe UI', system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .box { background: rgba(15, 37, 75, 0.85); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 45px 35px; max-width: 480px; box-shadow: 0 20px 45px rgba(0,0,0,0.6); backdrop-filter: blur(12px); }
        .icon { font-size: 3.5rem; color: #EF4444; margin-bottom: 20px; }
        h1 { margin: 0 0 12px; font-size: 2rem; color: #FFFFFF; font-weight: 800; }
        p { color: #94A3B8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; background: #1E3A8A; color: #FFFFFF; text-decoration: none; padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; border: none; cursor: pointer; }
        .btn:hover { background: #2563EB; }
        .btn-back { background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); }
        .btn-back:hover { background: rgba(255, 255, 255, 0.22); }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h1>403 Forbidden</h1>
        <p>Access denied. You do not have sufficient permissions to access this section.</p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            {$dashboardBtn}
            <a href="{$safeReferer}" id="btnGoBack" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i> Go Back
            </a>
        </div>
    </div>
    <script>
        document.getElementById('btnGoBack').addEventListener('click', function(e) {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                e.preventDefault();
                window.history.back();
            }
        });
    </script>
</body>
</html>
HTML;
            }
            exit;
        }
    }
}

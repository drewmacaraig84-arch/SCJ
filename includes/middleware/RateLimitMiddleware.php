<?php
/**
 * Rate Limiting Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

class RateLimitMiddleware {
    /**
     * Check if the current IP/session has exceeded rate limits
     */
    public static function check(string $action = 'login', int $maxAttempts = 5, int $decaySeconds = 300, bool $isApi = false): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "_rate_limit_{$action}_{$ip}";

        if (isset($_SESSION[$key])) {
            $data = $_SESSION[$key];
            $elapsed = time() - $data['start_time'];

            if ($elapsed > $decaySeconds) {
                // Reset limit window
                $_SESSION[$key] = [
                    'attempts' => 0,
                    'start_time' => time()
                ];
                return true;
            }

            if ($data['attempts'] >= $maxAttempts) {
                $retryAfter = $decaySeconds - $elapsed;
                http_response_code(429);
                header("Retry-After: {$retryAfter}");

                if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Too many attempts. Please wait {$retryAfter} seconds before trying again."
                    ]);
                } else {
                    die("<h1>429 Too Many Requests</h1><p>Too many attempts. Please wait {$retryAfter} seconds before trying again.</p>");
                }
                exit;
            }
        } else {
            $_SESSION[$key] = [
                'attempts' => 0,
                'start_time' => time()
            ];
        }

        return true;
    }

    /**
     * Increment the attempt count
     */
    public static function hit(string $action = 'login'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "_rate_limit_{$action}_{$ip}";

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'start_time' => time()
            ];
        } else {
            $_SESSION[$key]['attempts']++;
        }
    }

    /**
     * Clear the attempts counter upon successful action
     */
    public static function clear(string $action = 'login'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "_rate_limit_{$action}_{$ip}";
        unset($_SESSION[$key]);
    }
}

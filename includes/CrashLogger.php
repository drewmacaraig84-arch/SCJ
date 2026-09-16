<?php
/**
 * Real-Time System Crash Logger & Error Telemetry Engine
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/config.php';

class CrashLogger {
    private static bool $registered = false;
    private static bool $isHandling = false;
    private static string $logDir = '';

    /**
     * Initialize and hook global error and exception handlers
     */
    public static function register(): void {
        if (self::$registered) {
            return;
        }

        self::$logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0777, true);
        }

        // Global uncaught exception handler
        set_exception_handler([__CLASS__, 'handleException']);

        // Global PHP error/warning handler
        set_error_handler([__CLASS__, 'handleError']);

        // Fatal error shutdown handler
        register_shutdown_function([__CLASS__, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Uncaught Exception Handler
     */
    public static function handleException(Throwable $e): void {
        self::record(
            'FATAL',
            $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        if (php_sapi_name() !== 'cli') {
            if (!headers_sent()) {
                http_response_code(500);
            }

            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'An unexpected server error occurred. Telemetry has logged this incident.',
                    'debug' => env('APP_DEBUG', false) ? $e->getMessage() : null
                ]);
            } else {
                self::renderErrorPage($e);
            }
        }
    }

    /**
     * PHP Error & Warning Handler
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool {
        // Obey @ error suppression
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $level = match ($severity) {
            E_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => 'ERROR',
            E_WARNING, E_USER_WARNING => 'WARNING',
            E_NOTICE, E_USER_NOTICE => 'NOTICE',
            default => 'INFO'
        };

        // Only log warnings, errors, and fatal alerts
        if (in_array($level, ['ERROR', 'WARNING', 'FATAL'])) {
            $trace = (new Exception())->getTraceAsString();
            self::record($level, $message, $file, $line, $trace);
        }

        return false; // Allow standard PHP processing to continue
    }

    /**
     * Fatal Error Shutdown Handler
     */
    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING])) {
            self::record('FATAL', $error['message'], $error['file'], $error['line'], 'Fatal shutdown trap.');
        }
    }

    /**
     * Core Record Function (Database + File Fallback)
     */
    public static function record(
        string $level,
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?string $trace = null
    ): void {
        if (self::$isHandling) {
            return; // Prevent recursion if an error occurs during logging
        }
        self::$isHandling = true;

        try {
            $url = $_SERVER['REQUEST_URI'] ?? (php_sapi_name() === 'cli' ? 'CLI Script' : 'Unknown');
            $method = $_SERVER['REQUEST_METHOD'] ?? (php_sapi_name() === 'cli' ? 'CLI' : 'GET');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $userId = null;
            $userRole = null;
            if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['scj_user'])) {
                $userId = (string)($_SESSION['scj_user']['id_number'] ?? $_SESSION['scj_user']['id'] ?? '');
                $userRole = (string)($_SESSION['scj_user']['role'] ?? '');
            }

            // 1. Attempt Database Write
            try {
                if (class_exists('DB')) {
                    $pdo = DB::getConnection();
                    $stmt = $pdo->prepare("
                        INSERT INTO crash_logs (level, message, file, line, trace, url, method, ip_address, user_id, user_role, resolved)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
                    ");
                    $stmt->execute([
                        strtoupper($level),
                        mb_substr($message, 0, 1000),
                        $file ? mb_substr($file, 0, 255) : null,
                        $line,
                        $trace,
                        mb_substr($url, 0, 500),
                        $method,
                        $ip,
                        $userId,
                        $userRole
                    ]);
                }
            } catch (Throwable $dbErr) {
                // Database logging failed, proceed directly to file logging
                self::writeFallbackFile("DB Logger failure: " . $dbErr->getMessage());
            }

            // 2. Always maintain File Fallback log
            $logEntry = sprintf(
                "[%s] [%s] %s in %s:%s | URL: %s [%s] | IP: %s | User: %s (%s)\n",
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message,
                $file ?? 'unknown',
                $line ?? '0',
                $url,
                $method,
                $ip,
                $userId ?? 'guest',
                $userRole ?? 'none'
            );
            self::writeFallbackFile($logEntry);

        } catch (Throwable $generalErr) {
            // Failsafe
            @error_log("CrashLogger failed: " . $generalErr->getMessage());
        } finally {
            self::$isHandling = false;
        }
    }

    /**
     * Fallback file writer
     */
    private static function writeFallbackFile(string $content): void {
        try {
            $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $file = $dir . DIRECTORY_SEPARATOR . 'crash.log';
            @file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            // ignore
        }
    }

    /**
     * Retrieve Log Statistics for Super Admin Dashboard
     */
    public static function getStats(): array {
        try {
            $pdo = get_db();
            $total = (int)$pdo->query("SELECT COUNT(*) FROM crash_logs")->fetchColumn();
            $unresolved = (int)$pdo->query("SELECT COUNT(*) FROM crash_logs WHERE resolved = 0")->fetchColumn();
            $fatal = (int)$pdo->query("SELECT COUNT(*) FROM crash_logs WHERE UPPER(level) = 'FATAL'")->fetchColumn();
            
            // Last 24 hours
            $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM crash_logs WHERE created_at >= ?");
            $stmt->execute([$yesterday]);
            $recent24h = (int)$stmt->fetchColumn();

            return [
                'total' => $total,
                'unresolved' => $unresolved,
                'fatal' => $fatal,
                'recent_24h' => $recent24h
            ];
        } catch (Throwable $e) {
            return ['total' => 0, 'unresolved' => 0, 'fatal' => 0, 'recent_24h' => 0];
        }
    }

    /**
     * Retrieve Filtered Logs
     */
    public static function getLogs(int $limit = 50, int $offset = 0, ?string $level = null, ?string $status = null, ?string $search = null): array {
        try {
            $pdo = get_db();
            $conditions = [];
            $params = [];

            if (!empty($level) && $level !== 'ALL') {
                $conditions[] = "UPPER(level) = ?";
                $params[] = strtoupper($level);
            }

            if ($status === 'unresolved') {
                $conditions[] = "resolved = 0";
            } elseif ($status === 'resolved') {
                $conditions[] = "resolved = 1";
            }

            if (!empty($search)) {
                $conditions[] = "(message LIKE ? OR file LIKE ? OR url LIKE ?)";
                $searchWild = "%{$search}%";
                $params[] = $searchWild;
                $params[] = $searchWild;
                $params[] = $searchWild;
            }

            $whereSql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

            // Total filtered count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM crash_logs {$whereSql}");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            // Rows
            $querySql = "SELECT * FROM crash_logs {$whereSql} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
            $queryStmt = $pdo->prepare($querySql);
            $queryStmt->execute($params);
            $logs = $queryStmt->fetchAll();

            return [
                'total' => $totalCount,
                'logs' => $logs
            ];
        } catch (Throwable $e) {
            return ['total' => 0, 'logs' => []];
        }
    }

    /**
     * Toggle Log Resolved Status
     */
    public static function toggleResolved(int $id): bool {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("UPDATE crash_logs SET resolved = CASE WHEN resolved = 1 THEN 0 ELSE 1 END WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Delete Single Log
     */
    public static function delete(int $id): bool {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("DELETE FROM crash_logs WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Purge All Logs
     */
    public static function clearAll(): bool {
        try {
            $pdo = get_db();
            $pdo->exec("DELETE FROM crash_logs");
            $fallbackFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'crash.log';
            if (file_exists($fallbackFile)) {
                @file_put_contents($fallbackFile, '');
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Safe Simulated Crash for Super Admin Live Verification
     */
    public static function simulateCrash(string $type = 'exception'): void {
        if ($type === 'exception') {
            throw new RuntimeException("Simulated Diagnostic Exception: Super Admin verified the real-time crash capture pipeline successfully.");
        } elseif ($type === 'warning') {
            trigger_error("Simulated System Warning: Diagnostic check triggered by Super Admin.", E_USER_WARNING);
        } else {
            self::record('FATAL', "Simulated Fatal Diagnostic Incident: Super Admin live telemetry validation.", __FILE__, __LINE__, "Manual test trace simulation.\nAt Super Admin Control Center");
        }
    }

    /**
     * Beautiful Fallback Error Page for Visitors
     */
    private static function renderErrorPage(Throwable $e): void {
        $isDebug = env('APP_DEBUG', false);
        $message = $isDebug ? e($e->getMessage()) : 'An unexpected application condition occurred. Our technical staff has received automated crash telemetry.';
        $file = $isDebug ? e($e->getFile() . ':' . $e->getLine()) : '';
        $trace = $isDebug ? '<pre style="background:rgba(0,0,0,0.5); padding:15px; border-radius:8px; text-align:left; font-size:0.8rem; overflow-x:auto; max-height:220px; color:#F87171;">' . e($e->getTraceAsString()) . '</pre>' : '';
        $homeUrl = base_url();

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error | SCJE System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; background: #071324; color: #E2E8F0; font-family: 'Segoe UI', system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .error-card { background: rgba(15, 37, 75, 0.9); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 45px 35px; max-width: 600px; box-shadow: 0 25px 50px rgba(0,0,0,0.7); backdrop-filter: blur(12px); }
        .icon { font-size: 3.5rem; color: #EF4444; margin-bottom: 20px; }
        h1 { margin: 0 0 12px; font-size: 1.8rem; color: #FFFFFF; font-weight: 800; }
        p { color: #94A3B8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; background: #1E3A8A; color: #FFFFFF; text-decoration: none; padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
        .btn:hover { background: #2563EB; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h1>System Alert</h1>
        <p>{$message}</p>
        {$trace}
        <div style="margin-top:25px;">
            <a href="{$homeUrl}" class="btn"><i class="fa-solid fa-rotate-right"></i> Return to Main Page</a>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

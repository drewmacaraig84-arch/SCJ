<?php
/**
 * Database Connection & Dual-Engine Manager (MySQL + SQLite Failover)
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $instance = null;
    private static string $activeDriver = '';

    /**
     * Retrieve the active PDO connection singleton
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Get the active database driver ('mysql' or 'sqlite')
     */
    public static function getDriver(): string {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$activeDriver;
    }

    /**
     * Establish the database connection with fallback capability
     */
    private static function connect(): void {
        $desiredDriver = strtolower((string)env('DB_CONNECTION', 'mysql'));

        if ($desiredDriver === 'mysql') {
            try {
                $host = env('DB_HOST', '127.0.0.1');
                $port = env('DB_PORT', '3306');
                $dbname = env('DB_DATABASE', 'scj_db');
                $user = env('DB_USERNAME', 'root');
                $pass = env('DB_PASSWORD', '');

                // First attempt server connection to ensure database exists
                try {
                    $serverPdo = new PDO(
                        "mysql:host={$host};port={$port};charset=utf8mb4",
                        $user,
                        $pass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 2
                        ]
                    );
                    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                } catch (PDOException $e) {
                    // If unable to connect without db, proceed to try with db directly
                }

                // Connect to the specific database
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 3
                ]);

                // Auto-initialize tables and seed data if not present
                try {
                    $tableCheck = $pdo->query("SHOW TABLES LIKE 'research'")->fetch();
                    if (!$tableCheck) {
                        require_once __DIR__ . '/../setup.php';
                        init_database($pdo, 'mysql');
                    }
                } catch (Exception $e) {
                    // ignore
                }

                self::$instance = $pdo;
                self::$activeDriver = 'mysql';
                return;
            } catch (PDOException $e) {
                // MySQL failed, log and fallback to SQLite
                error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to SQLite.");
            }
        }

        // SQLite connection (either selected or fallback)
        self::connectSqlite();
    }

    /**
     * Fallback / Direct SQLite Connection
     */
    private static function connectSqlite(): void {
        $sqliteRelPath = env('DB_SQLITE_PATH', 'database/scj.sqlite');
        $sqliteFullPath = APP_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sqliteRelPath);
        $sqliteDir = dirname($sqliteFullPath);

        if (!is_dir($sqliteDir)) {
            mkdir($sqliteDir, 0777, true);
        }

        $needsInit = !file_exists($sqliteFullPath) || filesize($sqliteFullPath) === 0;

        $pdo = new PDO("sqlite:{$sqliteFullPath}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Enable foreign keys for SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");

        self::$instance = $pdo;
        self::$activeDriver = 'sqlite';

        if ($needsInit) {
            require_once __DIR__ . '/../setup.php';
            init_database($pdo, 'sqlite');
        }
    }
}

/**
 * Convenience helper to get the PDO instance
 */
function get_db(): PDO {
    return DB::getConnection();
}

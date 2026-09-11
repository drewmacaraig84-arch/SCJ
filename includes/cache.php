<?php
/**
 * Lightweight High-Performance Query & Data Cache Manager
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/config.php';

class Cache {
    private static string $cacheDir = '';

    private static function init(): void {
        if (empty(self::$cacheDir)) {
            self::$cacheDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0777, true);
            }
        }
    }

    private static function getPath(string $key): string {
        self::init();
        $hash = md5($key);
        return self::$cacheDir . DIRECTORY_SEPARATOR . "cache_{$hash}.json";
    }

    /**
     * Get item from cache if exists and not expired
     */
    public static function get(string $key, $default = null) {
        $path = self::getPath($key);
        if (!file_exists($path)) {
            return $default;
        }

        $raw = file_get_contents($path);
        if (!$raw) {
            return $default;
        }

        $data = json_decode($raw, true);
        if (!$data || !isset($data['expires_at'])) {
            return $default;
        }

        if (time() > $data['expires_at']) {
            @unlink($path);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store item in cache with TTL in seconds (default 1 hour)
     */
    public static function set(string $key, $value, int $ttl = 3600): void {
        $path = self::getPath($key);
        $payload = [
            'key' => $key,
            'value' => $value,
            'created_at' => time(),
            'expires_at' => time() + $ttl
        ];
        file_put_contents($path, json_encode($payload), LOCK_EX);
    }

    /**
     * Remember: Get from cache, or execute callback, store, and return
     */
    public static function remember(string $key, int $ttl, callable $callback) {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $fresh = $callback();
        self::set($key, $fresh, $ttl);
        return $fresh;
    }

    /**
     * Delete specific cache key
     */
    public static function forget(string $key): void {
        $path = self::getPath($key);
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Invalidate all cache files
     */
    public static function flush(): void {
        self::init();
        $files = glob(self::$cacheDir . DIRECTORY_SEPARATOR . 'cache_*.json');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
    }
}

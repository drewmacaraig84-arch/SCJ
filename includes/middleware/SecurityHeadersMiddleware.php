<?php
/**
 * Security Headers Middleware
 * School of Criminal Justice Education (SCJE) Information System
 */

class SecurityHeadersMiddleware {
    public static function handle(): void {
        if (headers_sent()) {
            return;
        }

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        // Enable XSS filtering
        header('X-XSS-Protection: 1; mode=block');
        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Enforce Content-Security-Policy (allows Google Fonts, FontAwesome, inline safe styles/scripts)
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https: data: blob:; script-src 'self' 'unsafe-inline' https: https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https:; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data: https:; img-src 'self' data: https: blob:; connect-src 'self' https:;");
    }
}

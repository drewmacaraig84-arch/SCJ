<?php
/**
 * Logout Handler
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';

AuthMiddleware::logout();
header("Location: " . base_url());
exit;

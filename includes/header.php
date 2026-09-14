<?php
/**
 * Common Header & Top Navigation
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Execute security headers middleware
SecurityHeadersMiddleware::handle();

$currentUser = AuthMiddleware::user();
$isLoggedIn = AuthMiddleware::check();
$csrfToken = CsrfMiddleware::getToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'School of Criminal Justice Education | Information System') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <!-- Theme Mode Pre-Render Hydration (Defaults to Executive Midnight Dark, Prevents FOUC) -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('scj_theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>

    <!-- ========================================================= -->
    <!-- TOP INSTITUTIONAL BRANDING HEADER (MATCHING SAMPLE IMAGE) -->
    <!-- ========================================================= -->
    <header class="top-header">
        <div class="container top-header-inner">
            <!-- Left Logo: DWCC Official Seal -->
            <a href="<?= base_url() ?>" class="header-logo" title="Divine Word College of Calapan">
                <img src="<?= asset_url('assets/images/dwcc_logo.png') ?>" alt="Divine Word College of Calapan Official Seal" width="82" height="82">
            </a>

            <!-- Center Title: SCHOOL OF CRIMINAL JUSTICE INFORMATION SYSTEM -->
            <div class="header-title-center">
                <div class="header-sub-institution">
                    <i class="fa-solid fa-graduation-cap"></i> Divine Word College of Calapan
                </div>
                <h1>School of Criminal Justice</h1>
                <div class="sub-title"><i class="fa-solid fa-shield-halved" style="font-size:0.8rem; margin-right:4px;"></i> Information System</div>
            </div>

            <!-- Right Logo: SCJ Department Seal -->
            <div class="header-logo" title="School of Criminal Justice - Criminology Department">
                <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="School of Criminal Justice Department Seal" width="82" height="82">
            </div>
        </div>
    </header>

    <!-- ========================================================= -->
    <!-- STICKY NAVIGATION BAR (MATCHING REFERENCE & SKETCHES)     -->
    <!-- ========================================================= -->
    <nav class="navbar">
        <div class="container nav-container">
            <!-- Mobile Hamburger Toggle -->
            <button type="button" class="nav-mobile-toggle" id="navMobileToggle" aria-label="Toggle Navigation Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="<?= base_url('index.php') ?>" class="nav-link <?= (($activePage ?? 'home') === 'home') ? 'active' : '' ?>">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('about.php') ?>" class="nav-link <?= (($activePage ?? '') === 'about') ? 'active' : '' ?>">
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('research.php') ?>" class="nav-link <?= (($activePage ?? '') === 'research') ? 'active' : '' ?>">
                        Research
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('laboratories.php') ?>" class="nav-link <?= (($activePage ?? '') === 'laboratories') ? 'active' : '' ?>">
                        Laboratories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('faculty.php') ?>" class="nav-link <?= (($activePage ?? '') === 'faculty') ? 'active' : '' ?>">
                        Faculty
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('contact.php') ?>" class="nav-link <?= (($activePage ?? '') === 'contact') ? 'active' : '' ?>">
                        <i class="fa-solid fa-envelope"></i> Contact
                    </a>
                </li>
            </ul>

            <!-- Theme Mode Toggle & Portal Action Button -->
            <div class="nav-auth-action">
                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Switch Theme Mode" title="Switch Light / Dark Theme">
                    <span class="theme-toggle-icon">
                        <i class="fa-solid fa-moon icon-moon"></i>
                        <i class="fa-solid fa-sun icon-sun"></i>
                    </span>
                    <span class="theme-toggle-text">Dark</span>
                </button>
                <?php if ($isLoggedIn): ?>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <a href="<?= base_url('admin/') ?>" class="nav-portal-btn" style="background:#10B981; border-color:#34D399;">
                            <i class="fa-solid fa-gauge"></i> <?= e($currentUser['name']) ?> (<?= strtoupper(e($currentUser['role'])) ?>)
                        </a>
                        <a href="<?= base_url('logout.php') ?>" class="nav-portal-btn" style="background:#EF4444; border-color:#F87171;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <button type="button" class="nav-portal-btn open-login-modal">
                        <i class="fa-solid fa-lock"></i> Portal Login
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ========================================================= -->
    <!-- INFORMATION SYSTEM LOGIN MODAL (EXACT MATCH TO SKETCH)   -->
    <!-- ========================================================= -->
    <div id="loginModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-shield-halved" style="color:var(--color-gold); margin-right:8px;"></i> Information System Portal</h3>
                <button type="button" class="modal-close close-login-modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Outer Bordered Box (from Sketch Image 3) -->
                <div class="sketch-login-box">
                    <div class="sketch-login-header">
                        School of Criminal Justice<br>
                        <span style="font-size:0.85rem; color:var(--color-primary-light);">Information System Authentication</span>
                    </div>

                    <form action="<?= base_url('login.php') ?>" method="POST">
                        <?= CsrfMiddleware::field() ?>

                        <div class="form-group">
                            <label for="modalIdNumber"><i class="fa-solid fa-id-card"></i> USERNAME: ID NUMBER</label>
                            <input type="text" id="modalIdNumber" name="id_number" class="form-control" placeholder="e.g. Drew or Student ID" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="modalPassword"><i class="fa-solid fa-key"></i> PASSWORD:</label>
                            <input type="password" id="modalPassword" name="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                            <span style="font-size:0.75rem; color:var(--color-text-muted);">Default: <code>Drew</code> / <code>admin</code></span>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Content Container for Instant Navigation & Zero-Reload Transitions -->
    <main id="appContent">

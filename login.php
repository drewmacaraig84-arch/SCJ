<?php
/**
 * User & Administrator Login Handler
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/includes/middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';

SecurityHeadersMiddleware::handle();

$error = '';
$redirect = $_GET['redirect'] ?? '';

// If already logged in, redirect immediately
if (AuthMiddleware::check()) {
    $role = AuthMiddleware::user()['role'] ?? 'student';
    $target = ($role === 'admin') ? base_url('admin/') : base_url();
    header("Location: {$target}");
    exit;
}

// Process POST Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check Rate Limit (max 5 attempts per 5 minutes)
    RateLimitMiddleware::check('login', 5, 300);

    // Verify CSRF
    $token = $_POST['_csrf_token'] ?? '';
    if (!CsrfMiddleware::verify($token)) {
        $error = 'CSRF security token expired. Please refresh and try again.';
    } else {
        $idNumber = trim($_POST['id_number'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($idNumber) || empty($password)) {
            $error = 'Please enter both your ID Number and Password.';
        } else {
            try {
                $pdo = get_db();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = ? LIMIT 1");
                $stmt->execute([$idNumber]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Successful login
                    RateLimitMiddleware::clear('login');
                    AuthMiddleware::login($user);

                    $returnUrl = !empty($redirect) ? urldecode($redirect) : (($user['role'] === 'admin') ? base_url('admin/') : base_url());
                    header("Location: {$returnUrl}");
                    exit;
                } else {
                    RateLimitMiddleware::hit('login');
                    $error = 'Invalid ID Number or Password. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'Database connection error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | School of Criminal Justice Information System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #040D1A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-card-container {
            max-width: 480px;
            width: 100%;
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 2px solid var(--color-primary-light);
        }
        .login-card-header {
            background: linear-gradient(135deg, #0A192F 0%, #1E3A8A 100%);
            padding: 25px;
            text-align: center;
            color: #FFFFFF;
        }
        .login-card-header img {
            width: 70px;
            height: 70px;
            margin-bottom: 10px;
        }
        .login-card-body {
            padding: 30px;
        }
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            .login-card-container {
                max-width: 100%;
                border-radius: var(--radius-md);
            }
            .login-card-header {
                padding: 20px 16px;
            }
            .login-card-header img {
                width: 56px;
                height: 56px;
            }
            .login-card-header h2 {
                font-size: 1.15rem !important;
            }
            .login-card-body {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body>


<div class="login-card-container">
    <div class="login-card-header">
        <div style="display:flex; justify-content:center; align-items:center; gap:16px; margin-bottom:12px;">
            <div class="logo-circle-holder" style="width:72px; height:72px; padding:5px;">
                <img src="<?= asset_url('assets/images/dwcc_logo.png') ?>" alt="DWCC Logo">
            </div>
            <div class="logo-circle-holder" style="width:72px; height:72px; padding:5px;">
                <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="SCJ Logo">
            </div>
        </div>
        <h2 style="font-size:1.35rem; font-weight:800; letter-spacing:1px; text-transform:uppercase;">School of Criminal Justice</h2>
        <div style="font-size:0.85rem; color:var(--color-gold); font-weight:700; letter-spacing:2px;">Information System Portal</div>
    </div>

    <div class="login-card-body">
        <?php if (!empty($error)): ?>
            <div style="background:#FEE2E2; color:#991B1B; padding:12px; border-radius:6px; font-size:0.85rem; font-weight:600; margin-bottom:16px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Outer Bordered Box matching Sketch Image 3 -->
        <div class="sketch-login-box" style="margin-top:0;">
            <div class="sketch-login-header">
                System Authentication<br>
                <span style="font-size:0.75rem; color:var(--color-text-muted); text-transform:none;">Enter your institutional credentials below</span>
            </div>

            <form action="<?= base_url('login.php') ?><?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" method="POST">
                <?= CsrfMiddleware::field() ?>

                <div class="form-group">
                    <label for="idNumber"><i class="fa-solid fa-id-card"></i> USERNAME: ID NUMBER</label>
                    <input type="text" id="idNumber" name="id_number" class="form-control" placeholder="e.g. ADMIN-001 or ID Number" required autofocus value="<?= e($_POST['id_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-key"></i> PASSWORD:</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div style="margin-top:20px;">
                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:12px;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Portal
                    </button>
                </div>
            </form>
        </div>

        <div style="margin-top:20px; padding:12px; background:#F8FAFC; border-radius:6px; border:1px solid #E2E8F0; font-size:0.8rem; color:#475569;">
            <p><strong>Demo Test Accounts:</strong></p>
            <p>Admin: <code>ADMIN-001</code> / <code>password123</code></p>
            <p>Faculty: <code>FAC-2024-001</code> / <code>password123</code></p>
            <p>Student: <code>2024-10045</code> / <code>password123</code></p>
        </div>

        <div style="text-align:center; margin-top:16px;">
            <a href="<?= base_url() ?>" style="font-size:0.85rem; color:var(--color-primary-light); font-weight:700;">
                &larr; Return to Public Website
            </a>
        </div>
    </div>
</div>

</body>
</html>

<?php
/**
 * Admin: Institutional Site Content Management (Vision, Mission, Goals, History)
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/cache.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin']);

$pdo = get_db();
$message = '';
$error = '';

// Handle Update Content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_content') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed. Please try again.';
    } else {
        $vision = trim($_POST['vision'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $goals = trim($_POST['goals'] ?? '');
        $history = trim($_POST['history'] ?? '');

        if ($vision && $mission && $goals) {
            try {
                $updateStmt = $pdo->prepare("UPDATE site_content SET content = ?, updated_at = NOW() WHERE section_key = ?");
                $updateStmt->execute([$vision, 'vision']);
                $updateStmt->execute([$mission, 'mission']);
                $updateStmt->execute([$goals, 'goals']);
                if ($history) {
                    $updateStmt->execute([$history, 'history']);
                }

                // Also update SQLite fallback if exists
                $sqlitePath = dirname(__DIR__) . '/database/scj.sqlite';
                if (file_exists($sqlitePath)) {
                    try {
                        $sqlitePdo = new PDO("sqlite:" . $sqlitePath);
                        $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sqStmt = $sqlitePdo->prepare("UPDATE site_content SET content = ?, updated_at = datetime('now') WHERE section_key = ?");
                        $sqStmt->execute([$vision, 'vision']);
                        $sqStmt->execute([$mission, 'mission']);
                        $sqStmt->execute([$goals, 'goals']);
                        if ($history) {
                            $sqStmt->execute([$history, 'history']);
                        }
                    } catch (Exception $sqEx) {
                        // Silently continue if SQLite error
                    }
                }

                Cache::flush();
                $message = 'Site content (Vision, Mission, Goals, and History) updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update content: ' . $e->getMessage();
            }
        } else {
            $error = 'Vision, Mission, and Goals cannot be empty.';
        }
    }
}

// Fetch current site content
$stmt = $pdo->query("SELECT section_key, title, content, updated_at FROM site_content");
$contentMap = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $contentMap[$row['section_key']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Site Content | SCJE Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #071324; color: #E2E8F0; padding: 24px 0; border-right: 1px solid rgba(56,189,248,0.2); display: flex; flex-direction: column; }
        .admin-brand { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .admin-nav { list-style: none; padding: 20px 0; flex-grow: 1; }
        .admin-nav li a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: #CBD5E1; font-weight: 600; font-size: 0.9rem; transition: var(--transition); }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(30,58,138,0.5); color: var(--color-primary-accent); border-left: 4px solid var(--color-primary-accent); }
        .admin-main { background: var(--color-bg-page); color: var(--color-text-primary); padding: 30px; overflow-y: auto; }
        .content-card { background: var(--color-bg-surface); border-radius: var(--radius-md); padding: 28px; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border); margin-bottom: 24px; color: var(--color-text-primary); }
        .content-card h3 { font-size: 1.15rem; font-weight: 800; color: #0F254B; display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .content-card p.desc { font-size: 0.85rem; color: #64748B; margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; }
        .form-group textarea { width: 100%; border: 1px solid #CBD5E1; border-radius: var(--radius-sm); padding: 12px; font-family: inherit; font-size: 0.92rem; line-height: 1.6; resize: vertical; }
        .form-group textarea:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(30,58,138,0.1); }
        .alert-success { background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .alert-danger { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
    </style>
    <!-- Theme Mode Pre-Render Hydration -->
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

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="logo-circle-holder" style="width:44px; height:44px; padding:2px; margin-right:12px;">
                <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="SCJ Logo">
            </div>
            <div>
                <h4 style="color:#FFFFFF; font-size:0.95rem; font-weight:800;">SCJE Admin</h4>
                <span style="font-size:0.75rem; color:var(--color-primary-accent);">Information System</span>
            </div>
        </div>

        <ul class="admin-nav">
            <li><a href="<?= base_url('admin/index.php') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="<?= base_url('admin/researches.php') ?>"><i class="fa-solid fa-book-open"></i> Manage Research</a></li>
            <li><a href="<?= base_url('admin/equipment.php') ?>"><i class="fa-solid fa-microscope"></i> Manage Equipment</a></li>
            <li><a href="<?= base_url('admin/materials.php') ?>"><i class="fa-solid fa-flask-vial"></i> Manage Materials</a></li>
            <li><a href="<?= base_url('admin/faculty.php') ?>"><i class="fa-solid fa-users"></i> Manage Faculty</a></li>
            <li><a href="<?= base_url('admin/content.php') ?>" class="active"><i class="fa-solid fa-compass"></i> Site Content</a></li>
            <li><a href="<?= base_url('admin/messages.php') ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a></li>
            <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);"><a href="<?= base_url() ?>"><i class="fa-solid fa-globe"></i> View Public Site</a></li>
            <li><a href="<?= base_url('logout.php') ?>" style="color:#F87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>

        <div style="padding: 16px 20px; font-size:0.75rem; color:#64748B; border-top:1px solid rgba(255,255,255,0.05);">
            Engine: <strong><?= strtoupper(DB::getDriver()) ?></strong>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F;">Institutional Site Content</h2>
                <p style="color:#64748B; font-size:0.9rem;">Manage the institutional framework (Vision, Mission, Goals) and History displayed on public pages.</p>
            </div>
            <a href="<?= base_url('about.php#pillars') ?>" class="btn-secondary" target="_blank" style="padding:10px 18px;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview On Public Site
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="_csrf_token" value="<?= CsrfMiddleware::generate() ?>">
            <input type="hidden" name="action" value="update_content">

            <!-- Vision -->
            <div class="content-card">
                <h3><i class="fa-solid fa-eye" style="color:var(--color-primary-accent);"></i> Institutional Vision</h3>
                <p class="desc">The overarching institutional aspiration for the School of Criminal Justice Education.</p>
                <div class="form-group">
                    <label for="vision">Vision Statement</label>
                    <textarea id="vision" name="vision" rows="3" required><?= e($contentMap['vision']['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Mission -->
            <div class="content-card">
                <h3><i class="fa-solid fa-bullseye" style="color:var(--color-gold);"></i> Institutional Mission</h3>
                <p class="desc">The core institutional mission and educational commitment to criminology graduates.</p>
                <div class="form-group">
                    <label for="mission">Mission Statement</label>
                    <textarea id="mission" name="mission" rows="4" required><?= e($contentMap['mission']['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Goals -->
            <div class="content-card">
                <h3><i class="fa-solid fa-chart-line" style="color:#10B981;"></i> Department Goals</h3>
                <p class="desc">Specific objectives and pillars. You can use bullet points (•) and line breaks for clear itemization.</p>
                <div class="form-group">
                    <label for="goals">Department Goals</label>
                    <textarea id="goals" name="goals" rows="6" required><?= e($contentMap['goals']['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- History of SCJ -->
            <div class="content-card">
                <h3><i class="fa-solid fa-landmark" style="color:#8B5CF6;"></i> History of SCJ</h3>
                <p class="desc">The narrative history and founding story displayed on the About page.</p>
                <div class="form-group">
                    <label for="history">History of the Department</label>
                    <textarea id="history" name="history" rows="8"><?= e($contentMap['history']['content'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:12px;">
                <button type="submit" class="btn-primary" style="padding:12px 28px; font-size:0.95rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save & Publish Content
                </button>
            </div>
        </form>
    </main>
</div>

</body>
</html>

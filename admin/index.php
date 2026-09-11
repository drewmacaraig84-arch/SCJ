<?php
/**
 * Admin Dashboard
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin']);

$user = AuthMiddleware::user();
$pdo = get_db();

// Counts
$totalResearch = $pdo->query("SELECT COUNT(*) FROM research")->fetchColumn();
$totalEquipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$totalFaculty = $pdo->query("SELECT COUNT(*) FROM faculty")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Recent messages
$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5")->fetchAll();

// Active Driver
$activeDriver = DB::getDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SCJE Information System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: #071324;
            color: #E2E8F0;
            border-right: 1px solid rgba(56, 189, 248, 0.2);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
        }
        .admin-brand {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-nav {
            list-style: none;
            padding: 20px 0;
            flex-grow: 1;
        }
        .admin-nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #CBD5E1;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .admin-nav li a:hover, .admin-nav li a.active {
            background: rgba(30, 58, 138, 0.5);
            color: var(--color-primary-accent);
            border-left: 4px solid var(--color-primary-accent);
        }
        .admin-main {
            background: #F1F5F9;
            padding: 30px;
            overflow-y: auto;
        }
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 1024px) {
            .stat-cards-grid { grid-template-columns: repeat(2, 1fr); }
            .admin-layout { grid-template-columns: 1fr; }
        }
        .stat-card {
            background: #FFFFFF;
            border-radius: var(--radius-md);
            padding: 22px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-val {
            font-size: 2rem;
            font-weight: 900;
            color: var(--color-primary-dark);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--color-text-muted);
            margin-top: 4px;
            text-transform: uppercase;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            background: #EFF6FF;
            color: var(--color-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
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
            <li><a href="<?= base_url('admin/index.php') ?>" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="<?= base_url('admin/researches.php') ?>"><i class="fa-solid fa-book-open"></i> Manage Research</a></li>
            <li><a href="<?= base_url('admin/equipment.php') ?>"><i class="fa-solid fa-flask"></i> Manage Equipment</a></li>
            <li><a href="<?= base_url('admin/faculty.php') ?>"><i class="fa-solid fa-users"></i> Manage Faculty</a></li>
            <li><a href="<?= base_url('admin/messages.php') ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a></li>
            <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);"><a href="<?= base_url() ?>"><i class="fa-solid fa-globe"></i> View Public Site</a></li>
            <li><a href="<?= base_url('logout.php') ?>" style="color:#F87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>

        <div style="padding: 16px 20px; font-size:0.75rem; color:#64748B; border-top:1px solid rgba(255,255,255,0.05);">
            Engine: <strong><?= strtoupper($activeDriver) ?></strong>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F;">System Administration</h2>
                <p style="color:#64748B; font-size:0.9rem;">Welcome back, <strong><?= e($user['name']) ?></strong> (<?= e($user['id_number']) ?>)</p>
            </div>
            <a href="<?= base_url() ?>" class="btn-primary" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Website
            </a>
        </div>

        <!-- Metric Cards -->
        <div class="stat-cards-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalResearch ?></div>
                    <div class="stat-label">Research Papers</div>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalEquipment ?></div>
                    <div class="stat-label">Lab Equipment</div>
                </div>
                <div class="stat-icon" style="color:#059669; background:#ECFDF5;"><i class="fa-solid fa-flask"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalFaculty ?></div>
                    <div class="stat-label">Faculty & Staff</div>
                </div>
                <div class="stat-icon" style="color:#D97706; background:#FFFBEB;"><i class="fa-solid fa-users"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalMessages ?></div>
                    <div class="stat-label">Inquiries Received</div>
                </div>
                <div class="stat-icon" style="color:#7C3AED; background:#F5F3FF;"><i class="fa-solid fa-envelope"></i></div>
            </div>
        </div>

        <!-- Recent Contact Messages -->
        <div class="table-card">
            <div class="table-toolbar">
                <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B;"><i class="fa-solid fa-inbox"></i> Recent Public Inquiries</h3>
                <a href="<?= base_url('admin/messages.php') ?>" style="font-size:0.85rem; font-weight:700;">View All &rarr;</a>
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentMessages)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px; color:#64748B;">No inquiries submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentMessages as $msg): ?>
                                <tr>
                                    <td style="font-weight:700;"><?= e($msg['name']) ?></td>
                                    <td><?= e($msg['email']) ?></td>
                                    <td><?= e($msg['subject']) ?></td>
                                    <td style="color:#64748B; font-size:0.8rem;"><?= e($msg['created_at']) ?></td>
                                    <td><span class="badge badge-info"><?= e($msg['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>

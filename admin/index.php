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
require_once __DIR__ . '/../includes/CrashLogger.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['super_admin']);

$user = AuthMiddleware::user();
$pdo = get_db();
$isSuperAdmin = RoleMiddleware::isSuperAdmin();

// Super admin telemetry counts
$crashStats = CrashLogger::getStats();
$totalUsersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRolesCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();

// Counts
$totalResearch = $pdo->query("SELECT COUNT(*) FROM research")->fetchColumn();
$totalEquipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$totalMaterials = $pdo->query("SELECT COUNT(*) FROM materials_chemicals")->fetchColumn();
$totalFaculty = $pdo->query("SELECT COUNT(*) FROM faculty")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Recent messages
$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5")->fetchAll();

// Active Driver
$activeDriver = DB::getDriver();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Dashboard | SCJE Super Admin</title>
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
            background: var(--color-bg-page);
            color: var(--color-text-primary);
            padding: 30px;
            overflow-y: auto;
        }
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 1024px) {
            .admin-layout { grid-template-columns: 1fr; }
        }
        .stat-card {
            background: var(--color-bg-surface);
            border-radius: var(--radius-md);
            padding: 22px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-val {
            font-size: 2rem;
            font-weight: 900;
            color: var(--color-text-primary);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--color-text-muted);
            margin-top: 4px;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            background: var(--color-bg-subtle);
            color: var(--color-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
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
    <!-- Unified Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2 style="font-size:1.6rem; font-weight:900; color:var(--color-text-primary); margin:0; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-gauge" style="color:#F59E0B;"></i> System Dashboard
                </h2>
                <p style="color:var(--color-text-muted); font-size:0.9rem; margin-top:4px;">Welcome back, <strong><?= e($user['name']) ?></strong> (<?= e($user['id_number']) ?>) &bull; <span style="color:#F59E0B; font-weight:700;"><i class="fa-solid fa-crown"></i> Super Administrator Clearance</span></p>
            </div>
            <a href="<?= base_url() ?>" class="btn-primary" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Website
            </a>
        </div>

        <?php if ($isSuperAdmin): ?>
            <!-- SUPER ADMIN CONTROL CENTER BANNER -->
            <div style="background:linear-gradient(135deg, rgba(15, 37, 75, 0.95), rgba(7, 19, 36, 0.98)); border:1px solid rgba(245, 158, 11, 0.35); border-radius:var(--radius-md); padding:24px; margin-bottom:25px; box-shadow:0 10px 30px rgba(0,0,0,0.4); position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; right:0; width:300px; height:100%; background:radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%); pointer-events:none;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:18px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, #7C3AED, #F59E0B); display:flex; align-items:center; justify-content:center; color:#FFFFFF; font-size:1.2rem; box-shadow:0 0 15px rgba(245,158,11,0.4);">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#FFFFFF; letter-spacing:0.3px;">Super Administrator Command Center</h3>
                            <p style="margin:2px 0 0; font-size:0.8rem; color:#94A3B8;">Real-time system health, automated crash telemetry, and role authorization.</p>
                        </div>
                    </div>
                    <span class="badge" style="background:rgba(245,158,11,0.15); color:#F59E0B; border:1px solid rgba(245,158,11,0.3); font-weight:800; font-size:0.75rem; padding:4px 10px;">
                        <i class="fa-solid fa-shield-halved" style="margin-right:4px;"></i> ELEVATED CLEARANCE
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
                    <!-- Health Widget -->
                    <a href="<?= base_url('admin/health.php') ?>" style="background:rgba(255,255,255,0.04); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:14px 16px; text-decoration:none; color:inherit; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <div>
                            <div style="font-size:0.75rem; font-weight:800; color:#10B981; text-transform:uppercase;">System Health</div>
                            <div style="font-size:1.15rem; font-weight:900; color:#FFFFFF; margin-top:3px;">
                                <i class="fa-solid fa-circle-check" style="color:#10B981; font-size:0.95rem; margin-right:4px;"></i> Operational
                            </div>
                            <div style="font-size:0.72rem; color:#64748B; margin-top:2px;">PHP <?= PHP_VERSION ?> &bull; <?= strtoupper($activeDriver) ?></div>
                        </div>
                        <i class="fa-solid fa-heart-pulse" style="font-size:1.5rem; color:#10B981;"></i>
                    </a>

                    <!-- Crash Telemetry Widget -->
                    <a href="<?= base_url('admin/crash_logs.php') ?>" style="background:rgba(255,255,255,0.04); border:1px solid <?= ($crashStats['unresolved'] ?? 0) > 0 ? 'rgba(239,68,68,0.4)' : 'rgba(56,189,248,0.3)' ?>; border-radius:10px; padding:14px 16px; text-decoration:none; color:inherit; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <div>
                            <div style="font-size:0.75rem; font-weight:800; color:<?= ($crashStats['unresolved'] ?? 0) > 0 ? '#EF4444' : '#38BDF8' ?>; text-transform:uppercase;">Crash Telemetry</div>
                            <div style="font-size:1.15rem; font-weight:900; color:#FFFFFF; margin-top:3px;">
                                <?= $crashStats['unresolved'] ?? 0 ?> Unresolved
                            </div>
                            <div style="font-size:0.72rem; color:#64748B; margin-top:2px;"><?= $crashStats['total'] ?? 0 ?> Total Incident(s)</div>
                        </div>
                        <i class="fa-solid fa-bug" style="font-size:1.5rem; color:<?= ($crashStats['unresolved'] ?? 0) > 0 ? '#EF4444' : '#38BDF8' ?>;"></i>
                    </a>

                    <!-- Accounts & Roles Widget -->
                    <a href="<?= base_url('admin/users.php') ?>" style="background:rgba(255,255,255,0.04); border:1px solid rgba(168,85,247,0.3); border-radius:10px; padding:14px 16px; text-decoration:none; color:inherit; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s;" onmouseover="this.style.background='rgba(168,85,247,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <div>
                            <div style="font-size:0.75rem; font-weight:800; color:#A855F7; text-transform:uppercase;">Accounts &amp; Roles</div>
                            <div style="font-size:1.15rem; font-weight:900; color:#FFFFFF; margin-top:3px;">
                                <?= $totalUsersCount ?> Users
                            </div>
                            <div style="font-size:0.72rem; color:#64748B; margin-top:2px;"><?= $totalRolesCount ?> Defined Roles</div>
                        </div>
                        <i class="fa-solid fa-user-shield" style="font-size:1.5rem; color:#A855F7;"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>


        <!-- Metric Cards -->
        <div class="stat-cards-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalResearch ?></div>
                    <div class="stat-label">Research Papers</div>
                </div>
                <div class="stat-icon" style="color:var(--color-gold); background:rgba(212, 175, 55, 0.15);"><i class="fa-solid fa-book"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalEquipment ?></div>
                    <div class="stat-label">Equipment Units</div>
                </div>
                <div class="stat-icon" style="color:#F59E0B; background:rgba(245, 158, 11, 0.15);"><i class="fa-solid fa-microscope"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalMaterials ?></div>
                    <div class="stat-label">Materials &amp; Chemicals</div>
                </div>
                <div class="stat-icon" style="color:#38BDF8; background:rgba(56, 189, 248, 0.15);"><i class="fa-solid fa-flask-vial"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalFaculty ?></div>
                    <div class="stat-label">Faculty &amp; Staff</div>
                </div>
                <div class="stat-icon" style="color:#10B981; background:rgba(16, 185, 129, 0.15);"><i class="fa-solid fa-users"></i></div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalMessages ?></div>
                    <div class="stat-label">Inquiries Received</div>
                </div>
                <div class="stat-icon" style="color:#A855F7; background:rgba(168, 85, 247, 0.15);"><i class="fa-solid fa-envelope"></i></div>
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

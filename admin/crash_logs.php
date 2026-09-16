<?php
/**
 * Crash Logs & System Telemetry Center
 * School of Criminal Justice Education (SCJE) Information System
 * Super Administrator Exclusive Access
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/CrashLogger.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['super_admin']);

$pdo = get_db();
$user = AuthMiddleware::user();
$noticeMessage = '';
$noticeType = 'success';

// Handle Actions (Resolve, Delete, Clear, Simulate, Export)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $noticeMessage = 'Security token validation failed. Please try again.';
        $noticeType = 'error';
    } else {
        $act = $_POST['action'];

        if ($act === 'toggle_resolve') {
            $logId = (int)($_POST['log_id'] ?? 0);
            if ($logId > 0 && CrashLogger::toggleResolved($logId)) {
                $noticeMessage = "Log #{$logId} status toggled successfully.";
            }
        } elseif ($act === 'delete') {
            $logId = (int)($_POST['log_id'] ?? 0);
            if ($logId > 0 && CrashLogger::delete($logId)) {
                $noticeMessage = "Log incident #{$logId} permanently removed.";
            }
        } elseif ($act === 'clear_all') {
            if (CrashLogger::clearAll()) {
                $noticeMessage = "All telemetry and crash logs have been purged.";
            }
        } elseif ($act === 'simulate') {
            $simType = $_POST['sim_type'] ?? 'exception';
            try {
                CrashLogger::simulateCrash($simType);
            } catch (Throwable $e) {
                // Handled by global exception handler or trapped here
            }
            $noticeMessage = "Simulated {$simType} crash was generated and recorded in telemetry!";
        }
    }
}

// Handle Export
if (isset($_GET['export'])) {
    $format = strtolower($_GET['export']);
    $allLogs = $pdo->query("SELECT * FROM crash_logs ORDER BY id DESC")->fetchAll();

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="scje_crash_logs_' . date('Ymd_His') . '.json"');
        echo json_encode($allLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="scje_crash_logs_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Level', 'Message', 'File', 'Line', 'URL', 'Method', 'IP Address', 'User ID', 'Role', 'Resolved', 'Created At']);
        foreach ($allLogs as $row) {
            fputcsv($out, [
                $row['id'],
                $row['level'],
                $row['message'],
                $row['file'],
                $row['line'],
                $row['url'],
                $row['method'],
                $row['ip_address'],
                $row['user_id'],
                $row['user_role'],
                $row['resolved'] ? 'Yes' : 'No',
                $row['created_at']
            ]);
        }
        fclose($out);
        exit;
    }
}

// Telemetry Stats
$stats = CrashLogger::getStats();

// Query Filters
$filterLevel = trim($_GET['level'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$logResults = CrashLogger::getLogs($perPage, $offset, $filterLevel, $filterStatus, $searchQuery);
$totalLogs = $logResults['total'];
$logs = $logResults['logs'];
$totalPages = max(1, ceil($totalLogs / $perPage));

$activePage = 'crash_logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crash Logs &amp; Error Telemetry | SCJE Super Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #071324; color: #E2E8F0; padding: 24px 0; border-right: 1px solid rgba(56,189,248,0.2); display: flex; flex-direction: column; }
        .admin-brand { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; }
        .admin-nav { list-style: none; padding: 10px 0; flex-grow: 1; overflow-y: auto; }
        .admin-nav li a { display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: #CBD5E1; font-weight: 600; font-size: 0.88rem; transition: var(--transition); text-decoration: none; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(30,58,138,0.5); color: var(--color-primary-accent); border-left: 4px solid var(--color-primary-accent); }
        .admin-main { background: var(--color-bg-page); color: var(--color-text-primary); padding: 30px; overflow-y: auto; }

        /* Metric Summary Cards */
        .crash-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        .crash-stat-card {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }
        .crash-stat-val {
            font-size: 1.85rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1;
        }
        .crash-stat-lbl {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--color-text-muted);
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .crash-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        /* Filter Toolbar */
        .filter-toolbar {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Form Controls */
        .form-control {
            background: #0B192C !important;
            border: 1px solid var(--color-border) !important;
            color: #FFFFFF !important;
            border-radius: 6px;
            outline: none;
            transition: var(--transition-fast);
        }
        .form-control:focus {
            border-color: #38BDF8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        .form-control::placeholder {
            color: #94A3B8 !important;
        }
        .form-control option {
            background: #0B192C;
            color: #FFFFFF;
        }

        /* Crash Level Badges */
        .badge-level {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.76rem;
            font-weight: 900;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .level-fatal {
            background: rgba(239, 68, 68, 0.25);
            color: #FCA5A5;
            border: 1px solid rgba(248, 113, 113, 0.6);
            text-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
            animation: fatalBlink 2.5s infinite;
        }
        @keyframes fatalBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.65; }
        }
        .level-error {
            background: rgba(249, 115, 22, 0.25);
            color: #FDBA74;
            border: 1px solid rgba(251, 146, 60, 0.6);
            text-shadow: 0 0 8px rgba(249, 115, 22, 0.3);
        }
        .level-warning {
            background: rgba(245, 158, 11, 0.25);
            color: #FEF08A; /* Radiant bright pastel yellow with high contrast */
            border: 1px solid rgba(250, 204, 21, 0.65);
            text-shadow: 0 0 8px rgba(234, 179, 8, 0.35);
        }
        .level-notice {
            background: rgba(56, 189, 248, 0.25);
            color: #BAE6FD;
            border: 1px solid rgba(56, 189, 248, 0.6);
        }

        /* Modal Stack Trace Inspection */
        .trace-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .trace-modal.active { display: flex; }
        .trace-card {
            background: #0B192C;
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 16px;
            width: 100%;
            max-width: 820px;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }
        .trace-header {
            padding: 20px 24px;
            background: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .trace-body {
            padding: 24px;
            overflow-y: auto;
            color: #E2E8F0;
        }
        .trace-pre {
            background: #050C16;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #F87171;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 340px;
            overflow-y: auto;
        }

        /* Action Buttons */
        .btn-act {
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 0.82rem;
            transition: all 0.2s;
            color: var(--color-text-muted);
        }
        .btn-act:hover { background: rgba(255, 255, 255, 0.08); color: var(--color-text-primary); }

        /* Pagination */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0 0;
            font-size: 0.85rem;
            color: var(--color-text-muted);
        }
        .page-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            color: var(--color-text-primary);
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.82rem;
        }
        .page-link-btn:hover { background: var(--color-border); }
        .page-link-btn.disabled { opacity: 0.4; pointer-events: none; }
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
    <!-- Super Admin Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:0.8rem; font-weight:700; color:var(--color-primary-accent); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">
                    <i class="fa-solid fa-crown" style="color:#F59E0B; margin-right:4px;"></i> Super Admin Authority
                </div>
                <h1 style="font-size:1.65rem; font-weight:900; margin:0; color:var(--color-text-primary);">
                    Real-Time Crash Logs &amp; Error Telemetry
                </h1>
            </div>
            <!-- Quick Actions -->
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="button" class="btn-primary" onclick="document.getElementById('simModal').classList.add('active')" style="background:#EF4444; border-color:#F87171; font-size:0.82rem; padding:8px 14px;">
                    <i class="fa-solid fa-vial"></i> Simulate Test Crash
                </button>
                <div style="display:inline-flex; border-radius:6px; overflow:hidden; border:1px solid var(--color-border);">
                    <a href="?export=json" class="btn-act" style="background:var(--color-bg-surface); padding:8px 12px; font-weight:700;" title="Export JSON">
                        <i class="fa-solid fa-file-code"></i> JSON
                    </a>
                    <a href="?export=csv" class="btn-act" style="background:var(--color-bg-surface); padding:8px 12px; font-weight:700; border-left:1px solid var(--color-border);" title="Export CSV">
                        <i class="fa-solid fa-file-csv"></i> CSV
                    </a>
                </div>
                <?php if ($stats['total'] > 0): ?>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Permanently clear all crash logs?');">
                        <?= CsrfMiddleware::field() ?>
                        <input type="hidden" name="action" value="clear_all">
                        <button type="submit" class="btn-act" style="background:rgba(239, 68, 68, 0.15); color:#EF4444; border:1px solid rgba(239,68,68,0.3); padding:8px 12px; font-weight:700;">
                            <i class="fa-solid fa-trash-can"></i> Purge All
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($noticeMessage)): ?>
            <div style="padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:0.88rem; font-weight:700; background:rgba(16, 185, 129, 0.15); border:1px solid #10B981; color:#34D399;">
                <i class="fa-solid fa-circle-check"></i> <?= e($noticeMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Stat Overview Cards -->
        <div class="crash-stat-grid">
            <div class="crash-stat-card">
                <div>
                    <div class="crash-stat-val" style="color:#38BDF8;"><?= number_format($stats['total']) ?></div>
                    <div class="crash-stat-lbl">Total Incidents</div>
                </div>
                <div class="crash-icon-box" style="background:rgba(56, 189, 248, 0.15); color:#38BDF8;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>

            <div class="crash-stat-card">
                <div>
                    <div class="crash-stat-val" style="color:#EF4444;"><?= number_format($stats['unresolved']) ?></div>
                    <div class="crash-stat-lbl">Unresolved Alerts</div>
                </div>
                <div class="crash-icon-box" style="background:rgba(239, 68, 68, 0.15); color:#EF4444;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <div class="crash-stat-card">
                <div>
                    <div class="crash-stat-val" style="color:#F59E0B;"><?= number_format($stats['fatal']) ?></div>
                    <div class="crash-stat-lbl">Fatal Crashes</div>
                </div>
                <div class="crash-icon-box" style="background:rgba(245, 158, 11, 0.15); color:#F59E0B;">
                    <i class="fa-solid fa-skull"></i>
                </div>
            </div>

            <div class="crash-stat-card">
                <div>
                    <div class="crash-stat-val" style="color:#10B981;"><?= number_format($stats['recent_24h']) ?></div>
                    <div class="crash-stat-lbl">Logged Last 24h</div>
                </div>
                <div class="crash-icon-box" style="background:rgba(16, 185, 129, 0.15); color:#10B981;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="filter-toolbar">
            <form method="GET" class="filter-group" style="margin:0; width:100%; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <!-- Severity Filter -->
                    <select name="level" class="form-control" style="width:140px; padding:7px 12px; font-size:0.82rem;" onchange="this.form.submit()">
                        <option value="">All Severities</option>
                        <option value="FATAL" <?= $filterLevel === 'FATAL' ? 'selected' : '' ?>>FATAL</option>
                        <option value="ERROR" <?= $filterLevel === 'ERROR' ? 'selected' : '' ?>>ERROR</option>
                        <option value="WARNING" <?= $filterLevel === 'WARNING' ? 'selected' : '' ?>>WARNING</option>
                        <option value="NOTICE" <?= $filterLevel === 'NOTICE' ? 'selected' : '' ?>>NOTICE</option>
                    </select>

                    <!-- Status Filter -->
                    <select name="status" class="form-control" style="width:140px; padding:7px 12px; font-size:0.82rem;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="unresolved" <?= $filterStatus === 'unresolved' ? 'selected' : '' ?>>Unresolved</option>
                        <option value="resolved" <?= $filterStatus === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>

                    <!-- Search Input -->
                    <div style="position:relative;">
                        <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search message, file, url..." class="form-control" style="width:240px; padding:7px 12px 7px 32px; font-size:0.82rem;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:11px; top:11px; color:#64748B; font-size:0.75rem;"></i>
                    </div>

                    <button type="submit" class="btn-primary" style="padding:7px 14px; font-size:0.82rem;">
                        Filter
                    </button>
                    <?php if ($filterLevel || $filterStatus || $searchQuery): ?>
                        <a href="crash_logs.php" class="btn-act" style="font-size:0.82rem;">Reset</a>
                    <?php endif; ?>
                </div>

                <div style="font-size:0.82rem; color:#CBD5E1; font-weight:700;">
                    Showing <span style="color:#F59E0B; font-weight:800;"><?= count($logs) ?></span> of <span style="color:#F59E0B; font-weight:800;"><?= number_format($totalLogs) ?></span> incidents
                </div>
            </form>
        </div>

        <!-- Crash Log Table -->
        <div class="table-card">
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Level</th>
                            <th>Incident Summary</th>
                            <th>Origin (File:Line)</th>
                            <th>Request Context</th>
                            <th>Logged At</th>
                            <th>Status</th>
                            <th style="text-align:right; width:130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:35px 20px; color:#64748B;">
                                    <i class="fa-solid fa-shield-heart" style="font-size:2rem; color:#10B981; margin-bottom:10px; display:block;"></i>
                                    No crash incidents matching the current criteria. System telemetry reports stable operations.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): 
                                $lvl = strtoupper($log['level'] ?? 'ERROR');
                                $badgeClass = match ($lvl) {
                                    'FATAL' => 'level-fatal',
                                    'ERROR' => 'level-error',
                                    'WARNING' => 'level-warning',
                                    default => 'level-notice'
                                };
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge-level <?= $badgeClass ?>">
                                            <?= $lvl ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#FFFFFF; font-size:0.88rem; margin-bottom:3px; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($log['message']) ?>">
                                            <?= e($log['message']) ?>
                                        </div>
                                        <?php if (!empty($log['user_id'])): ?>
                                            <div style="font-size:0.75rem; color:#CBD5E1; margin-top:2px; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fa-solid fa-user" style="color:#F59E0B; font-size:0.7rem;"></i> 
                                                <strong style="color:#FFFFFF;"><?= e($log['user_id']) ?></strong> 
                                                <span style="color:#94A3B8;">(<?= e($log['user_role'] ?? 'user') ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-family:'JetBrains Mono', monospace; font-size:0.8rem; color:#E2E8F0; font-weight:600;">
                                            <i class="fa-solid fa-file-code" style="color:#38BDF8; font-size:0.75rem; margin-right:4px;"></i><?= e(basename((string)$log['file'])) ?><span style="color:#F59E0B;"><?= $log['line'] ? ':' . $log['line'] : '' ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-family:'JetBrains Mono', monospace; font-size:0.78rem;">
                                            <span class="badge" style="background:rgba(56,189,248,0.2); color:#38BDF8; border:1px solid rgba(56,189,248,0.4); font-size:0.68rem; font-weight:800; padding:1px 6px; border-radius:4px;"><?= e($log['method'] ?? 'GET') ?></span>
                                            <span style="color:#E2E8F0; font-weight:600; margin-left:4px;"><?= e(mb_strimwidth((string)$log['url'], 0, 30, '...')) ?></span>
                                        </div>
                                        <div style="font-size:0.74rem; color:#94A3B8; margin-top:3px;">IP: <span style="color:#CBD5E1; font-family:'JetBrains Mono', monospace;"><?= e($log['ip_address'] ?? '127.0.0.1') ?></span></div>
                                    </td>
                                    <td style="font-size:0.82rem; color:#E2E8F0; font-family:'JetBrains Mono', monospace; font-weight:600; white-space:nowrap;">
                                        <?= e($log['created_at']) ?>
                                    </td>
                                    <td>
                                        <?php if ($log['resolved']): ?>
                                            <span class="badge" style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.25); color:#6EE7B7; border:1px solid rgba(16,185,129,0.5); padding:3px 9px; border-radius:12px;">
                                                <i class="fa-solid fa-check"></i> RESOLVED
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="font-size:0.75rem; font-weight:800; background:rgba(239,68,68,0.25); color:#FCA5A5; border:1px solid rgba(239,68,68,0.5); padding:3px 9px; border-radius:12px;">
                                                <i class="fa-solid fa-circle-exclamation"></i> ACTIVE
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right; white-space:nowrap;">
                                        <!-- View Trace Button -->
                                        <button type="button" class="btn-act" onclick="openTraceModal(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8') ?>)" title="Inspect Full Trace" style="background:rgba(56,189,248,0.15); color:#38BDF8; border:1px solid rgba(56,189,248,0.3); padding:5px 9px; border-radius:6px;">
                                            <i class="fa-solid fa-terminal"></i>
                                        </button>

                                        <!-- Toggle Resolved -->
                                        <form method="POST" style="display:inline; margin:0;">
                                            <?= CsrfMiddleware::field() ?>
                                            <input type="hidden" name="action" value="toggle_resolve">
                                            <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                                            <button type="submit" class="btn-act" title="<?= $log['resolved'] ? 'Reopen Incident' : 'Mark as Resolved' ?>" style="background:rgba(16,185,129,0.15); color:<?= $log['resolved'] ? '#F59E0B' : '#34D399' ?>; border:1px solid <?= $log['resolved'] ? 'rgba(245,158,11,0.3)' : 'rgba(16,185,129,0.3)' ?>; padding:5px 9px; border-radius:6px;">
                                                <i class="fa-solid <?= $log['resolved'] ? 'fa-rotate-left' : 'fa-check' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Log -->
                                        <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this log entry?');">
                                            <?= CsrfMiddleware::field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                                            <button type="submit" class="btn-act" title="Delete Log" style="background:rgba(239,68,68,0.15); color:#F87171; border:1px solid rgba(239,68,68,0.3); padding:5px 9px; border-radius:6px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <div>
                        Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <a href="?p=<?= $page - 1 ?>&level=<?= urlencode($filterLevel) ?>&status=<?= urlencode($filterStatus) ?>&q=<?= urlencode($searchQuery) ?>" class="page-link-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            &larr; Previous
                        </a>
                        <a href="?p=<?= $page + 1 ?>&level=<?= urlencode($filterLevel) ?>&status=<?= urlencode($filterStatus) ?>&q=<?= urlencode($searchQuery) ?>" class="page-link-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            Next &rarr;
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ========================================================= -->
<!-- MODAL: STACK TRACE & INCIDENT INSPECTOR                   -->
<!-- ========================================================= -->
<div id="traceModal" class="trace-modal">
    <div class="trace-card">
        <div class="trace-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <span id="modalLevelBadge" class="badge-level level-fatal">FATAL</span>
                <h3 id="modalTitle" style="margin:0; font-size:1.05rem; font-weight:800; color:#FFFFFF;">Incident Details</h3>
            </div>
            <button type="button" class="btn-act" onclick="document.getElementById('traceModal').classList.remove('active')" style="font-size:1.3rem; color:#94A3B8;">&times;</button>
        </div>
        <div class="trace-body">
            <div style="margin-bottom:16px;">
                <div style="font-size:0.75rem; color:#64748B; font-weight:700; text-transform:uppercase;">Exception Message</div>
                <div id="modalMsg" style="font-size:0.95rem; font-weight:800; color:#F87171; margin-top:3px;"></div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:18px; background:rgba(0,0,0,0.3); padding:12px; border-radius:8px; font-size:0.8rem;">
                <div><strong>File:</strong> <span id="modalFile" style="font-family:'JetBrains Mono', monospace; color:#38BDF8;"></span></div>
                <div><strong>Line:</strong> <span id="modalLine" style="font-family:'JetBrains Mono', monospace; color:#F59E0B;"></span></div>
                <div><strong>Endpoint:</strong> <span id="modalEndpoint" style="font-family:'JetBrains Mono', monospace; color:#A855F7;"></span></div>
                <div><strong>Client IP:</strong> <span id="modalIp" style="font-family:'JetBrains Mono', monospace; color:#10B981;"></span></div>
                <div><strong>User Context:</strong> <span id="modalUser" style="color:#CBD5E1;"></span></div>
                <div><strong>Timestamp:</strong> <span id="modalTime" style="color:#94A3B8;"></span></div>
            </div>

            <div>
                <div style="font-size:0.75rem; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Stack Trace &amp; Execution Tree</div>
                <pre id="modalTrace" class="trace-pre"></pre>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: SIMULATE CRASH / TEST DIAGNOSTIC                   -->
<!-- ========================================================= -->
<div id="simModal" class="trace-modal">
    <div class="trace-card" style="max-width:500px;">
        <div class="trace-header">
            <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#FFFFFF;">
                <i class="fa-solid fa-vial" style="color:#EF4444; margin-right:8px;"></i> Simulate Diagnostic Crash
            </h3>
            <button type="button" class="btn-act" onclick="document.getElementById('simModal').classList.remove('active')" style="font-size:1.3rem; color:#94A3B8;">&times;</button>
        </div>
        <form method="POST" style="padding:24px; margin:0;">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="simulate">
            <p style="font-size:0.85rem; color:#94A3B8; margin-top:0; line-height:1.5;">
                Trigger a live synthetic error to verify the real-time crash handling, exception logging, and failover mechanics.
            </p>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:8px;">Diagnostic Incident Type</label>
                <select name="sim_type" class="form-control" style="width:100%;">
                    <option value="exception">Uncaught RuntimeException (FATAL)</option>
                    <option value="warning">PHP User Warning (WARNING)</option>
                    <option value="fatal">Simulated Fatal Trap (FATAL)</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('simModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#EF4444; border-color:#F87171;">
                    <i class="fa-solid fa-bolt"></i> Trigger Test Incident
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTraceModal(log) {
    document.getElementById('modalLevelBadge').textContent = log.level || 'ERROR';
    document.getElementById('modalLevelBadge').className = 'badge-level ' + (
        log.level === 'FATAL' ? 'level-fatal' : (log.level === 'WARNING' ? 'level-warning' : 'level-error')
    );
    document.getElementById('modalTitle').textContent = 'Incident #' + log.id;
    document.getElementById('modalMsg').textContent = log.message || 'No description provided';
    document.getElementById('modalFile').textContent = log.file || 'N/A';
    document.getElementById('modalLine').textContent = log.line || 'N/A';
    document.getElementById('modalEndpoint').textContent = (log.method || 'GET') + ' ' + (log.url || '/');
    document.getElementById('modalIp').textContent = log.ip_address || '127.0.0.1';
    document.getElementById('modalUser').textContent = (log.user_id ? log.user_id + ' (' + (log.user_role || 'user') + ')' : 'Anonymous visitor / Guest');
    document.getElementById('modalTime').textContent = log.created_at || 'N/A';
    document.getElementById('modalTrace').textContent = log.trace || 'No detailed stack trace captured for this event.';

    document.getElementById('traceModal').classList.add('active');
}
</script>

</body>
</html>

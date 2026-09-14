<?php
/**
 * Admin: Contact Inquiries Management
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin']);

$pdo = get_db();
$message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$delId]);
        $message = 'Inquiry deleted.';
    }
}

// Handle Mark Read
if (isset($_GET['mark_read'])) {
    $readId = intval($_GET['mark_read']);
    if ($readId > 0) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$readId]);
        $message = 'Inquiry marked as read.';
    }
}

$inquiries = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | SCJE Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #071324; color: #E2E8F0; padding: 24px 0; border-right: 1px solid rgba(56,189,248,0.2); }
        .admin-brand { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .admin-nav { list-style: none; padding: 20px 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: #CBD5E1; font-weight: 600; font-size: 0.9rem; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(30,58,138,0.5); color: var(--color-primary-accent); border-left: 4px solid var(--color-primary-accent); }
        .admin-main { background: var(--color-bg-page); color: var(--color-text-primary); padding: 30px; overflow-y: auto; }
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
            <li><a href="<?= base_url('admin/content.php') ?>"><i class="fa-solid fa-compass"></i> Site Content</a></li>
            <li><a href="<?= base_url('admin/messages.php') ?>" class="active"><i class="fa-solid fa-envelope"></i> Inquiries</a></li>
            <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);"><a href="<?= base_url() ?>"><i class="fa-solid fa-globe"></i> View Public Site</a></li>
            <li><a href="<?= base_url('logout.php') ?>" style="color:#F87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F; margin-bottom:20px;">Contact Inquiries</h2>

        <?php if ($message): ?>
            <div class="badge badge-success" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($message) ?></div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B;">Inbox (<?= count($inquiries) ?>)</h3>
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:30px; color:#64748B;">No contact messages received.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inq): 
                                $isUnread = $inq['status'] === 'unread';
                            ?>
                                <tr style="<?= $isUnread ? 'background:#EFF6FF; font-weight:600;' : '' ?>">
                                    <td>
                                        <span class="badge <?= $isUnread ? 'badge-danger' : 'badge-success' ?>"><?= strtoupper(e($inq['status'])) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= e($inq['name']) ?></strong>
                                        <div style="font-size:0.75rem; color:#64748B;"><?= e($inq['email']) ?></div>
                                    </td>
                                    <td><?= e($inq['subject']) ?></td>
                                    <td style="max-width:300px; font-size:0.85rem; color:#334155;"><?= nl2br(e($inq['message'])) ?></td>
                                    <td style="font-size:0.8rem; color:#64748B;"><?= e($inq['created_at']) ?></td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <?php if ($isUnread): ?>
                                            <a href="<?= base_url('admin/messages.php?mark_read=' . $inq['id']) ?>" 
                                               class="btn-primary" style="background:#059669; padding:5px 8px; font-size:0.75rem;" title="Mark Read">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/messages.php?delete=' . $inq['id']) ?>" 
                                           class="btn-primary" 
                                           data-confirm-title="Delete Inquiry"
                                           data-confirm="Are you sure you want to delete this message from '<?= e(addslashes($inq['sender_name'])) ?>'? This action cannot be undone."
                                           style="background:#EF4444; padding:5px 8px; font-size:0.75rem;" 
                                           title="Delete Message">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="<?= base_url('assets/js/admin-confirm.js') ?>"></script>
</body>
</html>


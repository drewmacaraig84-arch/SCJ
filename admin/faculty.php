<?php
/**
 * Admin: Faculty & Staff Directory Management
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

// Handle Add Faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $roleLevel = trim($_POST['role_level'] ?? 'faculty');
        $email = trim($_POST['email'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $interests = trim($_POST['research_interests'] ?? '');
        $office = trim($_POST['office_location'] ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $order = intval($_POST['order_index'] ?? 10);

        if ($name && $position) {
            $stmt = $pdo->prepare("INSERT INTO faculty (name, position, role_level, email, specialization, research_interests, office_location, photo_url, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $position, $roleLevel, $email, $specialization, $interests, $office, $photoUrl, $order]);
            Cache::flush();
            $message = 'Faculty member added.';
        } else {
            $error = 'Please fill out all required fields.';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM faculty WHERE id = ?");
        $stmt->execute([$delId]);
        Cache::flush();
        $message = 'Faculty member removed.';
    }
}

$faculty = $pdo->query("SELECT * FROM faculty ORDER BY order_index ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty | SCJE Admin</title>
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
    <?php 
    $activePage = 'faculty';
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <main class="admin-main">
        <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F; margin-bottom:20px;">Manage Faculty & Staff</h2>

        <?php if ($message): ?>
            <div class="badge badge-success" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="badge badge-danger" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Add Faculty Form -->
        <div class="table-card" style="padding:24px; margin-bottom:30px;">
            <h3 style="font-size:1.1rem; font-weight:800; color:#0F254B; margin-bottom:16px;">
                <i class="fa-solid fa-user-plus"></i> Add Faculty Member
            </h3>
            <form action="<?= base_url('admin/faculty.php') ?>" method="POST">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" name="action" value="add">

                <div style="display:grid; grid-template-columns: 2fr 2fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Full Name & Titles *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Prof. Juan Dela Cruz, RCrim., MS Crim." required>
                    </div>
                    <div class="form-group">
                        <label>Position / Designation *</label>
                        <input type="text" name="position" class="form-control" placeholder="e.g. Instructor - Forensic Ballistics" required>
                    </div>
                    <div class="form-group">
                        <label>Hierarchy Role</label>
                        <select name="role_level" class="form-control">
                            <option value="faculty">Faculty</option>
                            <option value="dean">OIC Dean</option>
                            <option value="chair">Program Chair</option>
                            <option value="coordinator">Coordinator</option>
                            <option value="custodian">Lab Custodian</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="faculty@dwcc-scje.edu.ph">
                    </div>
                    <div class="form-group">
                        <label>Office / Room</label>
                        <input type="text" name="office_location" class="form-control" placeholder="e.g. Faculty Hall Room 204">
                    </div>
                    <div class="form-group">
                        <label>Photo URL / Asset Path</label>
                        <input type="text" name="photo_url" class="form-control" placeholder="assets/images/faculty_name.png">
                    </div>
                    <div class="form-group">
                        <label>Display Order Priority</label>
                        <input type="number" name="order_index" class="form-control" value="10">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g. Criminalistics, Forensic Ballistics">
                    </div>
                    <div class="form-group">
                        <label>Research Interests / Key Roles</label>
                        <input type="text" name="research_interests" class="form-control" placeholder="e.g. Program Coordinator, NSTP-ROTC Leadership">
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Faculty Member
                </button>
            </form>
        </div>

        <!-- Faculty Directory Table -->
        <div class="table-card">
            <div class="table-toolbar">
                <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B;">Faculty & Staff Directory (<?= count($faculty) ?>)</h3>
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Photo</th>
                            <th style="width: 60px;">Order</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Role Level</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $f): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $fPhoto = (!empty($f['photo_url']) && file_exists(__DIR__ . '/../' . $f['photo_url']))
                                        ? base_url($f['photo_url']) . '?v=' . filemtime(__DIR__ . '/../' . $f['photo_url'])
                                        : base_url('assets/images/avatar_placeholder.svg');
                                    ?>
                                    <img src="<?= $fPhoto ?>" alt="<?= e($f['name']) ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--color-gold, #D97706); display:block;">
                                </td>
                                <td><?= e($f['order_index']) ?></td>
                                <td style="font-weight:700;"><?= e($f['name']) ?></td>
                                <td><?= e($f['position']) ?></td>
                                <td><span class="badge badge-info"><?= strtoupper(e($f['role_level'])) ?></span></td>
                                <td style="text-align:center;">
                                    <a href="<?= base_url('admin/faculty.php?delete=' . $f['id']) ?>" 
                                       class="btn-primary" 
                                       data-confirm-title="Remove Faculty Member"
                                       data-confirm="Are you sure you want to remove '<?= e(addslashes($f['name'])) ?>' from the faculty roster? This action cannot be undone."
                                       style="background:#EF4444; padding:5px 10px; font-size:0.75rem;"
                                       title="Remove Faculty Member">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="<?= base_url('assets/js/admin-confirm.js') ?>"></script>
</body>
</html>


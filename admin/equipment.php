<?php
/**
 * Admin: Laboratory Equipment Inventory Management
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

// Handle Add Equipment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } else {
        $code = trim($_POST['equipment_code'] ?? '');
        $name = trim($_POST['equipment_name'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $location = trim($_POST['current_location'] ?? '');
        $lab = trim($_POST['laboratory_category'] ?? '');
        $status = trim($_POST['status'] ?? 'Good Condition');
        $serial = trim($_POST['serial_number'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $person = trim($_POST['person_accountable'] ?? 'Sir Jom');

        if ($code && $name && $brand && $model && $location) {
            try {
                $stmt = $pdo->prepare("INSERT INTO equipment (equipment_code, equipment_name, brand, model, current_location, laboratory_category, status, serial_number, description, person_accountable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $name, $brand, $model, $location, $lab, $status, $serial, $desc, $person]);
                Cache::flush();
                $message = 'Laboratory equipment added to inventory.';
            } catch (Exception $e) {
                $error = 'Failed to save: ' . $e->getMessage();
            }
        } else {
            $error = 'Please fill out all required fields.';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$delId]);
        Cache::flush();
        $message = 'Equipment deleted from inventory.';
    }
}

$equipments = $pdo->query("SELECT * FROM equipment ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Equipment | SCJE Admin</title>
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
    $activePage = 'equipment';
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <main class="admin-main">
        <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F; margin-bottom:20px;">Manage Laboratory Equipment</h2>

        <?php if ($message): ?>
            <div class="badge badge-success" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="badge badge-danger" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Add Equipment Form -->
        <div class="table-card" style="padding:24px; margin-bottom:30px;">
            <h3 style="font-size:1.1rem; font-weight:800; color:#0F254B; margin-bottom:16px;">
                <i class="fa-solid fa-plus-circle"></i> Add Laboratory Equipment
            </h3>
            <form action="<?= base_url('admin/equipment.php') ?>" method="POST">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" name="action" value="add">

                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:16px;">
                    <div class="form-group">
                        <label>Equipment Code *</label>
                        <input type="text" name="equipment_code" class="form-control" placeholder="e.g. EQ-BAL-004" required>
                    </div>
                    <div class="form-group">
                        <label>Equipment Name *</label>
                        <input type="text" name="equipment_name" class="form-control" placeholder="e.g. Comparison Microscope" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. Leica" required>
                    </div>
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. FS4000" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Current Location *</label>
                        <select name="current_location" class="form-control" required>
                            <option value="Crime Lab">Crime Lab</option>
                            <option value="Forensic Photography Room">Forensic Photography Room</option>
                            <option value="Fingerprint Room">Fingerprint Room</option>
                            <option value="Polygraphy Room">Polygraphy Room</option>
                            <option value="Dean’s Office">Dean’s Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Good Condition">Good Condition</option>
                            <option value="Brandnew">Brandnew</option>
                            <option value="Out Of Service">Out Of Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Accountable Officer</label>
                        <input type="text" name="person_accountable" class="form-control" value="Sir Jom" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Technical specifications..."></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Equipment
                </button>
            </form>
        </div>

        <!-- Equipment Table -->
        <div class="table-card">
            <div class="table-toolbar">
                <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B;">Current Equipment Inventory (<?= count($equipments) ?>)</h3>
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Equipment Name</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Accountable</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipments as $eq): ?>
                            <tr>
                                <td style="font-family:monospace; font-weight:700; color:var(--color-primary-light, #38BDF8);"><?= e($eq['equipment_code']) ?></td>
                                <td style="font-weight:700;"><?= e($eq['equipment_name']) ?></td>
                                <td><?= e($eq['brand']) ?></td>
                                <td><?= e($eq['model']) ?></td>
                                <td><?= e($eq['current_location']) ?></td>
                                <td><span class="badge <?= (str_contains(strtolower($eq['status']), 'out')) ? 'badge-danger' : 'badge-success' ?>"><?= e($eq['status']) ?></span></td>
                                <td><?= e($eq['person_accountable'] ?? 'Sir Jom') ?></td>
                                <td style="text-align:center;">
                                    <a href="<?= base_url('admin/equipment.php?delete=' . $eq['id']) ?>" 
                                       class="btn-primary" 
                                       data-confirm-title="Delete Equipment Entry"
                                       data-confirm="Are you sure you want to delete equipment '<?= e(addslashes($eq['equipment_name'])) ?>' (<?= e($eq['equipment_code']) ?>)? This action cannot be undone."
                                       style="background:#EF4444; padding:5px 10px; font-size:0.75rem;"
                                       title="Delete Equipment">
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


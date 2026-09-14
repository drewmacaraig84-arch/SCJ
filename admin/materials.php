<?php
/**
 * Admin: Materials & Chemicals Inventory Management
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

// Handle Add Material
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } else {
        $code = trim($_POST['item_code'] ?? '');
        $name = trim($_POST['item_name'] ?? '');
        $qty = trim($_POST['qty'] ?? '1');
        $unit = trim($_POST['unit'] ?? 'N/A');
        $brand = trim($_POST['brand'] ?? 'N/A');
        $location = trim($_POST['location'] ?? 'Crime Laboratory');
        $status = trim($_POST['status'] ?? 'Good Condition');
        $person = trim($_POST['person_accountable'] ?? 'Sir Jom');

        if ($code && $name) {
            try {
                $stmt = $pdo->prepare("INSERT INTO materials_chemicals (item_code, qty, unit, item_name, person_accountable, brand, status, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $qty, $unit, $name, $person, $brand, $status, $location]);
                Cache::flush();
                $message = 'Material/Chemical reagent added to inventory.';
            } catch (Exception $e) {
                $error = 'Failed to save: ' . $e->getMessage();
            }
        } else {
            $error = 'Please fill out required fields (Code and Item Name).';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM materials_chemicals WHERE id = ?");
        $stmt->execute([$delId]);
        Cache::flush();
        $message = 'Material record deleted from inventory.';
    }
}

$materials = $pdo->query("SELECT * FROM materials_chemicals ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials & Chemicals | SCJE Admin</title>
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
            <li><a href="<?= base_url('admin/materials.php') ?>" class="active"><i class="fa-solid fa-flask-vial"></i> Manage Materials</a></li>
            <li><a href="<?= base_url('admin/faculty.php') ?>"><i class="fa-solid fa-users"></i> Manage Faculty</a></li>
            <li><a href="<?= base_url('admin/content.php') ?>"><i class="fa-solid fa-compass"></i> Site Content</a></li>
            <li><a href="<?= base_url('admin/messages.php') ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a></li>
            <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);"><a href="<?= base_url() ?>"><i class="fa-solid fa-globe"></i> View Public Site</a></li>
            <li><a href="<?= base_url('logout.php') ?>" style="color:#F87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F; margin-bottom:20px;">Manage Materials &amp; Chemicals Inventory</h2>

        <?php if ($message): ?>
            <div class="badge badge-success" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="badge badge-danger" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Add Material Card -->
        <div style="background:var(--color-bg-surface); border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-sm); border:1px solid var(--color-border); margin-bottom:25px; color:var(--color-text-primary);">
            <h3 style="font-size:1.05rem; font-weight:800; color:var(--color-text-primary); margin-bottom:16px;">
                <i class="fa-solid fa-plus-circle" style="color:var(--color-primary-accent);"></i> Add Material / Reagent
            </h3>
            <form method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= CsrfMiddleware::generate() ?>">
                <input type="hidden" name="action" value="add">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Item Code *</label>
                        <input type="text" name="item_code" placeholder="e.g. MC-065" required class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Item Name *</label>
                        <input type="text" name="item_name" placeholder="e.g. Luminol Powder" required class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Quantity</label>
                        <input type="text" name="qty" placeholder="e.g. 5" value="1" class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Unit / Capacity</label>
                        <input type="text" name="unit" placeholder="e.g. 500 Ml / Grams / Pcs" class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Brand / Manufacturer</label>
                        <input type="text" name="brand" placeholder="e.g. Sirchie / Pyrex" class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Storage Location</label>
                        <select name="location" class="filter-select" style="width:100%;">
                            <option value="Crime Laboratory">Crime Laboratory</option>
                            <option value="Fingerprint Room">Fingerprint Room</option>
                            <option value="Forensic Photography">Forensic Photography</option>
                            <option value="Consultation Room">Consultation Room</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Status</label>
                        <select name="status" class="filter-select" style="width:100%;">
                            <option value="Good Condition">Good Condition</option>
                            <option value="Brand New">Brand New</option>
                            <option value="Out Of Service">Out Of Service</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700; color:#475569;">Accountable Officer</label>
                        <input type="text" name="person_accountable" value="Sir Jom" class="sketch-search-input" style="width:100%; border:1px solid #CBD5E1; padding:8px 12px;">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="padding:8px 20px;">Save Material Record</button>
            </form>
        </div>

        <!-- Materials Table Card -->
        <div style="background:var(--color-bg-surface); border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-sm); border:1px solid var(--color-border); color:var(--color-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:1.05rem; font-weight:800; color:var(--color-text-primary);">Current Materials &amp; Chemicals Inventory (<?= count($materials) ?>)</h3>
                <input type="text" id="adminMatFilter" placeholder="Filter records..." class="sketch-search-input" style="padding:6px 12px; font-size:0.85rem; border:1px solid #CBD5E1; border-radius:4px;" onkeyup="filterAdminMat()">
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Item Name</th>
                            <th>Qty &amp; Unit</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Accountable</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="adminMatBody">
                        <?php foreach ($materials as $m): ?>
                            <tr>
                                <td style="font-family:monospace; font-weight:800;"><?= e($m['item_code']) ?></td>
                                <td style="font-weight:700;"><?= e($m['item_name']) ?></td>
                                <td><?= e($m['qty']) ?> <?= ($m['unit'] !== 'N/A') ? e($m['unit']) : '' ?></td>
                                <td><?= e($m['brand']) ?></td>
                                <td><?= e($m['location']) ?></td>
                                <td><span class="badge badge-info"><?= e($m['status']) ?></span></td>
                                <td><?= e($m['person_accountable'] ?? 'Sir Jom') ?></td>
                                <td>
                                    <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this material record?');" style="color:#EF4444; font-size:0.85rem; font-weight:700;">
                                        <i class="fa-solid fa-trash"></i> Delete
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

<script>
function filterAdminMat() {
    const q = document.getElementById('adminMatFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#adminMatBody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>

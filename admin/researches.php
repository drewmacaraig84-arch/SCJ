<?php
/**
 * Admin: Research Papers Management
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/cache.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin', 'faculty']);

$pdo = get_db();
$message = '';
$error = '';

// Handle Add Research
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } else {
        $author = trim($_POST['author_name'] ?? '');
        $title = trim($_POST['research_title'] ?? '');
        $date = trim($_POST['month_year'] ?? '');
        $cat = trim($_POST['category'] ?? '');
        $abstract = trim($_POST['abstract'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');

        if ($author && $title && $date && $cat) {
            $stmt = $pdo->prepare("INSERT INTO research (author_name, research_title, month_year, category, abstract, keywords) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$author, $title, $date, $cat, $abstract, $keywords]);
            Cache::flush();
            $message = 'Research paper added successfully.';
        } else {
            $error = 'Please fill out all required fields.';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    RoleMiddleware::handle(['admin']);
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM research WHERE id = ?");
        $stmt->execute([$delId]);
        Cache::flush();
        $message = 'Research paper deleted.';
    }
}

$researches = $pdo->query("SELECT * FROM research ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Research | SCJE Admin</title>
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
            <li><a href="<?= base_url('admin/researches.php') ?>" class="active"><i class="fa-solid fa-book-open"></i> Manage Research</a></li>
            <li><a href="<?= base_url('admin/equipment.php') ?>"><i class="fa-solid fa-microscope"></i> Manage Equipment</a></li>
            <li><a href="<?= base_url('admin/materials.php') ?>"><i class="fa-solid fa-flask-vial"></i> Manage Materials</a></li>
            <li><a href="<?= base_url('admin/faculty.php') ?>"><i class="fa-solid fa-users"></i> Manage Faculty</a></li>
            <li><a href="<?= base_url('admin/content.php') ?>"><i class="fa-solid fa-compass"></i> Site Content</a></li>
            <li><a href="<?= base_url('admin/messages.php') ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a></li>
            <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);"><a href="<?= base_url() ?>"><i class="fa-solid fa-globe"></i> View Public Site</a></li>
            <li><a href="<?= base_url('logout.php') ?>" style="color:#F87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <h2 style="font-size:1.6rem; font-weight:900; color:#0A192F; margin-bottom:20px;">Manage Criminological Research</h2>

        <?php if ($message): ?>
            <div class="badge badge-success" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="badge badge-danger" style="padding:10px 16px; margin-bottom:16px; display:block;"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Add New Research Form -->
        <div class="table-card" style="padding:24px; margin-bottom:30px;">
            <h3 style="font-size:1.1rem; font-weight:800; color:#0F254B; margin-bottom:16px;">
                <i class="fa-solid fa-plus-circle"></i> Add New Research Paper
            </h3>
            <form action="<?= base_url('admin/researches.php') ?>" method="POST">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" name="action" value="add">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Author / Researcher Name *</label>
                        <input type="text" name="author_name" class="form-control" placeholder="e.g. Joan Mae A. Gayacan, RCrim." required>
                    </div>
                    <div class="form-group">
                        <label>Publication Date (Month / Year) *</label>
                        <input type="text" name="month_year" class="form-control" placeholder="e.g. March 2025" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Research Title *</label>
                        <input type="text" name="research_title" class="form-control" placeholder="Full thesis or study title..." required>
                    </div>
                    <div class="form-group">
                        <label>Criminological Field *</label>
                        <select name="category" class="form-control" required>
                            <option value="Criminal Investigation">Criminal Investigation</option>
                            <option value="Criminalistics">Criminalistics</option>
                            <option value="Forensic Science">Forensic Science</option>
                            <option value="Questioned Document Examination">Questioned Document Examination</option>
                            <option value="Forensic Photography">Forensic Photography</option>
                            <option value="Forensic Ballistics">Forensic Ballistics</option>
                            <option value="Crime Prevention">Crime Prevention</option>
                            <option value="Juvenile Delinquency">Juvenile Delinquency</option>
                            <option value="Police Administration">Police Administration</option>
                            <option value="Corrections">Corrections</option>
                            <option value="Community-Based Studies">Community-Based Studies</option>
                            <option value="Cybercrime">Cybercrime</option>
                            <option value="Criminal Justice Administration">Criminal Justice Administration</option>
                            <option value="Victimology">Victimology</option>
                            <option value="Penology">Penology</option>
                            <option value="Public Safety">Public Safety</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Abstract</label>
                    <textarea name="abstract" rows="3" class="form-control" placeholder="Brief summary of findings..."></textarea>
                </div>

                <div class="form-group">
                    <label>Keywords</label>
                    <input type="text" name="keywords" class="form-control" placeholder="e.g. Ballistics, Striations, Forensics">
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Research Paper
                </button>
            </form>
        </div>

        <!-- Research Table -->
        <div class="table-card">
            <div class="table-toolbar">
                <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B;">Existing Research Catalog (<?= count($researches) ?>)</h3>
            </div>
            <div class="custom-table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Title</th>
                            <th>Field</th>
                            <th>Month / Year</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($researches as $r): ?>
                            <tr>
                                <td style="font-weight:700;"><?= e($r['author_name']) ?></td>
                                <td><?= e($r['research_title']) ?></td>
                                <td><span class="badge badge-info"><?= e($r['category']) ?></span></td>
                                <td><?= e($r['month_year']) ?></td>
                                <td style="text-align:center;">
                                    <a href="<?= base_url('admin/researches.php?delete=' . $r['id']) ?>" 
                                       class="btn-primary" 
                                       data-confirm-title="Delete Research Paper"
                                       data-confirm="Are you sure you want to delete '<?= e(addslashes($r['research_title'])) ?>'? This action cannot be undone."
                                       style="background:#EF4444; padding:5px 10px; font-size:0.75rem;"
                                       title="Delete Research Record">
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


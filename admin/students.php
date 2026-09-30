<?php
/**
 * Student Account & Password Management
 * School of Criminal Justice (SCJ) Information System
 * Accessible by: Administrator & Super Administrator
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/cache.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin', 'super_admin']);

$pdo = get_db();
$currentUser = AuthMiddleware::user();
$isSuperAdmin = RoleMiddleware::isSuperAdmin();

$noticeMessage = '';
$noticeType = 'success';

// -------------------------------------------------------------
// POST ACTIONS: CHANGE PASSWORD / ADD STUDENT / EDIT STUDENT
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $noticeMessage = 'Security token validation failed. Please refresh and try again.';
        $noticeType = 'error';
    } else {
        $action = $_POST['action'];

        // 1. CHANGE STUDENT PASSWORD
        if ($action === 'change_password') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');

            if ($studentId <= 0) {
                $noticeMessage = 'Invalid student account selected.';
                $noticeType = 'error';
            } elseif (strlen($newPassword) < 4) {
                $noticeMessage = 'The new password must be at least 4 characters long.';
                $noticeType = 'error';
            } elseif ($newPassword !== $confirmPassword) {
                $noticeMessage = 'The new password and confirmation password do not match.';
                $noticeType = 'error';
            } else {
                // Verify student exists and is indeed a student (or non-super admin)
                $stmt = $pdo->prepare("SELECT id, id_number, name, role FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$studentId]);
                $target = $stmt->fetch();

                if (!$target) {
                    $noticeMessage = 'Student record not found in system.';
                    $noticeType = 'error';
                } elseif (!$isSuperAdmin && in_array($target['role'], ['admin', 'super_admin'])) {
                    $noticeMessage = 'Administrative authorization error: You cannot modify privileged staff accounts here.';
                    $noticeType = 'error';
                } else {
                    $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$hashed, $studentId]);

                    // Sync to SQLite if active/present
                    if (file_exists(__DIR__ . '/../database/scj.sqlite')) {
                        try {
                            $sqlite = new PDO('sqlite:' . __DIR__ . '/../database/scj.sqlite');
                            $sqlite->prepare("UPDATE users SET password = ? WHERE id = ? OR id_number = ?")
                                   ->execute([$hashed, $studentId, $target['id_number']]);
                        } catch (Exception $e) {}
                    }

                    $noticeMessage = "Password for student <strong>" . htmlspecialchars($target['name']) . "</strong> (" . htmlspecialchars($target['id_number']) . ") has been successfully changed.";
                    $noticeType = 'success';
                }
            }
        }

        // 2. CREATE NEW STUDENT ACCOUNT
        elseif ($action === 'create_student') {
            $idNumber = trim($_POST['id_number'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? 'password123');

            if (empty($idNumber) || empty($name) || empty($email)) {
                $noticeMessage = 'Student ID Number, Full Name, and Email Address are required.';
                $noticeType = 'error';
            } else {
                $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(id_number) = LOWER(?) OR LOWER(email) = LOWER(?)");
                $check->execute([$idNumber, $email]);
                if ($check->fetchColumn() > 0) {
                    $noticeMessage = 'A student account with that ID Number or Email Address already exists.';
                    $noticeType = 'error';
                } else {
                    $hashed = password_hash(!empty($password) ? $password : 'password123', PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, 'student')");
                    $ins->execute([$idNumber, $name, $email, $hashed]);

                    if (file_exists(__DIR__ . '/../database/scj.sqlite')) {
                        try {
                            $sqlite = new PDO('sqlite:' . __DIR__ . '/../database/scj.sqlite');
                            $sqlite->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, 'student')")
                                   ->execute([$idNumber, $name, $email, $hashed]);
                        } catch (Exception $e) {}
                    }

                    $noticeMessage = "New student account for <strong>" . htmlspecialchars($name) . "</strong> (" . htmlspecialchars($idNumber) . ") created successfully.";
                    $noticeType = 'success';
                }
            }
        }

        // 3. EDIT STUDENT INFORMATION
        elseif ($action === 'edit_student') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $idNumber = trim($_POST['id_number'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');

            if ($studentId <= 0 || empty($idNumber) || empty($name) || empty($email)) {
                $noticeMessage = 'Valid student ID, Name, and Email are required.';
                $noticeType = 'error';
            } else {
                // Check duplicate ID/Email on OTHER users
                $dup = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (LOWER(id_number) = LOWER(?) OR LOWER(email) = LOWER(?)) AND id != ?");
                $dup->execute([$idNumber, $email, $studentId]);
                if ($dup->fetchColumn() > 0) {
                    $noticeMessage = 'Another user account already uses that ID Number or Email.';
                    $noticeType = 'error';
                } else {
                    if (!empty($newPassword)) {
                        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET id_number = ?, name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$idNumber, $name, $email, $hashed, $studentId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET id_number = ?, name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$idNumber, $name, $email, $studentId]);
                    }

                    if (file_exists(__DIR__ . '/../database/scj.sqlite')) {
                        try {
                            $sqlite = new PDO('sqlite:' . __DIR__ . '/../database/scj.sqlite');
                            if (!empty($newPassword)) {
                                $sqlite->prepare("UPDATE users SET id_number = ?, name = ?, email = ?, password = ? WHERE id = ?")
                                       ->execute([$idNumber, $name, $email, $hashed, $studentId]);
                            } else {
                                $sqlite->prepare("UPDATE users SET id_number = ?, name = ?, email = ? WHERE id = ?")
                                       ->execute([$idNumber, $name, $email, $studentId]);
                            }
                        } catch (Exception $e) {}
                    }

                    $noticeMessage = "Student record #{$studentId} updated successfully.";
                    $noticeType = 'success';
                }
            }
        }

        // 4. RESET STUDENT PASSWORD TO DEFAULT FORMULA (LAST NAME + ID NUMBER)
        elseif ($action === 'reset_default_password') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT id, id_number, name FROM users WHERE id = ? AND role = 'student' LIMIT 1");
            $stmt->execute([$studentId]);
            $target = $stmt->fetch();
            if ($target) {
                $parts = explode(',', $target['name']);
                $lastName = trim($parts[0]);
                $defaultPass = $lastName . $target['id_number'];
                $hashed = password_hash($defaultPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $studentId]);
                if (file_exists(__DIR__ . '/../database/scj.sqlite')) {
                    try {
                        $sqlite = new PDO('sqlite:' . __DIR__ . '/../database/scj.sqlite');
                        $sqlite->prepare("UPDATE users SET password = ? WHERE id = ? OR id_number = ?")
                               ->execute([$hashed, $studentId, $target['id_number']]);
                    } catch (Exception $e) {}
                }
                $noticeMessage = "Password for <strong>" . htmlspecialchars($target['name']) . "</strong> (" . htmlspecialchars($target['id_number']) . ") has been reset to default formula: <code>" . htmlspecialchars($defaultPass) . "</code>.";
                $noticeType = 'success';
            } else {
                $noticeMessage = 'Student record not found.';
                $noticeType = 'error';
            }
        }
    }
}

// -------------------------------------------------------------
// GET ACTION: DELETE STUDENT
// -------------------------------------------------------------
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        $stmt = $pdo->prepare("SELECT id, name, id_number, role FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$delId]);
        $target = $stmt->fetch();

        if ($target && $target['role'] === 'student') {
            $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $del->execute([$delId]);

            if (file_exists(__DIR__ . '/../database/scj.sqlite')) {
                try {
                    $sqlite = new PDO('sqlite:' . __DIR__ . '/../database/scj.sqlite');
                    $sqlite->prepare("DELETE FROM users WHERE id = ? OR id_number = ?")
                           ->execute([$delId, $target['id_number']]);
                } catch (Exception $e) {}
            }

            $noticeMessage = "Student account '<strong>" . htmlspecialchars($target['name']) . "</strong>' (" . htmlspecialchars($target['id_number']) . ") has been deleted.";
            $noticeType = 'success';
        } else {
            $noticeMessage = 'Invalid student record selected for deletion.';
            $noticeType = 'error';
        }
    }
}

// Fetch all student accounts with course, year_level, and gender
$students = $pdo->query("SELECT id, id_number, name, email, role, course, year_level, gender, created_at FROM users WHERE role = 'student' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalStudents = count($students);

$activePage = 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Accounts &amp; Passwords | SCJ Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #071324; color: #E2E8F0; padding: 24px 0; border-right: 1px solid rgba(56,189,248,0.2); }
        .admin-brand { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .admin-nav { list-style: none; padding: 20px 0; margin: 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: #CBD5E1; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.2s ease; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(30,58,138,0.5); color: var(--color-primary-accent, #E5C158); border-left: 4px solid var(--color-primary-accent, #E5C158); }
        .admin-main { background: var(--color-bg-page); color: var(--color-text-primary); padding: 30px; overflow-y: auto; }

        /* Stats Row */
        .student-stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 26px;
        }
        .stat-card-modern {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md, 12px);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }
        .stat-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-icon-gold { background: rgba(212,175,55,0.15); color: var(--color-gold); border: 1px solid rgba(212,175,55,0.3); }
        .stat-icon-blue { background: rgba(56,189,248,0.15); color: #38BDF8; border: 1px solid rgba(56,189,248,0.3); }
        .stat-icon-emerald { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }

        .stat-data h4 { margin: 0 0 4px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.6px; color: var(--color-text-muted); font-weight: 700; }
        .stat-data .stat-number { font-size: 1.55rem; font-weight: 900; color: var(--color-text-primary); margin: 0; font-family: var(--font-heading); }

        /* Password Modal Styling */
        .scj-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(4, 8, 16, 0.75);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .scj-modal-overlay.active {
            display: flex !important;
        }
        .scj-modal-card {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            max-width: 500px;
            width: 100%;
            padding: 28px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.5);
            position: relative;
            animation: scjModalZoomIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes scjModalZoomIn {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 14px;
        }
        .modal-header-flex h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--color-text-primary);
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--color-text-muted);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .modal-close-btn:hover {
            color: #EF4444;
            background: rgba(239,68,68,0.1);
        }
        .password-input-group {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            padding: 6px;
            font-size: 0.95rem;
        }
        .password-toggle-btn:hover {
            color: var(--color-gold);
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
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header Banner -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <div>
                <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 6px 0; color:var(--color-text-primary);">
                    <i class="fa-solid fa-user-graduate" style="color:var(--color-gold);"></i> Student Account Management
                </h2>
                <p style="margin:0; font-size:0.88rem; color:var(--color-text-muted);">
                    Manage student registrations, reset security credentials, and modify student passwords.
                </p>
            </div>
            <div>
                <button type="button" onclick="openAddStudentModal()" class="btn-primary" style="padding:10px 20px; font-size:0.88rem; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-user-plus"></i> Register New Student
                </button>
            </div>
        </div>

        <!-- Alert Notification -->
        <?php if (!empty($noticeMessage)): ?>
            <div class="badge badge-<?= $noticeType === 'error' ? 'danger' : 'success' ?>" style="display:block; padding:14px 18px; margin-bottom:22px; font-size:0.92rem; border-radius:8px; line-height:1.5;">
                <i class="fa-solid <?= $noticeType === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>" style="margin-right:8px;"></i>
                <?= $noticeMessage ?>
            </div>
        <?php endif; ?>

        <!-- Stat Counter Cards -->
        <div class="student-stats-row">
            <div class="stat-card-modern">
                <div class="stat-icon-wrap stat-icon-blue">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div class="stat-data">
                    <h4>Total Registered Students</h4>
                    <div class="stat-number"><?= number_format($totalStudents) ?></div>
                </div>
            </div>

            <div class="stat-card-modern">
                <div class="stat-icon-wrap stat-icon-gold">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div class="stat-data">
                    <h4>Student Password Formula</h4>
                    <div class="stat-number" style="font-size:1.05rem; font-family:monospace; color:var(--color-gold);">Last Name + ID Number</div>
                </div>
            </div>

            <div class="stat-card-modern">
                <div class="stat-icon-wrap stat-icon-emerald">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="stat-data">
                    <h4>Protected Access</h4>
                    <div class="stat-number" style="font-size:1.05rem; color:#34D399;">Laboratories &amp; Research</div>
                </div>
            </div>
        </div>

        <!-- Student Directory Table Card -->
        <div class="table-card">
            <div class="table-toolbar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; padding:18px 22px;">
                <h3 style="font-size:1.1rem; font-weight:800; margin:0; color:var(--color-text-primary); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-list-check" style="color:var(--color-gold);"></i> 
                    Enrolled Criminology Students (<?= $totalStudents ?>)
                </h3>
                <!-- Real-time Live Search Input -->
                <div style="position:relative; width:320px; max-width:100%;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--color-text-muted); font-size:0.85rem;"></i>
                    <input type="text" id="studentSearchInput" placeholder="Filter by ID, Name, Course, Year..." onkeyup="filterStudentTable()" class="form-control" style="padding-left:34px; font-size:0.85rem;">
                </div>
            </div>

            <div class="custom-table-container">
                <table class="custom-table" id="studentTable">
                    <thead>
                        <tr>
                            <th style="width:110px;">Student ID</th>
                            <th>Student Name</th>
                            <th style="width:130px;">Course &amp; Year</th>
                            <th style="width:90px;">Gender</th>
                            <th>Email Address</th>
                            <th style="width:150px;">Default Password</th>
                            <th style="width:200px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:36px; color:var(--color-text-muted);">
                                    <i class="fa-solid fa-user-slash" style="font-size:2rem; margin-bottom:12px; display:block; opacity:0.5;"></i>
                                    No student accounts currently registered in database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): 
                                $nameParts = explode(',', $s['name']);
                                $lastName = trim($nameParts[0]);
                                $defaultPass = $lastName . $s['id_number'];
                                $yearLevelText = !empty($s['year_level']) ? "Year " . $s['year_level'] : "N/A";
                            ?>
                                <tr class="student-row">
                                    <td>
                                        <span class="badge badge-info" style="font-weight:800; font-size:0.8rem; letter-spacing:0.5px;">
                                            <i class="fa-solid fa-id-card"></i> <?= e($s['id_number']) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight:700; color:var(--color-text-primary);">
                                        <?= e($s['name']) ?>
                                    </td>
                                    <td>
                                        <span style="font-size:0.82rem; font-weight:700; color:#38BDF8; display:block;"><?= e($s['course'] ?? 'BS CRIM') ?></span>
                                        <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= e($yearLevelText) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge" style="font-size:0.72rem; font-weight:700; background:<?= ($s['gender'] ?? '') === 'FEMALE' ? 'rgba(236,72,153,0.15); color:#F472B6;' : 'rgba(59,130,246,0.15); color:#60A5FA;' ?>">
                                            <?= e($s['gender'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--color-text-secondary); font-size:0.84rem;">
                                        <?= e($s['email']) ?>
                                    </td>
                                    <td>
                                        <code style="background:rgba(212,175,55,0.12); color:var(--color-gold); padding:3px 8px; border-radius:4px; font-size:0.78rem; font-weight:700;">
                                            <?= e($defaultPass) ?>
                                        </code>
                                    </td>
                                    <td style="text-align:center;">
                                        <div style="display:inline-flex; align-items:center; gap:6px;">
                                            <!-- Change Password Action Button -->
                                            <button type="button" 
                                                    class="btn-primary" 
                                                    style="background:rgba(212,175,55,0.18); color:var(--color-gold); border:1px solid rgba(212,175,55,0.45); padding:6px 10px; font-size:0.78rem; font-weight:800; border-radius:6px; cursor:pointer;"
                                                    onclick="openChangePasswordModal(<?= $s['id'] ?>, '<?= e(addslashes($s['name'])) ?>', '<?= e(addslashes($s['id_number'])) ?>')"
                                                    title="Change Password">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            <!-- Reset to Default Formula Password -->
                                            <form action="<?= base_url('admin/students.php') ?>" method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Reset password for <?= e(addslashes($s['name'])) ?> to default formula (<?= e(addslashes($defaultPass)) ?>)?');">
                                                <?= CsrfMiddleware::field() ?>
                                                <input type="hidden" name="action" value="reset_default_password">
                                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                                <button type="submit" 
                                                        class="btn-primary" 
                                                        style="background:rgba(16,185,129,0.18); color:#34D399; border:1px solid rgba(16,185,129,0.4); padding:6px 10px; font-size:0.78rem; font-weight:800; border-radius:6px; cursor:pointer;"
                                                        title="Reset to Default Formula (<?= e($defaultPass) ?>)">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>

                                            <!-- Edit Student Action Button -->
                                            <button type="button" 
                                                    class="btn-primary" 
                                                    style="background:rgba(56,189,248,0.15); color:#38BDF8; border:1px solid rgba(56,189,248,0.3); padding:6px 10px; font-size:0.78rem; border-radius:6px; cursor:pointer;"
                                                    onclick="openEditStudentModal(<?= $s['id'] ?>, '<?= e(addslashes($s['id_number'])) ?>', '<?= e(addslashes($s['name'])) ?>', '<?= e(addslashes($s['email'])) ?>')"
                                                    title="Edit Student Info">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Delete Student Action Button -->
                                            <a href="<?= base_url('admin/students.php?delete=' . $s['id']) ?>" 
                                               class="btn-primary" 
                                               style="background:#EF4444; border:1px solid #DC2626; padding:6px 10px; font-size:0.78rem; border-radius:6px;"
                                               data-confirm-title="Remove Student Account"
                                               data-confirm="Are you sure you want to permanently delete student account '<?= e(addslashes($s['name'])) ?>' (<?= e(addslashes($s['id_number'])) ?>)?"
                                               title="Delete Student Account">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
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

<!-- ============================================================== -->
<!-- MODAL: CHANGE STUDENT PASSWORD                                 -->
<!-- ============================================================== -->
<div id="changePasswordModal" class="scj-modal-overlay">
    <div class="scj-modal-card">
        <div class="modal-header-flex">
            <h3><i class="fa-solid fa-key" style="color:var(--color-gold);"></i> Change Student Password</h3>
            <button type="button" class="modal-close-btn" onclick="closeChangePasswordModal()">&times;</button>
        </div>

        <form action="<?= base_url('admin/students.php') ?>" method="POST">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" id="modalStudentId" name="student_id" value="">

            <div style="background:var(--color-bg-subtle, rgba(255,255,255,0.03)); border:1px solid var(--color-border); border-radius:8px; padding:12px 16px; margin-bottom:18px;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-text-muted); font-weight:700; margin-bottom:4px;">Target Student Account</div>
                <div id="modalStudentName" style="font-weight:800; font-size:1.02rem; color:var(--color-text-primary);">Student Name</div>
                <div id="modalStudentIdNumber" style="font-size:0.82rem; color:var(--color-gold); font-family:monospace; margin-top:2px;">ID Number</div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    New Password *
                </label>
                <div class="password-input-group">
                    <input type="password" id="modalNewPassword" name="new_password" class="form-control" placeholder="Enter new password (min. 4 chars)" required minlength="4" style="padding-right:42px;">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modalNewPassword', 'toggleEyeIcon1')">
                        <i id="toggleEyeIcon1" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Confirm New Password *
                </label>
                <div class="password-input-group">
                    <input type="password" id="modalConfirmPassword" name="confirm_password" class="form-control" placeholder="Re-enter new password" required minlength="4" style="padding-right:42px;">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modalConfirmPassword', 'toggleEyeIcon2')">
                        <i id="toggleEyeIcon2" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Quick Password Generators -->
            <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
                <button type="button" onclick="setQuickPassword('password123')" class="btn-primary" style="background:var(--color-bg-subtle); color:var(--color-text-secondary); border:1px solid var(--color-border); font-size:0.75rem; padding:4px 10px; border-radius:4px;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Use 'password123'
                </button>
                <button type="button" onclick="generateRandomPassword()" class="btn-primary" style="background:var(--color-bg-subtle); color:var(--color-text-secondary); border:1px solid var(--color-border); font-size:0.75rem; padding:4px 10px; border-radius:4px;">
                    <i class="fa-solid fa-shuffle"></i> Generate Secure Password
                </button>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--color-border); padding-top:16px;">
                <button type="button" onclick="closeChangePasswordModal()" class="btn-primary" style="background:var(--color-bg-subtle); color:var(--color-text-secondary); border:1px solid var(--color-border); font-weight:700;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="font-weight:800;">
                    <i class="fa-solid fa-check"></i> Save New Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: REGISTER NEW STUDENT                                   -->
<!-- ============================================================== -->
<div id="addStudentModal" class="scj-modal-overlay">
    <div class="scj-modal-card">
        <div class="modal-header-flex">
            <h3><i class="fa-solid fa-user-plus" style="color:var(--color-primary-light);"></i> Register New Student</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddStudentModal()">&times;</button>
        </div>

        <form action="<?= base_url('admin/students.php') ?>" method="POST">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="create_student">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Student ID Number *
                </label>
                <input type="text" name="id_number" class="form-control" placeholder="e.g. 2026-10088" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Full Name *
                </label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Juan P. Dela Cruz" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Email Address *
                </label>
                <input type="email" name="email" class="form-control" placeholder="e.g. jdelacruz@student.dwcc-scje.edu.ph" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Initial Password (Default: password123)
                </label>
                <input type="text" name="password" class="form-control" value="password123">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--color-border); padding-top:16px;">
                <button type="button" onclick="closeAddStudentModal()" class="btn-primary" style="background:var(--color-bg-subtle); color:var(--color-text-secondary); border:1px solid var(--color-border); font-weight:700;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="font-weight:800;">
                    <i class="fa-solid fa-user-check"></i> Create Student Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: EDIT STUDENT INFORMATION                                -->
<!-- ============================================================== -->
<div id="editStudentModal" class="scj-modal-overlay">
    <div class="scj-modal-card">
        <div class="modal-header-flex">
            <h3><i class="fa-solid fa-pen-to-square" style="color:#38BDF8;"></i> Edit Student Details</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditStudentModal()">&times;</button>
        </div>

        <form action="<?= base_url('admin/students.php') ?>" method="POST">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="edit_student">
            <input type="hidden" id="editStudentId" name="student_id" value="">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Student ID Number *
                </label>
                <input type="text" id="editIdNumber" name="id_number" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Full Name *
                </label>
                <input type="text" id="editName" name="name" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Email Address *
                </label>
                <input type="email" id="editEmail" name="email" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-weight:700; font-size:0.86rem; color:var(--color-text-primary); margin-bottom:6px; display:block;">
                    Reset Password (Leave blank to keep unchanged)
                </label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password or leave blank">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--color-border); padding-top:16px;">
                <button type="button" onclick="closeEditStudentModal()" class="btn-primary" style="background:var(--color-bg-subtle); color:var(--color-text-secondary); border:1px solid var(--color-border); font-weight:700;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="font-weight:800;">
                    <i class="fa-solid fa-floppy-disk"></i> Update Student
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/admin-confirm.js') ?>"></script>
<script>
    // 1. Password Modal Controls
    function openChangePasswordModal(studentId, studentName, idNumber) {
        document.getElementById('modalStudentId').value = studentId;
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalStudentIdNumber').textContent = idNumber;
        document.getElementById('modalNewPassword').value = '';
        document.getElementById('modalConfirmPassword').value = '';
        document.getElementById('changePasswordModal').classList.add('active');
        document.getElementById('modalNewPassword').focus();
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.remove('active');
    }

    // 2. Add Student Modal Controls
    function openAddStudentModal() {
        document.getElementById('addStudentModal').classList.add('active');
    }

    function closeAddStudentModal() {
        document.getElementById('addStudentModal').classList.remove('active');
    }

    // 3. Edit Student Modal Controls
    function openEditStudentModal(id, idNumber, name, email) {
        document.getElementById('editStudentId').value = id;
        document.getElementById('editIdNumber').value = idNumber;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editStudentModal').classList.add('active');
    }

    function closeEditStudentModal() {
        document.getElementById('editStudentModal').classList.remove('active');
    }

    // 4. Toggle Password Visibility
    function togglePasswordVisibility(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // 5. Quick Set Password
    function setQuickPassword(val) {
        document.getElementById('modalNewPassword').value = val;
        document.getElementById('modalConfirmPassword').value = val;
    }

    // 6. Generate Random Password
    function generateRandomPassword() {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
        var generated = '';
        for (var i = 0; i < 10; i++) {
            generated += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('modalNewPassword').value = generated;
        document.getElementById('modalConfirmPassword').value = generated;
        document.getElementById('modalNewPassword').type = 'text';
        document.getElementById('modalConfirmPassword').type = 'text';
        document.getElementById('toggleEyeIcon1').className = 'fa-solid fa-eye-slash';
        document.getElementById('toggleEyeIcon2').className = 'fa-solid fa-eye-slash';
    }

    // 7. Live Table Filter
    function filterStudentTable() {
        var query = document.getElementById('studentSearchInput').value.toLowerCase();
        var rows = document.querySelectorAll('.student-row');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
        });
    }

    // Close on Escape or click outside
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChangePasswordModal();
            closeAddStudentModal();
            closeEditStudentModal();
        }
    });

    [document.getElementById('changePasswordModal'), document.getElementById('addStudentModal'), document.getElementById('editStudentModal')].forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>
</body>
</html>

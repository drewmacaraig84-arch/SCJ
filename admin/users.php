<?php
/**
 * Account & Dynamic Role Management Center
 * School of Criminal Justice Education (SCJE) Information System
 * Super Administrator Exclusive Access
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['super_admin']);

$pdo = get_db();
$currentUser = AuthMiddleware::user();
$noticeMessage = '';
$noticeType = 'success';

// Active Tab: 'accounts' or 'roles'
$activeTab = $_GET['tab'] ?? 'accounts';

// -------------------------------------------------------------
// POST ACTIONS: CREATE / EDIT / DELETE ACCOUNTS & ROLES
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $noticeMessage = 'Security token validation failed. Please try again.';
        $noticeType = 'error';
    } else {
        $act = $_POST['action'];

        // --- ACCOUNT MANAGEMENT ---
        if ($act === 'create_user') {
            $idNumber = trim($_POST['id_number'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'student');

            if (empty($idNumber) || empty($name) || empty($email) || empty($password)) {
                $noticeMessage = 'All account fields (ID Number, Name, Email, Password) are required.';
                $noticeType = 'error';
            } else {
                // Check uniqueness
                $dupCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(id_number) = LOWER(?) OR LOWER(email) = LOWER(?)");
                $dupCheck->execute([$idNumber, $email]);
                if ($dupCheck->fetchColumn() > 0) {
                    $noticeMessage = 'An account with that ID Number or Email Address already exists.';
                    $noticeType = 'error';
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$idNumber, $name, $email, $hashed, $role]);
                    $noticeMessage = "Account for '{$name}' ({$idNumber}) successfully created with role [{$role}].";
                }
            }
        } elseif ($act === 'edit_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $idNumber = trim($_POST['id_number'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? 'student');
            $newPassword = (string)($_POST['new_password'] ?? '');

            if ($userId <= 0 || empty($idNumber) || empty($name) || empty($email)) {
                $noticeMessage = 'Invalid user data provided.';
                $noticeType = 'error';
            } else {
                // Check if user is demoting the last super_admin
                if ($role !== 'super_admin') {
                    $superCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
                    $targetIsSuper = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE id = {$userId} AND role = 'super_admin'")->fetchColumn();
                    if ($targetIsSuper && $superCount <= 1) {
                        $noticeMessage = 'Cannot remove Super Admin role from the last remaining Super Administrator account.';
                        $noticeType = 'error';
                    }
                }

                if ($noticeType !== 'error') {
                    if (!empty($newPassword)) {
                        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET id_number = ?, name = ?, email = ?, role = ?, password = ? WHERE id = ?");
                        $stmt->execute([$idNumber, $name, $email, $role, $hashed, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET id_number = ?, name = ?, email = ?, role = ? WHERE id = ?");
                        $stmt->execute([$idNumber, $name, $email, $role, $userId]);
                    }
                    $noticeMessage = "User #{$userId} ('{$name}') updated successfully.";
                }
            }
        } elseif ($act === 'delete_user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId === (int)($currentUser['id'] ?? 0)) {
                $noticeMessage = 'Action prohibited: You cannot delete your own currently active Super Admin account.';
                $noticeType = 'error';
            } else {
                // Check if deleting the last super admin
                $targetIsSuper = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE id = {$userId} AND role = 'super_admin'")->fetchColumn();
                $superCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
                if ($targetIsSuper && $superCount <= 1) {
                    $noticeMessage = 'Safety guard: You cannot delete the sole remaining Super Administrator account.';
                    $noticeType = 'error';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $noticeMessage = "Account #{$userId} permanently removed.";
                }
            }
        }

        // --- ROLE MANAGEMENT ---
        elseif ($act === 'create_role') {
            $activeTab = 'roles';
            $roleKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($_POST['role_key'] ?? '')));
            $roleName = trim($_POST['role_name'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            if (empty($roleKey) || empty($roleName)) {
                $noticeMessage = 'Role Key (slug) and Display Name are required.';
                $noticeType = 'error';
            } else {
                $check = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE LOWER(role_key) = LOWER(?)");
                $check->execute([$roleKey]);
                if ($check->fetchColumn() > 0) {
                    $noticeMessage = "Role identifier '{$roleKey}' is already registered.";
                    $noticeType = 'error';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO roles (role_key, role_name, description, is_system) VALUES (?, ?, ?, 0)");
                    $stmt->execute([$roleKey, $roleName, $desc]);
                    $noticeMessage = "New role '{$roleName}' ({$roleKey}) successfully defined and registered.";
                }
            }
        } elseif ($act === 'delete_role') {
            $activeTab = 'roles';
            $roleId = (int)($_POST['role_id'] ?? 0);
            $roleData = $pdo->query("SELECT * FROM roles WHERE id = {$roleId}")->fetch();

            if (!$roleData) {
                $noticeMessage = 'Selected role does not exist.';
                $noticeType = 'error';
            } elseif ($roleData['is_system']) {
                $noticeMessage = "System role '{$roleData['role_name']}' is core to application security and cannot be deleted.";
                $noticeType = 'error';
            } else {
                // Check if any users have this role assigned
                $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE LOWER(role) = LOWER('{$roleData['role_key']}')")->fetchColumn();
                if ($userCount > 0) {
                    $noticeMessage = "Cannot delete role '{$roleData['role_name']}': {$userCount} user account(s) are currently assigned to this role.";
                    $noticeType = 'error';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
                    $stmt->execute([$roleId]);
                    $noticeMessage = "Role '{$roleData['role_name']}' removed from registry.";
                }
            }
        }
    }
}

// -------------------------------------------------------------
// FETCH ACCOUNTS & ROLES DATA
// -------------------------------------------------------------
$allRoles = $pdo->query("SELECT * FROM roles ORDER BY is_system DESC, id ASC")->fetchAll();
$roleMap = [];
foreach ($allRoles as $r) {
    $roleMap[$r['role_key']] = $r['role_name'];
}

// Count users per role for the registry
$roleUserCounts = [];
$countsQuery = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role")->fetchAll();
foreach ($countsQuery as $cq) {
    $roleUserCounts[strtolower($cq['role'])] = (int)$cq['cnt'];
}

// User Accounts Filter
$filterRole = trim($_GET['role'] ?? '');
$searchUser = trim($_GET['q'] ?? '');

$userWhere = [];
$userParams = [];
if (!empty($filterRole)) {
    $userWhere[] = "LOWER(role) = LOWER(?)";
    $userParams[] = $filterRole;
}
if (!empty($searchUser)) {
    $userWhere[] = "(id_number LIKE ? OR name LIKE ? OR email LIKE ?)";
    $wild = "%{$searchUser}%";
    $userParams[] = $wild;
    $userParams[] = $wild;
    $userParams[] = $wild;
}
$userWhereSql = !empty($userWhere) ? "WHERE " . implode(" AND ", $userWhere) : "";

$userStmt = $pdo->prepare("SELECT * FROM users {$userWhereSql} ORDER BY id ASC");
$userStmt->execute($userParams);
$usersList = $userStmt->fetchAll();

// Metrics
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$superAdminsCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
$adminsCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$facultyCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'faculty'")->fetchColumn();
$studentsCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

$activePage = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account &amp; Role Management | SCJE Super Admin</title>
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

        /* Stats Cards */
        .user-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .user-stat-card {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }
        .user-stat-val {
            font-size: 1.7rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1;
        }
        .user-stat-lbl {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--color-text-muted);
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* Tabs Header */
        .tab-switcher {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 12px;
        }
        .tab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--color-text-muted);
            font-weight: 800;
            font-size: 0.92rem;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .tab-btn:hover {
            color: var(--color-text-primary);
            background: rgba(255,255,255,0.04);
        }
        .tab-btn.active {
            background: var(--color-primary-accent);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.4);
        }

        /* Role Badges */
        .role-badge {
            font-size: 0.72rem;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-super_admin {
            background: linear-gradient(135deg, #7C3AED, #F59E0B);
            color: #FFFFFF;
            box-shadow: 0 0 10px rgba(245,158,11,0.3);
        }
        .role-admin {
            background: rgba(56, 189, 248, 0.2);
            color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.4);
        }
        .role-faculty {
            background: rgba(16, 185, 129, 0.2);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        .role-student {
            background: rgba(148, 163, 184, 0.2);
            color: #94A3B8;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }
        .role-custom {
            background: rgba(168, 85, 247, 0.2);
            color: #C084FC;
            border: 1px solid rgba(168, 85, 247, 0.4);
        }

        /* Modal Overlays */
        .modal-popup {
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
        .modal-popup.active { display: flex; }
        .modal-content-card {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }
        .modal-header-bar {
            padding: 18px 24px;
            background: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
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
                    Account &amp; Dynamic Role Management
                </h1>
            </div>
            <!-- Action Buttons -->
            <div style="display:flex; gap:10px;">
                <?php if ($activeTab === 'accounts'): ?>
                    <button type="button" class="btn-primary" onclick="document.getElementById('addUserModal').classList.add('active')">
                        <i class="fa-solid fa-user-plus"></i> Add Account
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-primary" onclick="document.getElementById('addRoleModal').classList.add('active')" style="background:#7C3AED; border-color:#8B5CF6;">
                        <i class="fa-solid fa-shield-plus"></i> Add New Role
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($noticeMessage)): ?>
            <div style="padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:0.88rem; font-weight:700; background:<?= $noticeType === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; border:1px solid <?= $noticeType === 'success' ? '#10B981' : '#EF4444' ?>; color:<?= $noticeType === 'success' ? '#34D399' : '#F87171' ?>;">
                <i class="fa-solid <?= $noticeType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i> <?= e($noticeMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Stat Overview Cards -->
        <div class="user-stat-grid">
            <div class="user-stat-card">
                <div>
                    <div class="user-stat-val" style="color:#FFFFFF;"><?= $totalUsers ?></div>
                    <div class="user-stat-lbl">Total Users</div>
                </div>
                <i class="fa-solid fa-users" style="color:#64748B; font-size:1.4rem;"></i>
            </div>
            <div class="user-stat-card">
                <div>
                    <div class="user-stat-val" style="color:#F59E0B;"><?= $superAdminsCount ?></div>
                    <div class="user-stat-lbl">Super Admins</div>
                </div>
                <i class="fa-solid fa-crown" style="color:#F59E0B; font-size:1.4rem;"></i>
            </div>
            <div class="user-stat-card">
                <div>
                    <div class="user-stat-val" style="color:#38BDF8;"><?= $adminsCount ?></div>
                    <div class="user-stat-lbl">Admins</div>
                </div>
                <i class="fa-solid fa-user-gear" style="color:#38BDF8; font-size:1.4rem;"></i>
            </div>
            <div class="user-stat-card">
                <div>
                    <div class="user-stat-val" style="color:#10B981;"><?= $facultyCount ?></div>
                    <div class="user-stat-lbl">Faculty</div>
                </div>
                <i class="fa-solid fa-chalkboard-user" style="color:#10B981; font-size:1.4rem;"></i>
            </div>
            <div class="user-stat-card">
                <div>
                    <div class="user-stat-val" style="color:#94A3B8;"><?= $studentsCount ?></div>
                    <div class="user-stat-lbl">Students</div>
                </div>
                <i class="fa-solid fa-graduation-cap" style="color:#94A3B8; font-size:1.4rem;"></i>
            </div>
        </div>

        <!-- Navigation Tabs: Accounts vs Roles -->
        <div class="tab-switcher">
            <a href="?tab=accounts" class="tab-btn <?= $activeTab === 'accounts' ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear"></i> User Accounts
            </a>
            <a href="?tab=roles" class="tab-btn <?= $activeTab === 'roles' ? 'active' : '' ?>">
                <i class="fa-solid fa-shield-halved"></i> Role Registry &amp; Authority
            </a>
        </div>

        <!-- ========================================================= -->
        <!-- TAB 1: USER ACCOUNTS                                      -->
        <!-- ========================================================= -->
        <?php if ($activeTab === 'accounts'): ?>
            <!-- Filter / Search Bar -->
            <div style="background:var(--color-bg-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:20px;">
                <form method="GET" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin:0;">
                    <input type="hidden" name="tab" value="accounts">
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <select name="role" class="form-control" style="width:160px; padding:7px 12px; font-size:0.82rem;" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <?php foreach ($allRoles as $r): ?>
                                <option value="<?= e($r['role_key']) ?>" <?= $filterRole === $r['role_key'] ? 'selected' : '' ?>>
                                    <?= e($r['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div style="position:relative;">
                            <input type="text" name="q" value="<?= e($searchUser) ?>" placeholder="Search name, ID, or email..." class="form-control" style="width:250px; padding:7px 12px 7px 32px; font-size:0.82rem;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:11px; top:11px; color:#64748B; font-size:0.75rem;"></i>
                        </div>

                        <button type="submit" class="btn-primary" style="padding:7px 14px; font-size:0.82rem;">Search</button>
                        <?php if ($filterRole || $searchUser): ?>
                            <a href="users.php?tab=accounts" style="font-size:0.82rem; color:var(--color-text-muted); font-weight:700;">Reset</a>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:0.8rem; color:#64748B; font-weight:700;">
                        Showing <?= count($usersList) ?> account(s)
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="table-card">
                <div class="custom-table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>ID Number / User</th>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Assigned Role</th>
                                <th>Registered</th>
                                <th style="text-align:right; width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usersList)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:35px 20px; color:#64748B;">
                                        No user accounts match the current filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usersList as $u): 
                                    $uRole = strtolower($u['role'] ?? 'student');
                                    $badgeStyle = match ($uRole) {
                                        'super_admin' => 'role-super_admin',
                                        'admin' => 'role-admin',
                                        'faculty' => 'role-faculty',
                                        'student' => 'role-student',
                                        default => 'role-custom'
                                    };
                                ?>
                                    <tr>
                                        <td style="color:#64748B; font-size:0.8rem;"><?= $u['id'] ?></td>
                                        <td>
                                            <span style="font-family:'JetBrains Mono', monospace; font-weight:800; color:var(--color-primary-light, #38BDF8);">
                                                <?= e($u['id_number']) ?>
                                            </span>
                                            <?php if ((int)$u['id'] === (int)$currentUser['id']): ?>
                                                <span style="font-size:0.65rem; background:rgba(212,175,55,0.2); color:#F59E0B; padding:1px 5px; border-radius:4px; margin-left:4px; font-weight:800;">YOU</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight:700; color:var(--color-text-primary);">
                                            <?= e($u['name']) ?>
                                        </td>
                                        <td style="color:#94A3B8; font-size:0.85rem;">
                                            <?= e($u['email']) ?>
                                        </td>
                                        <td>
                                            <span class="role-badge <?= $badgeStyle ?>">
                                                <?php if ($uRole === 'super_admin'): ?>
                                                    <i class="fa-solid fa-crown" style="font-size:0.6rem;"></i>
                                                <?php endif; ?>
                                                <?= e($roleMap[$uRole] ?? ucfirst($uRole)) ?>
                                            </span>
                                        </td>
                                        <td style="color:#64748B; font-size:0.78rem;">
                                            <?= e(date('M d, Y', strtotime($u['created_at'] ?? 'now'))) ?>
                                        </td>
                                        <td style="text-align:right; white-space:nowrap;">
                                            <!-- Edit User Button -->
                                            <button type="button" class="btn-act" onclick="openEditModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Account">
                                                <i class="fa-solid fa-pen-to-square" style="color:#38BDF8;"></i>
                                            </button>

                                            <!-- Delete User Button -->
                                            <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                                <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Permanently delete account for <?= e($u['name']) ?>?');">
                                                    <?= CsrfMiddleware::field() ?>
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn-act" title="Delete Account">
                                                        <i class="fa-solid fa-trash" style="color:#EF4444;"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================================= -->
        <!-- TAB 2: ROLE REGISTRY                                      -->
        <!-- ========================================================= -->
        <?php else: ?>
            <div class="table-card">
                <div class="table-toolbar">
                    <h3 style="font-size:1.05rem; font-weight:800; color:#0F254B; margin:0;">
                        <i class="fa-solid fa-shield-halved" style="color:#7C3AED; margin-right:8px;"></i> Registered Roles &amp; Authority Scope
                    </h3>
                    <button type="button" class="btn-primary" onclick="document.getElementById('addRoleModal').classList.add('active')" style="background:#7C3AED; border-color:#8B5CF6; font-size:0.8rem; padding:7px 14px;">
                        <i class="fa-solid fa-plus"></i> Define Role
                    </button>
                </div>
                <div class="custom-table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Role Identifier (Key)</th>
                                <th>Display Title</th>
                                <th>Authority Scope &amp; Purpose</th>
                                <th>Assigned Accounts</th>
                                <th>Type</th>
                                <th style="text-align:right; width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allRoles as $r): 
                                $rKey = strtolower($r['role_key']);
                                $cnt = $roleUserCounts[$rKey] ?? 0;
                            ?>
                                <tr>
                                    <td>
                                        <span style="font-family:'JetBrains Mono', monospace; font-weight:800; color:var(--color-primary-light, #38BDF8);">
                                            <?= e($r['role_key']) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight:800; color:var(--color-text-primary);">
                                        <?= e($r['role_name']) ?>
                                    </td>
                                    <td style="color:#94A3B8; font-size:0.85rem;">
                                        <?= e($r['description'] ?? 'No description provided') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-size:0.75rem; font-weight:800;">
                                            <?= $cnt ?> Account(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['is_system']): ?>
                                            <span class="badge" style="background:rgba(56, 189, 248, 0.15); color:#38BDF8; font-size:0.7rem; font-weight:800;">
                                                <i class="fa-solid fa-lock"></i> System Protected
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background:rgba(168, 85, 247, 0.15); color:#C084FC; font-size:0.7rem; font-weight:800;">
                                                <i class="fa-solid fa-sparkles"></i> Custom Dynamic
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if (!$r['is_system']): ?>
                                            <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete custom role <?= e($r['role_name']) ?>?');">
                                                <?= CsrfMiddleware::field() ?>
                                                <input type="hidden" name="action" value="delete_role">
                                                <input type="hidden" name="role_id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="btn-act" title="Delete Custom Role" <?= $cnt > 0 ? 'disabled style="opacity:0.3;"' : '' ?>>
                                                    <i class="fa-solid fa-trash" style="color:#EF4444;"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:#64748B; font-size:0.75rem;"><i class="fa-solid fa-shield"></i> Protected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- ========================================================= -->
<!-- MODAL: ADD NEW ACCOUNT                                    -->
<!-- ========================================================= -->
<div id="addUserModal" class="modal-popup">
    <div class="modal-content-card">
        <div class="modal-header-bar">
            <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:#FFFFFF;">
                <i class="fa-solid fa-user-plus" style="color:#10B981; margin-right:8px;"></i> Create New Account
            </h3>
            <button type="button" class="btn-act" onclick="document.getElementById('addUserModal').classList.remove('active')" style="font-size:1.3rem; color:#94A3B8;">&times;</button>
        </div>
        <form method="POST" style="padding:24px; margin:0;">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="create_user">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">ID Number / Username</label>
                <input type="text" name="id_number" class="form-control" placeholder="e.g., ADM-2025-01 or 2024-10045" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Dr. Juan Dela Cruz, RCrim." required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="e.g., jdelacruz@dwcc-scje.edu.ph" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Initial Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">System Role</label>
                <select name="role" class="form-control" required>
                    <?php foreach ($allRoles as $r): ?>
                        <option value="<?= e($r['role_key']) ?>" <?= $r['role_key'] === 'student' ? 'selected' : '' ?>>
                            <?= e($r['role_name']) ?> (<?= e($r['role_key']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('addUserModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: EDIT ACCOUNT                                       -->
<!-- ========================================================= -->
<div id="editUserModal" class="modal-popup">
    <div class="modal-content-card">
        <div class="modal-header-bar">
            <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:#FFFFFF;">
                <i class="fa-solid fa-user-pen" style="color:#38BDF8; margin-right:8px;"></i> Edit Account
            </h3>
            <button type="button" class="btn-act" onclick="document.getElementById('editUserModal').classList.remove('active')" style="font-size:1.3rem; color:#94A3B8;">&times;</button>
        </div>
        <form method="POST" style="padding:24px; margin:0;">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="editUserId">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">ID Number</label>
                <input type="text" name="id_number" id="editIdNumber" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Full Name</label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Email Address</label>
                <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">System Role</label>
                <select name="role" id="editRole" class="form-control" required>
                    <?php foreach ($allRoles as $r): ?>
                        <option value="<?= e($r['role_key']) ?>">
                            <?= e($r['role_name']) ?> (<?= e($r['role_key']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Reset Password <span style="font-weight:400; color:#94A3B8;">(Leave blank to keep unchanged)</span></label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password if resetting">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editUserModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: ADD NEW ROLE                                       -->
<!-- ========================================================= -->
<div id="addRoleModal" class="modal-popup">
    <div class="modal-content-card">
        <div class="modal-header-bar">
            <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:#FFFFFF;">
                <i class="fa-solid fa-shield-plus" style="color:#7C3AED; margin-right:8px;"></i> Define New Role
            </h3>
            <button type="button" class="btn-act" onclick="document.getElementById('addRoleModal').classList.remove('active')" style="font-size:1.3rem; color:#94A3B8;">&times;</button>
        </div>
        <form method="POST" style="padding:24px; margin:0;">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="create_role">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Role Identifier (Key)</label>
                <input type="text" name="role_key" class="form-control" placeholder="e.g., lab_custodian, intern_lead" pattern="[a-zA-Z0-9_]+" required>
                <span style="font-size:0.72rem; color:#94A3B8;">Letters, numbers, and underscores only.</span>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Display Name</label>
                <input type="text" name="role_name" class="form-control" placeholder="e.g., Laboratory Custodian" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#E2E8F0; margin-bottom:6px;">Scope of Authority &amp; Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Describe the responsibilities and scope of this role..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('addRoleModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#7C3AED; border-color:#8B5CF6;">
                    <i class="fa-solid fa-plus"></i> Register Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editIdNumber').value = user.id_number || '';
    document.getElementById('editName').value = user.name || '';
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editRole').value = user.role || 'student';

    document.getElementById('editUserModal').classList.add('active');
}
</script>

</body>
</html>

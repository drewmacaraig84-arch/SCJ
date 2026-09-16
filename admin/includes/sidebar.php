<?php
/**
 * Unified Admin & Super Admin Sidebar Navigation
 * School of Criminal Justice Education (SCJE) Information System
 * 
 * @param string $activePage The current active page key (e.g., 'dashboard', 'health', 'crash_logs', 'users')
 */

$currentUser = AuthMiddleware::user();
$isSuperAdmin = RoleMiddleware::isSuperAdmin();
$activeDriver = DB::getDriver();

// Fetch unresolved crash count for badge
$unresolvedCrashCount = 0;
if ($isSuperAdmin) {
    try {
        $crashStats = CrashLogger::getStats();
        $unresolvedCrashCount = $crashStats['unresolved'] ?? 0;
    } catch (Throwable $e) {
        $unresolvedCrashCount = 0;
    }
}

// Inquiries unread count
$unreadMessagesCount = 0;
try {
    $pdo = get_db();
    $unreadMessagesCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
} catch (Throwable $e) {
    $unreadMessagesCount = 0;
}
?>
<aside class="admin-sidebar">
    <!-- Brand Header -->
    <div class="admin-brand">
        <div class="logo-circle-holder" style="width:44px; height:44px; padding:2px; margin-right:12px; background:linear-gradient(135deg, #1E3A8A, #D4AF37); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 0 15px rgba(212,175,55,0.3);">
            <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="SCJ Logo" style="width:38px; height:38px; border-radius:50%; object-fit:cover;">
        </div>
        <div style="overflow:hidden;">
            <h4 style="color:#FFFFFF; font-size:0.95rem; font-weight:800; margin:0; white-space:nowrap; letter-spacing:0.3px;">SCJE Portal</h4>
            <div style="margin-top:3px;">
                <?php if ($isSuperAdmin): ?>
                    <span style="display:inline-flex; align-items:center; gap:4px; background:linear-gradient(135deg, #7C3AED, #F59E0B); color:#FFFFFF; font-size:0.65rem; font-weight:800; padding:2px 7px; border-radius:10px; text-transform:uppercase; letter-spacing:0.6px; box-shadow:0 0 8px rgba(245,158,11,0.4);">
                        <i class="fa-solid fa-crown" style="font-size:0.6rem;"></i> Super Admin
                    </span>
                <?php else: ?>
                    <span style="display:inline-block; background:rgba(56, 189, 248, 0.2); color:#38BDF8; font-size:0.65rem; font-weight:700; padding:2px 7px; border-radius:10px; text-transform:uppercase; letter-spacing:0.5px;">
                        Administrator
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation List -->
    <ul class="admin-nav">
        <!-- Main Core Section -->
        <li class="nav-section-label">CORE MANAGEMENT</li>
        <?php if ($isSuperAdmin): ?>
        <li>
            <a href="<?= base_url('admin/index.php') ?>" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge" style="color:#F59E0B;"></i> 
                <span>System Dashboard</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="<?= base_url('admin/researches.php') ?>" class="<?= ($activePage ?? '') === 'researches' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i> 
                <span>Research Papers</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/equipment.php') ?>" class="<?= ($activePage ?? '') === 'equipment' ? 'active' : '' ?>">
                <i class="fa-solid fa-microscope"></i> 
                <span>Lab Equipment</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/materials.php') ?>" class="<?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>">
                <i class="fa-solid fa-flask-vial"></i> 
                <span>Materials &amp; Reagents</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/faculty.php') ?>" class="<?= ($activePage ?? '') === 'faculty' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> 
                <span>Faculty &amp; Staff</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/content.php') ?>" class="<?= ($activePage ?? '') === 'content' ? 'active' : '' ?>">
                <i class="fa-solid fa-compass"></i> 
                <span>Site Content</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/messages.php') ?>" class="<?= ($activePage ?? '') === 'messages' ? 'active' : '' ?>" style="display:flex; justify-content:space-between; align-items:center;">
                <span style="display:flex; align-items:center; gap:12px;">
                    <i class="fa-solid fa-envelope"></i> 
                    <span>Public Inquiries</span>
                </span>
                <?php if ($unreadMessagesCount > 0): ?>
                    <span class="nav-badge badge-unread"><?= $unreadMessagesCount ?></span>
                <?php endif; ?>
            </a>
        </li>

        <!-- SUPER ADMIN EXCLUSIVE SECTION -->
        <?php if ($isSuperAdmin): ?>
            <li class="nav-section-label super-admin-label">
                <span>SUPER ADMIN CENTER</span>
                <i class="fa-solid fa-shield-halved" style="color:#F59E0B; font-size:0.75rem;"></i>
            </li>
            <li>
                <a href="<?= base_url('admin/health.php') ?>" class="<?= ($activePage ?? '') === 'health' ? 'active' : '' ?> super-nav-link">
                    <i class="fa-solid fa-heart-pulse" style="color:#10B981;"></i> 
                    <span>System Health</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/crash_logs.php') ?>" class="<?= ($activePage ?? '') === 'crash_logs' ? 'active' : '' ?> super-nav-link" style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="display:flex; align-items:center; gap:12px;">
                        <i class="fa-solid fa-bug" style="color:#F87171;"></i> 
                        <span>Crash Logs</span>
                    </span>
                    <?php if ($unresolvedCrashCount > 0): ?>
                        <span class="nav-badge badge-crash" title="<?= $unresolvedCrashCount ?> Unresolved Crashes"><?= $unresolvedCrashCount ?></span>
                    <?php else: ?>
                        <span style="font-size:0.65rem; color:#10B981;"><i class="fa-solid fa-check"></i></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/users.php') ?>" class="<?= ($activePage ?? '') === 'users' ? 'active' : '' ?> super-nav-link">
                    <i class="fa-solid fa-user-shield" style="color:#A855F7;"></i> 
                    <span>Accounts &amp; Roles</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Quick Links -->
        <li class="nav-section-label">SYSTEM</li>
        <li>
            <a href="<?= base_url() ?>" target="_blank">
                <i class="fa-solid fa-globe"></i> 
                <span>Public Website</span>
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7rem; margin-left:auto; opacity:0.6;"></i>
            </a>
        </li>
        <li>
            <a href="<?= base_url('logout.php') ?>" style="color:#F87171;">
                <i class="fa-solid fa-right-from-bracket"></i> 
                <span>Log Out</span>
            </a>
        </li>
    </ul>

    <!-- Footer Meta -->
    <div class="admin-sidebar-footer">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
            <span style="color:#64748B; font-size:0.7rem; font-weight:700;">ACTIVE ENGINE</span>
            <span class="engine-badge"><?= strtoupper($activeDriver) ?></span>
        </div>
        <div style="color:#475569; font-size:0.68rem; display:flex; align-items:center; gap:6px;">
            <span class="live-dot"></span> SCJE Security v1.3
        </div>
    </div>
</aside>

<style>
/* Sidebar Common Refined Styling */
.nav-section-label {
    padding: 16px 24px 6px;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    color: #64748B;
    text-transform: uppercase;
}
.super-admin-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    color: #F59E0B;
}
.super-nav-link {
    position: relative;
}
.nav-badge {
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 800;
    line-height: 1;
}
.badge-unread {
    background: #3B82F6;
    color: #FFFFFF;
}
.badge-crash {
    background: #EF4444;
    color: #FFFFFF;
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
    animation: pulseBadge 2s infinite ease-in-out;
}
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.85; }
}
.admin-sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.2);
}
.engine-badge {
    background: rgba(56, 189, 248, 0.15);
    color: #38BDF8;
    border: 1px solid rgba(56, 189, 248, 0.3);
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 6px #10B981;
}
</style>

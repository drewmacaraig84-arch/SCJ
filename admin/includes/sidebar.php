<?php
/**
 * Clean Admin & Super Admin Sidebar Navigation
 * School of Criminal Justice Education (SCJE) Information System
 * 
 * @param string $activePage The current active page key
 */

$currentUser = AuthMiddleware::user();
$isSuperAdmin = RoleMiddleware::isSuperAdmin();
$activeDriver = DB::getDriver();
?>
<aside class="admin-sidebar">
    <!-- Brand Header -->
    <div class="admin-brand">
        <div class="logo-circle-holder" style="width:44px; height:44px; padding:2px; margin-right:12px;">
            <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="SCJ Logo" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
        </div>
        <div>
            <h4 style="color:#FFFFFF; font-size:0.95rem; font-weight:800; margin:0;">SCJE Admin</h4>
            <span style="font-size:0.75rem; color:var(--color-primary-accent, #E5C158);">Information System</span>
        </div>
    </div>

    <!-- Navigation List -->
    <ul class="admin-nav">
        <li>
            <a href="<?= base_url('admin/index.php') ?>" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge"></i> 
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/researches.php') ?>" class="<?= ($activePage ?? '') === 'researches' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i> 
                <span>Manage Research</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/equipment.php') ?>" class="<?= ($activePage ?? '') === 'equipment' ? 'active' : '' ?>">
                <i class="fa-solid fa-microscope"></i> 
                <span>Manage Equipment</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/materials.php') ?>" class="<?= ($activePage ?? '') === 'materials' ? 'active' : '' ?>">
                <i class="fa-solid fa-flask-vial"></i> 
                <span>Manage Materials</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/faculty.php') ?>" class="<?= ($activePage ?? '') === 'faculty' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> 
                <span>Manage Faculty</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/content.php') ?>" class="<?= ($activePage ?? '') === 'content' ? 'active' : '' ?>">
                <i class="fa-solid fa-compass"></i> 
                <span>Site Content</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/messages.php') ?>" class="<?= ($activePage ?? '') === 'messages' ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope"></i> 
                <span>Inquiries</span>
            </a>
        </li>

        <?php if ($isSuperAdmin): ?>
            <li>
                <a href="<?= base_url('admin/health.php') ?>" class="<?= ($activePage ?? '') === 'health' ? 'active' : '' ?>">
                    <i class="fa-solid fa-heart-pulse"></i> 
                    <span>System Health</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/crash_logs.php') ?>" class="<?= ($activePage ?? '') === 'crash_logs' ? 'active' : '' ?>">
                    <i class="fa-solid fa-bug"></i> 
                    <span>Crash Logs</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/users.php') ?>" class="<?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-shield"></i> 
                    <span>Accounts &amp; Roles</span>
                </a>
            </li>
        <?php endif; ?>

        <li style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1);">
            <a href="<?= base_url() ?>" target="_blank">
                <i class="fa-solid fa-globe"></i> 
                <span>View Public Site</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('logout.php') ?>" style="color:#F87171;">
                <i class="fa-solid fa-right-from-bracket"></i> 
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <!-- Footer Meta -->
    <div class="admin-sidebar-footer">
        Engine: <strong><?= strtoupper($activeDriver) ?></strong>
    </div>
</aside>

<style>
/* Clean Original Sidebar Styling */
.admin-sidebar {
    background: #071324 !important;
    color: #E2E8F0 !important;
    padding: 24px 0 0 0 !important;
    border-right: 1px solid rgba(56,189,248,0.2) !important;
    display: flex !important;
    flex-direction: column !important;
    box-sizing: border-box !important;
}

.admin-brand {
    padding: 0 20px 20px !important;
    border-bottom: 1px solid rgba(255,255,255,0.1) !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.admin-nav {
    list-style: none !important;
    padding: 20px 0 !important;
    margin: 0 !important;
    flex-grow: 1 !important;
}

.admin-nav li a {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 24px !important;
    color: #CBD5E1 !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    border-left: 4px solid transparent !important;
}

.admin-nav li a:hover, 
.admin-nav li a.active {
    background: rgba(30,58,138,0.5) !important;
    color: var(--color-primary-accent, #E5C158) !important;
    border-left: 4px solid var(--color-primary-accent, #E5C158) !important;
}

.admin-sidebar-footer {
    padding: 16px 20px !important;
    font-size: 0.75rem !important;
    color: #64748B !important;
    border-top: 1px solid rgba(255,255,255,0.05) !important;
}

/* Fixed Stationary Sidebar Layout (Scrolling page never scrolls sidebar) */
@media (min-width: 768px) {
    html, body {
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .admin-layout {
        display: grid !important;
        grid-template-columns: 260px minmax(0, 1fr) !important;
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .admin-sidebar {
        height: 100vh !important;
        max-height: 100vh !important;
        position: sticky !important;
        top: 0 !important;
        left: 0 !important;
        align-self: start !important;
        flex-shrink: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        z-index: 100 !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(56, 189, 248, 0.25) transparent;
    }
    .admin-sidebar::-webkit-scrollbar {
        width: 4px;
    }
    .admin-sidebar::-webkit-scrollbar-thumb {
        background: rgba(56, 189, 248, 0.2);
        border-radius: 4px;
    }
    .admin-main {
        height: 100vh !important;
        max-height: 100vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
        padding: clamp(20px, 2.5vw, 36px) !important;
        box-sizing: border-box !important;
    }
}
</style>
<script src="<?= asset_url('assets/js/admin-animations.js') ?>"></script>


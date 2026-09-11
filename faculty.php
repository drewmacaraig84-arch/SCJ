<?php
/**
 * Faculty & Staff Organizational Directory Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'faculty';
$pageTitle = 'Faculty & Staff | SCJE Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();

// Fetch Dean (Cached 1 hr)
$dean = Cache::remember('faculty_dean', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level = 'dean' LIMIT 1")->fetch();
});

// Fetch All Other Faculty (Cached 1 hr)
$facultyList = Cache::remember('faculty_list', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level != 'dean' ORDER BY order_index ASC")->fetchAll();
});
?>

    <!-- Page Banner -->
    <div style="background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #1E3A8A 100%); color: #FFFFFF; padding: 40px 0; border-bottom: 3px solid var(--color-primary-accent);">
        <div class="container">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:var(--radius-sm); background:rgba(56,189,248,0.2); border:1px solid var(--color-primary-accent); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--color-primary-accent);">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h2 style="font-size:1.85rem; font-weight:900; letter-spacing:1px; text-transform:uppercase;">Faculty &amp; Staff</h2>
                    <p style="color:#94A3B8; font-size:0.9rem;">Organizational Hierarchy &bull; Department Leadership &bull; Instructional Faculty</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FACULTY & STAFF SECTION (EXACT SKETCH IMAGE 1 HIERARCHY)  -->
    <!-- ========================================================= -->
    <section class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-sitemap"></i> Organizational Chart</h2>
                <span style="font-size:0.85rem; color:#FEF08A; font-weight:700;">DWCC School of Criminal Justice Education</span>
            </div>

            <div class="faculty-org-chart">
                <!-- Top Card: OIC - DEAN, SCJ (From Sketch Image 1) -->
                <?php if ($dean): ?>
                    <div id="dean" class="dean-card-wrapper">
                        <div class="dean-card">
                            <div class="faculty-avatar">
                                <img src="<?= base_url('assets/images/avatar_placeholder.svg') ?>" alt="<?= e($dean['name']) ?>">
                            </div>
                            <h3 class="faculty-name"><?= e($dean['name']) ?></h3>
                            <div class="faculty-position">OIC - DEAN, SCJ</div>
                            <div class="faculty-details">
                                <?php if (!empty($dean['specialization'])): ?>
                                    <p><strong>Specialization:</strong> <?= e($dean['specialization']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($dean['research_interests'])): ?>
                                    <p style="margin-top:6px; font-size:0.78rem; color:#CBD5E1;">
                                        <strong>Research Interests:</strong> <?= e($dean['research_interests']) ?>
                                    </p>
                                <?php endif; ?>
                                <p style="margin-top:6px; font-size:0.75rem; color:#94A3B8;">
                                    <i class="fa-solid fa-envelope"></i> <?= e($dean['email']) ?> &bull; <?= e($dean['office_location']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Vertical Hierarchical Tree Connector -->
                    <div class="org-connector-vertical"></div>
                <?php endif; ?>

                <!-- Subordinate Faculty Grid (From Sketch Image 1 Grid of Cards) -->
                <div id="directory" class="subordinate-faculty-grid">
                    <?php foreach ($facultyList as $fac): ?>
                        <div class="faculty-sub-card">
                            <div class="faculty-sub-avatar">
                                <img src="<?= base_url('assets/images/avatar_placeholder.svg') ?>" alt="<?= e($fac['name']) ?>">
                            </div>
                            <div class="faculty-sub-info">
                                <h4><?= e($fac['name']) ?></h4>
                                <div class="faculty-sub-position"><?= e($fac['position']) ?></div>
                                <?php if (!empty($fac['specialization'])): ?>
                                    <div class="faculty-sub-spec">
                                        <strong>Specialization:</strong> <?= e($fac['specialization']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($fac['research_interests'])): ?>
                                    <div class="faculty-sub-spec" style="margin-top:4px; color:#475569;">
                                        <strong>Interests:</strong> <?= e($fac['research_interests']) ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top:6px; font-size:0.72rem; color:#64748B;">
                                    <i class="fa-solid fa-envelope"></i> <?= e($fac['email']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

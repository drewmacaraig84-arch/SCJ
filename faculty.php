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
    <div class="page-banner">
        <div class="container page-banner-inner">
            <div class="page-banner-icon">
                <i class="fa-solid fa-sitemap"></i>
            </div>
            <div class="page-banner-content">
                <h2>Faculty &amp; Staff</h2>
                <p>Organizational Hierarchy &bull; Department Leadership &bull; Instructional Faculty</p>
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
                <span class="badge badge-primary">DWCC School of Criminal Justice Education</span>
            </div>

            <div class="faculty-org-chart">
                <!-- Top Card: OIC - DEAN, SCJ (From Sketch Image 1) -->
                <?php if ($dean): ?>
                    <?php 
                    $deanPhoto = (!empty($dean['photo_url']) && file_exists(__DIR__ . '/' . $dean['photo_url']))
                        ? base_url($dean['photo_url']) . '?v=' . filemtime(__DIR__ . '/' . $dean['photo_url'])
                        : base_url('assets/images/avatar_placeholder.svg');
                    ?>
                    <div id="dean" class="dean-card-wrapper">
                        <div class="dean-card">
                            <div class="faculty-avatar">
                                <img src="<?= $deanPhoto ?>" alt="<?= e($dean['name']) ?>">
                            </div>
                            <h3 class="faculty-name"><?= e($dean['name']) ?></h3>
                            <div class="faculty-position"><?= e($dean['position'] ?: 'OFFICER-IN-CHARGE') ?></div>
                            <div class="faculty-details">
                                <?php if (!empty($dean['specialization'])): ?>
                                    <p><strong>Specialization:</strong> <?= e($dean['specialization']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($dean['research_interests'])): ?>
                                    <p style="margin-top:6px; font-size:0.78rem; color:var(--color-text-secondary);">
                                        <strong>Roles &amp; Experience:</strong> <?= e($dean['research_interests']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($dean['office_location'])): ?>
                                    <p style="margin-top:6px; font-size:0.75rem; color:var(--color-text-muted);">
                                        <i class="fa-solid fa-location-dot"></i> <?= e($dean['office_location']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Vertical Hierarchical Tree Connector -->
                    <div class="org-connector-vertical"></div>
                <?php endif; ?>

                <!-- Subordinate Faculty Grid (From Sketch Image 1 Grid of Cards) -->
                <div id="directory" class="subordinate-faculty-grid">
                    <?php foreach ($facultyList as $fac): ?>
                        <?php 
                        $facPhoto = (!empty($fac['photo_url']) && file_exists(__DIR__ . '/' . $fac['photo_url']))
                            ? base_url($fac['photo_url']) . '?v=' . filemtime(__DIR__ . '/' . $fac['photo_url'])
                            : base_url('assets/images/avatar_placeholder.svg');
                        ?>
                        <div class="faculty-sub-card">
                            <div class="faculty-sub-avatar">
                                <img src="<?= $facPhoto ?>" alt="<?= e($fac['name']) ?>">
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
                                    <div class="faculty-sub-spec" style="margin-top:4px; color:var(--color-text-secondary);">
                                        <strong>Interests:</strong> <?= e($fac['research_interests']) ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

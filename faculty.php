<?php
/**
 * Faculty & Staff Organizational Directory Page
 * School of Criminal Justice (SCJ) Information System
 */

$activePage = 'faculty';
$pageTitle = 'Faculty & Staff | SCJ Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();

// Fetch Dean (Cached 1 hr)
$dean = Cache::remember('faculty_dean', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level = 'dean' LIMIT 1")->fetch();
});

// Fetch Academic Leadership & Full-Time Faculty (Cached 1 hr)
$fullTimeFaculty = Cache::remember('faculty_full_time', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level NOT IN ('dean', 'part_time') ORDER BY order_index ASC")->fetchAll();
});

// Fetch Part-Time Faculty (Cached 1 hr)
$partTimeFaculty = Cache::remember('faculty_part_time', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level = 'part_time' ORDER BY order_index ASC")->fetchAll();
});
?>

    <!-- Page Banner -->
    <div class="page-banner">
        <div class="container page-banner-inner">
            <div class="page-banner-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="page-banner-content">
                <h2>Faculty &amp; Staff Directory</h2>
                <p>Academic Leadership &bull; Instructional Faculty &bull; Professional Lecturers</p>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FACULTY & STAFF SECTION                                   -->
    <!-- ========================================================= -->
    <section class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-sitemap"></i> Department Leadership</h2>
                <span class="badge badge-primary">DWCC School of Criminal Justice</span>
            </div>

            <div class="faculty-org-chart">
                <!-- Top Card: OIC - DEAN, SCJ -->
                <?php if ($dean): ?>
                    <?php 
                    $deanPhoto = (!empty($dean['photo_url']) && file_exists(__DIR__ . '/' . $dean['photo_url']))
                        ? base_url($dean['photo_url']) . '?v=' . filemtime(__DIR__ . '/' . $dean['photo_url'])
                        : base_url('assets/images/avatar_placeholder.svg');
                    
                    $deanLines = array_filter(array_map('trim', explode("\n", $dean['description'] ?? '')));
                    ?>
                    <div id="dean" class="dean-card-wrapper">
                        <div class="dean-card">
                            <div class="faculty-avatar">
                                <img src="<?= $deanPhoto ?>" alt="<?= e($dean['name']) ?>">
                            </div>
                            <h3 class="faculty-name"><?= e($dean['name']) ?></h3>
                            <div class="faculty-position-badge"><?= e($dean['position'] ?: 'OFFICER-IN-CHARGE') ?></div>

                            <?php if (!empty($deanLines)): ?>
                                <ul class="dean-desc-list">
                                    <?php foreach ($deanLines as $line): ?>
                                        <li>
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span><?= e($line) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($dean['office_location'])): ?>
                                <div class="faculty-office-meta">
                                    <i class="fa-solid fa-location-dot"></i> <?= e($dean['office_location']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Vertical Hierarchical Tree Connector -->
                    <div class="org-connector-vertical"></div>
                <?php endif; ?>

                <!-- Full-Time Academic Leadership & Instructional Faculty -->
                <div class="faculty-group-heading">
                    <h3><i class="fa-solid fa-user-shield"></i> Academic Leadership &amp; Full-Time Faculty</h3>
                </div>

                <div id="directory" class="faculty-modern-grid">
                    <?php foreach ($fullTimeFaculty as $fac): ?>
                        <?php 
                        $facPhoto = (!empty($fac['photo_url']) && file_exists(__DIR__ . '/' . $fac['photo_url']))
                            ? base_url($fac['photo_url']) . '?v=' . filemtime(__DIR__ . '/' . $fac['photo_url'])
                            : base_url('assets/images/avatar_placeholder.svg');

                        $lines = array_filter(array_map('trim', explode("\n", $fac['description'] ?? '')));
                        ?>
                        <div class="faculty-modern-card">
                            <div class="faculty-card-header">
                                <div class="faculty-avatar-wrap">
                                    <img src="<?= $facPhoto ?>" alt="<?= e($fac['name']) ?>">
                                </div>
                                <div class="faculty-header-info">
                                    <h4 class="faculty-card-name"><?= e($fac['name']) ?></h4>
                                    <?php if (!empty($fac['position'])): ?>
                                        <div class="faculty-role-badge badge-<?= e($fac['role_level']) ?>">
                                            <?= e($fac['position']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="faculty-card-divider"></div>

                            <div class="faculty-card-body">
                                <?php if (!empty($lines)): ?>
                                    <ul class="faculty-desc-list">
                                        <?php foreach ($lines as $line): ?>
                                            <li>
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span><?= e($line) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- PART TIME FACULTY SECTION                                 -->
            <!-- ========================================================= -->
            <?php if (!empty($partTimeFaculty)): ?>
                <div class="section-header-banner" style="margin-top: 55px;">
                    <h2><i class="fa-solid fa-scale-balanced"></i> Part-Time Faculty</h2>
                    <span class="badge badge-primary">Legal Counsel &amp; Professional Lecturers</span>
                </div>

                <div class="faculty-modern-grid" style="margin-top: 24px;">
                    <?php foreach ($partTimeFaculty as $pt): ?>
                        <?php 
                        $ptPhoto = (!empty($pt['photo_url']) && file_exists(__DIR__ . '/' . $pt['photo_url']))
                            ? base_url($pt['photo_url']) . '?v=' . filemtime(__DIR__ . '/' . $pt['photo_url'])
                            : base_url('assets/images/avatar_placeholder.svg');

                        $ptLines = array_filter(array_map('trim', explode("\n", $pt['description'] ?? '')));
                        ?>
                        <div class="faculty-modern-card">
                            <div class="faculty-card-header">
                                <div class="faculty-avatar-wrap">
                                    <img src="<?= $ptPhoto ?>" alt="<?= e($pt['name']) ?>">
                                </div>
                                <div class="faculty-header-info">
                                    <h4 class="faculty-card-name"><?= e($pt['name']) ?></h4>
                                    <?php if (!empty($pt['position'])): ?>
                                        <div class="faculty-role-badge badge-part-time">
                                            <?= e($pt['position']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="faculty-card-divider"></div>

                            <div class="faculty-card-body">
                                <?php if (!empty($ptLines)): ?>
                                    <ul class="faculty-desc-list">
                                        <?php foreach ($ptLines as $line): ?>
                                            <li>
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span><?= e($line) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

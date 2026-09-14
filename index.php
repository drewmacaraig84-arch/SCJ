<?php
/**
 * Main Portal Homepage & Executive Summary
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'home';
$pageTitle = 'School of Criminal Justice Education | DWCC Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();

// 1. Fetch Top 4 Recent Researches for Homepage Preview (Cached 1 hr)
$recentResearches = Cache::remember('home_recent_researches', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM research ORDER BY id DESC LIMIT 4")->fetchAll();
});

// 2. Fetch Dean & Featured Faculty (Cached 1 hr)
$dean = Cache::remember('home_dean', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level = 'dean' LIMIT 1")->fetch();
});

$featuredFaculty = Cache::remember('home_featured_faculty', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM faculty WHERE role_level != 'dean' ORDER BY order_index ASC LIMIT 2")->fetchAll();
});

// 3. Fetch Site Content (Cached 1 hr)
$siteContent = Cache::remember('site_content', 3600, function() use ($pdo) {
    $contentStmt = $pdo->query("SELECT section_key, title, content FROM site_content");
    $content = [];
    while ($row = $contentStmt->fetch()) {
        $content[$row['section_key']] = $row;
    }
    return $content;
});
?>

    <!-- ========================================================= -->
    <!-- ========================================================= -->
    <!-- HERO SECTION WITH QUICK TILES (EXECUTIVE MODERN)          -->
    <!-- ========================================================= -->
    <section class="hero-section">
        <div class="container hero-grid">
            <!-- Left Headline & Description -->
            <div class="hero-content">
                <span class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i> Divine Word College of Calapan &bull; SCJE
                </span>
                <div class="hero-kicker">
                    <i class="fa-solid fa-scale-balanced"></i> Center of Excellence in Criminological Education
                </div>
                <h2>
                    WELCOME TO THE
                    <span class="hero-title-highlight">SCHOOL OF CRIMINAL JUSTICE</span>
                </h2>
                <p>
                    The School of Criminal Justice provides quality criminological education, forensic laboratory training, 
                    empirical research opportunities, and leadership development for aspiring law enforcement and criminal justice professionals.
                </p>

                <!-- 3 Metric Highlight Chips -->
                <div class="hero-stats-chips">
                    <div class="hero-chip">
                        <i class="fa-solid fa-flask"></i>
                        <span><strong>5</strong> Forensic Labs</span>
                    </div>
                    <div class="hero-chip">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span><strong>82</strong> Tracked Assets</span>
                    </div>
                    <div class="hero-chip">
                        <i class="fa-solid fa-award"></i>
                        <span><strong>AY 2025-2026</strong> Accredited</span>
                    </div>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="<?= base_url('research.php') ?>" class="btn-primary" style="box-shadow: 0 4px 14px rgba(29, 78, 216, 0.35);">
                        <i class="fa-solid fa-magnifying-glass"></i> Criminological Research
                    </a>
                    <a href="<?= base_url('laboratories.php') ?>" class="btn-secondary">
                        <i class="fa-solid fa-flask"></i> Laboratory Facilities
                    </a>
                    <a href="<?= base_url('about.php') ?>" class="btn-outline">
                        <i class="fa-solid fa-circle-info"></i> About SCJE
                    </a>
                </div>
            </div>

            <!-- Right: 4 Quick Access Tiles -->
            <div class="hero-tiles-grid">
                <!-- Tile 1: Criminological Research -->
                <a href="<?= base_url('research.php') ?>" class="hero-tile">
                    <div class="hero-tile-left">
                        <div class="hero-tile-icon icon-research"><i class="fa-solid fa-book-bookmark"></i></div>
                        <div class="hero-tile-text">
                            <span class="hero-tile-meta">Forensics &bull; Theses</span>
                            <div class="hero-tile-title">Criminological Research</div>
                        </div>
                    </div>
                    <div class="hero-tile-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                </a>

                <!-- Tile 2: Laboratory Equipment -->
                <a href="<?= base_url('laboratories.php') ?>" class="hero-tile">
                    <div class="hero-tile-left">
                        <div class="hero-tile-icon icon-lab"><i class="fa-solid fa-microscope"></i></div>
                        <div class="hero-tile-text">
                            <span class="hero-tile-meta">Crime Lab &bull; Ballistics &bull; CSI</span>
                            <div class="hero-tile-title">Laboratory Equipment</div>
                        </div>
                    </div>
                    <div class="hero-tile-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                </a>

                <!-- Tile 3: Faculty -->
                <a href="<?= base_url('faculty.php') ?>" class="hero-tile">
                    <div class="hero-tile-left">
                        <div class="hero-tile-icon icon-faculty"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="hero-tile-text">
                            <span class="hero-tile-meta">Dean &bull; Instructors &bull; Roster</span>
                            <div class="hero-tile-title">Faculty &amp; Staff</div>
                        </div>
                    </div>
                    <div class="hero-tile-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                </a>

                <!-- Tile 4: Contact & Inquiries -->
                <a href="<?= base_url('contact.php') ?>" class="hero-tile">
                    <div class="hero-tile-left">
                        <div class="hero-tile-icon icon-contact"><i class="fa-solid fa-envelope-circle-check"></i></div>
                        <div class="hero-tile-text">
                            <span class="hero-tile-meta">Dean's Office &bull; Advising</span>
                            <div class="hero-tile-title">Contact &amp; Inquiries</div>
                        </div>
                    </div>
                    <div class="hero-tile-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 3 INSTITUTIONAL PILLARS: VISION, MISSION, GOALS           -->
    <!-- ========================================================= -->
    <section class="pillars-section">
        <div class="container">
            <div class="pillars-grid">
                <!-- Vision -->
                <a href="<?= base_url('about.php#pillars') ?>" class="pillar-card pillar-vision" style="color:inherit;">
                    <i class="fa-solid fa-eye pillar-watermark"></i>
                    <div class="pillar-icon-badge"><i class="fa-solid fa-eye"></i></div>
                    <div class="pillar-text">
                        <h3><i class="fa-solid fa-compass" style="color:var(--color-blue-royal); font-size:0.95rem;"></i> <?= e($siteContent['vision']['title'] ?? 'VISION') ?></h3>
                        <p><?= nl2br(e($siteContent['vision']['content'] ?? 'To become the center and primer in the pursuit of quality instruction in the field of Criminal Justice in the entire Mindoro Region.')) ?></p>
                    </div>
                </a>

                <!-- Mission -->
                <a href="<?= base_url('about.php#pillars') ?>" class="pillar-card pillar-mission" style="color:inherit;">
                    <i class="fa-solid fa-bullseye pillar-watermark"></i>
                    <div class="pillar-icon-badge"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="pillar-text">
                        <h3><i class="fa-solid fa-flag" style="color:var(--color-gold); font-size:0.95rem;"></i> <?= e($siteContent['mission']['title'] ?? 'MISSION') ?></h3>
                        <p><?= nl2br(e($siteContent['mission']['content'] ?? 'To produce professionally competent and morally upright graduates equipped with contemporary and functional knowledge and skills in the field of law enforcement administration, crime detection and investigation, correctional administration, criminal sociology and forensic science.')) ?></p>
                    </div>
                </a>

                <!-- Goals -->
                <a href="<?= base_url('about.php#pillars') ?>" class="pillar-card pillar-goals" style="color:inherit;">
                    <i class="fa-solid fa-chart-line pillar-watermark"></i>
                    <div class="pillar-icon-badge"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="pillar-text">
                        <h3><i class="fa-solid fa-crosshairs" style="color:#0284C7; font-size:0.95rem;"></i> <?= e($siteContent['goals']['title'] ?? 'GOALS') ?></h3>
                        <p><?= nl2br(e($siteContent['goals']['content'] ?? "• Foster the value of god-fearing, social responsibility, self-sacrifice and discipline;\n• Provide students with theoretical, technical, practical and actual knowledge relative to criminology profession; and\n• Prepare students for careers in any agencies under the Philippine Criminal Justice System")) ?></p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- SECTION 1 SUMMARY: CRIMINOLOGICAL RESEARCH TITLES         -->
    <!-- ========================================================= -->
    <section class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Criminological Research Titles</h2>
                <a href="<?= base_url('research.php') ?>" class="btn-primary" style="font-size:0.8rem; padding:8px 18px; border-radius:var(--radius-xs);">
                    View Complete Research Catalog <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- 16 Categories Grid (Reference Screenshot) -->
            <div class="research-categories-grid">
                <?php
                $categories = [
                    ['name' => 'Criminal Investigation', 'icon' => 'fa-magnifying-glass'],
                    ['name' => 'Criminalistics', 'icon' => 'fa-fingerprint'],
                    ['name' => 'Forensic Science', 'icon' => 'fa-flask-vial'],
                    ['name' => 'Questioned Document Examination', 'icon' => 'fa-file-signature'],
                    ['name' => 'Forensic Photography', 'icon' => 'fa-camera'],
                    ['name' => 'Forensic Ballistics', 'icon' => 'fa-crosshairs'],
                    ['name' => 'Crime Prevention', 'icon' => 'fa-shield-halved'],
                    ['name' => 'Juvenile Delinquency', 'icon' => 'fa-children'],
                    ['name' => 'Police Administration', 'icon' => 'fa-user-shield'],
                    ['name' => 'Corrections', 'icon' => 'fa-bars'],
                    ['name' => 'Community-Based Studies', 'icon' => 'fa-people-roof'],
                    ['name' => 'Cybercrime', 'icon' => 'fa-laptop-code'],
                    ['name' => 'Criminal Justice Administration', 'icon' => 'fa-scale-balanced'],
                    ['name' => 'Victimology', 'icon' => 'fa-hand-holding-heart'],
                    ['name' => 'Penology', 'icon' => 'fa-handcuffs'],
                    ['name' => 'Public Safety', 'icon' => 'fa-person-shelter'],
                ];

                foreach ($categories as $cat):
                ?>
                    <a href="<?= base_url('research.php') ?>" class="category-card">
                        <div class="category-icon"><i class="fa-solid <?= $cat['icon'] ?>"></i></div>
                        <div class="category-title"><?= e($cat['name']) ?></div>
                        <div class="category-link">View Titles <i class="fa-solid fa-arrow-right"></i></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Recent Research Highlights Table Preview -->
            <div class="table-card">
                <div class="table-toolbar">
                    <h3 class="table-toolbar-title">
                        <i class="fa-solid fa-clock-rotate-left" style="color:var(--color-gold);"></i> Latest Published Research Highlights
                    </h3>
                    <a href="<?= base_url('research.php') ?>" class="table-toolbar-link">
                        Open Searchable Repository Table &rarr;
                    </a>
                </div>

                <div class="custom-table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">NAME</th>
                                <th style="width: 50%;">RESEARCH TITLE</th>
                                <th style="width: 15%;">MONTH / YEAR</th>
                                <th style="width: 10%; text-align:center;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentResearches as $r): ?>
                                <tr>
                                    <td class="research-author-cell">
                                        <i class="fa-solid fa-user-pen"></i>
                                        <?= e($r['author_name']) ?>
                                    </td>
                                    <td>
                                        <span class="research-title-text"><?= e($r['research_title']) ?></span>
                                        <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:3px;">
                                            <span class="badge badge-info"><?= e($r['category']) ?></span>
                                        </div>
                                    </td>
                                    <td class="research-date-cell"><?= e($r['month_year']) ?></td>
                                    <td style="text-align:center;">
                                        <a href="<?= base_url('research.php') ?>" class="btn-primary" style="padding:5px 12px; font-size:0.75rem;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- SECTION 2 SUMMARY: CRIMINOLOGY LABORATORIES               -->
    <!-- ========================================================= -->
    <section class="section-wrapper section-alt">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-flask"></i> Criminology Laboratories</h2>
                <a href="<?= base_url('laboratories.php') ?>" class="btn-primary" style="font-size:0.8rem; padding:8px 18px; border-radius:var(--radius-xs);">
                    View Complete Laboratory Inventory <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- 5 Specialized Laboratory Cards (Reference Screenshot) -->
            <div class="laboratories-visual-grid">
                <!-- Card 1 -->
                <a href="<?= base_url('laboratories.php') ?>" class="lab-card" style="color:inherit;">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_criminalistics.svg') ?>" alt="Criminalistics Laboratory">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Criminalistics Laboratory</div>
                        <ul class="lab-card-list">
                            <li>Fingerprint examination</li>
                            <li>Questioned documents</li>
                            <li>Forensic photography</li>
                            <li>Toolmark examination</li>
                        </ul>
                    </div>
                </a>

                <!-- Card 2 -->
                <a href="<?= base_url('laboratories.php') ?>" class="lab-card" style="color:inherit;">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_csi.svg') ?>" alt="Crime Scene Investigation Laboratory">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Crime Scene Investigation</div>
                        <ul class="lab-card-list">
                            <li>Crime scene processing</li>
                            <li>Evidence collection</li>
                            <li>Evidence packaging</li>
                            <li>Crime scene reconstruction</li>
                        </ul>
                    </div>
                </a>

                <!-- Card 3 -->
                <a href="<?= base_url('laboratories.php') ?>" class="lab-card" style="color:inherit;">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_forensic_science.svg') ?>" alt="Forensic Science Laboratory">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Forensic Science Laboratory</div>
                        <ul class="lab-card-list">
                            <li>Forensic chemistry</li>
                            <li>Forensic biology</li>
                            <li>Toxicology</li>
                            <li>Trace evidence</li>
                        </ul>
                    </div>
                </a>

                <!-- Card 4 -->
                <a href="<?= base_url('laboratories.php') ?>" class="lab-card" style="color:inherit;">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_ballistics.svg') ?>" alt="Forensic Ballistics">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Forensic Ballistics</div>
                        <ul class="lab-card-list">
                            <li>Firearms identification</li>
                            <li>Ammunition examination</li>
                            <li>Bullet/cartridge examination</li>
                        </ul>
                    </div>
                </a>

                <!-- Card 5 -->
                <a href="<?= base_url('laboratories.php') ?>" class="lab-card" style="color:inherit;">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_specialized.svg') ?>" alt="Other Specialized Areas">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Other Specialized Areas</div>
                        <ul class="lab-card-list">
                            <li>Polygraph suite</li>
                            <li>Medicolegal investigation</li>
                            <li>Digital forensics</li>
                        </ul>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- SECTION 3 SUMMARY: FACULTY & STAFF LEADERSHIP             -->
    <!-- ========================================================= -->
    <section class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-users"></i> Faculty &amp; Staff Leadership</h2>
                <a href="<?= base_url('faculty.php') ?>" class="btn-primary" style="font-size:0.8rem; padding:8px 18px; border-radius:var(--radius-xs);">
                    View Complete Organizational Chart <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- Dean Card Preview (Matching Sketch Top Center) -->
            <?php if ($dean): ?>
                <div style="max-width:540px; margin: 0 auto 30px;">
                    <div class="dean-card">
                        <div class="faculty-avatar">
                            <img src="<?= base_url('assets/images/avatar_placeholder.svg') ?>" alt="<?= e($dean['name']) ?>">
                        </div>
                        <h3 class="faculty-name"><?= e($dean['name']) ?></h3>
                        <div class="faculty-position">OIC - DEAN, SCHOOL OF CRIMINAL JUSTICE</div>
                        <div class="faculty-details">
                            <?php if (!empty($dean['specialization'])): ?>
                                <p><strong>Specialization:</strong> <?= e($dean['specialization']) ?></p>
                            <?php endif; ?>
                            <p style="margin-top:6px; font-size:0.75rem; color:var(--color-text-muted);">
                                <i class="fa-solid fa-envelope"></i> <?= e($dean['email']) ?>
                            </p>
                        </div>
                        <div style="margin-top:14px;">
                            <a href="<?= base_url('faculty.php') ?>" class="btn-primary" style="padding:6px 16px; font-size:0.78rem;">
                                View Full Faculty Hierarchy &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Featured Faculty Cards -->
            <div class="featured-faculty-grid">
                <?php foreach ($featuredFaculty as $ff): ?>
                    <div class="faculty-sub-card">
                        <div class="faculty-sub-avatar">
                            <img src="<?= base_url('assets/images/avatar_placeholder.svg') ?>" alt="<?= e($ff['name']) ?>">
                        </div>
                        <div class="faculty-sub-info">
                            <h4><?= e($ff['name']) ?></h4>
                            <div class="faculty-sub-position"><?= e($ff['position']) ?></div>
                            <?php if (!empty($ff['specialization'])): ?>
                                <div class="faculty-sub-spec"><strong>Specialization:</strong> <?= e($ff['specialization']) ?></div>
                            <?php endif; ?>
                            <div style="margin-top:8px;">
                                <a href="<?= base_url('faculty.php') ?>" class="table-toolbar-link" style="font-size:0.75rem;">
                                    View Faculty Profile &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- SECTION 4 SUMMARY: CONTACT & INQUIRIES BANNER             -->
    <!-- ========================================================= -->
    <section class="section-wrapper section-alt" style="border-top: 1px solid var(--color-border);">
        <div class="container">
            <div class="cta-banner-card">
                <div style="max-width:680px;">
                    <span class="hero-badge">
                        <i class="fa-solid fa-comments"></i> Connect with SCJE
                    </span>
                    <h3 class="cta-banner-title">
                        Have Questions or Inquiries for the Dean's Office?
                    </h3>
                    <p class="cta-banner-desc">
                        Reach out directly to the Office of the Dean, Criminology Department Chairperson, or Laboratory Supervisors for enrollment, academic advising, and laboratory facility schedules.
                    </p>
                </div>
                <div>
                    <a href="<?= base_url('contact.php') ?>" class="btn-primary" style="padding:14px 28px; font-size:0.95rem;">
                        <i class="fa-solid fa-paper-plane"></i> Contact Us Now &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

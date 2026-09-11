<?php
/**
 * About SCJE Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'about';
$pageTitle = 'About Us | School of Criminal Justice Education - DWCC';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();
$siteContent = Cache::remember('site_content', 3600, function() use ($pdo) {
    $contentStmt = $pdo->query("SELECT section_key, title, content FROM site_content");
    $content = [];
    while ($row = $contentStmt->fetch()) {
        $content[$row['section_key']] = $row;
    }
    return $content;
});
?>

    <!-- Page Banner -->
    <div style="background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #1E3A8A 100%); color: #FFFFFF; padding: 40px 0; border-bottom: 3px solid var(--color-primary-accent);">
        <div class="container">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:var(--radius-sm); background:rgba(56,189,248,0.2); border:1px solid var(--color-primary-accent); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--color-primary-accent);">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div>
                    <h2 style="font-size:1.85rem; font-weight:900; letter-spacing:1px; text-transform:uppercase;">About the School</h2>
                    <p style="color:#94A3B8; font-size:0.9rem;">History, Vision, Mission, Goals, and Academic Excellence in Criminological Education</p>
                </div>
            </div>
        </div>
    </div>

    <!-- History of SCJ Section (from Sketch Image 2) -->
    <section id="history" class="section-wrapper" style="background:#FFFFFF;">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-book-bookmark"></i> <?= e($siteContent['history']['title'] ?? 'HISTORY OF SCJ') ?></h2>
                <span style="font-size:0.85rem; color:#FEF08A; font-weight:700;">DWCC Academic Foundation</span>
            </div>

            <div style="background:#F8FAFC; border:1px solid #CBD5E1; border-radius:var(--radius-md); padding:32px; border-left:6px solid var(--color-primary-light); box-shadow:var(--shadow-card); margin-bottom:35px;">
                <p style="font-size:1rem; line-height:1.85; color:#334155; white-space:pre-line;">
                    <?= e($siteContent['history']['content'] ?? 'The School of Criminal Justice Education (SCJE) at Divine Word College of Calapan (DWCC) has been a steadfast pillar of academic rigor, professional integrity, and moral formation in Oriental Mindoro and the MIMAROPA region.') ?>
                </p>
            </div>

            <!-- Milestones Grid -->
            <div class="about-pillars-grid">
                <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:var(--radius-md); padding:20px; text-align:center;">
                    <div style="font-size:2rem; font-weight:900; color:var(--color-primary);"><i class="fa-solid fa-award"></i></div>
                    <h4 style="font-weight:800; margin:8px 0 4px; color:var(--color-primary-dark);">PACUCOA Level II</h4>
                    <p style="font-size:0.82rem; color:#475569;">Re-Accredited Criminology Academic Program guaranteeing high instructional standards.</p>
                </div>
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:var(--radius-md); padding:20px; text-align:center;">
                    <div style="font-size:2rem; font-weight:900; color:#D97706;"><i class="fa-solid fa-star"></i></div>
                    <h4 style="font-weight:800; margin:8px 0 4px; color:#92400E;">PRC Board Excellence</h4>
                    <p style="font-size:0.82rem; color:#78350F;">Consistently higher-than-national-average passing rate in Criminologist Licensure Examinations.</p>
                </div>
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:var(--radius-md); padding:20px; text-align:center;">
                    <div style="font-size:2rem; font-weight:900; color:#059669;"><i class="fa-solid fa-microscope"></i></div>
                    <h4 style="font-weight:800; margin:8px 0 4px; color:#065F46;">Modern Laboratories</h4>
                    <p style="font-size:0.82rem; color:#047857;">State-of-the-art ballistic comparison, dactyloscopy, polygraph, and crime scene facilities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Institutional Pillars (Vision, Mission, Goals) -->
    <section id="pillars" class="pillars-section" style="background:#F1F5F9; border-top:1px solid #E2E8F0; border-bottom:1px solid #E2E8F0;">
        <div class="container">
            <div class="section-header-banner" style="margin-bottom:24px;">
                <h2><i class="fa-solid fa-compass"></i> Institutional Framework</h2>
                <span style="font-size:0.85rem; color:#FEF08A; font-weight:700;">Vision &bull; Mission &bull; Goals</span>
            </div>

            <div class="pillars-grid">
                <!-- Vision -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-eye"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['vision']['title'] ?? 'VISION') ?></h3>
                        <p><?= nl2br(e($siteContent['vision']['content'] ?? 'A leading center of excellence in criminology and criminal justice education.')) ?></p>
                    </div>
                </div>

                <!-- Mission -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['mission']['title'] ?? 'MISSION') ?></h3>
                        <p><?= nl2br(e($siteContent['mission']['content'] ?? 'To provide quality education, research, and professional training for a safer and just society.')) ?></p>
                    </div>
                </div>

                <!-- Goals -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['goals']['title'] ?? 'GOALS') ?></h3>
                        <p><?= nl2br(e($siteContent['goals']['content'] ?? 'To produce competent, ethical, and service-oriented criminology professionals.')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academic Programs Section -->
    <section id="programs" class="section-wrapper" style="background:#FFFFFF;">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-graduation-cap"></i> Academic Programs Offered</h2>
                <span style="font-size:0.85rem; color:#FEF08A; font-weight:700;">Undergraduate Curriculum</span>
            </div>

            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius-md); padding:28px;">
                <h3 style="color:var(--color-primary-dark); font-size:1.3rem; font-weight:800; margin-bottom:8px;">
                    Bachelor of Science in Criminology (BS Crim)
                </h3>
                <p style="font-size:0.92rem; color:#475569; margin-bottom:20px; line-height:1.6;">
                    A four-year degree program designed to provide students with the knowledge, skills, and values required for effective criminal justice administration, law enforcement, scientific crime investigation, forensic analysis, institutional corrections, and crime prevention.
                </p>

                <div class="about-highlights-grid">
                    <div style="background:#FFFFFF; padding:16px; border-radius:var(--radius-sm); border:1px solid #E2E8F0;">
                        <h4 style="font-weight:700; color:var(--color-primary); margin-bottom:6px;"><i class="fa-solid fa-shield-halved"></i> Major Discipline Areas</h4>
                        <ul style="font-size:0.85rem; color:#475569; padding-left:18px; line-height:1.6;">
                            <li>Criminal Law, Jurisprudence &amp; Procedure</li>
                            <li>Law Enforcement Administration (LEA) &amp; Police Patrol</li>
                            <li>Criminalistics &amp; Forensic Science Technologies</li>
                            <li>Crime Detection &amp; Criminal Investigation (CDI)</li>
                            <li>Correctional Administration &amp; Penology</li>
                            <li>Criminological Theories &amp; Victimology</li>
                        </ul>
                    </div>

                    <div style="background:#FFFFFF; padding:16px; border-radius:var(--radius-sm); border:1px solid #E2E8F0;">
                        <h4 style="font-weight:700; color:var(--color-primary); margin-bottom:6px;"><i class="fa-solid fa-briefcase"></i> Career Pathways</h4>
                        <ul style="font-size:0.85rem; color:#475569; padding-left:18px; line-height:1.6;">
                            <li>Philippine National Police (PNP) Commissioned &amp; Non-Commissioned Officers</li>
                            <li>National Bureau of Investigation (NBI) Special Agents</li>
                            <li>Bureau of Jail Management and Penology (BJMP) Officers</li>
                            <li>Bureau of Fire Protection (BFP) Arson Investigators</li>
                            <li>Forensic Science Specialists &amp; Crime Laboratory Analysts</li>
                            <li>Industrial Security &amp; Cyber Threat Intelligence Managers</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

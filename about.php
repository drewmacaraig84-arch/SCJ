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
    <div class="page-banner">
        <div class="container page-banner-inner">
            <div class="page-banner-icon">
                <i class="fa-solid fa-landmark"></i>
            </div>
            <div class="page-banner-content">
                <h2>About the School</h2>
                <p>Vision, Mission, Goals, and Academic Excellence in Criminological Education</p>
            </div>
        </div>
    </div>


    <!-- Institutional Pillars (Vision, Mission, Goals) -->
    <section id="pillars" class="pillars-section section-wrapper">
        <div class="container">
            <div class="section-header-banner" style="margin-bottom:24px;">
                <h2><i class="fa-solid fa-compass"></i> Institutional Framework</h2>
                <span class="badge badge-primary">Vision &bull; Mission &bull; Goals</span>
            </div>

            <div class="pillars-grid">
                <!-- Vision -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-eye"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['vision']['title'] ?? 'VISION') ?></h3>
                        <p><?= nl2br(e($siteContent['vision']['content'] ?? 'To become the center and primer in the pursuit of quality instruction in the field of Criminal Justice in the entire Mindoro Region.')) ?></p>
                    </div>
                </div>

                <!-- Mission -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['mission']['title'] ?? 'MISSION') ?></h3>
                        <p><?= nl2br(e($siteContent['mission']['content'] ?? 'To produce professionally competent and morally upright graduates equipped with contemporary and functional knowledge and skills in the field of law enforcement administration, crime detection and investigation, correctional administration, criminal sociology and forensic science.')) ?></p>
                    </div>
                </div>

                <!-- Goals -->
                <div class="pillar-card">
                    <div class="pillar-icon-badge"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="pillar-text">
                        <h3><?= e($siteContent['goals']['title'] ?? 'GOALS') ?></h3>
                        <p><?= nl2br(e($siteContent['goals']['content'] ?? "• Foster the value of god-fearing, social responsibility, self-sacrifice and discipline;\n• Provide students with theoretical, technical, practical and actual knowledge relative to criminology profession; and\n• Prepare students for careers in any agencies under the Philippine Criminal Justice System")) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academic Programs Section -->
    <section id="programs" class="section-wrapper section-alt">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-graduation-cap"></i> Academic Programs Offered</h2>
                <span class="badge badge-primary">Undergraduate Curriculum</span>
            </div>

            <div class="academic-program-card">
                <h3 class="academic-program-title">
                    Bachelor of Science in Criminology (BS Crim)
                </h3>
                <p class="academic-program-desc">
                    A four-year degree program designed to provide students with the knowledge, skills, and values required for effective criminal justice administration, law enforcement, scientific crime investigation, forensic analysis, institutional corrections, and crime prevention.
                </p>

                <div class="about-highlights-grid">
                    <div class="academic-pathway-card">
                        <h4><i class="fa-solid fa-shield-halved"></i> Major Discipline Areas</h4>
                        <ul>
                            <li>Criminal Law, Jurisprudence &amp; Procedure</li>
                            <li>Law Enforcement Administration (LEA) &amp; Police Patrol</li>
                            <li>Criminalistics &amp; Forensic Science Technologies</li>
                            <li>Crime Detection &amp; Criminal Investigation (CDI)</li>
                            <li>Correctional Administration &amp; Penology</li>
                            <li>Criminological Theories &amp; Victimology</li>
                        </ul>
                    </div>

                    <div class="academic-pathway-card">
                        <h4><i class="fa-solid fa-briefcase"></i> Career Pathways</h4>
                        <ul>
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

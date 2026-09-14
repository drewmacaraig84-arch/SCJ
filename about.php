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
                <p>History, Vision, Mission, Goals, and Academic Excellence in Criminological Education</p>
            </div>
        </div>
    </div>

    <!-- History of SCJ Section (from Official Institutional Slides) -->
    <section id="history" class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-book-bookmark"></i> <?= e($siteContent['history']['title'] ?? 'HISTORY OF SCJ') ?></h2>
                <span class="badge badge-gold"><i class="fa-solid fa-clock-rotate-left"></i> Institutional Journey &bull; 2009 to Present</span>
            </div>


            <!-- Chronological Milestones Timeline (Matching 4 Official Slides) -->
            <div class="history-timeline">
                <!-- Milestone 1: 2009 -->
                <div class="timeline-item">
                    <div class="timeline-badge"><i class="fa-solid fa-seedling"></i></div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <div class="timeline-year"><i class="fa-solid fa-calendar-check"></i> 2009</div>
                            <span class="badge badge-secondary">Academic Inception</span>
                        </div>
                        <div class="timeline-title">Introduction of Criminology under Liberal Arts</div>
                        <ul class="timeline-points">
                            <li>Offered in Divine Word College of Calapan (DWCC) under the Liberal Arts Department.</li>
                            <li>Formally headed and organized by <strong>Mr. Dennis S. Alcaraz</strong>.</li>
                        </ul>
                    </div>
                </div>

                <!-- Milestone 2: 2012 -->
                <div class="timeline-item">
                    <div class="timeline-badge"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <div class="timeline-year"><i class="fa-solid fa-calendar-check"></i> 2012</div>
                            <span class="badge badge-primary">Departmental Independence</span>
                        </div>
                        <div class="timeline-title">Separation into an Independent Criminology Department</div>
                        <ul class="timeline-points">
                            <li>Separated and placed under an independent academic department called the <strong>Criminology Department</strong>.</li>
                            <li>Headed by <strong>Ms. Janenovelle A. Cuenca</strong> as Department Head.</li>
                        </ul>
                    </div>
                </div>

                <!-- Milestone 3: 2013 -->
                <div class="timeline-item">
                    <div class="timeline-badge"><i class="fa-solid fa-award"></i></div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <div class="timeline-year"><i class="fa-solid fa-calendar-check"></i> 2013</div>
                            <span class="badge badge-gold">100% Board Passing Precedent</span>
                        </div>
                        <div class="timeline-title">First Batch Triumph, Regional Leadership &amp; In-House Board Review</div>
                        <ul class="timeline-points">
                            <li><strong>Ms. Janenovelle A. Cuenca</strong> was appointed as one of the members of the Regional Selection and Screening Committee for applicants of the Philippine National Police (PNP) in MIMAROPA.</li>
                            <li>DWCC Criminology Department pioneered and offered its own <strong>in-house and formal review</strong> program for the licensure board examination.</li>
                            <li>The first batch of graduates took the Criminology Board Examination and set an extraordinary precedent passing rate of <strong>100%</strong>.</li>
                            <li>The Criminology Department established a sustained yearly board examination passing rate far exceeding the national passing rate.</li>
                            <li>On various occasions, recognized as the <strong>Number One (#1) school of criminology in the province</strong> and one among the best in the entire MIMAROPA region for board examination performance.</li>
                            <li>Actively hosted and participated in several prestigious international and local criminology seminars.</li>
                        </ul>
                    </div>
                </div>

                <!-- Milestone 4: 2019 to Present -->
                <div class="timeline-item">
                    <div class="timeline-badge"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <div class="timeline-year"><i class="fa-solid fa-calendar-check"></i> 2019 &ndash; Present</div>
                            <span class="badge badge-success">&gt;90% Employment Rate</span>
                        </div>
                        <div class="timeline-title">Elevation to School of Criminal Justice (SCJ) &amp; Sustained Expansion</div>
                        <ul class="timeline-points">
                            <li>Formally renamed and elevated to the <strong>School of Criminal Justice</strong> in compliance with the mandate of the Commission on Higher Education (CHED), preparing for the offering of additional allied criminological programs.</li>
                            <li><strong>Present</strong> &ndash; <strong>More than 90%</strong> of graduates are employed in stable professional jobs, with the majority serving in law enforcement agencies, primarily the <strong>Philippine National Police (PNP)</strong>, BJMP, BFP, and security administration.</li>
                            <li>Keeps on expanding with a dramatic increase in the population of its students every school year as the premier center of criminal justice education.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Historical Milestone Key Performance Indicators -->
            <div class="history-kpi-grid">
                <div class="history-kpi-card">
                    <div class="history-kpi-val" style="color:var(--color-gold-deep);">100%</div>
                    <div class="history-kpi-label">First Batch Passing Rate</div>
                    <div class="history-kpi-desc">Historic 100% precedent set by the pioneering graduate batch in the PRC Criminologist Licensure Examination.</div>
                </div>
                <div class="history-kpi-card">
                    <div class="history-kpi-val" style="color:var(--color-primary);">&gt;90%</div>
                    <div class="history-kpi-label">Graduate Employment</div>
                    <div class="history-kpi-desc">Over 90% of graduates gainfully employed in stable careers across the PNP, BJMP, BFP, and law enforcement.</div>
                </div>
                <div class="history-kpi-card">
                    <div class="history-kpi-val" style="color:#0F766E;">#1</div>
                    <div class="history-kpi-label">Provincial &amp; Regional Pride</div>
                    <div class="history-kpi-desc">Consistently outperforming national passing rates as the top school of criminology in Oriental Mindoro and MIMAROPA.</div>
                </div>
            </div>
        </div>
    </section>

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

<?php
/**
 * Research Repository & Criminological Studies Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'research';
$pageTitle = 'Criminological Research | SCJE Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();
$researches = Cache::remember('all_researches', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM research ORDER BY id DESC")->fetchAll();
});

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
?>

    <!-- Page Banner -->
    <div style="background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #1E3A8A 100%); color: #FFFFFF; padding: 40px 0; border-bottom: 3px solid var(--color-primary-accent);">
        <div class="container">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:var(--radius-sm); background:rgba(56,189,248,0.2); border:1px solid var(--color-primary-accent); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--color-primary-accent);">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <h2 style="font-size:1.85rem; font-weight:900; letter-spacing:1px; text-transform:uppercase;">Criminological Research</h2>
                    <p style="color:#94A3B8; font-size:0.9rem;">Empirical Studies, Theses, and Forensics Research Repository</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Research Section -->
    <section class="section-wrapper">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Browse by Criminological Field</h2>
                <div class="section-search-box">
                    <i class="fa-solid fa-search search-icon"></i>
                    <input type="text" id="topResearchSearch" placeholder="Search research titles...">
                </div>
            </div>

            <!-- 16 Categories Grid (Reference Screenshot) -->
            <div class="research-categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <div class="category-card" data-category-name="<?= e($cat['name']) ?>">
                        <div class="category-icon"><i class="fa-solid <?= $cat['icon'] ?>"></i></div>
                        <div class="category-title"><?= e($cat['name']) ?></div>
                        <div class="category-link">View Titles <i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ===================================================== -->
            <!-- RESEARCH DATA TABLE (EXACT MATCH TO SKETCH IMAGE 2)  -->
            <!-- ===================================================== -->
            <div id="table" class="table-card">
                <div class="table-toolbar">
                    <div class="table-toolbar-left">
                        <label for="researchCategoryFilter"><i class="fa-solid fa-filter"></i> Field:</label>
                        <select id="researchCategoryFilter" class="filter-select">
                            <option value="all">All Criminological Disciplines</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sketch Search Box: SEARCH: [______] -->
                    <div class="sketch-search-box">
                        <span>SEARCH:</span>
                        <input type="text" id="researchSearchInput" class="sketch-search-input" placeholder="Search by name, title, date...">
                    </div>
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
                        <tbody id="researchTableBody">
                            <?php if (empty($researches)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:30px; color:#64748B;">No research records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($researches as $r): ?>
                                    <tr class="research-row" 
                                        data-author="<?= strtolower(e($r['author_name'])) ?>" 
                                        data-title="<?= strtolower(e($r['research_title'])) ?>" 
                                        data-category="<?= strtolower(e($r['category'])) ?>" 
                                        data-date="<?= strtolower(e($r['month_year'])) ?>">
                                        <td style="font-weight:700; color:var(--color-primary-dark);">
                                            <i class="fa-solid fa-user-pen" style="color:var(--color-primary-light); margin-right:6px;"></i>
                                            <?= e($r['author_name']) ?>
                                        </td>
                                        <td>
                                            <span style="font-weight:600; color:#1E293B;"><?= e($r['research_title']) ?></span>
                                            <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:2px;">
                                                <span class="badge badge-info"><?= e($r['category']) ?></span>
                                            </div>
                                        </td>
                                        <td style="font-weight:600; color:#475569;"><?= e($r['month_year']) ?></td>
                                        <td style="text-align:center;">
                                            <button type="button" class="btn-primary btn-view-research" 
                                                    style="padding:5px 12px; font-size:0.75rem;"
                                                    data-title="<?= e($r['research_title']) ?>"
                                                    data-author="<?= e($r['author_name']) ?>"
                                                    data-date="<?= e($r['month_year']) ?>"
                                                    data-category="<?= e($r['category']) ?>"
                                                    data-abstract="<?= e($r['abstract']) ?>"
                                                    data-keywords="<?= e($r['keywords']) ?>">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="researchNoDataRow" style="display:none;">
                                <td colspan="4" style="text-align:center; padding:25px; color:#64748B;">
                                    <i class="fa-solid fa-circle-exclamation" style="color:var(--color-warning);"></i> No matching research papers found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

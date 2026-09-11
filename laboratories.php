<?php
/**
 * Criminology Laboratories & Equipment Inventory Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'laboratories';
$pageTitle = 'Laboratories & Equipment | SCJE Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();
$equipments = Cache::remember('all_equipment', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM equipment ORDER BY id ASC")->fetchAll();
});
?>

    <!-- Page Banner -->
    <div style="background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #1E3A8A 100%); color: #FFFFFF; padding: 40px 0; border-bottom: 3px solid var(--color-primary-accent);">
        <div class="container">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:var(--radius-sm); background:rgba(56,189,248,0.2); border:1px solid var(--color-primary-accent); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--color-primary-accent);">
                    <i class="fa-solid fa-flask"></i>
                </div>
                <div>
                    <h2 style="font-size:1.85rem; font-weight:900; letter-spacing:1px; text-transform:uppercase;">Criminology Laboratories</h2>
                    <p style="color:#94A3B8; font-size:0.9rem;">Specialized Forensic Facilities &amp; Equipment Inventory</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laboratories Section -->
    <section class="section-wrapper" style="background:#F1F5F9;">
        <div class="container">
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-flask-vial"></i> Specialized Forensic Facilities</h2>
                <span style="font-size:0.85rem; color:#FEF08A; font-weight:700;">5 Dedicated Laboratory Suites</span>
            </div>

            <!-- 5 Specialized Laboratory Cards (From Reference Screenshot) -->
            <div class="laboratories-visual-grid">
                <!-- Card 1: Criminalistics Laboratory -->
                <div class="lab-card" data-lab-name="Criminalistics Laboratory">
                    <div class="lab-card-image">
                        <img src="<?= base_url('assets/images/lab_criminalistics.svg') ?>" alt="Criminalistics Laboratory">
                    </div>
                    <div class="lab-card-body">
                        <div class="lab-card-title">Criminalistics Laboratory</div>
                        <ul class="lab-card-list">
                            <li>Fingerprint examination</li>
                            <li>Questioned documents</li>
                            <li>Forensic photography</li>
                            <li>Evidence collection</li>
                            <li>Toolmark examination</li>
                        </ul>
                    </div>
                </div>

                <!-- Card 2: Crime Scene Investigation Laboratory -->
                <div class="lab-card" data-lab-name="Crime Scene Investigation Laboratory">
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
                </div>

                <!-- Card 3: Forensic Science Laboratory -->
                <div class="lab-card" data-lab-name="Forensic Science Laboratory">
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
                </div>

                <!-- Card 4: Forensic Ballistics -->
                <div class="lab-card" data-lab-name="Forensic Ballistics">
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
                </div>

                <!-- Card 5: Other Specialized Areas -->
                <div class="lab-card" data-lab-name="Other Specialized Areas">
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
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- LABORATORY EQUIPMENT INVENTORY TABLE (SKETCH IMAGE 1) -->
            <!-- ===================================================== -->
            <div id="table" class="table-card">
                <div class="table-toolbar">
                    <div class="table-toolbar-left">
                        <label for="equipCategoryFilter"><i class="fa-solid fa-filter"></i> Lab Facility:</label>
                        <select id="equipCategoryFilter" class="filter-select">
                            <option value="all">All Laboratory Facilities</option>
                            <option value="Criminalistics Laboratory">Criminalistics Laboratory</option>
                            <option value="Crime Scene Investigation Laboratory">Crime Scene Investigation Laboratory</option>
                            <option value="Forensic Science Laboratory">Forensic Science Laboratory</option>
                            <option value="Forensic Ballistics">Forensic Ballistics</option>
                            <option value="Other Specialized Areas">Other Specialized Areas</option>
                        </select>
                    </div>

                    <!-- Sketch Search Box: SEARCH: [______] -->
                    <div class="sketch-search-box">
                        <span>SEARCH:</span>
                        <input type="text" id="equipSearchInput" class="sketch-search-input" placeholder="Search equipment code, brand, model...">
                    </div>
                </div>

                <div class="custom-table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 18%;">Equipment Code</th>
                                <th style="width: 28%;">Equipment Name</th>
                                <th style="width: 18%;">BRAND</th>
                                <th style="width: 16%;">MODEL</th>
                                <th style="width: 20%;">CURRENT LOCATION</th>
                            </tr>
                        </thead>
                        <tbody id="equipTableBody">
                            <?php if (empty($equipments)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px; color:#64748B;">No equipment records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($equipments as $eq): ?>
                                    <tr class="equip-row"
                                        data-code="<?= strtolower(e($eq['equipment_code'])) ?>"
                                        data-name="<?= strtolower(e($eq['equipment_name'])) ?>"
                                        data-brand="<?= strtolower(e($eq['brand'])) ?>"
                                        data-model="<?= strtolower(e($eq['model'])) ?>"
                                        data-location="<?= strtolower(e($eq['current_location'])) ?>"
                                        data-lab="<?= strtolower(e($eq['laboratory_category'])) ?>">
                                        <td style="font-family:monospace; font-weight:800; color:var(--color-primary-light);">
                                            <?= e($eq['equipment_code']) ?>
                                        </td>
                                        <td style="font-weight:700; color:#1E293B;">
                                            <a href="javascript:void(0)" class="btn-view-equip"
                                               data-code="<?= e($eq['equipment_code']) ?>"
                                               data-name="<?= e($eq['equipment_name']) ?>"
                                               data-brand="<?= e($eq['brand']) ?>"
                                               data-model="<?= e($eq['model']) ?>"
                                               data-location="<?= e($eq['current_location']) ?>"
                                               data-status="<?= e($eq['status']) ?>"
                                               data-serial="<?= e($eq['serial_number']) ?>"
                                               data-desc="<?= e($eq['description']) ?>">
                                                <?= e($eq['equipment_name']) ?>
                                            </a>
                                        </td>
                                        <td style="font-weight:600;"><?= e($eq['brand']) ?></td>
                                        <td><?= e($eq['model']) ?></td>
                                        <td style="color:#475569;">
                                            <i class="fa-solid fa-location-dot" style="color:var(--color-danger); margin-right:4px;"></i>
                                            <?= e($eq['current_location']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="equipNoDataRow" style="display:none;">
                                <td colspan="5" style="text-align:center; padding:25px; color:#64748B;">
                                    <i class="fa-solid fa-circle-exclamation" style="color:var(--color-warning);"></i> No matching equipment found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Criminology Laboratories & Equipment Inventory Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'laboratories';
$pageTitle = 'Laboratories & Inventory Dashboard | SCJE Information System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cache.php';

$pdo = get_db();

// 1. Fetch Official Equipment (AY 2025-2026: 18 items)
$equipments = Cache::remember('all_equipment', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM equipment ORDER BY id ASC")->fetchAll();
});

// 2. Fetch Official Materials & Chemicals (AY 2025-2026: 64 items)
$materials = Cache::remember('all_materials', 3600, function() use ($pdo) {
    return $pdo->query("SELECT * FROM materials_chemicals ORDER BY id ASC")->fetchAll();
});

// Calculate metrics
$totalEquip = count($equipments);
$totalMat = count($materials);
$totalAssets = $totalEquip + $totalMat;
$equipFunctional = 0;
foreach ($equipments as $eq) {
    if (strtolower($eq['status']) !== 'out of service') {
        $equipFunctional++;
    }
}
$readinessRate = $totalEquip > 0 ? round(($equipFunctional / $totalEquip) * 100, 1) : 100;
?>

    <!-- Page Banner -->
    <div class="page-banner">
        <div class="container page-banner-inner">
            <div class="page-banner-icon">
                <i class="fa-solid fa-flask"></i>
            </div>
            <div class="page-banner-content">
                <h2>Criminology Laboratories &amp; Inventory</h2>
                <p>Specialized Forensic Facilities • Equipment &amp; Reagents Directory (AY 2025-2026)</p>
            </div>
        </div>
    </div>

    <!-- Laboratories Section -->
    <section class="section-wrapper">
        <div class="container">

            <!-- ===================================================== -->
            <!-- INVENTORY SUMMARY DASHBOARD - AY 2025-2026            -->
            <!-- ===================================================== -->
            <div class="inventory-summary-banner">
                <div class="inventory-banner-header">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div class="inventory-banner-icon">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div>
                            <div style="font-size:0.8rem; font-weight:800; color:var(--color-gold); letter-spacing:1px; text-transform:uppercase;">School of Criminal Justice Education</div>
                            <h3 class="inventory-banner-title">Inventory Summary Dashboard — AY 2025-2026</h3>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-success" style="font-size:0.85rem; padding:8px 14px;">
                            <i class="fa-solid fa-check-double"></i> Active Audit Certified
                        </span>
                    </div>
                </div>

                <!-- KPI Metric Cards -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
                    <!-- Metric 1: Materials & Chemicals -->
                    <div class="lab-kpi-card" style="border-left:4px solid #0284C7;">
                        <div class="lab-kpi-label">Materials &amp; Chemicals</div>
                        <div class="lab-kpi-val" style="color:#0284C7;"><?= $totalMat ?></div>
                        <div class="lab-kpi-sub"><i class="fa-solid fa-vial"></i> Glassware, reagents &amp; consumables</div>
                    </div>

                    <!-- Metric 2: Equipment Inventory -->
                    <div class="lab-kpi-card" style="border-left:4px solid #D97706;">
                        <div class="lab-kpi-label">Equipment Inventory</div>
                        <div class="lab-kpi-val" style="color:#D97706;"><?= $totalEquip ?></div>
                        <div class="lab-kpi-sub"><i class="fa-solid fa-microscope"></i> Balances, polygraph, optics, kits</div>
                    </div>

                    <!-- Metric 3: Total Assets -->
                    <div class="lab-kpi-card" style="border-left:4px solid #16A34A;">
                        <div class="lab-kpi-label">Total Tracked Records</div>
                        <div class="lab-kpi-val" style="color:#16A34A;"><?= $totalAssets ?></div>
                        <div class="lab-kpi-sub"><i class="fa-solid fa-boxes-stacked"></i> Combined departmental assets</div>
                    </div>

                    <!-- Metric 4: Accountability -->
                    <div class="lab-kpi-card" style="border-left:4px solid #4F46E5;">
                        <div class="lab-kpi-label">Accountable Officer</div>
                        <div class="lab-kpi-val" style="font-size:1.35rem; color:#4F46E5; margin:6px 0;">Sir Jom</div>
                        <div class="lab-kpi-sub"><i class="fa-solid fa-user-shield"></i> Laboratory Custodian &amp; Officer</div>
                    </div>
                </div>
            </div>

            <!-- Specialized Forensic Facilities -->
            <div class="section-header-banner">
                <h2><i class="fa-solid fa-flask-vial"></i> Specialized Forensic Facilities</h2>
                <span class="badge badge-primary">5 Dedicated Laboratory Suites</span>
            </div>

            <!-- 5 Specialized Laboratory Cards -->
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
                            <li>Documentation techniques</li>
                            <li>Chain of custody custody</li>
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
                            <li>Chemical analysis</li>
                            <li>Microscopic analysis</li>
                            <li>Blood presumptive testing</li>
                            <li>Biological fluid analysis</li>
                            <li>Toxicology testing</li>
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
                            <li>Firearms examination</li>
                            <li>Fired bullet examination</li>
                            <li>Fired shell examination</li>
                            <li>Bullet trajectory</li>
                            <li>Gunpowder residue</li>
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
                            <li>Forensic odontology</li>
                            <li>Accident reconstruction</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- DUAL-TABBED INVENTORY DIRECTORY (EQUIPMENT & MATERIALS) -->
            <!-- ===================================================== -->
            <div id="inventoryDirectory" class="table-card" style="margin-top:40px;">
                
                <!-- Category Switcher Tabs -->
                <div class="inventory-tabs-header">
                    <button type="button" id="tabBtnEquip" class="inventory-tab-btn active" onclick="switchInventoryTab('equip')">
                        <i class="fa-solid fa-microscope"></i> Equipment Inventory (<?= $totalEquip ?>)
                    </button>
                    <button type="button" id="tabBtnMat" class="inventory-tab-btn" onclick="switchInventoryTab('materials')">
                        <i class="fa-solid fa-flask-vial"></i> Materials &amp; Chemicals (<?= $totalMat ?>)
                    </button>
                </div>

                <!-- ------------------------------------------------- -->
                <!-- PANE 1: EQUIPMENT INVENTORY (18 ITEMS)            -->
                <!-- ------------------------------------------------- -->
                <div id="equipPane" style="display:block;">
                    <div class="table-toolbar">
                        <div class="table-toolbar-left">
                            <label for="equipCategoryFilter"><i class="fa-solid fa-filter"></i> Lab Facility:</label>
                            <select id="equipCategoryFilter" class="filter-select">
                                <option value="all">All Laboratory Facilities</option>
                                <option value="Crime Lab">Crime Lab</option>
                                <option value="Forensic Photography Room">Forensic Photography Room</option>
                                <option value="Fingerprint Room">Fingerprint Room</option>
                                <option value="Polygraphy Room">Polygraphy Room</option>
                                <option value="Dean’s Office">Dean’s Office</option>
                            </select>
                        </div>

                        <!-- Sketch Search Box: SEARCH: [______] -->
                        <div class="sketch-search-box">
                            <span>SEARCH:</span>
                            <input type="text" id="equipSearchInput" class="sketch-search-input" placeholder="Search code, name, brand, model...">
                        </div>
                    </div>

                    <div class="custom-table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 14%;">Equipment Code</th>
                                    <th style="width: 26%;">Equipment Name</th>
                                    <th style="width: 16%;">BRAND</th>
                                    <th style="width: 12%;">MODEL</th>
                                    <th style="width: 12%;">STATUS</th>
                                    <th style="width: 20%;">CURRENT LOCATION</th>
                                </tr>
                            </thead>
                            <tbody id="equipTableBody">
                                <?php if (empty($equipments)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:30px; color:#64748B;">No equipment records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($equipments as $eq): 
                                        $stat = trim($eq['status'] ?? 'Good Condition');
                                        $statLower = strtolower($stat);
                                        $badgeClass = 'badge-success';
                                        if (str_contains($statLower, 'out of service')) {
                                            $badgeClass = 'badge-danger';
                                        } elseif (str_contains($statLower, 'brand')) {
                                            $badgeClass = 'badge-info';
                                        }
                                    ?>
                                        <tr class="equip-row"
                                            data-code="<?= strtolower(e($eq['equipment_code'])) ?>"
                                            data-name="<?= strtolower(e($eq['equipment_name'])) ?>"
                                            data-brand="<?= strtolower(e($eq['brand'])) ?>"
                                            data-model="<?= strtolower(e($eq['model'])) ?>"
                                            data-location="<?= strtolower(e($eq['current_location'])) ?>"
                                            data-status="<?= strtolower(e($stat)) ?>">
                                            <td style="font-family:var(--font-mono); font-weight:800; color:var(--color-gold);">
                                                <?= e($eq['equipment_code']) ?>
                                            </td>
                                            <td style="font-weight:700;">
                                                <a href="javascript:void(0)" class="btn-view-equip" style="color:var(--color-text-primary);"
                                                   data-code="<?= e($eq['equipment_code']) ?>"
                                                   data-name="<?= e($eq['equipment_name']) ?>"
                                                   data-brand="<?= e($eq['brand']) ?>"
                                                   data-model="<?= e($eq['model']) ?>"
                                                   data-location="<?= e($eq['current_location']) ?>"
                                                   data-status="<?= e($stat) ?>"
                                                   data-serial="<?= e($eq['serial_number'] ?? 'N/A') ?>"
                                                   data-person="<?= e($eq['person_accountable'] ?? 'Sir Jom') ?>"
                                                   data-desc="<?= e($eq['description']) ?>">
                                                    <?= e($eq['equipment_name']) ?>
                                                </a>
                                            </td>
                                            <td style="font-weight:600; color:var(--color-text-secondary);"><?= e($eq['brand']) ?></td>
                                            <td style="color:var(--color-text-secondary);"><?= e($eq['model']) ?></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= e($stat) ?></span></td>
                                            <td style="color:var(--color-text-secondary);">
                                                <i class="fa-solid fa-location-dot" style="color:var(--color-danger); margin-right:4px;"></i>
                                                <?= e($eq['current_location']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr id="equipNoDataRow" style="display:none;">
                                    <td colspan="6" style="text-align:center; padding:25px; color:#64748B;">
                                        <i class="fa-solid fa-circle-exclamation" style="color:var(--color-warning);"></i> No matching equipment found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ------------------------------------------------- -->
                <!-- PANE 2: MATERIALS & CHEMICALS (64 ITEMS)          -->
                <!-- ------------------------------------------------- -->
                <div id="materialsPane" style="display:none;">
                    <div class="table-toolbar">
                        <div class="table-toolbar-left">
                            <label for="matLocationFilter"><i class="fa-solid fa-filter"></i> Storage Location:</label>
                            <select id="matLocationFilter" class="filter-select">
                                <option value="all">All Storage Locations</option>
                                <option value="Crime Laboratory">Crime Laboratory</option>
                                <option value="Fingerprint Room">Fingerprint Room</option>
                                <option value="Forensic Photography">Forensic Photography</option>
                                <option value="Consultation Room">Consultation Room</option>
                            </select>
                        </div>

                        <!-- Search Box -->
                        <div class="sketch-search-box">
                            <span>SEARCH:</span>
                            <input type="text" id="matSearchInput" class="sketch-search-input" placeholder="Search material, reagent, brand...">
                        </div>
                    </div>

                    <div class="custom-table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">Code</th>
                                    <th style="width: 28%;">Item Name</th>
                                    <th style="width: 14%;">Qty &amp; Unit</th>
                                    <th style="width: 14%;">Brand</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 20%;">Location</th>
                                </tr>
                            </thead>
                            <tbody id="matTableBody">
                                <?php if (empty($materials)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:30px; color:#64748B;">No materials or chemicals records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($materials as $m): 
                                        $mStat = trim($m['status'] ?? 'Good Condition');
                                        $mStatLower = strtolower($mStat);
                                        $mBadgeClass = 'badge-success';
                                        if (str_contains($mStatLower, 'out of service')) {
                                            $mBadgeClass = 'badge-danger';
                                        } elseif (str_contains($mStatLower, 'brand')) {
                                            $mBadgeClass = 'badge-info';
                                        }
                                    ?>
                                        <tr class="mat-row"
                                            data-code="<?= strtolower(e($m['item_code'])) ?>"
                                            data-name="<?= strtolower(e($m['item_name'])) ?>"
                                            data-brand="<?= strtolower(e($m['brand'])) ?>"
                                            data-location="<?= strtolower(e($m['location'])) ?>"
                                            data-status="<?= strtolower(e($mStat)) ?>">
                                            <td style="font-family:var(--font-mono); font-weight:800; color:var(--color-gold);">
                                                <?= e($m['item_code']) ?>
                                            </td>
                                            <td style="font-weight:700;">
                                                <a href="javascript:void(0)" class="btn-view-material" style="color:var(--color-text-primary);"
                                                   data-code="<?= e($m['item_code']) ?>"
                                                   data-name="<?= e($m['item_name']) ?>"
                                                   data-qty="<?= e($m['qty']) ?>"
                                                   data-unit="<?= e($m['unit']) ?>"
                                                   data-brand="<?= e($m['brand']) ?>"
                                                   data-status="<?= e($mStat) ?>"
                                                   data-location="<?= e($m['location']) ?>"
                                                   data-person="<?= e($m['person_accountable'] ?? 'Sir Jom') ?>">
                                                    <?= e($m['item_name']) ?>
                                                </a>
                                            </td>
                                            <td style="font-weight:700; color:var(--color-gold);">
                                                <?= e($m['qty']) ?> <?= ($m['unit'] !== 'N/A') ? e($m['unit']) : '' ?>
                                            </td>
                                            <td style="font-weight:600; color:var(--color-text-secondary);"><?= e($m['brand']) ?></td>
                                            <td><span class="badge <?= $mBadgeClass ?>"><?= e($mStat) ?></span></td>
                                            <td style="color:var(--color-text-secondary);">
                                                <i class="fa-solid fa-location-dot" style="color:var(--color-danger); margin-right:4px;"></i>
                                                <?= e($m['location']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr id="matNoDataRow" style="display:none;">
                                    <td colspan="6" style="text-align:center; padding:25px; color:#64748B;">
                                        <i class="fa-solid fa-circle-exclamation" style="color:var(--color-warning);"></i> No matching materials or chemicals found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Tab Switching Helper Script -->
    <script>
    function switchInventoryTab(tab) {
        const equipPane = document.getElementById('equipPane');
        const matPane = document.getElementById('materialsPane');
        const btnEquip = document.getElementById('tabBtnEquip');
        const btnMat = document.getElementById('tabBtnMat');

        if (tab === 'equip') {
            equipPane.style.display = 'block';
            matPane.style.display = 'none';
            btnEquip.style.color = 'var(--color-primary)';
            btnEquip.style.borderBottom = '3px solid var(--color-primary)';
            btnEquip.style.fontWeight = '800';
            btnMat.style.color = '#64748B';
            btnMat.style.borderBottom = '3px solid transparent';
            btnMat.style.fontWeight = '700';
        } else {
            equipPane.style.display = 'none';
            matPane.style.display = 'block';
            btnMat.style.color = 'var(--color-primary)';
            btnMat.style.borderBottom = '3px solid var(--color-primary)';
            btnMat.style.fontWeight = '800';
            btnEquip.style.color = '#64748B';
            btnEquip.style.borderBottom = '3px solid transparent';
            btnEquip.style.fontWeight = '700';
        }
    }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

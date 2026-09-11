<?php
/**
 * Common Footer Component
 * School of Criminal Justice Education (SCJE) Information System
 */
?>
    </main><!-- /#appContent -->

    <!-- Universal Details Modal (Research / Equipment / Faculty) -->
    <div id="detailsModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px;">
            <div class="modal-header">
                <h3 id="detailsModalTitle">Item Details</h3>
                <button type="button" class="modal-close" id="closeDetailsModal">&times;</button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Dynamically populated via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                        <div class="logo-circle-holder" style="width:46px; height:46px; padding:3px;">
                            <img src="<?= asset_url('assets/images/dwcc_logo.png') ?>" alt="DWCC Official Seal">
                        </div>
                        <div class="logo-circle-holder" style="width:46px; height:46px; padding:3px;">
                            <img src="<?= asset_url('assets/images/scj_logo.png') ?>" alt="SCJ Department Seal">
                        </div>
                    </div>
                    <h4>School of Criminal Justice Education</h4>
                    <p style="margin-bottom: 12px;">
                        Divine Word College of Calapan (DWCC)<br>
                        Gov. Infantado St., Calapan City, 5200 Oriental Mindoro, Philippines
                    </p>
                    <p style="font-size: 0.8rem; color: #64748B;">
                        PACUCOA Level II Re-Accredited Criminology Program. PRC Board Examination Top Performing School.
                    </p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= base_url('index.php') ?>">Home Page</a></li>
                        <li><a href="<?= base_url('about.php') ?>">History of SCJ</a></li>
                        <li><a href="<?= base_url('research.php') ?>">Criminological Research</a></li>
                        <li><a href="<?= base_url('laboratories.php') ?>">Laboratory Facilities</a></li>
                        <li><a href="<?= base_url('faculty.php') ?>">Faculty &amp; Staff Directory</a></li>
                        <li><a href="<?= base_url('contact.php') ?>">Contact &amp; Inquiries</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Academic Facilities</h4>
                    <ul class="footer-links">
                        <li><a href="<?= base_url('laboratories.php') ?>">Forensic Ballistics Lab</a></li>
                        <li><a href="<?= base_url('laboratories.php') ?>">Criminalistics &amp; Dactyloscopy</a></li>
                        <li><a href="<?= base_url('laboratories.php') ?>">Crime Scene Simulation Room</a></li>
                        <li><a href="<?= base_url('laboratories.php') ?>">Polygraph Examination Suite</a></li>
                        <li><a href="<?= base_url('laboratories.php') ?>">Forensic Science Laboratory</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Divine Word College of Calapan - School of Criminal Justice Education. All Rights Reserved. Information System v2.4.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= asset_url('assets/js/main.js') ?>"></script>
    <script src="<?= asset_url('assets/js/spa-nav.js') ?>"></script>
</body>
</html>


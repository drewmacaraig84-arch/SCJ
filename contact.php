<?php
/**
 * Contact & Inquiry Page
 * School of Criminal Justice Education (SCJE) Information System
 */

$activePage = 'contact';
$pageTitle = 'Contact Us | SCJE Information System';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Banner -->
    <div class="page-banner">
        <div class="container page-banner-inner">
            <div class="page-banner-icon">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="page-banner-content">
                <h2>Contact SCJE</h2>
                <p>Dean's Office &bull; Department Directory &bull; Public Inquiries</p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="section-wrapper">
        <div class="container">
            <div class="resources-contact-grid">
                <!-- Left: Contact Details & Office Information -->
                <div>
                    <div class="section-header-banner" style="margin-bottom:20px;">
                        <h2><i class="fa-solid fa-building-columns"></i> Office of the Dean</h2>
                    </div>

                    <div class="contact-details-box">
                        <h3>
                            School of Criminal Justice Education
                        </h3>
                        <p>
                            Divine Word College of Calapan (DWCC)<br>
                            Gov. Infantado St., Calapan City, 5200 Oriental Mindoro, Philippines
                        </p>

                        <div style="display:flex; flex-direction:column; gap:14px; font-size:0.9rem;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="contact-icon-circle">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div>
                                    <strong class="contact-item-label">Email Address:</strong><br>
                                    <a href="mailto:dean.scje@dwcc-scje.edu.ph" class="contact-item-link">dean.scje@dwcc-scje.edu.ph</a>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="contact-icon-circle">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div>
                                    <strong class="contact-item-label">Telephone / Hotlines:</strong><br>
                                    <span class="contact-item-val">+63 (043) 288-4001 local 214</span>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="contact-icon-circle">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <strong class="contact-item-label">Office Hours:</strong><br>
                                    <span class="contact-item-val">Monday – Friday: 8:00 AM – 5:00 PM | Saturday: 8:00 AM – 12:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Online Inquiry Form -->
                <div>
                    <div class="section-header-banner" style="margin-bottom:20px;">
                        <h2><i class="fa-solid fa-paper-plane"></i> Send an Inquiry</h2>
                    </div>

                    <div class="contact-form-card">
                        <div id="contactAlert" style="display:none;"></div>
                        <form id="contactForm">
                            <?= CsrfMiddleware::field() ?>
                            <div class="form-group">
                                <label for="contactName">Full Name *</label>
                                <input type="text" id="contactName" name="name" class="form-control" placeholder="e.g. Maria Santos" required>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Email Address *</label>
                                <input type="email" id="contactEmail" name="email" class="form-control" placeholder="msantos@example.com" required>
                            </div>
                            <div class="form-group">
                                <label for="contactSubject">Inquiry Subject *</label>
                                <input type="text" id="contactSubject" name="subject" class="form-control" placeholder="e.g. Criminology Laboratory Visit Request" required>
                            </div>
                            <div class="form-group">
                                <label for="contactMessage">Message / Details *</label>
                                <textarea id="contactMessage" name="message" rows="4" class="form-control" placeholder="Write your message here..." required></textarea>
                            </div>
                            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:12px;">
                                <i class="fa-solid fa-paper-plane"></i> Submit Inquiry
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

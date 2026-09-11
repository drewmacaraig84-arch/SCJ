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
    <div style="background: linear-gradient(135deg, #0A192F 0%, #0F254B 50%, #1E3A8A 100%); color: #FFFFFF; padding: 40px 0; border-bottom: 3px solid var(--color-primary-accent);">
        <div class="container">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:var(--radius-sm); background:rgba(56,189,248,0.2); border:1px solid var(--color-primary-accent); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--color-primary-accent);">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <h2 style="font-size:1.85rem; font-weight:900; letter-spacing:1px; text-transform:uppercase;">Contact SCJE</h2>
                    <p style="color:#94A3B8; font-size:0.9rem;">Dean's Office &bull; Department Directory &bull; Public Inquiries</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="section-wrapper" style="background:#FFFFFF;">
        <div class="container">
            <div class="resources-contact-grid">
                <!-- Left: Contact Details & Office Information -->
                <div>
                    <div class="section-header-banner" style="margin-bottom:20px;">
                        <h2><i class="fa-solid fa-building-columns"></i> Office of the Dean</h2>
                    </div>

                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius-md); padding:26px; margin-bottom:20px;">
                        <h3 style="color:var(--color-primary-dark); font-size:1.2rem; font-weight:800; margin-bottom:12px;">
                            School of Criminal Justice Education
                        </h3>
                        <p style="font-size:0.9rem; color:#475569; line-height:1.6; margin-bottom:20px;">
                            Divine Word College of Calapan (DWCC)<br>
                            Gov. Infantado St., Calapan City, 5200 Oriental Mindoro, Philippines
                        </p>

                        <div style="display:flex; flex-direction:column; gap:14px; font-size:0.9rem;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:#EFF6FF; color:var(--color-primary); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div>
                                    <strong style="color:var(--color-text-dark);">Email Address:</strong><br>
                                    <a href="mailto:dean.scje@dwcc-scje.edu.ph" style="color:var(--color-primary-light);">dean.scje@dwcc-scje.edu.ph</a>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:#EFF6FF; color:var(--color-primary); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div>
                                    <strong style="color:var(--color-text-dark);">Telephone / Hotlines:</strong><br>
                                    <span style="color:#475569;">+63 (043) 288-4001 local 214</span>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:#EFF6FF; color:var(--color-primary); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <strong style="color:var(--color-text-dark);">Office Hours:</strong><br>
                                    <span style="color:#475569;">Monday – Friday: 8:00 AM – 5:00 PM | Saturday: 8:00 AM – 12:00 PM</span>
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

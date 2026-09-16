/**
 * Main Application Script
 * School of Criminal Justice Education (SCJE) Information System
 */

window.initSCJEApp = function () {
    // -------------------------------------------------------------
    // Theme Mode Switcher (Executive Midnight Dark vs. Classic Light)
    // -------------------------------------------------------------
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    
    function updateThemeUI(theme) {
        if (!themeToggleBtn) return;
        const textSpan = themeToggleBtn.querySelector('.theme-toggle-text');
        const iconMoon = themeToggleBtn.querySelector('.icon-moon');
        const iconSun = themeToggleBtn.querySelector('.icon-sun');
        
        if (theme === 'dark') {
            themeToggleBtn.setAttribute('data-current-theme', 'dark');
            themeToggleBtn.setAttribute('title', 'Switch to Light Theme');
            themeToggleBtn.setAttribute('aria-label', 'Switch to Light Theme');
            if (textSpan) textSpan.textContent = 'Dark';
            if (iconMoon) iconMoon.style.display = 'inline-block';
            if (iconSun) iconSun.style.display = 'none';
        } else {
            themeToggleBtn.setAttribute('data-current-theme', 'light');
            themeToggleBtn.setAttribute('title', 'Switch to Executive Dark Theme');
            themeToggleBtn.setAttribute('aria-label', 'Switch to Executive Dark Theme');
            if (textSpan) textSpan.textContent = 'Light';
            if (iconMoon) iconMoon.style.display = 'none';
            if (iconSun) iconSun.style.display = 'inline-block';
        }
    }

    // Read current theme state
    const currentTheme = document.documentElement.getAttribute('data-theme') || (function() {
        try { return localStorage.getItem('scj_theme') || 'dark'; } catch(e) { return 'dark'; }
    })();
    updateThemeUI(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.onclick = function (e) {
            e.preventDefault();
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', nextTheme);
            try {
                localStorage.setItem('scj_theme', nextTheme);
            } catch (err) {}
            if (window.scjPageCache) {
                window.scjPageCache.clear();
            }
            updateThemeUI(nextTheme);
        };
    }

    // -------------------------------------------------------------
    // Mobile Navigation Hamburger Toggle
    // -------------------------------------------------------------
    const navToggle = document.getElementById('navMobileToggle');
    const navMenu = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        navToggle.onclick = function (e) {
            e.stopPropagation();
            navMenu.classList.toggle('nav-menu-open');
            navToggle.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            if (icon) {
                icon.className = navMenu.classList.contains('nav-menu-open') ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
            }
        };

        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.onclick = function () {
                navMenu.classList.remove('nav-menu-open');
                navToggle.classList.remove('active');
                const icon = navToggle.querySelector('i');
                if (icon) icon.className = 'fa-solid fa-bars';
            };
        });

        document.addEventListener('click', function (e) {
            if (navMenu && !navMenu.contains(e.target) && !navToggle.contains(e.target) && navMenu.classList.contains('nav-menu-open')) {
                navMenu.classList.remove('nav-menu-open');
                navToggle.classList.remove('active');
                const icon = navToggle.querySelector('i');
                if (icon) icon.className = 'fa-solid fa-bars';
            }
        });
    }

    // -------------------------------------------------------------
    // 1. Live Search for Criminological Research Table
    // -------------------------------------------------------------
    const researchSearchInput = document.getElementById('researchSearchInput');
    const researchCategoryFilter = document.getElementById('researchCategoryFilter');
    const researchTableBody = document.getElementById('researchTableBody');
    const researchRows = researchTableBody ? researchTableBody.querySelectorAll('tr.research-row') : [];

    function filterResearchTable() {
        const query = (researchSearchInput ? researchSearchInput.value : '').toLowerCase().trim();
        const selectedCategory = (researchCategoryFilter ? researchCategoryFilter.value : 'all').toLowerCase();
        let visibleCount = 0;

        researchRows.forEach(row => {
            const author = row.getAttribute('data-author') || '';
            const title = row.getAttribute('data-title') || '';
            const category = row.getAttribute('data-category') || '';
            const date = row.getAttribute('data-date') || '';

            const matchesQuery = query === '' || 
                author.includes(query) || 
                title.includes(query) || 
                date.includes(query) ||
                category.includes(query);

            const matchesCategory = selectedCategory === 'all' || category === selectedCategory;

            if (matchesQuery && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noDataRow = document.getElementById('researchNoDataRow');
        if (noDataRow) {
            noDataRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (researchSearchInput) {
        researchSearchInput.addEventListener('input', filterResearchTable);
    }
    if (researchCategoryFilter) {
        researchCategoryFilter.addEventListener('change', filterResearchTable);
    }

    // Connect top banner search input to research table search
    const topResearchSearch = document.getElementById('topResearchSearch');
    if (topResearchSearch && researchSearchInput) {
        topResearchSearch.addEventListener('input', (e) => {
            researchSearchInput.value = e.target.value;
            filterResearchTable();
            const targetSection = document.getElementById('research');
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Connect 16 Research Category Cards to filter table
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', () => {
            const category = card.getAttribute('data-category-name');
            categoryCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');

            if (researchCategoryFilter) {
                // Find matching option or set value
                researchCategoryFilter.value = category;
                filterResearchTable();
            }

            const tableElem = document.getElementById('researchTableSection');
            if (tableElem) {
                tableElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // -------------------------------------------------------------
    // 2. Live Search for Laboratory Equipment Table
    // -------------------------------------------------------------
    const equipSearchInput = document.getElementById('equipSearchInput');
    const equipCategoryFilter = document.getElementById('equipCategoryFilter');
    const equipTableBody = document.getElementById('equipTableBody');
    const equipRows = equipTableBody ? equipTableBody.querySelectorAll('tr.equip-row') : [];

    function filterEquipTable() {
        const query = (equipSearchInput ? equipSearchInput.value : '').toLowerCase().trim();
        const selectedLab = (equipCategoryFilter ? equipCategoryFilter.value : 'all').toLowerCase();
        let visibleCount = 0;

        equipRows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const name = row.getAttribute('data-name') || '';
            const brand = row.getAttribute('data-brand') || '';
            const model = row.getAttribute('data-model') || '';
            const location = row.getAttribute('data-location') || '';
            const lab = row.getAttribute('data-lab') || '';

            const matchesQuery = query === '' || 
                code.includes(query) || 
                name.includes(query) || 
                brand.includes(query) || 
                model.includes(query) || 
                location.includes(query);

            const matchesLab = selectedLab === 'all' || 
                location.includes(selectedLab) || 
                lab === selectedLab;

            if (matchesQuery && matchesLab) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noEquipRow = document.getElementById('equipNoDataRow');
        if (noEquipRow) {
            noEquipRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (equipSearchInput) {
        equipSearchInput.addEventListener('input', filterEquipTable);
    }
    if (equipCategoryFilter) {
        equipCategoryFilter.addEventListener('change', filterEquipTable);
    }

    // Connect 5 Laboratory Cards to filter equipment table
    const labCards = document.querySelectorAll('.lab-card');
    labCards.forEach(card => {
        card.addEventListener('click', () => {
            const labName = card.getAttribute('data-lab-name');
            if (equipCategoryFilter) {
                equipCategoryFilter.value = labName;
                filterEquipTable();
            }
            const tableElem = document.getElementById('inventoryDirectory');
            if (tableElem) {
                tableElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // -------------------------------------------------------------
    // 2b. Materials & Chemicals Table Filtering
    // -------------------------------------------------------------
    const matSearchInput = document.getElementById('matSearchInput');
    const matLocationFilter = document.getElementById('matLocationFilter');
    const matTableBody = document.getElementById('matTableBody');
    const matRows = matTableBody ? matTableBody.querySelectorAll('tr.mat-row') : [];

    function filterMatTable() {
        const query = (matSearchInput ? matSearchInput.value : '').toLowerCase().trim();
        const selectedLoc = (matLocationFilter ? matLocationFilter.value : 'all').toLowerCase();
        let visibleCount = 0;

        matRows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const name = row.getAttribute('data-name') || '';
            const brand = row.getAttribute('data-brand') || '';
            const location = row.getAttribute('data-location') || '';

            const matchesQuery = query === '' || 
                code.includes(query) || 
                name.includes(query) || 
                brand.includes(query) || 
                location.includes(query);

            const matchesLoc = selectedLoc === 'all' || location.includes(selectedLoc);

            if (matchesQuery && matchesLoc) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatRow = document.getElementById('matNoDataRow');
        if (noMatRow) {
            noMatRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (matSearchInput) matSearchInput.addEventListener('input', filterMatTable);
    if (matLocationFilter) matLocationFilter.addEventListener('change', filterMatTable);

    // -------------------------------------------------------------
    // 3. Modal Manager (Login & Details Viewers)
    // -------------------------------------------------------------
    const loginModal = document.getElementById('loginModal');
    const openLoginButtons = document.querySelectorAll('.open-login-modal');
    const closeLoginButtons = document.querySelectorAll('.close-login-modal');

    openLoginButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (loginModal) {
                loginModal.classList.add('active');
                const idInput = document.getElementById('modalIdNumber');
                if (idInput) setTimeout(() => idInput.focus(), 150);
            }
        });
    });

    closeLoginButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (loginModal) loginModal.classList.remove('active');
        });
    });

    // Generic Modal Close on Backdrop Click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Details Modal Logic for Research
    const detailsModal = document.getElementById('detailsModal');
    const viewResearchButtons = document.querySelectorAll('.btn-view-research');
    viewResearchButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const title = btn.getAttribute('data-title');
            const author = btn.getAttribute('data-author');
            const date = btn.getAttribute('data-date');
            const category = btn.getAttribute('data-category');
            const abstract = btn.getAttribute('data-abstract') || 'No abstract available.';
            const keywords = btn.getAttribute('data-keywords') || 'N/A';

            document.getElementById('detailsModalTitle').textContent = 'Research Details';
            document.getElementById('detailsModalBody').innerHTML = `
                <div style="margin-bottom: 12px;">
                    <span class="badge badge-info">${escapeHtml(category)}</span>
                    <span class="badge badge-warning" style="margin-left:6px;">${escapeHtml(date)}</span>
                </div>
                <h3 style="color: var(--color-text-primary); font-size: 1.25rem; font-weight:800; margin-bottom: 10px; line-height: 1.35;">${escapeHtml(title)}</h3>
                <p style="font-size: 0.9rem; color: var(--color-text-secondary); font-weight: 600; margin-bottom: 16px;">Author(s): <strong style="color:var(--color-text-primary);">${escapeHtml(author)}</strong></p>
                <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <h4 style="font-size: 0.85rem; color: var(--color-gold); text-transform:uppercase; margin-bottom: 8px; font-weight:800;">Abstract</h4>
                    <p style="font-size: 0.88rem; line-height: 1.6; color: var(--color-text-secondary);">${escapeHtml(abstract)}</p>
                </div>
                <p style="font-size: 0.82rem; color: var(--color-text-muted);"><strong style="color:var(--color-text-secondary);">Keywords:</strong> ${escapeHtml(keywords)}</p>
            `;
            if (detailsModal) detailsModal.classList.add('active');
        });
    });

    // Details Modal Logic for Equipment
    const viewEquipButtons = document.querySelectorAll('.btn-view-equip');
    viewEquipButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.getAttribute('data-code');
            const name = btn.getAttribute('data-name');
            const brand = btn.getAttribute('data-brand');
            const model = btn.getAttribute('data-model');
            const location = btn.getAttribute('data-location');
            const status = btn.getAttribute('data-status');
            const serial = btn.getAttribute('data-serial') || 'N/A';
            const person = btn.getAttribute('data-person') || 'Sir Jom';
            const desc = btn.getAttribute('data-desc') || 'Official SCJE Department Asset.';

            const statusLower = status.toLowerCase();
            let statusClass = 'badge-success';
            if (statusLower.includes('out of service')) {
                statusClass = 'badge-danger';
            } else if (statusLower.includes('brand')) {
                statusClass = 'badge-info';
            }

            document.getElementById('detailsModalTitle').textContent = 'Laboratory Equipment Details';
            document.getElementById('detailsModalBody').innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <span style="font-family: monospace; font-size: 0.95rem; font-weight:800; color: var(--color-gold);">${escapeHtml(code)}</span>
                    <span class="badge ${statusClass}">${escapeHtml(status)}</span>
                </div>
                <h3 style="color: var(--color-text-primary); font-size: 1.2rem; font-weight:800; margin-bottom: 14px;">${escapeHtml(name)}</h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; background: var(--color-bg-subtle); border: 1px solid var(--color-border); padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; font-size: 0.85rem; color: var(--color-text-secondary);">
                    <div><strong style="color:var(--color-text-primary);">Brand:</strong> ${escapeHtml(brand)}</div>
                    <div><strong style="color:var(--color-text-primary);">Model:</strong> ${escapeHtml(model)}</div>
                    <div><strong style="color:var(--color-text-primary);">Serial No.:</strong> ${escapeHtml(serial)}</div>
                    <div><strong style="color:var(--color-text-primary);">Location:</strong> ${escapeHtml(location)}</div>
                    <div><strong style="color:var(--color-text-primary);">Accountable:</strong> ${escapeHtml(person)}</div>
                </div>
                <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); padding: 14px; border-radius: 8px;">
                    <h4 style="font-size: 0.82rem; color: var(--color-gold); text-transform:uppercase; margin-bottom: 6px; font-weight:800;">Asset Information</h4>
                    <p style="font-size: 0.88rem; line-height: 1.5; color: var(--color-text-secondary);">${escapeHtml(desc)}</p>
                </div>
            `;
            if (detailsModal) detailsModal.classList.add('active');
        });
    });

    // Details Modal Logic for Materials & Chemicals
    const viewMaterialButtons = document.querySelectorAll('.btn-view-material');
    viewMaterialButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.getAttribute('data-code');
            const name = btn.getAttribute('data-name');
            const qty = btn.getAttribute('data-qty');
            const unit = btn.getAttribute('data-unit') || 'N/A';
            const brand = btn.getAttribute('data-brand') || 'N/A';
            const location = btn.getAttribute('data-location');
            const status = btn.getAttribute('data-status');
            const person = btn.getAttribute('data-person') || 'Sir Jom';

            const statusLower = status.toLowerCase();
            let statusClass = 'badge-success';
            if (statusLower.includes('out of service')) {
                statusClass = 'badge-danger';
            } else if (statusLower.includes('brand')) {
                statusClass = 'badge-info';
            }

            document.getElementById('detailsModalTitle').textContent = 'Material & Chemical Reagent Details';
            document.getElementById('detailsModalBody').innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <span style="font-family: monospace; font-size: 0.95rem; font-weight:800; color: var(--color-gold);">${escapeHtml(code)}</span>
                    <span class="badge ${statusClass}">${escapeHtml(status)}</span>
                </div>
                <h3 style="color: var(--color-text-primary); font-size: 1.2rem; font-weight:800; margin-bottom: 14px;">${escapeHtml(name)}</h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; background: var(--color-bg-subtle); border: 1px solid var(--color-border); padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; font-size: 0.85rem; color: var(--color-text-secondary);">
                    <div><strong style="color:var(--color-text-primary);">Quantity:</strong> ${escapeHtml(qty)} ${unit !== 'N/A' ? escapeHtml(unit) : ''}</div>
                    <div><strong style="color:var(--color-text-primary);">Brand / Spec:</strong> ${escapeHtml(brand)}</div>
                    <div><strong style="color:var(--color-text-primary);">Storage Location:</strong> ${escapeHtml(location)}</div>
                    <div><strong style="color:var(--color-text-primary);">Accountable:</strong> ${escapeHtml(person)}</div>
                </div>
                <div style="background: var(--color-bg-subtle); border: 1px solid var(--color-border); padding: 14px; border-radius: 8px;">
                    <h4 style="font-size: 0.82rem; color: var(--color-gold); text-transform:uppercase; margin-bottom: 6px; font-weight:800;">Inventory & Safety Classification</h4>
                    <p style="font-size: 0.88rem; line-height: 1.5; color: var(--color-text-secondary);">Department forensic consumable / reagent recorded under the AY 2025-2026 active laboratory inventory schedule.</p>
                </div>
            `;
            if (detailsModal) detailsModal.classList.add('active');
        });
    });

    const closeDetailsBtn = document.getElementById('closeDetailsModal');
    if (closeDetailsBtn && detailsModal) {
        closeDetailsBtn.addEventListener('click', () => {
            detailsModal.classList.remove('active');
        });
    }

    // -------------------------------------------------------------
    // 4. Contact Form AJAX Submission with Rate Limit & CSRF
    // -------------------------------------------------------------
    const contactForm = document.getElementById('contactForm');
    const contactAlert = document.getElementById('contactAlert');

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Sending...';

            if (contactAlert) {
                contactAlert.style.display = 'none';
                contactAlert.className = '';
            }

            const formData = new FormData(contactForm);

            try {
                const response = await fetch('api/contact.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (contactAlert) {
                    contactAlert.style.display = 'block';
                    if (data.status === 'success') {
                        contactAlert.className = 'badge badge-success';
                        contactAlert.style.padding = '10px 14px';
                        contactAlert.style.display = 'block';
                        contactAlert.style.marginBottom = '14px';
                        contactAlert.textContent = data.message;
                        contactForm.reset();
                    } else {
                        contactAlert.className = 'badge badge-danger';
                        contactAlert.style.padding = '10px 14px';
                        contactAlert.style.display = 'block';
                        contactAlert.style.marginBottom = '14px';
                        contactAlert.textContent = data.message || 'Submission failed.';
                    }
                }
            } catch (err) {
                if (contactAlert) {
                    contactAlert.style.display = 'block';
                    contactAlert.className = 'badge badge-danger';
                    contactAlert.style.padding = '10px 14px';
                    contactAlert.style.marginBottom = '14px';
                    contactAlert.textContent = 'A network error occurred. Please try again.';
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // -------------------------------------------------------------
    // 5. High-Performance Scroll-Reveal & Stagger Animation Engine
    // -------------------------------------------------------------
    function initScrollReveal() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('scje-revealed');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px'
            });

            const targets = document.querySelectorAll(`
                .section-header-banner,
                .category-card,
                .table-card,
                .faculty-card,
                .lab-card,
                .feature-card,
                .academic-program-card,
                .academic-pathway-card,
                .pillar-card,
                .contact-card,
                .hero-tile
            `);

            targets.forEach((el, index) => {
                el.classList.add('scroll-reveal');
                const staggerIndex = (index % 8) + 1;
                el.classList.add(`stagger-${staggerIndex}`);
                observer.observe(el);
            });
        } else {
            document.querySelectorAll('.scroll-reveal').forEach(el => el.classList.add('scje-revealed'));
        }
    }

    initScrollReveal();

    // Helper to sanitize text inside injected HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
};

document.addEventListener('DOMContentLoaded', window.initSCJEApp);


/**
 * Unified Admin Portal Animation & Interactive Enhancer
 * School of Criminal Justice Education (SCJE) Information System
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Staggered Entrance for Admin Cards & Widgets
    const cards = document.querySelectorAll('.stat-card, .health-card, .analytics-card, .table-card, .dashboard-widget, .action-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${(index * 0.06).toFixed(2)}s`;
    });

    // 2. Smooth Number Counter Animation on Metric Values
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number, .stat-value, .metric-number, .counter-value');
        counters.forEach(counter => {
            const text = counter.textContent.trim();
            const target = parseInt(text.replace(/[^0-9]/g, ''), 10);
            if (!isNaN(target) && target > 0) {
                const duration = Math.min(1200, Math.max(500, target * 10));
                const startTime = performance.now();
                const prefix = text.startsWith('+') ? '+' : '';
                const suffix = text.endsWith('%') ? '%' : (text.endsWith('+') ? '+' : '');

                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out cubic
                    const ease = 1 - Math.pow(1 - progress, 3);
                    const current = Math.floor(ease * target);
                    counter.textContent = `${prefix}${current.toLocaleString()}${suffix}`;

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        counter.textContent = text;
                    }
                }
                requestAnimationFrame(update);
            }
        });
    }

    animateCounters();

    // 3. Admin Table Row Smooth Highlight & Stagger
    const tableRows = document.querySelectorAll('.admin-table tbody tr, .custom-table tbody tr');
    tableRows.forEach((row, i) => {
        row.style.animation = 'scjeFadeInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both';
        row.style.animationDelay = `${Math.min(0.5, i * 0.02).toFixed(2)}s`;
    });

    // 4. Interactive Feedback for Action Buttons
    const actionBtns = document.querySelectorAll('.btn-sm, .btn-action, .btn-primary, .btn-danger, .btn-warning, .btn-success');
    actionBtns.forEach(btn => {
        btn.addEventListener('mousedown', () => {
            btn.style.transform = 'scale(0.96)';
        });
        btn.addEventListener('mouseup', () => {
            btn.style.transform = '';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
});

/**
 * Professional Confirmation Popup Dialog Modal
 * School of Criminal Justice Education (SCJE) Information System
 */

(function () {
    // 1. Create and inject modal elements if not already in DOM
    let modal = document.getElementById('scjeConfirmModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'scjeConfirmModal';
        modal.className = 'modal-overlay';
        modal.style.display = 'none';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(10, 25, 47, 0.75)';
        modal.style.backdropFilter = 'blur(6px)';
        modal.style.webkitBackdropFilter = 'blur(6px)';
        modal.style.zIndex = '999999';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.2s ease, visibility 0.2s ease';

        modal.innerHTML = `
            <div class="modal-card" style="max-width: 450px; width: 90%; background: var(--color-bg-surface); border-radius: 14px; padding: 28px 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); border: 1px solid rgba(239, 68, 68, 0.35); transform: scale(0.92); transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                <!-- Warning Icon Pulse -->
                <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 2px solid rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; color: #EF4444; font-size: 1.75rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                
                <h3 id="scjeConfirmTitle" style="font-size: 1.3rem; font-weight: 800; color: var(--color-text-primary); margin: 0 0 8px;">
                    Confirm Deletion
                </h3>
                
                <p id="scjeConfirmMessage" style="color: var(--color-text-secondary); font-size: 0.95rem; line-height: 1.55; margin: 0 0 24px;">
                    Are you sure you want to delete this record? This action cannot be undone.
                </p>
                
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" id="scjeConfirmCancel" class="btn-primary" style="background: var(--color-bg-subtle); color: var(--color-text-secondary); border: 1px solid var(--color-border); font-weight: 700; padding: 10px 22px; border-radius: 8px; cursor: pointer;">
                        Cancel
                    </button>
                    <a id="scjeConfirmAction" href="#" class="btn-primary" style="background: #EF4444; border: 1px solid #DC2626; color: #FFFFFF; font-weight: 700; padding: 10px 22px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                        <i class="fa-solid fa-trash"></i> Yes, Delete
                    </a>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Dynamic styles for hover states and transitions
        const style = document.createElement('style');
        style.textContent = `
            #scjeConfirmModal.scje-active {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            #scjeConfirmModal.scje-active .modal-card {
                transform: scale(1) !important;
            }
            #scjeConfirmCancel:hover {
                background: #E2E8F0 !important;
                color: #0F172A !important;
            }
            #scjeConfirmAction:hover {
                background: #DC2626 !important;
                box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45);
            }
        `;
        document.head.appendChild(style);
    }

    let targetHref = null;

    function showDialog(title, message, href) {
        const titleEl = document.getElementById('scjeConfirmTitle');
        const msgEl = document.getElementById('scjeConfirmMessage');
        const actionBtn = document.getElementById('scjeConfirmAction');

        if (titleEl) titleEl.textContent = title || 'Confirm Deletion';
        if (msgEl) msgEl.textContent = message || 'Are you sure you want to delete this record? This action cannot be undone.';
        if (actionBtn) {
            actionBtn.href = href || '#';
            targetHref = href;
        }

        modal.classList.add('scje-active');
        document.body.style.overflow = 'hidden';
    }

    function hideDialog() {
        modal.classList.remove('scje-active');
        document.body.style.overflow = '';
        targetHref = null;
    }

    // Cancel Button Click
    const cancelBtn = document.getElementById('scjeConfirmCancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            hideDialog();
        });
    }

    // Backdrop Click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideDialog();
        }
    });

    // Escape Key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('scje-active')) {
            hideDialog();
        }
    });

    // Capture click on delete links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-confirm], a[onclick*="confirm("], a[href*="delete="]');
        if (!link) return;

        // Prevent native confirm() execution
        e.preventDefault();
        e.stopImmediatePropagation();

        // Extract dialog copy
        let title = link.getAttribute('data-confirm-title') || 'Delete Record';
        let message = link.getAttribute('data-confirm') || link.getAttribute('data-confirm-message');

        if (!message) {
            const onclickAttr = link.getAttribute('onclick');
            if (onclickAttr) {
                const match = onclickAttr.match(/confirm\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    message = match[1];
                }
            }
        }

        if (!message) {
            message = 'Are you sure you want to delete this record? This action cannot be undone.';
        }

        showDialog(title, message, link.href);
    }, true); // true = capture phase to intercept before inline onclick executes
})();

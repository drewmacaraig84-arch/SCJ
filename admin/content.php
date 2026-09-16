<?php
/**
 * Admin: Institutional Site Content Management (Vision, Mission, Goals, History)
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/cache.php';

SecurityHeadersMiddleware::handle();
RoleMiddleware::handle(['admin']);

$pdo = get_db();
$message = '';
$error = '';

// Handle Update Content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_content') {
    if (!CsrfMiddleware::verify($_POST['_csrf_token'] ?? '')) {
        $error = 'Security verification failed. Please try again.';
    } else {
        $vision = trim($_POST['vision'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $goals = trim($_POST['goals'] ?? '');

        if ($vision && $mission && $goals) {
            try {
                $updateStmt = $pdo->prepare("UPDATE site_content SET content = ?, updated_at = NOW() WHERE section_key = ?");
                $updateStmt->execute([$vision, 'vision']);
                $updateStmt->execute([$mission, 'mission']);
                $updateStmt->execute([$goals, 'goals']);

                // Also update SQLite fallback if exists
                $sqlitePath = dirname(__DIR__) . '/database/scj.sqlite';
                if (file_exists($sqlitePath)) {
                    try {
                        $sqlitePdo = new PDO("sqlite:" . $sqlitePath);
                        $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sqStmt = $sqlitePdo->prepare("UPDATE site_content SET content = ?, updated_at = datetime('now') WHERE section_key = ?");
                        $sqStmt->execute([$vision, 'vision']);
                        $sqStmt->execute([$mission, 'mission']);
                        $sqStmt->execute([$goals, 'goals']);
                    } catch (Exception $sqEx) {
                        // Silently continue if SQLite error
                    }
                }

                Cache::flush();
                $message = 'Site content (Vision, Mission, and Goals) updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update content: ' . $e->getMessage();
            }
        } else {
            $error = 'Vision, Mission, and Goals cannot be empty.';
        }
    }
}

// Fetch current site content
$stmt = $pdo->query("SELECT section_key, title, content, updated_at FROM site_content");
$contentMap = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $contentMap[$row['section_key']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Site Content | SCJE Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/scj_logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <style>
        /* ==============================================================
           STATIONARY FIXED SIDEBAR & FLUID RESPONSIVE VIEWPORT
           ============================================================== */
        html, body {
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .admin-layout {
            display: grid !important;
            grid-template-columns: 260px minmax(0, 1fr) !important;
            height: 100vh !important;
            max-height: 100vh !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .admin-sidebar {
            background: #071324 !important;
            color: #E2E8F0 !important;
            border-right: 1px solid rgba(56,189,248,0.18) !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
            max-height: 100vh !important;
            position: sticky !important;
            top: 0 !important;
            left: 0 !important;
            align-self: start !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            flex-shrink: 0 !important;
            z-index: 100 !important;
        }
        .admin-main {
            background: var(--color-bg-page) !important;
            color: var(--color-text-primary) !important;
            padding: clamp(20px, 2.8vw, 36px) !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            box-sizing: border-box !important;
        }
        .admin-main::-webkit-scrollbar {
            width: 8px;
        }
        .admin-main::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.15);
        }
        .admin-main::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.25);
            border-radius: 4px;
        }
        .admin-main::-webkit-scrollbar-thumb:hover {
            background: var(--color-primary, #D4AF37);
        }

        /* ==============================================================
           RESPONSIVE CONTENT CARDS & THEME CONTRAST
           ============================================================== */
        .content-card {
            background: var(--color-bg-surface);
            border-radius: var(--radius-md, 12px);
            padding: clamp(20px, 2.5vw, 30px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.18);
            border: 1px solid var(--color-border);
            margin-bottom: 24px;
            color: var(--color-text-primary);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
            position: relative;
        }
        .content-card:hover {
            border-color: rgba(56, 189, 248, 0.35);
        }
        .content-card h3 {
            font-size: clamp(1.05rem, 1rem + 0.35vw, 1.25rem);
            font-weight: 800;
            color: var(--color-text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .content-card p.desc {
            font-size: clamp(0.82rem, 0.8rem + 0.15vw, 0.9rem);
            color: var(--color-text-muted);
            margin-bottom: 18px;
            line-height: 1.55;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: clamp(0.84rem, 0.8rem + 0.15vw, 0.92rem);
            color: var(--color-text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }
        .form-group label .label-hint {
            font-size: 0.76rem;
            font-weight: 500;
            color: var(--color-text-dim);
        }

        /* ==============================================================
           HIGHLY RESPONSIVE FLUID TEXTAREAS
           ============================================================== */
        .responsive-textarea-wrapper {
            position: relative;
            width: 100%;
        }
        .form-group textarea.responsive-input {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            background: var(--color-bg-page) !important;
            color: var(--color-text-primary) !important;
            border: 1.5px solid var(--color-border) !important;
            border-radius: var(--radius-sm, 10px) !important;
            padding: clamp(14px, 1.4vw, 20px) !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            font-size: clamp(0.92rem, 0.86rem + 0.3vw, 1.05rem) !important;
            line-height: 1.75 !important;
            letter-spacing: 0.15px !important;
            resize: vertical !important;
            min-height: 90px;
            overflow-y: hidden; /* dynamically expands via JS */
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .form-group textarea.responsive-input:focus {
            outline: none !important;
            border-color: var(--color-primary, #D4AF37) !important;
            box-shadow: 0 0 0 3.5px rgba(212, 175, 55, 0.22), inset 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            background: var(--color-bg-surface) !important;
        }
        [data-theme="light"] .form-group textarea.responsive-input {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #CBD5E1 !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        [data-theme="light"] .form-group textarea.responsive-input:focus {
            border-color: #1E3A8A !important;
            box-shadow: 0 0 0 3.5px rgba(30, 58, 138, 0.15), inset 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        /* Meta status & counter bar */
        .input-meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding: 0 4px;
            font-size: 0.78rem;
            color: var(--color-text-dim);
        }
        .input-meta-count {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--color-text-muted);
            font-weight: 600;
        }
        .input-quick-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(56, 189, 248, 0.1);
            color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .input-quick-action:hover {
            background: rgba(56, 189, 248, 0.2);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #34D399; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #F87171; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }

        @media (max-width: 767px) {
            html, body {
                height: auto !important;
                overflow-y: auto !important;
            }
            .admin-layout {
                display: block !important;
                height: auto !important;
            }
            .admin-sidebar {
                height: auto !important;
                max-height: 50vh !important;
            }
            .admin-main {
                height: auto !important;
                padding: 18px !important;
            }
        }
    </style>
    <!-- Theme Mode Pre-Render Hydration -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('scj_theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>

<div class="admin-layout">
    <?php 
    $activePage = 'content';
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <!-- Main Content -->
    <main class="admin-main">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:28px;">
            <div>
                <h2 style="font-size:clamp(1.3rem, 1.1rem + 1vw, 1.8rem); font-weight:900; color:var(--color-text-primary); margin:0 0 6px 0; letter-spacing:0.2px;">Institutional Site Content</h2>
                <p style="color:var(--color-text-muted); font-size:clamp(0.85rem, 0.8rem + 0.2vw, 0.95rem); margin:0;">Manage the institutional framework (Vision, Mission, Goals) displayed on public pages.</p>
            </div>
            <a href="<?= base_url('about.php#pillars') ?>" class="btn-secondary" target="_blank" style="padding:10px 18px; display:inline-flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview On Public Site
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="contentForm">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="update_content">

            <!-- Vision -->
            <div class="content-card">
                <h3><i class="fa-solid fa-eye" style="color:var(--color-primary-accent);"></i> Institutional Vision</h3>
                <p class="desc">The overarching institutional aspiration for the School of Criminal Justice Education.</p>
                <div class="form-group">
                    <label for="vision">
                        <span>Vision Statement</span>
                        <span class="label-hint"><i class="fa-solid fa-arrows-up-down"></i> Auto-responsive height</span>
                    </label>
                    <div class="responsive-textarea-wrapper">
                        <textarea id="vision" name="vision" class="responsive-input" rows="2" required placeholder="Enter institutional vision statement..."><?= e($contentMap['vision']['content'] ?? '') ?></textarea>
                    </div>
                    <div class="input-meta-bar">
                        <div class="input-meta-count" id="visionCount"><span>0 words</span> <span>•</span> <span>0 chars</span></div>
                        <span style="color:var(--color-text-dim); font-size:0.75rem;"><i class="fa-solid fa-bolt" style="color:var(--color-primary);"></i> Dynamic scaling enabled</span>
                    </div>
                </div>
            </div>

            <!-- Mission -->
            <div class="content-card">
                <h3><i class="fa-solid fa-bullseye" style="color:var(--color-gold);"></i> Institutional Mission</h3>
                <p class="desc">The core institutional mission and educational commitment to criminology graduates.</p>
                <div class="form-group">
                    <label for="mission">
                        <span>Mission Statement</span>
                        <span class="label-hint"><i class="fa-solid fa-arrows-up-down"></i> Auto-responsive height</span>
                    </label>
                    <div class="responsive-textarea-wrapper">
                        <textarea id="mission" name="mission" class="responsive-input" rows="3" required placeholder="Enter institutional mission statement..."><?= e($contentMap['mission']['content'] ?? '') ?></textarea>
                    </div>
                    <div class="input-meta-bar">
                        <div class="input-meta-count" id="missionCount"><span>0 words</span> <span>•</span> <span>0 chars</span></div>
                        <span style="color:var(--color-text-dim); font-size:0.75rem;"><i class="fa-solid fa-bolt" style="color:var(--color-primary);"></i> Dynamic scaling enabled</span>
                    </div>
                </div>
            </div>

            <!-- Goals -->
            <div class="content-card">
                <h3><i class="fa-solid fa-chart-line" style="color:#10B981;"></i> Department Goals</h3>
                <p class="desc">Specific objectives and pillars. You can use bullet points (•) and line breaks for clear itemization.</p>
                <div class="form-group">
                    <label for="goals">
                        <span>Department Goals</span>
                        <button type="button" class="input-quick-action" onclick="insertBullet('goals')">
                            <i class="fa-solid fa-plus"></i> Add Bullet (•)
                        </button>
                    </label>
                    <div class="responsive-textarea-wrapper">
                        <textarea id="goals" name="goals" class="responsive-input" rows="4" required placeholder="• Enter department goal or objective..."><?= e($contentMap['goals']['content'] ?? '') ?></textarea>
                    </div>
                    <div class="input-meta-bar">
                        <div class="input-meta-count" id="goalsCount"><span>0 words</span> <span>•</span> <span>0 chars</span></div>
                        <span style="color:var(--color-text-dim); font-size:0.75rem;">Tip: Use Enter for next bullet line</span>
                    </div>
                </div>
            </div>

            <div style="margin-top:28px; display:flex; gap:14px; align-items:center; flex-wrap:wrap; padding-bottom:30px;">
                <button type="submit" class="btn-primary" style="padding:14px 32px; font-size:1rem; font-weight:800; border-radius:var(--radius-sm, 10px); display:inline-flex; align-items:center; gap:10px; cursor:pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> Save &amp; Publish Content
                </button>
                <span style="font-size:0.82rem; color:var(--color-text-dim);">
                    <i class="fa-solid fa-keyboard"></i> Tip: Press <kbd style="background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px; font-size:0.75rem;">Ctrl</kbd> + <kbd style="background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px; font-size:0.75rem;">S</kbd> to quickly save
                </span>
            </div>
        </form>
    </main>
</div>

<!-- Auto-Expand & Responsive Input Engine -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var textareas = document.querySelectorAll('textarea.responsive-input');

    function autoExpand(el) {
        // Reset height to calculate true scrollHeight
        el.style.height = 'auto';
        var newHeight = Math.max(el.scrollHeight + 6, 95);
        el.style.height = newHeight + 'px';
    }

    function updateCounts(el) {
        var countId = el.id + 'Count';
        var countEl = document.getElementById(countId);
        if (!countEl) return;

        var text = el.value.trim();
        var words = text ? text.split(/\s+/).length : 0;
        var chars = el.value.length;
        countEl.innerHTML = '<span><i class="fa-solid fa-font"></i> ' + words + ' ' + (words === 1 ? 'word' : 'words') + '</span> <span>•</span> <span>' + chars + ' ' + (chars === 1 ? 'char' : 'chars') + '</span>';
    }

    textareas.forEach(function(ta) {
        autoExpand(ta);
        updateCounts(ta);

        ta.addEventListener('input', function() {
            autoExpand(this);
            updateCounts(this);
        });
    });

    // Recompute on window resize to ensure fluid responsiveness
    var resizeDebounce;
    window.addEventListener('resize', function() {
        clearTimeout(resizeDebounce);
        resizeDebounce = setTimeout(function() {
            textareas.forEach(function(ta) {
                autoExpand(ta);
            });
        }, 100);
    });

    // Ctrl+S / Cmd+S Quick Save
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            var form = document.getElementById('contentForm');
            if (form) form.submit();
        }
    });
});

// Helper: Quick insert bullet point
function insertBullet(textareaId) {
    var ta = document.getElementById(textareaId);
    if (!ta) return;
    var start = ta.selectionStart;
    var end = ta.selectionEnd;
    var val = ta.value;
    var prefix = (start > 0 && val.charAt(start - 1) !== '\n') ? '\n• ' : '• ';
    ta.value = val.substring(0, start) + prefix + val.substring(end);
    ta.selectionStart = ta.selectionEnd = start + prefix.length;
    ta.focus();
    ta.dispatchEvent(new Event('input'));
}
</script>
</body>
</html>


/**
 * Instant SPA Navigation & Pre-Fetch Caching Engine
 * School of Criminal Justice Education (SCJE) Information System
 */

(function () {
    const pageCache = new Map();
    const parser = new DOMParser();

    // Cache the current initial page
    const currentPath = window.location.pathname;
    pageCache.set(window.location.href, {
        title: document.title,
        content: document.getElementById('appContent')?.innerHTML || '',
        activePage: getActivePageName(window.location.href)
    });

    function getActivePageName(url) {
        if (url.includes('about.php')) return 'about';
        if (url.includes('research.php')) return 'research';
        if (url.includes('laboratories.php')) return 'laboratories';
        if (url.includes('faculty.php')) return 'faculty';
        if (url.includes('contact.php')) return 'contact';
        return 'home';
    }

    /**
     * Pre-fetch page in the background on hover / touch
     */
    async function prefetchPage(url) {
        if (pageCache.has(url)) return;

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;

            const html = await res.text();
            const doc = parser.parseFromString(html, 'text/html');
            const content = doc.getElementById('appContent')?.innerHTML;
            const title = doc.title;

            if (content) {
                pageCache.set(url, {
                    title: title,
                    content: content,
                    activePage: getActivePageName(url)
                });
            }
        } catch (e) {
            // Silently ignore prefetch errors
        }
    }

    /**
     * Seamlessly transition to the target page without reloading
     */
    async function navigateTo(url, pushState = true) {
        const appContent = document.getElementById('appContent');
        if (!appContent) {
            window.location.href = url;
            return;
        }

        // Show subtle top loading indicator
        showProgress();

        let pageData = pageCache.get(url);

        if (!pageData) {
            try {
                const res = await fetch(url);
                if (!res.ok) {
                    window.location.href = url;
                    return;
                }
                const html = await res.text();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.getElementById('appContent')?.innerHTML;
                const title = doc.title;

                if (!content) {
                    window.location.href = url;
                    return;
                }

                pageData = {
                    title: title,
                    content: content,
                    activePage: getActivePageName(url)
                };
                pageCache.set(url, pageData);
            } catch (err) {
                window.location.href = url;
                return;
            }
        }

        // Apply smooth transition
        appContent.style.opacity = '0';
        appContent.style.transition = 'opacity 0.12s ease';

        setTimeout(() => {
            appContent.innerHTML = pageData.content;
            document.title = pageData.title;

            if (pushState) {
                window.history.pushState({ url: url }, pageData.title, url);
            }

            // Update active navbar item
            updateActiveNavbar(pageData.activePage);

            // Re-bind all dynamic events (search, modals, forms)
            if (window.initSCJEApp) {
                window.initSCJEApp();
            }

            window.scrollTo({ top: 0, behavior: 'instant' });
            appContent.style.opacity = '1';
            hideProgress();
        }, 120);
    }

    function updateActiveNavbar(activePage) {
        document.querySelectorAll('.nav-menu .nav-link').forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href') || '';
            const clean = href.split('?')[0].split('#')[0];
            if (activePage === 'home' && (clean.endsWith('index.php') || clean.endsWith('/') || clean === '')) {
                link.classList.add('active');
            } else if (activePage !== 'home' && clean.includes(activePage + '.php')) {
                link.classList.add('active');
            }
        });
    }

    // Top subtle progress bar
    let progressBar = null;
    function showProgress() {
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.style.position = 'fixed';
            progressBar.style.top = '0';
            progressBar.style.left = '0';
            progressBar.style.height = '3px';
            progressBar.style.background = 'linear-gradient(90deg, #38BDF8, #F59E0B)';
            progressBar.style.zIndex = '9999';
            progressBar.style.transition = 'width 0.2s ease';
            progressBar.style.width = '0%';
            document.body.appendChild(progressBar);
        }
        progressBar.style.opacity = '1';
        progressBar.style.width = '70%';
    }

    function hideProgress() {
        if (progressBar) {
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.opacity = '0';
                progressBar.style.width = '0%';
            }, 200);
        }
    }

    // Intercept clicks on links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return;
        }

        // Don't intercept admin, logout, external, or modifier clicks (Ctrl/Cmd click)
        if (e.ctrlKey || e.metaKey || e.shiftKey || link.target === '_blank' || href.includes('/admin/') || href.includes('logout.php') || href.includes('setup.php')) {
            return;
        }

        // Check if internal origin
        const url = new URL(link.href, window.location.href);
        if (url.origin === window.location.origin) {
            e.preventDefault();
            navigateTo(url.href);
        }
    });

    // Hover Pre-fetching for instant 0ms responses
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('/admin/') || href.includes('logout.php')) return;

        const url = new URL(link.href, window.location.href);
        if (url.origin === window.location.origin) {
            prefetchPage(url.href);
        }
    });

    // Popstate handling (Back/Forward buttons)
    window.addEventListener('popstate', (e) => {
        navigateTo(window.location.href, false);
    });
})();

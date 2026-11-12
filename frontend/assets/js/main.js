// ============================================================================
// NSBM STUDY CIRCLE - FRONTEND INTERACTION & API INTEGRATION JAVASCRIPT
// Handles: Theme toggle, live search, AJAX filter to backend, active nav states
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dark / Light Theme Toggle with Unified Storage Synchronization
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const themeTooltip = document.getElementById('themeTooltip');
    const currentTheme = localStorage.getItem('theme') || localStorage.getItem('study_circle_theme') || 'light';

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        if (document.body) {
            document.body.setAttribute('data-bs-theme', theme);
        }
        localStorage.setItem('theme', theme);
        localStorage.setItem('study_circle_theme', theme);
        if (themeIcon) {
            themeIcon.className = (theme === 'dark') ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
        }
        const tooltipText = (theme === 'dark') ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        if (themeTooltip) {
            themeTooltip.textContent = tooltipText;
        }
        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('title', tooltipText);
            themeToggleBtn.setAttribute('aria-label', tooltipText);
        }
    }
    setTheme(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const activeTheme = document.documentElement.getAttribute('data-bs-theme');
            setTheme(activeTheme === 'dark' ? 'light' : 'dark');
        });
    }

    // 2. Floating Navbar Scroll Effect (Refined shadow and glassmorphism)
    const floatingNavbar = document.querySelector('.floating-navbar') || document.querySelector('.custom-navbar');
    if (floatingNavbar) {
        const handleScroll = () => {
            if (window.scrollY > 15) {
                floatingNavbar.classList.add('scrolled');
            } else {
                floatingNavbar.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    // 3. Navigation Link Stability & Mobile Auto-Collapse
    const navbarCollapse = document.getElementById('navbarStudyCircle');
    const navLinks = document.querySelectorAll('.custom-nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const currentPath = window.location.pathname.split('/').pop() || 'index.php';
            const linkHref = this.getAttribute('href') || '';
            const linkTarget = linkHref.split('#')[0].split('/').pop();

            // If already on this exact page and clicking the same link, smoothly scroll to top without reload
            const isHomePage = (currentPath === '' || currentPath === 'index.php' || currentPath === 'index.html');
            const isHomeTarget = (linkTarget === 'index.php' || linkTarget === 'index.html' || linkTarget === '');
            
            if ((isHomePage && isHomeTarget) || (currentPath === linkTarget && linkTarget !== '')) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                const toggler = document.querySelector('.navbar-toggler');
                if (toggler) toggler.click();
            }
        });
    });

    // 3. Faculty Filter Pills & Live Search (Connected to Backend API)
    const filterPills = document.querySelectorAll('.resource-filter-pill');
    const resourceCardsContainer = document.getElementById('resourceCardsContainer');
    const searchInput = document.getElementById('liveSearchInput');

    let activeFaculty = 'all';
    let searchDebounceTimeout = null;

    // Parse a fetch() response as JSON. Some hosts (e.g. InfinityFree's browser
    // check) can answer with an HTML page instead, so never trust response.json().
    async function readJson(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            showNoticeToast('Something went wrong loading data. Please refresh the page and try again.');
            const err = new Error('Non-JSON response from server');
            err.nonJson = true;
            throw err;
        }
    }

    function showNoticeToast(message) {
        const toastEl = document.createElement('div');
        toastEl.setAttribute('role', 'alert');
        toastEl.className = 'shadow';
        toastEl.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#0f172a;color:#fff;padding:12px 20px;border-radius:10px;font-weight:600;font-size:0.9rem;max-width:360px;';
        toastEl.textContent = message;
        document.body.appendChild(toastEl);
        setTimeout(() => toastEl.remove(), 4000);
    }

    async function fetchFilteredResources() {
        const query = searchInput ? searchInput.value.trim() : '';

        // If on HTTP / HTTPS, query the backend controller API
        if (window.location.protocol === 'http:' || window.location.protocol === 'https:') {
            try {
                const apiUrl = `../backend/controllers/home_controller.php?action=resources&faculty=${encodeURIComponent(activeFaculty)}&search=${encodeURIComponent(query)}`;
                const response = await fetch(apiUrl);
                const result = await readJson(response);

                if (result.success && Array.isArray(result.data)) {
                    renderResourceCards(result.data);
                    return;
                }
            } catch (err) {
                console.warn('API fetch failed, falling back to client filter:', err);
            }
        }

        // Fallback for file:/// protocol (offline client-side filter)
        const staticCards = document.querySelectorAll('.resource-filter-item');
        staticCards.forEach(card => {
            const cardFaculty = card.getAttribute('data-faculty') || '';
            const cardTitle = (card.getAttribute('data-title') || '').toLowerCase();
            const matchesFaculty = (activeFaculty === 'all' || cardFaculty.toLowerCase() === activeFaculty.toLowerCase());
            const matchesSearch = (!query || cardTitle.includes(query.toLowerCase()));

            card.style.display = (matchesFaculty && matchesSearch) ? '' : 'none';
        });
    }

    function renderResourceCards(items) {
        if (!resourceCardsContainer) return;

        if (items.length === 0) {
            resourceCardsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold text-muted">No resources found</h5>
                    <p class="small text-muted">Try adjusting your search terms or faculty filter.</p>
                </div>
            `;
            return;
        }

        const facultyBadgeClasses = {
            'Computing': 'pill-computing',
            'Business': 'pill-business',
            'Engineering': 'pill-engineering',
            'Science': 'pill-science'
        };

        const avatarColors = {
            'Computing': '#0f172a',
            'Business': '#064e3b',
            'Engineering': '#4338ca',
            'Science': '#0f766e'
        };

        resourceCardsContainer.innerHTML = items.map(item => {
            const pillClass = facultyBadgeClasses[item.faculty] || 'pill-computing';
            const avatarColor = avatarColors[item.faculty] || '#0f172a';
            const fileIcon = item.file_type === 'DOCX' ? 'bi-file-earmark-word text-primary' : 'bi-file-earmark-pdf text-danger';

            return `
                <div class="col-lg-3 col-md-6 resource-filter-item" data-faculty="${item.faculty}" data-title="${item.title}">
                    <div class="resource-grid-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="res-format-badge"><i class="bi ${fileIcon}"></i> ${item.file_type}</span>
                                <button class="btn btn-sm p-0 text-muted border-0" title="Bookmark"><i class="bi bi-bookmark"></i></button>
                            </div>
                            <span class="res-faculty-pill ${pillClass}">${item.faculty}</span>
                            <h5 class="res-card-title">${item.title}</h5>
                            <p class="res-card-desc">${item.description}</p>
                        </div>
                        <div>
                            <div class="res-author-row">
                                <div class="res-author-avatar" style="background:${avatarColor};">${item.author_initials || 'ST'}</div>
                                <div class="res-author-text">
                                    Shared by<br><strong>${item.author_name}</strong>
                                </div>
                            </div>
                            <div class="res-card-footer">
                                <div><i class="bi bi-star-fill text-warning me-1"></i>${item.rating} &nbsp; <i class="bi bi-download text-muted me-1"></i>${item.downloads}</div>
                                <a href="login.html" class="text-success fw-bold text-decoration-none">View resource</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeFaculty = this.getAttribute('data-filter') || 'all';
            fetchFilteredResources();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(fetchFilteredResources, 250);
        });
    }
});

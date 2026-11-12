/**
 * NSBM STUDY CIRCLE - DYNAMIC RESOURCES & DETAILS BACKEND INTEGRATION
 * Communicates with backend/controllers/resource_controller.php with offline fallback
 */

(function () {
    'use strict';

    // API endpoint helper
    const API_BASE = (function () {
        const isPhp = window.location.pathname.endsWith('.php');
        const inFrontend = window.location.pathname.includes('/frontend/');
        if (inFrontend) {
            return '../backend/controllers/resource_controller.php';
        }
        return 'backend/controllers/resource_controller.php';
    })();

    // In-Memory Fallback Dataset (ensures offline file:/// reliability)
    const FALLBACK_RESOURCES = [
        {
            id: 1,
            title: "Data Structures - Complete Revision Guide",
            description: "Clear summaries, worked examples, and exam-focused notes covering the full module.",
            faculty: "Computing",
            faculty_badge: "Computing",
            badge_class: "badge-computing",
            module_code: "CS2102",
            academic_year: "Year 2",
            updated_at: "18 May 2025",
            file_type: "PDF",
            file_size_mb: "5.8 MB",
            page_count: 42,
            downloads: 1284,
            rating: 4.9,
            ratings_count: 38,
            language: "English",
            author_name: "Nethmi Perera",
            author_initials: "NP",
            author_role: "Computing - Year 3",
            community_reviewed: true
        },
        {
            id: 2,
            title: "Financial Accounting: Core Concepts",
            description: "A concise lecture companion with key formulas, journal entries, and practice questions.",
            faculty: "Business",
            faculty_badge: "Business",
            badge_class: "badge-business",
            module_code: "BUS102",
            academic_year: "Year 1",
            updated_at: "12 May 2025",
            file_type: "PDF",
            file_size_mb: "3.4 MB",
            page_count: 28,
            downloads: 864,
            rating: 4.8,
            ratings_count: 24,
            language: "English",
            author_name: "Kavindu Silva",
            author_initials: "KS",
            author_role: "Business - Year 2",
            community_reviewed: true
        },
        {
            id: 3,
            title: "Business Statistics Formula Book",
            description: "A clean reference for probability, regression, and hypothesis testing.",
            faculty: "Business",
            faculty_badge: "Business",
            badge_class: "badge-business",
            module_code: "BUS105",
            academic_year: "Year 1",
            updated_at: "10 May 2025",
            file_type: "PDF",
            file_size_mb: "1.8 MB",
            page_count: 14,
            downloads: 518,
            rating: 4.6,
            ratings_count: 15,
            language: "English",
            author_name: "Ruvin De Silva",
            author_initials: "RDS",
            author_role: "Business - Year 1",
            community_reviewed: true
        },
        {
            id: 4,
            title: "Engineering Mechanics - First-Year Guide",
            description: "Core mechanics principles, force diagrams, worked examples, and revision questions.",
            faculty: "Engineering",
            faculty_badge: "Engineering",
            badge_class: "badge-engineering",
            module_code: "ENG103",
            academic_year: "Year 1",
            updated_at: "15 May 2025",
            file_type: "PDF",
            file_size_mb: "6.2 MB",
            page_count: 50,
            downloads: 706,
            rating: 4.7,
            ratings_count: 19,
            language: "English",
            author_name: "Ravindu Mendis",
            author_initials: "RM",
            author_role: "Engineering - Year 2",
            community_reviewed: true
        },
        {
            id: 5,
            title: "Scientific Communication: Lab Report Template",
            description: "A standard reporting layout for laboratory experiments and academic write-ups.",
            faculty: "Science",
            faculty_badge: "Science",
            badge_class: "badge-science",
            module_code: "SCI106",
            academic_year: "Year 1",
            updated_at: "08 May 2025",
            file_type: "DOCX",
            file_size_mb: "1.2 MB",
            page_count: 8,
            downloads: 420,
            rating: 4.5,
            ratings_count: 11,
            language: "English",
            author_name: "Ishara Senanayake",
            author_initials: "IS",
            author_role: "Science - Year 2",
            community_reviewed: true
        },
        {
            id: 6,
            title: "Database Systems - SQL & Normalization Companion",
            description: "Relational database concepts, SQL query exercises, and step-by-step 1NF to BCNF normalization.",
            faculty: "Computing",
            faculty_badge: "Computing",
            badge_class: "badge-computing",
            module_code: "CS2105",
            academic_year: "Year 2",
            updated_at: "16 May 2025",
            file_type: "PDF",
            file_size_mb: "4.1 MB",
            page_count: 36,
            downloads: 942,
            rating: 4.8,
            ratings_count: 31,
            language: "English",
            author_name: "Tharindu Perera",
            author_initials: "TP",
            author_role: "Computing - Year 2",
            community_reviewed: true
        },
        {
            id: 7,
            title: "Laboratory Skills & Safety Handbook",
            description: "Safety protocols, chemical hazard classifications, and apparatus handling procedures for science labs.",
            faculty: "Science",
            faculty_badge: "Science",
            badge_class: "badge-science",
            module_code: "SCI104",
            academic_year: "Year 1",
            updated_at: "02 May 2025",
            file_type: "PDF",
            file_size_mb: "2.7 MB",
            page_count: 22,
            downloads: 312,
            rating: 4.6,
            ratings_count: 9,
            language: "English",
            author_name: "Sanduni Jayawardena",
            author_initials: "SJ",
            author_role: "Science - Year 3",
            community_reviewed: true
        }
    ];

    function showToast(message, isSuccess = true) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const icon = isSuccess ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-warning';
        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-dark border-0 show shadow';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon} fs-6"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toastEl);
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 400);
        }, 3200);
    }

    // Parse a fetch() response as JSON. Some hosts (e.g. InfinityFree's browser
    // check) can answer with an HTML page instead, so never trust response.json().
    async function readJson(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            showToast('Something went wrong loading data. Please refresh the page and try again.', false);
            const err = new Error('Non-JSON response from server');
            err.nonJson = true;
            throw err;
        }
    }

    // Must match RESOURCE_MAX_UPLOAD_MB in backend/controllers/resource_controller.php
    const MAX_UPLOAD_MB = 10;
    const ALLOWED_UPLOAD_EXTS = ['pdf', 'docx', 'pptx'];

    // =========================================================================
    // 1. RESOURCES LIST PAGE (resources.html & resources.php)
    // =========================================================================
    function initResourcesListPage() {
        const grid = document.getElementById('resourceGrid');
        if (!grid) return;

        const searchInput = document.getElementById('resourceSearchInput');
        const subjectFilter = document.getElementById('subjectFilter');
        const formatFilter = document.getElementById('formatFilter');
        const yearFilter = document.getElementById('yearFilter');
        const sortSelect = document.getElementById('sortSelect');
        const clearBtn = document.getElementById('clearFiltersBtn');
        const resultsCount = document.getElementById('resultsCount');

        let debounceTimer = null;
        let activeSavedIds = [];

        async function fetchResources() {
            const query = (searchInput ? searchInput.value : '').trim();
            const subjectVal = (subjectFilter ? subjectFilter.value : 'all').trim();
            const formatVal = (formatFilter ? formatFilter.value : 'all').trim();
            const yearVal = (yearFilter ? yearFilter.value : 'all').trim();
            const sortVal = (sortSelect ? sortSelect.value : 'most_downloaded').trim();

            const params = new URLSearchParams({
                action: 'list',
                format: 'json',
                subject: subjectVal,
                format: formatVal,
                year: yearVal,
                q: query,
                sort: sortVal
            });

            try {
                const response = await fetch(`${API_BASE}?${params.toString()}`);
                if (response.ok) {
                    const data = await readJson(response);
                    if (data.success && Array.isArray(data.resources)) {
                        activeSavedIds = data.saved_ids || [];
                        renderCards(data.resources, activeSavedIds);
                        return;
                    }
                }
            } catch (err) {
                // Network or file protocol fallback
            }

            // Client-side fallback if server unreachable
            applyClientFallback(query, subjectVal, formatVal, yearVal, sortVal);
        }

        function applyClientFallback(query, subjectVal, formatVal, yearVal, sortVal) {
            let filtered = FALLBACK_RESOURCES.filter(item => {
                if (subjectVal !== 'all' && subjectVal !== '') {
                    if (['computing', 'business', 'engineering', 'science'].includes(subjectVal.toLowerCase())) {
                        if (item.faculty.toLowerCase() !== subjectVal.toLowerCase()) return false;
                    } else {
                        const m = (item.title + ' ' + item.description + ' ' + item.module_code).toLowerCase();
                        if (!m.includes(subjectVal.toLowerCase())) return false;
                    }
                }
                if (formatVal !== 'all' && formatVal !== 'all formats' && formatVal !== '') {
                    if (item.file_type.toLowerCase() !== formatVal.toLowerCase()) return false;
                }
                if (yearVal !== 'all' && yearVal !== 'any year' && yearVal !== '') {
                    if (item.academic_year.toLowerCase() !== yearVal.toLowerCase()) return false;
                }
                if (query) {
                    const matchText = (item.title + ' ' + item.description + ' ' + item.module_code + ' ' + item.author_name).toLowerCase();
                    if (!matchText.includes(query.toLowerCase())) return false;
                }
                return true;
            });

            if (sortVal === 'highest_rated') {
                filtered.sort((a, b) => b.rating - a.rating || b.downloads - a.downloads);
            } else if (sortVal === 'newest_first') {
                filtered.sort((a, b) => b.id - a.id);
            } else {
                filtered.sort((a, b) => b.downloads - a.downloads || b.rating - a.rating);
            }

            // LocalStorage bookmark check fallback
            try {
                activeSavedIds = JSON.parse(localStorage.getItem('nsbm_saved_resources') || '[]');
            } catch (e) { activeSavedIds = []; }

            renderCards(filtered, activeSavedIds);
        }

        function renderCards(items, savedIds) {
            if (resultsCount) {
                resultsCount.textContent = `${items.length} resource${items.length === 1 ? '' : 's'}`;
            }

            if (items.length === 0) {
                grid.innerHTML = `
                    <div class="col-12 py-5 text-center">
                        <div class="p-5 bg-light rounded-4 border">
                            <i class="bi bi-search text-muted fs-1 mb-3 d-block"></i>
                            <h4 class="fw-bold">No resources found</h4>
                            <p class="text-muted">Try adjusting your search terms or filters to find what you need.</p>
                            <button id="emptyResetBtn" class="btn btn-outline-success mt-2">Reset all filters</button>
                        </div>
                    </div>
                `;
                const emptyBtn = document.getElementById('emptyResetBtn');
                if (emptyBtn) emptyBtn.addEventListener('click', resetFilters);
                return;
            }

            const isPhp = window.location.pathname.endsWith('.php');
            const detailsBase = isPhp ? 'resource-details.php' : 'resource-details.html';

            let html = '';
            items.forEach(res => {
                const resId = parseInt(res.id, 10);
                const isSaved = savedIds.includes(resId);
                const fileType = (res.file_type || 'PDF').toUpperCase();
                const fileIconClass = fileType === 'DOCX' ? 'bi-file-earmark-word text-primary' : 'bi-file-earmark-pdf text-danger';
                const badgeClass = res.badge_class || ('badge-' + (res.faculty || 'computing').toLowerCase());
                const faculty = res.faculty || 'Computing';
                const rating = typeof res.rating === 'number' ? res.rating.toFixed(1) : parseFloat(res.rating || 5.0).toFixed(1);
                const downloads = parseInt(res.downloads || 0, 10).toLocaleString();
                const initials = res.author_initials || 'ST';
                const authorName = res.author_name || 'Student Contributor';

                html += `
                    <div class="col-lg-6 mb-4">
                        <div class="resource-card card h-100 shadow-sm border">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="resource-format-icon bg-light rounded-2 p-2 border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi ${fileIconClass} fs-5"></i>
                                        </div>
                                        <span class="subject-badge ${badgeClass} fw-semibold">${faculty}</span>
                                    </div>
                                    <button type="button" class="bookmark-btn ${isSaved ? 'saved' : ''}" data-id="${resId}" title="${isSaved ? 'Saved to bookmarks' : 'Save resource'}">
                                        <i class="bi ${isSaved ? 'bi-bookmark-fill text-primary' : 'bi-bookmark'}"></i>
                                    </button>
                                </div>
                                <h3 class="resource-title h5 fw-bold mb-2">
                                    <a href="${detailsBase}?id=${resId}" class="text-decoration-none text-dark">${res.title}</a>
                                </h3>
                                <p class="resource-desc text-secondary small mb-4 flex-grow-1">${res.description || ''}</p>
                                
                                <div class="resource-author d-flex align-items-center gap-2 mb-3 pt-2 border-top">
                                    <div class="author-avatar bg-light border text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        ${initials}
                                    </div>
                                    <span class="author-name small text-secondary">Shared by <strong class="text-dark">${authorName}</strong></span>
                                </div>

                                <div class="resource-meta d-flex align-items-center justify-content-between pt-1">
                                    <div class="d-flex align-items-center gap-3 text-secondary small">
                                        <span class="d-flex align-items-center gap-1"><i class="bi bi-star-fill text-warning"></i> <strong class="text-dark">${rating}</strong></span>
                                        <span>•</span>
                                        <span class="d-flex align-items-center gap-1"><i class="bi bi-download"></i> ${downloads}</span>
                                    </div>
                                    <a href="${detailsBase}?id=${resId}" class="btn btn-sm btn-outline-success fw-semibold px-3 py-1 rounded-pill">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;

            // Bookmark button interactions
            grid.querySelectorAll('.bookmark-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = parseInt(btn.getAttribute('data-id'), 10);
                    await handleBookmarkToggle(id, btn);
                });
            });
        }

        async function handleBookmarkToggle(resourceId, btnEl) {
            try {
                const formData = new FormData();
                formData.append('action', 'bookmark');
                formData.append('id', resourceId);

                const response = await fetch(API_BASE, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                if (response.status === 401) {
                    showToast('Please log in to bookmark study materials.', false);
                    setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                    return;
                }

                const resData = await readJson(response);
                if (resData.success) {
                    const isSaved = resData.is_saved;
                    if (isSaved) {
                        btnEl.classList.add('saved');
                        btnEl.innerHTML = '<i class="bi bi-bookmark-fill text-primary"></i>';
                        btnEl.setAttribute('title', 'Saved to bookmarks');
                    } else {
                        btnEl.classList.remove('saved');
                        btnEl.innerHTML = '<i class="bi bi-bookmark"></i>';
                        btnEl.setAttribute('title', 'Save resource');
                    }
                    showToast(resData.message, true);
                    return;
                }
            } catch (e) {
                if (e && e.nonJson) return; // friendly toast already shown
                // Fallback to local storage if offline
                let saved = [];
                try { saved = JSON.parse(localStorage.getItem('nsbm_saved_resources') || '[]'); } catch (err) {}
                const idx = saved.indexOf(resourceId);
                let nowSaved = false;
                if (idx > -1) {
                    saved.splice(idx, 1);
                    btnEl.classList.remove('saved');
                    btnEl.innerHTML = '<i class="bi bi-bookmark"></i>';
                } else {
                    saved.push(resourceId);
                    nowSaved = true;
                    btnEl.classList.add('saved');
                    btnEl.innerHTML = '<i class="bi bi-bookmark-fill text-primary"></i>';
                }
                localStorage.setItem('nsbm_saved_resources', JSON.stringify(saved));
                showToast(nowSaved ? 'Resource saved to your bookmarks!' : 'Resource removed from bookmarks.');
            }
        }

        function resetFilters() {
            if (searchInput) searchInput.value = '';
            if (subjectFilter) subjectFilter.value = 'all';
            if (formatFilter) formatFilter.value = 'all';
            if (yearFilter) yearFilter.value = 'all';
            if (sortSelect) sortSelect.value = 'most_downloaded';
            fetchResources();
        }

        // Event listeners with debounce for typing
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchResources, 280);
            });
        }
        if (subjectFilter) subjectFilter.addEventListener('change', fetchResources);
        if (formatFilter) formatFilter.addEventListener('change', fetchResources);
        if (yearFilter) yearFilter.addEventListener('change', fetchResources);
        if (sortSelect) sortSelect.addEventListener('change', fetchResources);
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                resetFilters();
            });
        }

        // Quick Browse Cards Click (Computing, Business, Engineering, Science)
        document.querySelectorAll('.subject-browse-card').forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                const faculty = card.getAttribute('data-faculty');
                if (subjectFilter && faculty) {
                    subjectFilter.value = faculty;
                    fetchResources();
                    const filterSection = document.getElementById('resourcesExploreSection');
                    if (filterSection) filterSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        const viewAllBtn = document.getElementById('viewAllSubjectsBtn');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                resetFilters();
                const filterSection = document.getElementById('resourcesExploreSection');
                if (filterSection) filterSection.scrollIntoView({ behavior: 'smooth' });
            });
        }

        // Upload Form Handling (if present in modal or page)
        const uploadForm = document.getElementById('resourceUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Block unauthenticated guests from submitting uploads
                if (!window.__NSBM_IS_LOGGED_IN) {
                    if (typeof window.promptLoginRequired === 'function') {
                        window.promptLoginRequired('upload');
                    } else {
                        window.location.href = 'login.php?error=' + encodeURIComponent('Please log in to your student account to upload study materials.');
                    }
                    return;
                }

                // Client-side file checks (the server enforces the same rules)
                const fileInput = uploadForm.querySelector('input[type="file"]');
                const chosenFile = fileInput && fileInput.files ? fileInput.files[0] : null;
                if (chosenFile) {
                    const ext = (chosenFile.name.split('.').pop() || '').toLowerCase();
                    if (!ALLOWED_UPLOAD_EXTS.includes(ext)) {
                        showToast('Unsupported file format. Only PDF, DOCX, and PPTX files are allowed.', false);
                        return;
                    }
                    if (chosenFile.size > MAX_UPLOAD_MB * 1024 * 1024) {
                        showToast(`File is too large (${(chosenFile.size / 1048576).toFixed(1)} MB). The maximum upload size is ${MAX_UPLOAD_MB} MB.`, false);
                        return;
                    }
                }

                const submitBtn = uploadForm.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : 'Upload';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
                }

                try {
                    const formData = new FormData(uploadForm);
                    formData.append('action', 'upload');

                    const response = await fetch(API_BASE, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });

                    if (response.status === 401) {
                        showToast('Please log in to upload materials.', false);
                        setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                        return;
                    }

                    const data = await readJson(response);
                    if (data.success) {
                        showToast(data.message || 'Resource uploaded successfully!', true);
                        uploadForm.reset();
                        // Close modal if bootstrap modal
                        const modalEl = document.getElementById('uploadResourceModal');
                        if (modalEl && window.bootstrap) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        // Refresh grid
                        fetchResources();
                    } else {
                        showToast(data.message || 'Upload failed.', false);
                    }
                } catch (err) {
                    if (err && err.nonJson) return; // friendly toast already shown
                    showToast('Connection error during upload. Please check your network.', false);
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            });
        }

        // Initial fetch from MySQL
        fetchResources();
    }

    // =========================================================================
    // 2. RESOURCE DETAILS PAGE (resource-details.html & resource-details.php)
    // =========================================================================
    function initResourceDetailsPage() {
        const detailsContainer = document.getElementById('resourceDetailsContainer');
        if (!detailsContainer) return;

        const urlParams = new URLSearchParams(window.location.search);
        const resourceId = parseInt(urlParams.get('id') || '1', 10);

        async function loadResourceDetails() {
            try {
                const response = await fetch(`${API_BASE}?action=get&format=json&id=${resourceId}`);
                if (response.ok) {
                    const data = await readJson(response);
                    if (data.success && data.resource) {
                        populateDetails(data.resource, data.is_saved);
                        return;
                    }
                }
            } catch (err) {
                // Offline fallback
            }

            const fallback = FALLBACK_RESOURCES.find(r => r.id === resourceId) || FALLBACK_RESOURCES[0];
            let isSaved = false;
            try {
                const saved = JSON.parse(localStorage.getItem('nsbm_saved_resources') || '[]');
                isSaved = saved.includes(resourceId);
            } catch (e) {}
            populateDetails(fallback, isSaved);
        }

        function populateDetails(resource, isSaved) {
            // Header elements
            const formatIcon = document.getElementById('detailFormatIcon');
            const badgeFaculty = document.getElementById('detailFacultyBadge');
            const titleEl = document.getElementById('detailTitle');
            const breadcrumbTitle = document.getElementById('breadcrumbTitle');
            const metaInfo = document.getElementById('detailMetaInfo');
            const aboutText = document.getElementById('detailAboutText');
            const includedList = document.getElementById('detailWhatsIncludedList');

            // Preview elements
            const previewBadge = document.getElementById('detailPreviewBadge');
            const previewHeading = document.getElementById('detailPreviewHeading');
            const previewSubheading = document.getElementById('detailPreviewSubheading');
            const previewIndicator = document.getElementById('detailPreviewPageIndicator');

            // Right side specs
            const ratingVal = document.getElementById('detailRatingValue');
            const ratingsCount = document.getElementById('detailRatingsCount');
            const specSize = document.getElementById('detailSpecSize');
            const specPages = document.getElementById('detailSpecPages');
            const specDownloads = document.getElementById('detailSpecDownloads');
            const specLanguage = document.getElementById('detailSpecLanguage');

            // Author info
            const authorInitials = document.getElementById('detailAuthorInitials');
            const authorName = document.getElementById('detailAuthorName');
            const authorRole = document.getElementById('detailAuthorRole');

            // Feedback list
            const feedbackList = document.getElementById('detailFeedbackList');

            if (breadcrumbTitle) breadcrumbTitle.textContent = resource.title;
            if (titleEl) titleEl.textContent = resource.title;
            if (metaInfo) {
                const updated = resource.updated_at || 'May 2025';
                metaInfo.textContent = `${resource.module_code} • ${resource.academic_year} • Updated ${updated}`;
            }
            if (badgeFaculty) {
                badgeFaculty.textContent = resource.faculty;
                badgeFaculty.className = `subject-badge badge-${resource.faculty.toLowerCase()} fw-semibold`;
            }
            if (formatIcon) {
                const isDocx = (resource.file_type || '').toUpperCase() === 'DOCX';
                formatIcon.innerHTML = isDocx ?
                    '<i class="bi bi-file-earmark-word text-primary fs-3"></i>' :
                    '<i class="bi bi-file-earmark-pdf text-danger fs-3"></i>';
            }
            if (aboutText) aboutText.textContent = resource.about_text || resource.description;

            if (includedList && resource.whats_included) {
                const list = Array.isArray(resource.whats_included) ? resource.whats_included : [resource.whats_included];
                includedList.innerHTML = list.map(item => `
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success mt-1"></i>
                        <span>${item}</span>
                    </li>
                `).join('');
            }

            // Preview info
            const preview = resource.preview_doc || {};
            if (previewBadge) previewBadge.textContent = preview.badge || strtoupper(resource.faculty);
            if (previewHeading) previewHeading.textContent = preview.heading || resource.title;
            if (previewSubheading) previewSubheading.textContent = preview.subheading || resource.description;
            if (previewIndicator) previewIndicator.textContent = preview.page_indicator || `Page 1 of ${resource.page_count || 30}`;

            // Specs
            const rScore = typeof resource.rating === 'number' ? resource.rating.toFixed(1) : parseFloat(resource.rating || 5.0).toFixed(1);
            if (ratingVal) ratingVal.textContent = rScore;
            if (ratingsCount) ratingsCount.textContent = `(${resource.ratings_count || 0} ratings)`;
            if (specSize) specSize.textContent = resource.file_size_mb || '3.5 MB';
            if (specPages) specPages.textContent = resource.page_count || 24;
            if (specDownloads) specDownloads.textContent = parseInt(resource.downloads || 0, 10).toLocaleString();
            if (specLanguage) specLanguage.textContent = resource.language || 'English';

            // Author
            if (authorInitials) authorInitials.textContent = resource.author_initials || 'ST';
            if (authorName) authorName.textContent = resource.author_name || 'Student Contributor';
            if (authorRole) authorRole.textContent = resource.author_role || (resource.faculty + ' • Year 2');

            // Render Feedback
            renderFeedbackReviews(resource.feedback || []);

            // Bookmark Button Setup
            const saveBtn = document.getElementById('detailSaveBtn');
            if (saveBtn) {
                updateBookmarkButtonUI(saveBtn, isSaved);
                saveBtn.onclick = async () => {
                    await toggleDetailBookmark(resource.id, saveBtn);
                };
            }

            // Download Button Setup (Restricted Exclusively to Logged-in Users)
            const downloadBtn = document.getElementById('detailDownloadBtn');
            if (downloadBtn) {
                downloadBtn.onclick = (e) => {
                    e.preventDefault();
                    if (!window.__NSBM_IS_LOGGED_IN) {
                        if (typeof window.promptLoginRequired === 'function') {
                            window.promptLoginRequired('download', resource.title);
                        } else {
                            window.location.href = 'login.php?error=' + encodeURIComponent('Please log in to your student account to download study materials.');
                        }
                        return;
                    }

                    // Update downloads count in UI immediately
                    const currentDownloads = parseInt(specDownloads ? specDownloads.textContent.replace(/,/g, '') : '0', 10);
                    if (specDownloads) specDownloads.textContent = (currentDownloads + 1).toLocaleString();
                    showToast(`Downloading "${resource.title}.${(resource.file_type || 'pdf').toLowerCase()}"...`);

                    // Trigger secure download from backend
                    window.location.href = `${API_BASE}?action=download&id=${resource.id}`;
                };
            }

            // Report Button Setup
            const reportBtn = document.getElementById('detailReportBtn');
            if (reportBtn) {
                reportBtn.onclick = async (e) => {
                    e.preventDefault();
                    const reason = prompt("Please specify the reason for reporting (e.g., Copyright, Inappropriate content, Incorrect answers):", "Academic integrity");
                    if (reason) {
                        try {
                            const fd = new FormData();
                            fd.append('action', 'report');
                            fd.append('id', resource.id);
                            fd.append('reason', reason);
                            fd.append('snippet', resource.title);
                            await fetch(API_BASE, { method: 'POST', body: fd });
                        } catch (err) {}
                        showToast("Thank you. A moderation report has been submitted to the academic team.");
                    }
                };
            }

            // Student Rating & Feedback Submission Form Setup
            const reviewForm = document.getElementById('resourceReviewForm');
            if (reviewForm) {
                reviewForm.onsubmit = async (e) => {
                    e.preventDefault();
                    const ratingInput = reviewForm.querySelector('input[name="rating"]:checked') || reviewForm.querySelector('#reviewRatingSelect');
                    const commentInput = reviewForm.querySelector('#reviewCommentText');
                    const ratingVal = ratingInput ? parseInt(ratingInput.value, 10) : 5;
                    const commentVal = commentInput ? commentInput.value.trim() : '';

                    if (!commentVal) {
                        showToast("Please enter a short comment with your review.", false);
                        return;
                    }

                    try {
                        const fd = new FormData();
                        fd.append('action', 'review');
                        fd.append('id', resource.id);
                        fd.append('rating', ratingVal);
                        fd.append('comment', commentVal);

                        const resp = await fetch(API_BASE, {
                            method: 'POST',
                            body: fd,
                            headers: { 'Accept': 'application/json' }
                        });

                        if (resp.status === 401) {
                            showToast("Please log in to submit a rating or review.", false);
                            setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                            return;
                        }

                        const d = await readJson(resp);
                        if (d.success) {
                            showToast(d.message || "Review submitted successfully!", true);
                            if (d.average_rating && ratingValEl) ratingValEl.textContent = parseFloat(d.average_rating).toFixed(1);
                            if (d.ratings_count && ratingsCount) ratingsCount.textContent = `(${d.ratings_count} ratings)`;
                            if (d.reviews) renderFeedbackReviews(d.reviews);
                            reviewForm.reset();
                        } else {
                            showToast(d.message || "Failed to submit review.", false);
                        }
                    } catch (err) {
                        if (err && err.nonJson) return; // friendly toast already shown
                        showToast("Failed to connect to review server.", false);
                    }
                };
            }
        }

        function renderFeedbackReviews(reviews) {
            const feedbackList = document.getElementById('detailFeedbackList');
            if (!feedbackList) return;

            if (reviews.length === 0) {
                feedbackList.innerHTML = `
                    <div class="text-secondary small py-2">
                        <i class="bi bi-chat-left-text me-1"></i> No reviews yet. Be the first student to review this material below!
                    </div>
                `;
                return;
            }

            feedbackList.innerHTML = reviews.map(fb => `
                <div class="feedback-item d-flex gap-3 pt-3 border-top">
                    <div class="feedback-avatar bg-light border text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        ${fb.initials || 'ST'}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h5 class="h6 fw-bold mb-0 text-dark">${fb.name}</h5>
                            <span class="text-muted small" style="font-size:0.75rem;">${fb.created_at || 'Recently'}</span>
                        </div>
                        <div class="text-warning small mb-1">
                            ${'<i class="bi bi-star-fill"></i>'.repeat(fb.rating || 5)}
                        </div>
                        <p class="text-secondary small mb-0">${fb.comment}</p>
                    </div>
                </div>
            `).join('');
        }

        function updateBookmarkButtonUI(btn, isSaved) {
            if (isSaved) {
                btn.innerHTML = '<i class="bi bi-bookmark-fill me-2 text-primary"></i> Saved to bookmarks';
                btn.classList.add('btn-primary-subtle');
            } else {
                btn.innerHTML = '<i class="bi bi-bookmark me-2"></i> Save resource';
                btn.classList.remove('btn-primary-subtle');
            }
        }

        async function toggleDetailBookmark(resId, btn) {
            try {
                const fd = new FormData();
                fd.append('action', 'bookmark');
                fd.append('id', resId);

                const response = await fetch(API_BASE, {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json' }
                });

                if (response.status === 401) {
                    showToast('Please log in to bookmark resources.', false);
                    setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                    return;
                }

                const resData = await readJson(response);
                if (resData.success) {
                    updateBookmarkButtonUI(btn, resData.is_saved);
                    showToast(resData.message, true);
                    return;
                }
            } catch (err) {
                if (err && err.nonJson) return; // friendly toast already shown
            }

            // Local fallback
            let saved = [];
            try { saved = JSON.parse(localStorage.getItem('nsbm_saved_resources') || '[]'); } catch (e) {}
            const idx = saved.indexOf(resId);
            let nowSaved = false;
            if (idx > -1) {
                saved.splice(idx, 1);
            } else {
                saved.push(resId);
                nowSaved = true;
            }
            localStorage.setItem('nsbm_saved_resources', JSON.stringify(saved));
            updateBookmarkButtonUI(btn, nowSaved);
            showToast(nowSaved ? 'Resource saved to your bookmarks!' : 'Resource removed from bookmarks.');
        }

        loadResourceDetails();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        initResourcesListPage();
        initResourceDetailsPage();
    });
})();

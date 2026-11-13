<?php
// ============================================================================
// NSBM STUDY CIRCLE - DYNAMIC RESOURCES PAGE (PHP + MySQL)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/Resource.php';

$resourceModel = new ResourceModel();
$subject = $_GET['subject'] ?? 'all';
$format = $_GET['format'] ?? 'all';
$year = $_GET['year'] ?? 'all';
$search = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'most_downloaded';

$resources = $resourceModel->getFilteredResources($subject, $format, $year, $search, $sort, 20, 0);

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userRole = $_SESSION['role'] ?? 'student';
$userFaculty = $_SESSION['faculty'] ?? 'Computing';

// Check if user has saved bookmarks
$savedResourceIds = [];
if ($isLoggedIn) {
    $savedResourceIds = $resourceModel->getUserSavedResourceIds($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Library - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
        <!-- Website Favicon: Study Circle logo -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="alternate icon" type="image/jpeg" href="../src/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.svg"><link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="alternate icon" type="image/jpeg" href="../src/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.svg">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ====================================================================
         1. SHARED PREMIUM ANIMATED NAVBAR
         ==================================================================== -->
    <?php $activePage = 'resources'; include __DIR__ . '/includes/header.php'; ?>

    <!-- ====================================================================
         2. RESOURCE LIBRARY HERO SECTION
         ==================================================================== -->
    <header class="resource-hero">
        <div class="container-xl">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8">
                    <span class="hero-library-pill">
                        <i class="bi bi-collection-play-fill me-1"></i> RESOURCE LIBRARY
                    </span>
                    <h1 class="hero-library-title">Find your next study advantage</h1>
                    <p class="hero-library-subtitle">
                        Search student-shared notes, revision guides, and learning tools organized by subject and module.
                    </p>
                </div>
                <div class="col-lg-4 d-flex justify-content-lg-end mt-4 mt-lg-0">
                    <div class="hero-counter-box text-center text-lg-start">
                        <div class="counter-number">320+</div>
                        <div class="counter-label">Curated resources</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ====================================================================
         3. BROWSE ALL FIRST-YEAR SUBJECTS
         ==================================================================== -->
    <section class="py-5" style="background-color: var(--nsbm-bg);">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
                <div>
                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 mb-2 rounded-pill text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">NSBM Year 1</span>
                    <h2 class="h3 fw-bold mb-0">Browse all first-year subjects</h2>
                </div>
                <button id="viewAllSubjectsBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold">
                    View all subjects <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>

            <div class="row g-4">
                <!-- 1. Computing -->
                <div class="col-lg-3 col-md-6">
                    <div class="subject-browse-card computing h-100 p-4" data-faculty="Computing" role="button" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="subject-icon-box bg-white shadow-sm">
                                <i class="bi bi-code-slash text-primary"></i>
                            </div>
                            <i class="bi bi-arrow-right subject-arrow"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3 text-dark">Computing</h3>
                        <ul class="subject-modules-list list-unstyled mb-0">
                            <li>Intro to Programming</li>
                            <li>Computer Systems</li>
                            <li>Algorithms & Logic</li>
                            <li>Web Development</li>
                            <li>Discrete Mathematics</li>
                            <li>Database Principles</li>
                        </ul>
                    </div>
                </div>

                <!-- 2. Business -->
                <div class="col-lg-3 col-md-6">
                    <div class="subject-browse-card business h-100 p-4" data-faculty="Business" role="button" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="subject-icon-box bg-white shadow-sm">
                                <i class="bi bi-briefcase text-success"></i>
                            </div>
                            <i class="bi bi-arrow-right subject-arrow"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3 text-dark">Business</h3>
                        <ul class="subject-modules-list list-unstyled mb-0">
                            <li>Principles of Management</li>
                            <li>Financial Accounting</li>
                            <li>Marketing Fundamentals</li>
                            <li>Business Economics</li>
                            <li>Business Statistics</li>
                            <li>Organizational Behaviour</li>
                        </ul>
                    </div>
                </div>

                <!-- 3. Engineering -->
                <div class="col-lg-3 col-md-6">
                    <div class="subject-browse-card engineering h-100 p-4" data-faculty="Engineering" role="button" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="subject-icon-box bg-white shadow-sm">
                                <i class="bi bi-gear-wide-connected text-warning"></i>
                            </div>
                            <i class="bi bi-arrow-right subject-arrow"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3 text-dark">Engineering</h3>
                        <ul class="subject-modules-list list-unstyled mb-0">
                            <li>Engineering Mathematics I</li>
                            <li>Engineering Physics</li>
                            <li>Mechanics of Materials</li>
                            <li>CAD & Drafting Basics</li>
                            <li>Materials Science</li>
                            <li>Electrical Circuit Theory</li>
                        </ul>
                    </div>
                </div>

                <!-- 4. Science -->
                <div class="col-lg-3 col-md-6">
                    <div class="subject-browse-card science h-100 p-4" data-faculty="Science" role="button" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="subject-icon-box bg-white shadow-sm">
                                <i class="bi bi-virus text-info"></i>
                            </div>
                            <i class="bi bi-arrow-right subject-arrow"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3 text-dark">Science</h3>
                        <ul class="subject-modules-list list-unstyled mb-0">
                            <li>General Chemistry I</li>
                            <li>Cell Biology & Genetics</li>
                            <li>Applied Statistics</li>
                            <li>Laboratory Techniques</li>
                            <li>Physics for Sciences</li>
                            <li>Scientific Communication</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         4. RESOURCE EXPLORATION & FILTERING SECTION
         ==================================================================== -->
    <section class="py-5" id="resourcesExploreSection">
        <div class="container-xl">
            <div class="row g-4">
                
                <!-- Left Sidebar Filters -->
                <div class="col-lg-3">
                    <div class="filter-sidebar-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h4 class="h6 fw-bold mb-0 text-uppercase tracking-wider" style="letter-spacing: 0.5px;">Filters</h4>
                            <a href="#" id="clearFiltersBtn" class="clear-filters-link">Clear all</a>
                        </div>

                        <!-- Filter: Subject -->
                        <div class="filter-group">
                            <label for="subjectFilter" class="filter-label">Subject</label>
                            <select id="subjectFilter" class="form-select filter-select">
                                <option value="all" <?= $subject === 'all' ? 'selected' : '' ?>>All subjects</option>
                                <option value="Computing" <?= $subject === 'Computing' ? 'selected' : '' ?>>Computing</option>
                                <option value="Business" <?= $subject === 'Business' ? 'selected' : '' ?>>Business</option>
                                <option value="Engineering" <?= $subject === 'Engineering' ? 'selected' : '' ?>>Engineering</option>
                                <option value="Science" <?= $subject === 'Science' ? 'selected' : '' ?>>Science</option>
                            </select>
                        </div>

                        <!-- Filter: Resource Type -->
                        <div class="filter-group">
                            <label for="formatFilter" class="filter-label">Resource Type</label>
                            <select id="formatFilter" class="form-select filter-select">
                                <option value="all" <?= $format === 'all' ? 'selected' : '' ?>>All formats</option>
                                <option value="pdf" <?= strtolower($format) === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                <option value="docx" <?= strtolower($format) === 'docx' ? 'selected' : '' ?>>DOCX</option>
                                <option value="pptx" <?= strtolower($format) === 'pptx' ? 'selected' : '' ?>>PPTX</option>
                            </select>
                        </div>

                        <!-- Filter: Academic Year -->
                        <div class="filter-group mb-3">
                            <label for="yearFilter" class="filter-label">Academic Year</label>
                            <select id="yearFilter" class="form-select filter-select">
                                <option value="all" <?= $year === 'all' ? 'selected' : '' ?>>Any year</option>
                                <option value="Year 1" <?= $year === 'Year 1' ? 'selected' : '' ?>>Year 1</option>
                                <option value="Year 2" <?= $year === 'Year 2' ? 'selected' : '' ?>>Year 2</option>
                                <option value="Year 3" <?= $year === 'Year 3' ? 'selected' : '' ?>>Year 3</option>
                            </select>
                        </div>

                        <!-- Upload Resource Trigger Box -->
                        <div class="border-top pt-3 text-center">
                            <?php if ($isLoggedIn): ?>
                                <a href="student/uploads.php" class="btn btn-outline-success w-100 fw-bold btn-sm py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-cloud-arrow-up"></i> Upload Study Material
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-success w-100 fw-bold btn-sm py-2 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="promptLoginRequired('upload')">
                                    <i class="bi bi-lock-fill text-muted"></i> Upload Study Material
                                </button>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Share your notes with the NSBM community</small>
                        </div>
                    </div>
                </div>

                <!-- Right Main Resource Grid -->
                <div class="col-lg-9">
                    <!-- Top Search & Sort Bar -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div class="flex-grow-1 position-relative" style="min-width: 260px;">
                            <input type="text" id="resourceSearchInput" class="form-control resource-search-bar" placeholder="Search by title, topic, or keyword..." value="<?= htmlspecialchars($search) ?>">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="right: 18px;"></i>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="sortSelect" class="small text-muted fw-semibold mb-0 text-nowrap">Sort by:</label>
                            <select id="sortSelect" class="form-select sort-select form-select-sm">
                                <option value="most_downloaded" <?= $sort === 'most_downloaded' ? 'selected' : '' ?>>Most downloaded</option>
                                <option value="highest_rated" <?= $sort === 'highest_rated' ? 'selected' : '' ?>>Highest rated</option>
                                <option value="newest_first" <?= $sort === 'newest_first' ? 'selected' : '' ?>>Newest first</option>
                            </select>
                        </div>
                    </div>

                    <!-- Counter & Verification Subtext -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span id="resultsCount" class="fw-bold text-dark fs-6"><?= count($resources) ?> resources</span>
                        <span class="text-secondary small d-flex align-items-center gap-1">
                            <i class="bi bi-patch-check-fill text-success"></i> Showing verified community uploads
                        </span>
                    </div>

                    <!-- Cards Grid -->
                    <div class="row" id="resourceGrid">
                        <!-- Populated and dynamically hydrated by resources.js -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         UPLOAD RESOURCE MODAL (Student Upload System)
         ==================================================================== -->
    <div class="modal fade" id="uploadResourceModal" tabindex="-1" aria-labelledby="uploadResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="uploadResourceModalLabel">
                            <i class="bi bi-cloud-arrow-up text-success me-2"></i> Upload Study Material
                        </h5>
                        <p class="text-secondary small mb-0">Contribute your lecture summaries, revision guides, or formula books.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="resourceUploadForm" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <?php if (!$isLoggedIn): ?>
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                                <div>You need to be logged in to upload materials. <a href="login.php" class="fw-bold alert-link">Log in here</a></div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="uploadTitle" class="form-label fw-semibold small">Resource Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="uploadTitle" name="title" placeholder="e.g. Data Structures - Complete Revision Guide" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="uploadFaculty" class="form-label fw-semibold small">Faculty <span class="text-danger">*</span></label>
                                <select class="form-select" id="uploadFaculty" name="faculty" required>
                                    <option value="Computing">Computing</option>
                                    <option value="Business">Business</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Science">Science</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="uploadModule" class="form-label fw-semibold small">Module Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="uploadModule" name="module_code" placeholder="e.g. CS2102 or COM101" required>
                            </div>
                            <div class="col-md-4">
                                <label for="uploadYear" class="form-label fw-semibold small">Academic Year</label>
                                <select class="form-select" id="uploadYear" name="academic_year">
                                    <option value="Year 1">Year 1</option>
                                    <option value="Year 2">Year 2</option>
                                    <option value="Year 3">Year 3</option>
                                    <option value="Year 4">Year 4</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="uploadDesc" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" id="uploadDesc" name="description" rows="3" placeholder="Brief summary of topics covered in this resource..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="uploadFile" class="form-label fw-semibold small">Document File (PDF, DOCX, PPTX - Max 10MB) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="uploadFile" name="resource_file" accept=".pdf,.docx,.pptx" required>
                            <small class="text-muted" style="font-size:0.75rem;">Verified community uploads are scanned for security and academic integrity.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold px-4" <?= !$isLoggedIn ? 'disabled' : '' ?>>
                            <i class="bi bi-upload me-1"></i> Submit Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         5. FOOTER
         ==================================================================== -->
    <footer class="figma-footer">
        <div class="container-xl">
            <div class="row g-4 justify-content-between">
                <!-- Col 1: Brand Info -->
                <div class="col-lg-4 col-md-6">
                    <a href="index.php" class="brand-logo-container mb-3 text-decoration-none">
                        <div class="brand-logo-wrapper">
                            <img src="assets/images/logo.svg" alt="NSBM Study Circle Logo" class="brand-logo-img" onerror="this.onerror=null; this.src='assets/images/logo.jpg';">
                        </div>
                        <div class="brand-text-group">
                            <span class="brand-title">NSBM Study Circle</span>
                            <span class="brand-subtitle">Learn Together. Grow Together.</span>
                        </div>
                    </a>
                    <p class="text-muted small" style="max-width: 320px;">
                        A focused academic community for sharing knowledge, finding support, and learning better together.
                    </p>
                </div>

                <!-- Col 2: Platform -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6>PLATFORM</h6>
                    <a href="resources.php">Study materials</a>
                    <a href="discussions.php">Discussions</a>
                    <a href="study-groups.php">Study groups</a>
                    <a href="sessions.php">Study sessions</a>
                </div>

                <!-- Col 3: Community -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6>COMMUNITY</h6>
                    <a href="about.php">About</a>
                    <a href="contact.php">Community guide</a>
                    <a href="contact.php">Contact &amp; help</a>
                </div>

                <!-- Col 4: Account -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6>ACCOUNT</h6>
                    <a href="login.php">Log in</a>
                    <a href="signup.php">Create account</a>
                    <a href="student/index.php">Student dashboard</a>
                    <a href="admin/index.php">Admin console</a>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="footer-bottom-line d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>&copy; 2026 NSBM Study Circle. An independent student learning platform.</div>
                <div class="d-flex gap-3">
                    <a href="contact.php" class="text-muted text-decoration-none">Privacy</a>
                    <a href="contact.php" class="text-muted text-decoration-none">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ====================================================================
         AUTHENTICATION REQUIRED MODAL (Blocks Unauthenticated Guests)
         ==================================================================== -->
    <div class="modal fade" id="authRequiredModal" tabindex="-1" aria-labelledby="authRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 p-2">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 pt-0 pb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px; background: rgba(22, 163, 74, 0.12); color: #16A34A; font-size: 1.8rem;">
                        <i class="bi bi-shield-lock-fill" id="authPromptIcon"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark" id="authRequiredModalLabel">Authentication Required</h5>
                    <p class="text-secondary small mb-4" id="authPromptMessage">
                        Uploading and downloading study materials is restricted exclusively to authenticated students. Please log in to your student account to continue.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="login.php?error=<?= urlencode('Please log in to your student account to access this feature.') ?>" id="authPromptLoginBtn" class="btn btn-success fw-bold py-2 rounded-3 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In to Continue
                        </a>
                        <a href="signup.php" class="btn btn-outline-secondary fw-semibold py-2 rounded-3">
                            Create an Account
                        </a>
                    </div>
                    <div class="text-muted small mt-3" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i> Uploads &amp; downloads are securely managed from the Student Dashboard.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global State Bridge -->
    <script>
        window.__NSBM_IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;

        window.promptLoginRequired = function(actionType = 'upload', resourceTitle = '') {
            const modalEl = document.getElementById('authRequiredModal');
            const msgEl = document.getElementById('authPromptMessage');
            const iconEl = document.getElementById('authPromptIcon');
            const loginBtn = document.getElementById('authPromptLoginBtn');
            
            let message = 'Access is restricted exclusively to authenticated students.';
            let errorParam = 'Please log in to your student account.';
            
            if (actionType === 'download') {
                message = resourceTitle
                    ? `Downloading "${resourceTitle}" is restricted exclusively to verified students. Please log in to your Student Dashboard to download.`
                    : 'Downloading study materials is restricted exclusively to authenticated students. Please log in to download full documents.';
                errorParam = 'Please log in to your student account to download study materials.';
                if (iconEl) iconEl.className = 'bi bi-file-earmark-lock-fill';
            } else if (actionType === 'upload') {
                message = 'Uploading study materials is reserved for authenticated students from the Student Dashboard. Please log in to contribute notes.';
                errorParam = 'Please log in to your student account to upload study materials.';
                if (iconEl) iconEl.className = 'bi bi-cloud-arrow-up-fill';
            } else if (actionType === 'save') {
                message = 'Saving resources to bookmarks is available to authenticated students. Please log in to manage your library.';
                errorParam = 'Please log in to your student account to save resources.';
                if (iconEl) iconEl.className = 'bi bi-bookmark-plus-fill';
            }
            
            if (msgEl) msgEl.textContent = message;
            if (loginBtn) {
                loginBtn.href = `login.php?error=${encodeURIComponent(errorParam)}`;
            }
            
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                alert(message);
                window.location.href = `login.php?error=${encodeURIComponent(errorParam)}`;
            }
        };
    </script>

    <!-- Global Interactions & Stability JS -->
    <script src="assets/js/main.js"></script>

    <!-- Resources Logic Script -->
    <script src="assets/js/resources.js"></script>
</body>
</html>

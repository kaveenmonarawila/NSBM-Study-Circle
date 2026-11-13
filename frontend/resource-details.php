<?php
// ============================================================================
// NSBM STUDY CIRCLE - DYNAMIC RESOURCE DETAILS (PHP + MySQL)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/Resource.php';

$resourceModel = new ResourceModel();
$id = (int)($_GET['id'] ?? 1);
$resource = $resourceModel->getResourceById($id);

if (!$resource) {
    header("Location: resources.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userRole = $_SESSION['role'] ?? 'student';
$currentUserId = $_SESSION['user_id'] ?? null;

$isSaved = false;
if ($isLoggedIn) {
    $isSaved = $resourceModel->isResourceSaved($id, $currentUserId);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resource['title']) ?> - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
        <!-- Website Favicon: Study Circle logo -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
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
         2. BREADCRUMB & BACK LINK
         ==================================================================== -->
    <div class="bg-body-secondary py-3 border-bottom">
        <div class="container-xl">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom mb-0">
                    <li class="breadcrumb-item"><a href="resources.php"><i class="bi bi-arrow-left me-1"></i> Resources</a></li>
                    <li class="breadcrumb-item active" aria-current="page" id="breadcrumbTitle"><?= htmlspecialchars($resource['title']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- ====================================================================
         3. RESOURCE DETAILS MAIN CONTENT
         ==================================================================== -->
    <main class="py-5" id="resourceDetailsContainer">
        <div class="container-xl">
            <div class="row g-4 g-lg-5">
                
                <!-- Left Details Column (2/3 width) -->
                <div class="col-lg-8">
                    <div class="resource-main-card shadow-sm">
                        
                        <!-- Header with File Icon, Badge, Title, Meta -->
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="resource-format-icon bg-light rounded-3 p-3 border d-flex align-items-center justify-content-center" id="detailFormatIcon" style="width: 58px; height: 58px;">
                                <?php if (strtoupper($resource['file_type']) === 'DOCX'): ?>
                                    <i class="bi bi-file-earmark-word text-primary fs-3"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-pdf text-danger fs-3"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <span class="subject-badge badge-<?= strtolower($resource['faculty']) ?> mb-2 d-inline-block fw-semibold" id="detailFacultyBadge"><?= htmlspecialchars($resource['faculty']) ?></span>
                                <h1 class="h3 fw-bold mb-2 text-dark" id="detailTitle"><?= htmlspecialchars($resource['title']) ?></h1>
                                <div class="text-secondary small fw-medium" id="detailMetaInfo"><?= htmlspecialchars($resource['module_code']) ?> • <?= htmlspecialchars($resource['academic_year']) ?> • Updated <?= htmlspecialchars($resource['updated_at'] ?? 'May 2025') ?></div>
                            </div>
                        </div>

                        <!-- Community Reviewed Pill Box -->
                        <div class="review-verified-pill mb-4">
                            <i class="bi bi-patch-check-fill fs-5 text-success flex-shrink-0"></i>
                            <div>
                                <strong class="d-block text-success-emphasis">Community reviewed</strong>
                                <span class="small text-secondary">Checked for relevance, clarity, and appropriate academic use.</span>
                            </div>
                        </div>

                        <!-- About This Resource Section -->
                        <section class="mb-4">
                            <h2 class="h5 fw-bold mb-3 text-dark">About this resource</h2>
                            <p class="text-secondary leading-relaxed mb-0" id="detailAboutText" style="line-height: 1.75;">
                                <?= htmlspecialchars($resource['about_text'] ?? $resource['description']) ?>
                            </p>
                        </section>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- What's Included Section -->
                        <section class="mb-5">
                            <h2 class="h5 fw-bold mb-3 text-dark">What's included</h2>
                            <ul class="list-unstyled mb-0" id="detailWhatsIncludedList">
                                <?php if (!empty($resource['whats_included'])): ?>
                                    <?php foreach ($resource['whats_included'] as $inc): ?>
                                        <li class="d-flex align-items-start gap-2 mb-2">
                                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                            <span><?= htmlspecialchars($inc) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </section>

                        <!-- Document Preview Canvas Box -->
                        <section class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0 text-dark">Document preview</h2>
                                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" id="detailPreviewPageIndicator"><?= htmlspecialchars($resource['preview_doc']['page_indicator'] ?? 'Page 1 of ' . ($resource['page_count'] ?? 30)) ?></span>
                            </div>

                            <div class="document-preview-box">
                                <div class="preview-paper-sheet">
                                    <div class="mb-3">
                                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 1px;" id="detailPreviewBadge"><?= htmlspecialchars($resource['preview_doc']['badge'] ?? strtoupper($resource['faculty'])) ?></span>
                                    </div>
                                    <h3 class="h4 fw-bold text-dark mb-2" id="detailPreviewHeading"><?= htmlspecialchars($resource['preview_doc']['heading'] ?? 'Course Revision Guide') ?></h3>
                                    <p class="text-secondary small mb-4" id="detailPreviewSubheading"><?= htmlspecialchars($resource['preview_doc']['subheading'] ?? 'Core concepts, visual explanations, and worked examples.') ?></p>
                                    
                                    <!-- Simulated Preview Layout Lines -->
                                    <div class="preview-doc-lines w-100 px-3">
                                        <div class="bg-secondary-subtle rounded-1 mb-2" style="height: 10px; width: 85%; margin: 0 auto;"></div>
                                        <div class="bg-secondary-subtle rounded-1 mb-2" style="height: 10px; width: 95%; margin: 0 auto;"></div>
                                        <div class="bg-secondary-subtle rounded-1 mb-2" style="height: 10px; width: 90%; margin: 0 auto;"></div>
                                        <div class="bg-secondary-subtle rounded-1 mb-4" style="height: 10px; width: 70%; margin: 0 auto;"></div>

                                        <div class="border rounded p-3 text-start bg-light-subtle mb-3">
                                            <div class="fw-bold small text-dark mb-1">Key Principles & Module Summary</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Verified summaries and formulas aligned with the semester curriculum.</div>
                                        </div>
                                    </div>

                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-file-earmark-lock text-secondary me-1"></i> Preview mode • Full document available on download
                                    </div>
                                </div>
                            </div>
                        </section>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Student Feedback Section -->
                        <section class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0 text-dark">Student feedback</h2>
                                <span class="badge bg-light text-secondary border px-2 py-1"><?= count($resource['feedback'] ?? []) ?> reviews</span>
                            </div>

                            <div id="detailFeedbackList">
                                <?php if (!empty($resource['feedback'])): ?>
                                    <?php foreach ($resource['feedback'] as $fb): ?>
                                        <div class="feedback-item d-flex gap-3 pt-3 border-top">
                                            <div class="feedback-avatar bg-light border text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                                <?= htmlspecialchars($fb['initials']) ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <h3 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($fb['name']) ?></h3>
                                                    <span class="text-muted small" style="font-size:0.75rem;"><?= htmlspecialchars($fb['created_at'] ?? 'Recently') ?></span>
                                                </div>
                                                <div class="text-warning small mb-1">
                                                    <?php for ($s = 0; $s < ($fb['rating'] ?? 5); $s++): ?>
                                                        <i class="bi bi-star-fill"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="text-secondary small mb-0">
                                                    <?= htmlspecialchars($fb['comment']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-secondary small py-2">
                                        <i class="bi bi-chat-left-text me-1"></i> No reviews yet. Be the first student to review this material below!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Submit Rating & Feedback Form (Requirement 8) -->
                        <section class="mt-4 pt-3 border-top">
                            <h3 class="h6 fw-bold text-dark mb-2">Leave your feedback</h3>
                            <?php if ($isLoggedIn): ?>
                                <form id="resourceReviewForm" class="bg-light p-3 rounded-3 border">
                                    <div class="row g-2 align-items-center mb-2">
                                        <div class="col-auto">
                                            <label class="form-label small fw-semibold mb-0">Your Rating:</label>
                                        </div>
                                        <div class="col-auto">
                                            <select class="form-select form-select-sm" id="reviewRatingSelect" name="rating">
                                                <option value="5">⭐⭐⭐⭐⭐ (5 - Outstanding)</option>
                                                <option value="4">⭐⭐⭐⭐ (4 - Very Helpful)</option>
                                                <option value="3">⭐⭐⭐ (3 - Good)</option>
                                                <option value="2">⭐⭐ (2 - Fair)</option>
                                                <option value="1">⭐ (1 - Needs Work)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <textarea class="form-control form-control-sm" id="reviewCommentText" name="comment" rows="2" placeholder="Share your experience with this study resource (e.g. clarity, helpful diagrams, exam readiness)..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3">
                                        <i class="bi bi-send me-1"></i> Post Feedback
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="bg-light p-3 rounded-3 border text-center">
                                    <p class="small text-secondary mb-2">Please log in to rate and share your feedback on this material.</p>
                                    <a href="login.php" class="btn btn-sm btn-outline-success fw-semibold">Log in to Review</a>
                                </div>
                            <?php endif; ?>
                        </section>

                    </div>
                </div>

                <!-- Right Sticky Specs Sidebar (1/3 width) -->
                <div class="col-lg-4">
                    <div class="resource-specs-card shadow-sm">
                        
                        <!-- Rating Header -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <span class="fs-1 fw-bold text-dark lh-1" id="detailRatingValue"><?= number_format($resource['rating'], 1) ?></span>
                            <div>
                                <div class="text-warning mb-1">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="small text-secondary" id="detailRatingsCount">(<?= $resource['ratings_count'] ?? 25 ?> ratings)</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mb-4">
                            <?php if ($isLoggedIn): ?>
                                <button id="detailDownloadBtn" class="btn btn-success fw-bold py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-download"></i> Download <?= strtoupper($resource['file_type'] ?? 'PDF') ?>
                                </button>
                            <?php else: ?>
                                <button id="detailDownloadBtn" class="btn btn-success fw-bold py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="promptLoginRequired('download', '<?= addslashes(htmlspecialchars($resource['title'])) ?>')">
                                    <i class="bi bi-lock-fill"></i> Log In to Download <?= strtoupper($resource['file_type'] ?? 'PDF') ?>
                                </button>
                            <?php endif; ?>
                            <button id="detailSaveBtn" class="btn btn-outline-secondary fw-semibold py-2 rounded-3 d-flex align-items-center justify-content-center gap-2 <?= $isSaved ? 'btn-primary-subtle' : '' ?>" <?= !$isLoggedIn ? 'onclick="promptLoginRequired(\'save\')"' : '' ?>>
                                <i class="bi <?= $isSaved ? 'bi-bookmark-fill text-primary' : 'bi-bookmark' ?> me-2"></i> <?= $isSaved ? 'Saved to bookmarks' : 'Save resource' ?>
                            </button>
                        </div>

                        <!-- 2x2 Specs Grid -->
                        <div class="specs-grid">
                            <div>
                                <div class="specs-item-label">File size</div>
                                <div class="specs-item-value" id="detailSpecSize"><?= htmlspecialchars($resource['file_size_mb'] ?? '4.2 MB') ?></div>
                            </div>
                            <div>
                                <div class="specs-item-label">Pages</div>
                                <div class="specs-item-value" id="detailSpecPages"><?= (int)($resource['page_count'] ?? 24) ?></div>
                            </div>
                            <div>
                                <div class="specs-item-label">Downloads</div>
                                <div class="specs-item-value" id="detailSpecDownloads"><?= number_format($resource['downloads'] ?? 500) ?></div>
                            </div>
                            <div>
                                <div class="specs-item-label">Language</div>
                                <div class="specs-item-value" id="detailSpecLanguage"><?= htmlspecialchars($resource['language'] ?? 'English') ?></div>
                            </div>
                        </div>

                        <!-- Uploaded By Card -->
                        <div class="uploaded-by-box mb-4">
                            <div class="small fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.6px;">Uploaded by</div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="author-avatar bg-light border text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" id="detailAuthorInitials" style="width: 44px; height: 44px;">
                                    <?= htmlspecialchars($resource['author_initials'] ?? 'ST') ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" id="detailAuthorName"><?= htmlspecialchars($resource['author_name'] ?? 'Student Contributor') ?></div>
                                    <div class="small text-secondary" id="detailAuthorRole"><?= htmlspecialchars($resource['author_role'] ?? ($resource['faculty'] . ' • Year 2')) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Resource Link -->
                        <div class="text-center pt-2 border-top">
                            <a href="#" id="detailReportBtn" class="text-secondary small text-decoration-none">
                                <i class="bi bi-flag me-1"></i> Found an issue? Report this resource
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- ====================================================================
         4. FOOTER
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
                        Accessing file downloads is restricted exclusively to authenticated students. Please log in to your student account to download study materials.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="login.php?error=<?= urlencode('Please log in to your student account to download study materials.') ?>" id="authPromptLoginBtn" class="btn btn-success fw-bold py-2 rounded-3 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In to Continue
                        </a>
                        <a href="signup.php" class="btn btn-outline-secondary fw-semibold py-2 rounded-3">
                            Create an Account
                        </a>
                    </div>
                    <div class="text-muted small mt-3" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i> Downloads are securely managed from the Student Dashboard.
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

        window.promptLoginRequired = function(actionType = 'download', resourceTitle = '') {
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
                message = 'Uploading study materials is reserved for authenticated students from the Student Dashboard. Please log in to share notes.';
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

    <!-- Resources Details Script -->
    <script src="assets/js/resources.js"></script>
</body>
</html>

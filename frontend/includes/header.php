<?php
// ============================================================================
// NSBM STUDY CIRCLE - STANDARDIZED PREMIUM FLOATING PILL NAVBAR
// Consistent Across All Pages: Logo, Nav, Buttons, Theme Toggle
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = $activePage ?? '';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'student';
$userName = $_SESSION['name'] ?? '';
?>
<!-- Premium Floating Glassmorphism Navigation Header -->
<header class="floating-header-wrapper site-header" id="mainSiteHeader">
    <nav class="navbar navbar-expand-lg floating-navbar site-navbar" id="mainFloatingHeader">
        <div class="container-fluid px-0">
            <!-- Left Branding: Custom NSBM Study Circle Logo + Brand Title + Tagline -->
            <a class="brand-logo-container" href="index.php" aria-label="NSBM Study Circle Home">
                <div class="brand-logo-wrapper">
                    <img src="assets/images/logo.svg" alt="NSBM Study Circle Logo" class="brand-logo-img">
                </div>
                <div class="brand-text-group">
                    <span class="brand-title">NSBM Study Circle</span>
                    <span class="brand-subtitle">Learn Together. Grow Together.</span>
                </div>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="navbar-toggler custom-hamburger-btn border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarStudyCircle" aria-controls="navbarStudyCircle" aria-expanded="false" aria-label="Toggle navigation">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>

            <!-- Center Nav Links + Right Actions -->
            <div class="collapse navbar-collapse" id="navbarStudyCircle">
                <ul class="navbar-nav mb-2 mb-lg-0 header-nav-list">
                    <!-- Home -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>

                    <!-- Explore Resources -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'resources') ? 'active' : ''; ?>" href="resources.php">Explore Resources</a>
                    </li>

                    <!-- Discussions -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'discussions') ? 'active' : ''; ?>" href="discussions.php">Discussions</a>
                    </li>

                    <!-- Study Groups -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'study-groups') ? 'active' : ''; ?>" href="study-groups.php">Study Groups</a>
                    </li>

                    <!-- Study Sessions -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'sessions') ? 'active' : ''; ?>" href="sessions.php">Study Sessions</a>
                    </li>

                    <!-- About -->
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link <?php echo ($activePage === 'about') ? 'active' : ''; ?>" href="about.php">About</a>
                    </li>
                </ul>

                <!-- Right Action Buttons -->
                <div class="header-actions-group d-flex align-items-center gap-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo ($userRole === 'admin') ? 'admin/index.php' : 'student/index.php'; ?>" class="btn-header-login">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                        <a href="../backend/controllers/auth_controller.php?action=logout" class="btn btn-sm btn-outline-danger btn-logout-pill" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <!-- Log In Button (Refined Translucent Bordered Pill with Person Icon) -->
                        <a href="login.php" class="btn-header-login">
                            <i class="bi bi-person-fill me-1"></i> Log in
                        </a>

                        <!-- Primary CTA Button (Standout NSBM Green Gradient with People & Arrow Icons) -->
                        <a href="signup.php" class="btn-header-join">
                            <i class="bi bi-people-fill me-1"></i> Join NSBM Study Circle <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Dark/Light Theme Toggle (Positioned smoothly inward within pill boundary) -->
                    <div class="theme-toggle-container position-relative">
                        <button type="button" class="btn-header-theme" id="themeToggleBtn" aria-label="Toggle theme" title="Switch Theme">
                            <i class="bi bi-moon-stars" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- ====================================================================
     UNIVERSAL GUEST AUTHENTICATION REQUIRED MODAL (Strict Access Control)
     ==================================================================== -->
<div class="modal fade" id="universalAuthRequiredModal" tabindex="-1" aria-labelledby="universalAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pt-0 pb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px; background: rgba(22, 163, 74, 0.12); color: #16A34A; font-size: 1.85rem;">
                    <i class="bi bi-shield-lock-fill" id="universalAuthModalIcon"></i>
                </div>
                <h5 class="fw-bold mb-2 text-dark" id="universalAuthModalLabel">Authentication Required</h5>
                <p class="text-secondary small mb-4" id="universalAuthModalMessage">
                    You are browsing in View-Only guest mode. Interactive actions, uploads, downloads, and community features require logging into your student account.
                </p>
                <div class="d-grid gap-2">
                    <a href="login.php" id="universalAuthModalLoginBtn" class="btn btn-success fw-bold py-2 rounded-3 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In to Student Account
                    </a>
                    <a href="signup.php" class="btn btn-outline-secondary fw-semibold py-2 rounded-3">
                        Create an Account
                    </a>
                </div>
                <div class="text-muted small mt-3" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i> Interactive tools are accessible after logging in to the Student Dashboard.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Global Authentication Access Control Script -->
<script>
    window.__NSBM_IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    window.__NSBM_USER_ROLE = <?php echo json_encode($userRole); ?>;

    window.promptLoginRequired = function(actionType = 'general', contextTitle = '') {
        const modalEl = document.getElementById('universalAuthRequiredModal') || document.getElementById('authRequiredModal');
        const msgEl = document.getElementById('universalAuthModalMessage') || document.getElementById('authPromptMessage');
        const iconEl = document.getElementById('universalAuthModalIcon') || document.getElementById('authPromptIcon');
        const loginBtn = document.getElementById('universalAuthModalLoginBtn') || document.getElementById('authPromptLoginBtn');

        let message = 'You are browsing in View-Only guest mode. Interactive actions require logging into your student account.';
        let errorParam = 'Please log in to your student account to access this feature.';
        let iconClass = 'bi bi-shield-lock-fill';

        switch (actionType) {
            case 'download':
                message = contextTitle
                    ? `Downloading "${contextTitle}" is restricted to verified students. Please log in to download materials from the Student Dashboard.`
                    : 'File downloads are restricted exclusively to authenticated students. Please log in to download full study materials.';
                errorParam = 'Please log in to your student account to download study materials.';
                iconClass = 'bi bi-file-earmark-lock-fill';
                break;
            case 'upload':
                message = 'Uploading notes and study resources is reserved for verified students. Please log in to access the upload workspace.';
                errorParam = 'Please log in to your student account to upload study materials.';
                iconClass = 'bi bi-cloud-arrow-up-fill';
                break;
            case 'question':
            case 'ask':
                message = 'Asking questions and starting academic discussions is reserved for registered students. Please log in to post your question.';
                errorParam = 'Please log in to your student account to ask questions and participate in discussions.';
                iconClass = 'bi bi-chat-dots-fill';
                break;
            case 'answer':
            case 'reply':
                message = 'Replying and posting answers is available to registered community members. Please log in to contribute your answer.';
                errorParam = 'Please log in to your student account to post answers.';
                iconClass = 'bi bi-reply-fill';
                break;
            case 'vote':
            case 'upvote':
                message = 'Marking questions or answers as helpful requires an authenticated student account.';
                errorParam = 'Please log in to vote on helpful answers.';
                iconClass = 'bi bi-hand-thumbs-up-fill';
                break;
            case 'group_create':
                message = 'Creating new study groups is accessible from the Student Dashboard after logging in.';
                errorParam = 'Please log in to your student account to create study groups.';
                iconClass = 'bi bi-people-fill';
                break;
            case 'group_join':
                message = 'Joining study circles and receiving meeting updates is exclusive to logged-in students.';
                errorParam = 'Please log in to your student account to join study groups.';
                iconClass = 'bi bi-person-plus-fill';
                break;
            case 'session_create':
                message = 'Scheduling academic peer sessions is managed from the Student Dashboard. Please log in to schedule.';
                errorParam = 'Please log in to your student account to schedule study sessions.';
                iconClass = 'bi bi-calendar-plus-fill';
                break;
            case 'session_book':
            case 'session_join':
                message = 'Reserving a place in study sessions requires a registered student account. Please log in to confirm your attendance.';
                errorParam = 'Please log in to your student account to reserve study sessions.';
                iconClass = 'bi bi-calendar-check-fill';
                break;
            case 'save':
            case 'bookmark':
                message = 'Saving resources to personal bookmarks is exclusive to authenticated student accounts.';
                errorParam = 'Please log in to save resources to your library.';
                iconClass = 'bi bi-bookmark-plus-fill';
                break;
            case 'review':
                message = 'Rating and reviewing resources is reserved for enrolled students.';
                errorParam = 'Please log in to review study materials.';
                iconClass = 'bi bi-star-fill';
                break;
        }

        if (msgEl) msgEl.textContent = message;
        if (iconEl) iconEl.className = iconClass;
        if (loginBtn) loginBtn.href = `login.php?error=${encodeURIComponent(errorParam)}`;

        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            alert(message);
            window.location.href = `login.php?error=${encodeURIComponent(errorParam)}`;
        }
    };

    // Strict Global Event Capture for Unauthenticated Guests
    if (!window.__NSBM_IS_LOGGED_IN) {
        document.addEventListener('DOMContentLoaded', () => {
            // Interactive click interceptor
            document.body.addEventListener('click', (e) => {
                const target = e.target.closest(
                    '.btn-ask-question, [data-bs-target="#askQuestionModal"], ' +
                    '.btn-create-group, [data-bs-target="#createStudyGroupModal"], ' +
                    '.btn-create-session, [data-bs-target="#createSessionModal"], ' +
                    '.btn-join-group-action, #joinGroupBtn, .join-group-btn, ' +
                    '.btn-reserve-place, #reservePlaceBtn, .reserve-session-btn, ' +
                    '#questionUpvoteBtn, .btn-vote-up, .btn-answer-helpful, ' +
                    '.btn-answer-reply, ' +
                    '#detailDownloadBtn, .download-material-btn, ' +
                    '#detailSaveBtn, .bookmark-btn, .bookmark-toggle-btn'
                );

                if (target) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    let type = 'general';
                    if (target.matches('.btn-ask-question, [data-bs-target="#askQuestionModal"]')) type = 'question';
                    else if (target.matches('.btn-create-group, [data-bs-target="#createStudyGroupModal"]')) type = 'group_create';
                    else if (target.matches('.btn-create-session, [data-bs-target="#createSessionModal"]')) type = 'session_create';
                    else if (target.matches('.btn-join-group-action, #joinGroupBtn, .join-group-btn')) type = 'group_join';
                    else if (target.matches('.btn-reserve-place, #reservePlaceBtn, .reserve-session-btn')) type = 'session_book';
                    else if (target.matches('#questionUpvoteBtn, .btn-vote-up, .btn-answer-helpful')) type = 'vote';
                    else if (target.matches('.btn-answer-reply')) type = 'reply';
                    else if (target.matches('#detailDownloadBtn, .download-material-btn')) type = 'download';
                    else if (target.matches('#detailSaveBtn, .bookmark-btn, .bookmark-toggle-btn')) type = 'save';

                    window.promptLoginRequired(type);
                    return false;
                }
            }, true);

            // Interactive form submission interceptor
            document.body.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.matches('#askQuestionForm, #postAnswerForm, #createStudyGroupForm, #createSessionForm, #resourceUploadForm, #resourceReviewForm')) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    let type = 'general';
                    if (form.id === 'askQuestionForm') type = 'question';
                    else if (form.id === 'postAnswerForm') type = 'answer';
                    else if (form.id === 'createStudyGroupForm') type = 'group_create';
                    else if (form.id === 'createSessionForm') type = 'session_create';
                    else if (form.id === 'resourceUploadForm') type = 'upload';
                    else if (form.id === 'resourceReviewForm') type = 'review';

                    window.promptLoginRequired(type);
                    return false;
                }
            }, true);
        });
    }
</script>

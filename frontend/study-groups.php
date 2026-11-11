<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY GROUPS MAIN PAGE (PDF SCREENSHOTS PAGES 1 - 5)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/StudyGroup.php';

$model = new StudyGroupModel($conn);
$featured = $model->getFeaturedGroup();
$groups = $model->getGroups('All', '');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Groups - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
        <!-- Website Favicon using official NSBM Study Circle Logo -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="alternate icon" type="image/jpeg" href="../src/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.svg">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ====================================================================
         1. SHARED PREMIUM ANIMATED NAVBAR
         ==================================================================== -->
    <?php $activePage = 'study-groups'; include __DIR__ . '/includes/header.php'; ?>

    <!-- ====================================================================
         2. HERO SECTION (Exact Match to PDF Page 1)
         ==================================================================== -->
    <header class="study-groups-hero">
        <div class="container-xl">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8">
                    <span class="study-groups-hero-label">LEARNING COMMUNITIES</span>
                    <h1 class="study-groups-hero-title">Find people who are learning what you are</h1>
                    <p class="study-groups-hero-desc">
                        Join focused circles, exchange ideas, and turn independent study into shared momentum.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a href="student/groups.php" class="btn-create-group text-decoration-none d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-people me-2"></i> Create a study group
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn-create-group" onclick="promptLoginRequired('group_create')">
                            <i class="bi bi-lock-fill me-1"></i> Create a study group
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ====================================================================
         3. SEARCH AND CATEGORY FILTERS (Exact Match to PDF Page 1 & 2)
         ==================================================================== -->
    <div class="container-xl">
        <div class="study-search-filter-box">
            <div class="study-search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="groupSearchInput" class="study-search-input" placeholder="Search groups by name or subject" autocomplete="off">
            </div>
            <div class="category-pills-wrap" role="group">
                <button type="button" class="category-pill-btn active" data-category="All">All</button>
                <button type="button" class="category-pill-btn" data-category="Computing">Computing</button>
                <button type="button" class="category-pill-btn" data-category="Business">Business</button>
                <button type="button" class="category-pill-btn" data-category="Engineering">Engineering</button>
                <button type="button" class="category-pill-btn" data-category="Science">Science</button>
            </div>
        </div>

        <!-- ====================================================================
             4. FEATURED COMMUNITY SECTION (Exact Match to PDF Page 2)
             ==================================================================== -->
        <?php if (!empty($featured)): ?>
        <section class="featured-community-card">
            <span class="featured-community-label">FEATURED COMMUNITY</span>
            <div class="featured-card-body">
                <div class="featured-info-left">
                    <div class="featured-group-badge">
                        <?php echo htmlspecialchars($featured['code']); ?>
                    </div>
                    <div>
                        <h2 class="featured-group-title"><?php echo htmlspecialchars($featured['name']); ?></h2>
                        <p class="featured-group-desc"><?php echo htmlspecialchars($featured['description']); ?></p>
                        <div class="featured-group-meta">
                            <span><i class="bi bi-people me-1"></i> <?php echo (int)$featured['member_count']; ?> members</span>
                            <span><i class="bi bi-calendar2-week me-1"></i> <?php echo htmlspecialchars($featured['schedule_info']); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="study-group-details.php?id=<?php echo $featured['id']; ?>" class="btn-view-featured">
                        View group
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ====================================================================
             5. STUDY GROUPS CARDS GRID (Exact Match to PDF Pages 2, 3, 4)
             ==================================================================== -->
        <div class="study-groups-grid" id="studyGroupsGrid">
            <?php foreach ($groups as $grp): ?>
            <div class="study-card-item" 
                 data-id="<?php echo $grp['id']; ?>"
                 data-category="<?php echo htmlspecialchars($grp['category']); ?>"
                 data-name="<?php echo htmlspecialchars($grp['name']); ?>"
                 data-code="<?php echo htmlspecialchars($grp['code']); ?>"
                 data-desc="<?php echo htmlspecialchars($grp['description']); ?>">
                
                <!-- Card Top: Badge & Active Indicator -->
                <div class="card-top-row">
                    <div class="group-badge-box <?php echo htmlspecialchars($grp['badge_style']['class'] ?? 'badge-ds'); ?>">
                        <?php echo htmlspecialchars($grp['code']); ?>
                    </div>
                    <div class="active-week-dot">
                        <span class="active-dot-indicator"></span>
                        <span><?php echo htmlspecialchars($grp['activity_status']); ?></span>
                    </div>
                </div>

                <!-- Card Middle: Group Title & Description -->
                <div>
                    <h3 class="study-card-title"><?php echo htmlspecialchars($grp['name']); ?></h3>
                    <p class="study-card-desc"><?php echo htmlspecialchars($grp['description']); ?></p>
                </div>

                <!-- Card Bottom: Avatar Stack, Member Count & View Button -->
                <div class="card-bottom-row">
                    <div class="members-avatar-row">
                        <div class="avatar-stack">
                            <span class="avatar-circle">NP</span>
                            <span class="avatar-circle avatar-blue">KS</span>
                            <span class="avatar-circle">+<?php echo max(1, $grp['member_count'] - 2); ?></span>
                        </div>
                        <span class="members-count-text"><?php echo (int)$grp['member_count']; ?> members</span>
                    </div>
                    <a href="study-group-details.php?id=<?php echo $grp['id']; ?>" class="btn-card-view-group">
                        View group
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ====================================================================
         6. CREATE STUDY GROUP MODAL
         ==================================================================== -->
    <div class="modal fade" id="createStudyGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1" id="createGroupModalLabel">Create a Study Group</h5>
                        <p class="text-muted small mb-0">Build a community around a shared module or subject.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createStudyGroupForm">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Group Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Mobile Application Development Circle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Faculty / Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="Computing" selected>Computing</option>
                                <option value="Business">Business</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Science">Science</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Short Description *</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of what this circle focuses on" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">What We Study (comma-separated topics)</label>
                            <input type="text" name="what_we_study" class="form-control" placeholder="e.g. Flutter, Kotlin, State Management, APIs">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary">Full About Text</label>
                            <textarea name="about_text" class="form-control" rows="3" placeholder="Explain weekly meetings, study objectives, and group guidelines..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light" style="border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         7. FOOTER (Exact Match to Figma / PDF Pages 4, 5, 9, 12)
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Study Groups JS -->
    <script src="assets/js/study-groups.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>


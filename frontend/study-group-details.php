<?php
// ============================================================================
// NSBM STUDY CIRCLE - INDIVIDUAL STUDY GROUP DETAILS (PDF SCREENSHOTS PAGES 10 - 12)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/StudyGroup.php';

$groupId = (int)($_GET['id'] ?? 1);
$currentUserId = $_SESSION['user_id'] ?? null;

$model = new StudyGroupModel($conn);
$group = $model->getGroupById($groupId, $currentUserId);

if (!$group) {
    header("Location: study-groups.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['name']); ?> - NSBM Study Circle</title>
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
         2. INDIVIDUAL GROUP HERO SECTION (Exact Match to PDF Page 10)
         ==================================================================== -->
    <div class="container-xl">
        <header class="group-details-hero">
            <span class="group-details-label">PUBLIC STUDY GROUP</span>
            <div class="group-hero-content">
                <div class="group-hero-meta-left">
                    <div class="group-hero-large-badge">
                        <?php echo htmlspecialchars($group['code']); ?>
                    </div>
                    <div>
                        <h1 class="group-hero-title"><?php echo htmlspecialchars($group['name']); ?></h1>
                        <p class="group-hero-desc">
                            <?php echo !empty($group['about_text']) ? htmlspecialchars(substr($group['about_text'], 0, 140) . '...') : 'Make complex algorithms easier through consistent practice and clear peer explanations.'; ?>
                        </p>
                        <div class="group-hero-stats">
                            <span id="detailsMemberCount"><i class="bi bi-people me-1"></i> <?php echo (int)$group['member_count']; ?> members</span>
                            <span><i class="bi bi-calendar2-week me-1"></i> <?php echo htmlspecialchars($group['created_date_str']); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="btn-join-group-action <?php echo !empty($group['is_member']) ? 'joined' : ''; ?>" id="joinGroupBtn" data-group-id="<?php echo $group['id']; ?>">
                            <?php if (!empty($group['is_member'])): ?>
                                <i class="bi bi-check-lg me-1"></i> Joined (Leave)
                            <?php else: ?>
                                Join this group
                            <?php endif; ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-join-group-action" onclick="promptLoginRequired('group_join')">
                            <i class="bi bi-lock-fill me-1"></i> Log in to join group
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- ====================================================================
             3. 2-COLUMN MAIN CONTENT & SIDEBAR (Exact Match to PDF Pages 10 - 12)
             ==================================================================== -->
        <div class="row g-4 mb-5">
            <!-- Left Main Column -->
            <div class="col-lg-8">
                <!-- About This Group Card -->
                <div class="details-section-card">
                    <h2 class="details-section-title">About this group</h2>
                    <p class="details-section-text">
                        <?php echo nl2br(htmlspecialchars($group['about_text'])); ?>
                    </p>

                    <h3 class="what-we-study-subtitle">What we study</h3>
                    <div class="study-topic-pills">
                        <?php if (!empty($group['tags'])): ?>
                            <?php foreach ($group['tags'] as $tag): ?>
                                <span class="topic-pill"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="topic-pill">Arrays & linked lists</span>
                            <span class="topic-pill">Trees</span>
                            <span class="topic-pill">Graphs</span>
                            <span class="topic-pill">Sorting</span>
                            <span class="topic-pill">Complexity</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity Section (PDF Page 11 & 12) -->
                <div class="details-section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="details-section-title mb-0">Recent activity</h2>
                        <a href="discussions.php" class="btn btn-outline-secondary btn-sm fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-chat-left-text me-1"></i> Open Discussion
                        </a>
                    </div>

                    <div class="activity-items-container">
                        <?php foreach ($group['activities'] as $act): ?>
                        <div class="activity-item-row">
                            <div class="activity-user-info">
                                <div class="activity-avatar">
                                    <?php echo htmlspecialchars($act['user_initials'] ?? 'NP'); ?>
                                </div>
                                <div>
                                    <div class="activity-text-line">
                                        <span class="activity-author-name"><?php echo htmlspecialchars($act['user_name']); ?></span>
                                        <span><?php echo htmlspecialchars($act['action_text']); ?></span>
                                    </div>
                                    <span class="activity-time-stamp"><?php echo htmlspecialchars($act['relative_time']); ?></span>
                                </div>
                            </div>
                            <div>
                                <a href="<?php echo htmlspecialchars($act['link_url'] ?? 'discussions.php'); ?>" class="btn-activity-view">
                                    View
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column -->
            <div class="col-lg-4">
                <!-- Next Group Session Card -->
                <div class="sidebar-info-card">
                    <h3 class="sidebar-card-header-title">Next group session</h3>
                    <div class="session-date-display">
                        <span class="session-date-number"><?php echo htmlspecialchars($group['next_session']['day'] ?? '24'); ?></span>
                        <span class="session-date-month"><?php echo htmlspecialchars($group['next_session']['month'] ?? 'MAY'); ?></span>
                    </div>
                    <h4 class="sidebar-session-title"><?php echo htmlspecialchars($group['next_session']['title'] ?? 'Graph Traversal Practice'); ?></h4>
                    <p class="sidebar-session-details"><?php echo htmlspecialchars($group['next_session']['details'] ?? 'Saturday · 6:30 PM · Online'); ?></p>
                    <a href="index.php#sessions" class="btn-sidebar-outline">
                        View session
                    </a>
                </div>

                <!-- Group Coordinators Card -->
                <div class="sidebar-info-card">
                    <h3 class="sidebar-card-header-title">Group coordinators</h3>
                    <div class="coordinators-list">
                        <?php foreach ($group['coordinators'] as $coord): ?>
                        <div class="coordinator-row">
                            <div class="coordinator-avatar">
                                <?php echo htmlspecialchars($coord['initials']); ?>
                            </div>
                            <div>
                                <h5 class="coordinator-name"><?php echo htmlspecialchars($coord['name']); ?></h5>
                                <span class="coordinator-role"><?php echo htmlspecialchars($coord['role_title']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Help / Guidelines Card -->
                <div class="sidebar-info-card" style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); border-color: #bbf7d0;">
                    <h4 class="fw-bold mb-2 text-dark" style="font-size: 0.95rem;">
                        <i class="bi bi-shield-check text-success me-1"></i> Community Standard
                    </h4>
                    <p class="small text-muted mb-0" style="line-height: 1.5;">
                        We encourage peer explanations, concept clarity, and past-paper revision. Prohibited assignment sharing is not allowed.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         4. FOOTER (Exact Match to Figma / PDF Pages 4, 5, 9, 12)
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


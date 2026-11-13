<?php
// ============================================================================
// NSBM STUDY CIRCLE - DYNAMIC DISCUSSIONS PAGE (PHP + MySQL)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/Discussion.php';

$discussionModel = new DiscussionModel();
$filter = $_GET['filter'] ?? 'recent';
$category = $_GET['category'] ?? 'all';
$search = $_GET['q'] ?? '';

$discussions = $discussionModel->getFilteredDiscussions($filter, $category, $search, 25, 0);
$categoryCounts = $discussionModel->getCategoryCounts();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userRole = $_SESSION['role'] ?? 'student';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussions - NSBM Study Circle</title>
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
<body class="discussion-page-bg">

    <!-- ====================================================================
         1. SHARED PREMIUM ANIMATED NAVBAR
         ==================================================================== -->
    <?php $activePage = 'discussions'; include __DIR__ . '/includes/header.php'; ?>

    <!-- ====================================================================
         2. HERO SECTION
         ==================================================================== -->
    <header class="discussion-hero">
        <div class="container-xl">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8">
                    <span class="discussion-hero-label">STUDENT KNOWLEDGE EXCHANGE</span>
                    <h1 class="discussion-hero-title">Ask clearly. Answer generously.</h1>
                    <p class="discussion-hero-desc">
                        A thoughtful space for academic questions, useful explanations, and shared understanding.
                    </p>
                </div>
                <div class="col-lg-4 d-flex justify-content-lg-end mt-4 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a href="student/discussions.php" class="btn-ask-question text-decoration-none d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-question-circle me-2"></i> Ask a question
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn-ask-question" onclick="promptLoginRequired('question')">
                            <i class="bi bi-lock-fill me-1"></i> Ask a question
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ====================================================================
         3. MAIN DISCUSSION EXPLORATION SECTION
         ==================================================================== -->
    <main class="py-4">
        <div class="container-xl">
            <div class="row g-4">
                
                <!-- Left Main Discussions Column -->
                <div class="col-lg-8">
                    
                    <!-- Search and Filters Bar -->
                    <div class="discussion-search-filter-card d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center flex-grow-1">
                            <i class="bi bi-search text-muted ps-1"></i>
                            <input type="text" id="discussionSearchInput" class="discussion-search-input" placeholder="Search discussions" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="discussion-filter-tab <?= $filter === 'recent' ? 'active' : '' ?>" data-filter="recent">Recent</button>
                            <button type="button" class="discussion-filter-tab <?= $filter === 'popular' ? 'active' : '' ?>" data-filter="popular">Popular</button>
                            <button type="button" class="discussion-filter-tab <?= $filter === 'unanswered' ? 'active' : '' ?>" data-filter="unanswered">Unanswered</button>
                        </div>
                    </div>

                    <!-- Discussion Cards List -->
                    <div id="discussionsListContainer">
                        <?php if (empty($discussions)): ?>
                            <div class="card border p-5 text-center bg-white rounded-3">
                                <i class="bi bi-chat-left-dots text-muted fs-1 mb-2"></i>
                                <h5 class="fw-bold text-dark">No discussions found</h5>
                                <p class="text-secondary small mb-3">Try asking the community or clearing your search filters.</p>
                                <a href="discussions.php" class="btn btn-outline-success btn-sm m-auto">Reset filters</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($discussions as $d): ?>
                                <?php
                                $category = $d['category_badge'] ?? $d['category'] ?? $d['faculty'] ?? 'Computing';
                                $catClass = 'category-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $category));
                                ?>
                                <a href="discussion-details.php?id=<?= $d['id'] ?>" class="discussion-card">
                                    <div class="discussion-stats-box">
                                        <div class="stat-item">
                                            <div class="stat-count"><?= (int)$d['upvotes'] ?></div>
                                            <div class="stat-text">helpful</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-count"><?= (int)$d['answers_count'] ?></div>
                                            <div class="stat-text">answers</div>
                                        </div>
                                    </div>

                                    <div class="discussion-content-box">
                                        <span class="discussion-category-pill <?= $catClass ?>"><?= htmlspecialchars($category) ?></span>
                                        <h2 class="discussion-item-title"><?= htmlspecialchars($d['title']) ?></h2>
                                        <p class="discussion-item-snippet"><?= htmlspecialchars($d['body']) ?></p>
                                        <div class="discussion-item-meta">
                                            Asked by <?= htmlspecialchars($d['author_name']) ?> · <?= htmlspecialchars($d['time_ago']) ?>
                                        </div>
                                    </div>

                                    <i class="bi bi-arrow-right discussion-arrow-icon"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Sidebar Column -->
                <div class="col-lg-4">
                    
                    <!-- Browse by Subject Card -->
                    <div class="sidebar-section-card">
                        <h3 class="sidebar-title-header">Browse by subject</h3>
                        <div class="subject-browse-list">
                            <?php foreach ($categoryCounts as $catName => $count): ?>
                                <a href="#" class="subject-browse-item <?= strtolower($category) === strtolower($catName) ? 'active' : '' ?>" data-subject="<?= htmlspecialchars($catName) ?>">
                                    <span><?= htmlspecialchars($catName) ?></span>
                                    <span class="subject-count-badge"><?= $count ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Keep it Constructive Tip Card -->
                    <div class="constructive-tip-card">
                        <div class="tip-check-circle">
                            <i class="bi bi-check2"></i>
                        </div>
                        <h4 class="tip-heading">Keep it constructive</h4>
                        <p class="tip-text">
                            Show what you tried, explain your context, and credit useful answers.
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- ====================================================================
         ASK A QUESTION MODAL
         ==================================================================== -->
    <div class="modal fade" id="askQuestionModal" tabindex="-1" aria-labelledby="askQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="askQuestionModalLabel">
                            <i class="bi bi-question-circle text-success me-2"></i> Ask a Question
                        </h5>
                        <p class="text-secondary small mb-0">Ask clearly so peers and seniors can give generous explanations.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="askQuestionForm">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="questionTitle" class="form-label fw-semibold small">Question Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="questionTitle" name="title" placeholder="e.g. How do you choose between merge sort and quicksort in practice?" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="questionCategory" class="form-label fw-semibold small">Subject / Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="questionCategory" name="category" required>
                                    <option value="Computing">Computing</option>
                                    <option value="Accounting">Accounting</option>
                                    <option value="Business">Business</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Science">Science</option>
                                    <option value="Databases">Databases</option>
                                    <option value="Academic Skills">Academic Skills</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="questionTags" class="form-label fw-semibold small">Tags (comma-separated)</label>
                                <input type="text" class="form-control" id="questionTags" name="tags" placeholder="e.g. algorithms, sorting, complexity">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="questionBody" class="form-label fw-semibold small">Explanation & Context <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="questionBody" name="body" rows="4" placeholder="Explain what you understand so far, what you tried, and what specific part you need help with..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-send me-1"></i> Post Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global Interactions & Stability JS -->
    <script src="assets/js/main.js"></script>

    <!-- Discussions Logic Script -->
    <script src="assets/js/discussions.js"></script>
</body>
</html>


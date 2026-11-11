<?php
// ============================================================================
// NSBM STUDY CIRCLE - DYNAMIC DISCUSSION DETAILS (PHP + MySQL)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/Discussion.php';

$discussionModel = new DiscussionModel();
$id = (int)($_GET['id'] ?? 1);
$discussion = $discussionModel->getDiscussionById($id);

if (!$discussion) {
    header("Location: discussions.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userRole = $_SESSION['role'] ?? 'student';

$category = $discussion['category_badge'] ?? $discussion['category'] ?? $discussion['faculty'] ?? 'Computing';
$catClass = 'category-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $category));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($discussion['title']) ?> - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
        <!-- Website Favicon using official NSBM Study Circle Logo -->
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
         2. BREADCRUMB (PDF Page 5)
         ==================================================================== -->
    <div class="py-3 border-bottom" style="background: transparent;">
        <div class="container-xl">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom mb-0">
                    <li class="breadcrumb-item"><a href="discussions.php" class="text-decoration-none text-muted">Discussions</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Question</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- ====================================================================
         3. QUESTION DETAIL & ANSWERS (PDF Pages 5 - 8)
         ==================================================================== -->
    <main class="py-4" id="discussionDetailContainer">
        <div class="container-xl">
            <div class="row g-4">
                
                <!-- Main Question Column -->
                <div class="col-lg-8">
                    
                    <!-- Question Card (PDF Page 5) -->
                    <div class="question-detail-card">
                        <!-- Upvote Box -->
                        <div class="question-upvote-box">
                            <button class="btn-vote-up" id="questionUpvoteBtn" title="Vote this question as helpful">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                            <span class="question-vote-count" id="questionUpvoteCount"><?= (int)$discussion['upvotes'] ?></span>
                            <span class="question-vote-label">helpful</span>
                        </div>

                        <!-- Question Content -->
                        <div class="flex-grow-1">
                            <span class="discussion-category-pill <?= $catClass ?>" id="detailCategoryBadge"><?= htmlspecialchars($category) ?></span>
                            <h1 class="question-detail-title" id="detailQuestionTitle">
                                <?= htmlspecialchars($discussion['title']) ?>
                            </h1>
                            
                            <div class="question-author-meta">
                                <div class="author-circle-avatar" id="detailAuthorInitials"><?= htmlspecialchars($discussion['author_initials'] ?? 'AP') ?></div>
                                <span>Asked by <strong class="text-dark" id="detailAuthorName"><?= htmlspecialchars($discussion['author_name']) ?></strong> · <span id="detailTimeAgo"><?= htmlspecialchars($discussion['time_ago']) ?></span></span>
                            </div>

                            <div class="question-detail-body" id="detailQuestionBody">
                                <?= nl2br(htmlspecialchars($discussion['body'])) ?>
                            </div>

                            <!-- Tags -->
                            <div class="d-flex flex-wrap gap-2 pt-2" id="detailTagsContainer">
                                <?php foreach ($discussion['tags_list'] as $tag): ?>
                                    <a href="discussions.php?q=<?= urlencode($tag) ?>" class="tag-badge"><?= htmlspecialchars($tag) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Answers Header -->
                    <div class="answers-section-header">
                        <h2 class="answers-count-title" id="answersCountHeader"><?= count($discussion['answers'] ?? []) ?> Answers</h2>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted fw-semibold">Sort by:</span>
                            <select class="form-select form-select-sm" style="width: auto; font-size: 0.85rem; font-weight: 600;">
                                <option value="helpful">Most helpful</option>
                                <option value="newest">Newest first</option>
                            </select>
                        </div>
                    </div>

                    <!-- Answers List (PDF Pages 6 & 7) -->
                    <div id="answersListContainer">
                        <?php if (!empty($discussion['answers'])): ?>
                            <?php foreach ($discussion['answers'] as $ans): ?>
                                <?php $isSolution = !empty($ans['is_solution']); ?>
                                <div class="answer-card <?= $isSolution ? 'highlight-solution' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="author-circle-avatar" style="background: <?= $isSolution ? '#0284c7' : '#10b981' ?>;">
                                                <?= htmlspecialchars($ans['author_initials'] ?? 'ST') ?>
                                            </div>
                                            <div>
                                                <div class="answer-author-name"><?= htmlspecialchars($ans['author_name']) ?></div>
                                                <div class="answer-author-role"><?= htmlspecialchars($ans['author_role'] ?? 'Student member') ?></div>
                                            </div>
                                        </div>
                                        <?php if ($isSolution): ?>
                                            <span class="most-helpful-badge"><i class="bi bi-check2"></i> Most helpful</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="answer-body-text">
                                        <?= nl2br(htmlspecialchars($ans['body'])) ?>
                                    </div>

                                    <div class="answer-actions-bar">
                                        <button class="btn-answer-helpful" type="button"><i class="bi bi-arrow-up"></i> Helpful (<?= (int)$ans['upvotes'] ?>)</button>
                                        <button class="btn-answer-reply" type="button">Reply</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card border p-4 text-center bg-white rounded-3 mb-3 text-muted">
                                <i class="bi bi-chat-square-text fs-2 d-block mb-2"></i>
                                No answers yet. Be the first to share your understanding below!
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Post Answer Form Section (Exact PDF Pages 7 & 8) -->
                    <div class="post-answer-card" id="postAnswerCardSection">
                        <h3 class="post-answer-title">Your answer</h3>
                        <?php if (!$isLoggedIn): ?>
                            <div class="alert alert-warning py-2 small mb-3 d-flex align-items-center gap-2 rounded-3">
                                <i class="bi bi-shield-lock-fill text-warning flex-shrink-0"></i>
                                <span>You are viewing in Guest Mode. Please <a href="login.php?error=<?= urlencode('Please log in to your student account to post answers.') ?>" class="alert-link fw-bold">log in to your student account</a> to submit answers and upvote.</span>
                            </div>
                        <?php endif; ?>
                        <form id="postAnswerForm">
                            <div class="mb-3">
                                <textarea class="post-answer-textarea" id="answerTextarea" name="body" placeholder="<?= $isLoggedIn ? 'Explain your reasoning clearly and respectfully...' : 'Please log in to your student account to contribute an answer...' ?>" <?= !$isLoggedIn ? 'readonly onclick="promptLoginRequired(\'answer\')"' : 'required' ?>></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="char-counter-text" id="charCounter">0/2000 characters</span>
                                <button type="submit" class="btn-post-answer" <?= !$isLoggedIn ? 'onclick="event.preventDefault(); promptLoginRequired(\'answer\');"' : '' ?>>
                                    <?= $isLoggedIn ? 'Post answer' : '<i class="bi bi-lock-fill me-1"></i> Log in to answer' ?>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Right Sidebar Column -->
                <div class="col-lg-4">
                    
                    <!-- Question Activity Card (PDF Page 5) -->
                    <div class="sidebar-section-card">
                        <h3 class="sidebar-title-header">Question activity</h3>
                        <div class="activity-stat-row">
                            <i class="bi bi-eye text-muted"></i>
                            <span id="detailViewsCount"><?= (int)$discussion['views_count'] ?> views</span>
                        </div>
                        <div class="activity-stat-row">
                            <i class="bi bi-chat-left-text text-muted"></i>
                            <span id="detailAnswersCount"><?= count($discussion['answers'] ?? []) ?> answers</span>
                        </div>
                        <div class="activity-stat-row mb-0">
                            <i class="bi bi-hand-thumbs-up text-muted"></i>
                            <span id="detailVotesCount"><?= (int)$discussion['upvotes'] ?> helpful votes</span>
                        </div>
                    </div>

                    <!-- Related Questions Card (PDF Page 5) -->
                    <div class="sidebar-section-card">
                        <h3 class="sidebar-title-header">Related questions</h3>
                        <?php if (!empty($discussion['related_questions'])): ?>
                            <?php foreach ($discussion['related_questions'] as $rq): ?>
                                <a href="discussion-details.php?id=<?= $rq['id'] ?>" class="related-question-link">
                                    <?= htmlspecialchars($rq['title']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- ====================================================================
         4. FOOTER (Exact Website Footer Match)
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

    <!-- Discussions Script -->
    <script src="assets/js/discussions.js"></script>
</body>
</html>


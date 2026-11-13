<?php
// ============================================================================
// NSBM STUDY CIRCLE - DYNAMIC HOMEPAGE (PHP SERVER-RENDERED)
// Fetches live data from backend models with graceful fallback
// ============================================================================

require_once __DIR__ . '/../backend/config/db_config.php';
require_once __DIR__ . '/../backend/models/Resource.php';
require_once __DIR__ . '/../backend/models/Discussion.php';
require_once __DIR__ . '/../backend/models/StudyGroup.php';
require_once __DIR__ . '/../backend/models/StudySession.php';

$resourceModel = new ResourceModel($conn);
$discussionModel = new DiscussionModel($conn);
$groupModel = new StudyGroupModel($conn);
$sessionModel = new StudySessionModel($conn);

$resources = method_exists($resourceModel, 'getResources') ? $resourceModel->getResources('all', '', 4) : $resourceModel->getFilteredResources('all', 'all', 'all', '', 'most_downloaded', 4);
$discussions = method_exists($discussionModel, 'getTrendingDiscussions') ? $discussionModel->getTrendingDiscussions(2) : $discussionModel->getFilteredDiscussions('trending', 'all', '', 2);
$groups = method_exists($groupModel, 'getFeaturedGroups') ? $groupModel->getFeaturedGroups(4) : $groupModel->getGroups('All', '', 4);
$sessions = $sessionModel->getUpcomingSessions(3);

$facultyBadgeClasses = [
    'Computing' => 'pill-computing',
    'Business' => 'pill-business',
    'Engineering' => 'pill-engineering',
    'Science' => 'pill-science'
];

$avatarColors = [
    'Computing' => '#0f172a',
    'Business' => '#064e3b',
    'Engineering' => '#4338ca',
    'Science' => '#0f766e'
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="GVyRPWkr5Q-xd8N8B2opT6UGopNCuw6GNGCSQV9Vpdc" />
    <meta name="description" content="Study Circle is an independent, student-run study platform for sharing notes, discussions and study groups. Not affiliated with or endorsed by NSBM Green University.">
    <title>NSBM Study Circle - Learn Together. Grow Together.</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <!-- Website Favicon: Study Circle logo -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="alternate icon" type="image/jpeg" href="assets/images/logo.jpg">
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
    <link rel="stylesheet" href="assets/css/style.css?v=20261113">
</head>
<body>

    <!-- 1. SHARED PREMIUM ANIMATED NAVBAR -->
    <?php $activePage = 'home'; include __DIR__ . '/includes/header.php'; ?>

    <!-- 2. HERO SECTION (Matches Screenshot Reference) -->
    <header class="hero-section" id="home">
        <div class="hero-overlay"></div>
        <div class="container-xl d-flex flex-column justify-content-center h-100 position-relative z-2">
            <div class="hero-content">
                <!-- Eyebrow Badge with Pulse Indicator -->
                <div class="hero-eyebrow-badge">
                    <span class="badge-pulse-dot"></span>
                    <span>INDEPENDENT STUDENT LEARNING COMMUNITY</span>
                </div>

                <!-- 3-line Headline with Emerald Accent -->
                <h1 class="hero-headline">
                    Learn Better.<br>
                    Achieve More.<br>
                    <span class="text-hero-emerald">Together.</span>
                </h1>

                <!-- Subtitle -->
                <p class="hero-subtitle">
                    Discover valuable study materials, exchange ideas, and connect with fellow students through one collaborative learning community.
                </p>

                <!-- Two CTA Pills Side-by-Side -->
                <div class="hero-cta-group">
                    <a href="resources.php" class="btn-hero-primary-pill">
                        <i class="bi bi-book-half"></i>
                        <span>Explore Resources</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="study-groups.php" class="btn-hero-glass-pill">
                        <i class="bi bi-people-fill"></i>
                        <span>Join the Community</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Organic Curved Wave Divider at Bottom Edge -->
        <div class="hero-curved-wave">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,45 C320,115 560,35 880,75 C1140,110 1340,55 1440,68 L1440,120 L0,120 Z" fill="rgba(50, 217, 139, 0.28)"></path>
                <path d="M0,70 C360,125 640,65 940,92 C1200,118 1360,78 1440,84 L1440,120 L0,120 Z" fill="var(--nsbm-light-bg)"></path>
            </svg>
        </div>
    </header>

    <!-- 3. BUILT FOR BETTER LEARNING -->
    <section class="py-5 my-3" id="about">
        <div class="container-xl">
            <div class="text-center mb-5">
                <span class="section-tag">BUILT FOR BETTER LEARNING</span>
                <h2 class="section-title">Everything students need to move forward</h2>
                <p class="section-subtitle mx-auto">One focused space for the resources, people, and study habits that turn effort into progress.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box-card">
                        <div>
                            <div class="feature-icon-wrapper feature-icon-blue">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <h4>Trusted study materials</h4>
                            <p>Find organized notes, revision guides, and past-paper support shared by your academic community.</p>
                        </div>
                        <a href="#resources" class="feature-card-link">Learn more <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box-card">
                        <div>
                            <div class="feature-icon-wrapper feature-icon-green">
                                <i class="bi bi-chat-square-quote"></i>
                            </div>
                            <h4>Knowledge that moves</h4>
                            <p>Ask thoughtful questions and learn from clear, peer-reviewed answers across every subject.</p>
                        </div>
                        <a href="#discussions" class="feature-card-link">Learn more <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box-card">
                        <div>
                            <div class="feature-icon-wrapper feature-icon-purple">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4>Real academic connection</h4>
                            <p>Build focused study circles, plan sessions, and make difficult modules feel manageable.</p>
                        </div>
                        <a href="#study-groups" class="feature-card-link">Learn more <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. EXPLORE RESOURCES (Dynamic Backend Powered) -->
    <section class="py-5 bg-body-tertiary" id="resources">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
                <div>
                    <span class="section-tag">EXPLORE RESOURCES</span>
                    <h2 class="section-title mb-1">Study smarter with material you can trust</h2>
                    <p class="section-subtitle mb-0">Discover useful, student-shared resources organized for the way you actually learn.</p>
                </div>
                <a href="resources.php" class="text-success fw-bold text-decoration-none small">
                    View all resources <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="position-relative" style="min-width: 320px; max-width: 450px; flex: 1;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
                    <input type="text" id="liveSearchInput" class="form-control ps-5 py-2" placeholder="Search notes, modules, or subjects..." style="border-radius: 10px;">
                </div>
                <div class="d-flex gap-2 flex-wrap" id="facultyFilterButtons">
                    <button class="resource-filter-pill active" data-filter="all">All subjects</button>
                    <button class="resource-filter-pill" data-filter="Computing">Computing</button>
                    <button class="resource-filter-pill" data-filter="Business">Business</button>
                    <button class="resource-filter-pill" data-filter="Engineering">Engineering</button>
                    <button class="resource-filter-pill" data-filter="Science">Science</button>
                </div>
            </div>

            <div class="row g-4" id="resourceCardsContainer">
                <?php foreach ($resources as $res): ?>
                    <?php 
                        $badgeClass = $facultyBadgeClasses[$res['faculty']] ?? 'pill-computing';
                        $avatarColor = $avatarColors[$res['faculty']] ?? '#0f172a';
                        $iconClass = ($res['file_type'] === 'DOCX') ? 'bi-file-earmark-word text-primary' : 'bi-file-earmark-pdf text-danger';
                    ?>
                    <div class="col-lg-3 col-md-6 resource-filter-item" data-faculty="<?php echo htmlspecialchars($res['faculty']); ?>" data-title="<?php echo htmlspecialchars($res['title']); ?>">
                        <div class="resource-grid-card">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="res-format-badge"><i class="bi <?php echo $iconClass; ?>"></i> <?php echo htmlspecialchars($res['file_type']); ?></span>
                                    <button class="btn btn-sm p-0 text-muted border-0" title="Bookmark"><i class="bi bi-bookmark"></i></button>
                                </div>
                                <span class="res-faculty-pill <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($res['faculty']); ?></span>
                                <h5 class="res-card-title"><?php echo htmlspecialchars($res['title']); ?></h5>
                                <p class="res-card-desc"><?php echo htmlspecialchars($res['description']); ?></p>
                            </div>
                            <div>
                                <div class="res-author-row">
                                    <div class="res-author-avatar" style="background:<?php echo $avatarColor; ?>;"><?php echo htmlspecialchars($res['author_initials'] ?? 'ST'); ?></div>
                                    <div class="res-author-text">
                                        Shared by<br><strong><?php echo htmlspecialchars($res['author_name']); ?></strong>
                                    </div>
                                </div>
                                <div class="res-card-footer">
                                    <div><i class="bi bi-star-fill text-warning me-1"></i><?php echo $res['rating']; ?> &nbsp; <i class="bi bi-download text-muted me-1"></i><?php echo $res['downloads']; ?></div>
                                    <a href="resource-details.php?id=<?php echo (int)($res['id'] ?? 1); ?>" class="text-success fw-bold text-decoration-none">View resource</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 5. A SIMPLE RHYTHM -->
    <section class="py-5">
        <div class="container-xl">
            <div class="rhythm-dark-card">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <span class="text-uppercase fw-bold small text-info" style="letter-spacing: 2px;">A SIMPLE RHYTHM</span>
                        <h2 class="display-6 fw-bold my-3 text-white">From searching to succeeding</h2>
                        <p class="text-light opacity-75 mb-4" style="max-width: 480px; font-size: 1.05rem;">
                            NSBM Study Circle keeps the path from curiosity to confidence clear and collaborative.
                        </p>
                        <a href="#resources" class="btn btn-outline-light rounded-2 px-4 py-2 fw-semibold">
                            Start exploring <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="col-lg-6">
                        <div class="rhythm-step-item">
                            <div class="rhythm-step-num">01</div>
                            <div>
                                <h5 class="fw-bold mb-1 text-white">Discover</h5>
                                <p class="text-light opacity-75 small mb-0">Find relevant materials, discussions, and people.</p>
                            </div>
                        </div>
                        <div class="rhythm-step-item">
                            <div class="rhythm-step-num">02</div>
                            <div>
                                <h5 class="fw-bold mb-1 text-white">Collaborate</h5>
                                <p class="text-light opacity-75 small mb-0">Share context, solve problems, and study together.</p>
                            </div>
                        </div>
                        <div class="rhythm-step-item">
                            <div class="rhythm-step-num">03</div>
                            <div>
                                <h5 class="fw-bold mb-1 text-white">Achieve</h5>
                                <p class="text-light opacity-75 small mb-0">Build understanding and make consistent progress.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. ACADEMIC DISCUSSIONS (Dynamic Backend Powered) -->
    <section class="py-5 bg-body-tertiary" id="discussions">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
                <div>
                    <span class="section-tag">ACADEMIC DISCUSSIONS</span>
                    <h2 class="section-title mb-1">Questions worth asking. Answers worth sharing.</h2>
                    <p class="section-subtitle mb-0">Join active conversations and turn individual questions into community knowledge.</p>
                </div>
                <a href="discussions.php?action=ask" class="btn btn-outline-secondary btn-sm py-2 px-3 fw-semibold">
                    Ask a question
                </a>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-8">
                    <?php foreach ($discussions as $disc): ?>
                        <div class="discussion-item-card mb-3">
                            <div class="upvote-box">
                                <i class="bi bi-arrow-up fs-5 text-success"></i>
                                <span class="count"><?php echo $disc['upvotes']; ?></span>
                                <span class="label">helpful</span>
                            </div>
                            <div class="flex-grow-1">
                                <span class="res-faculty-pill pill-computing"><?php echo htmlspecialchars($disc['faculty']); ?></span>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($disc['title']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($disc['body']); ?></p>
                                <div class="d-flex align-items-center gap-3 small text-muted">
                                    <span><span class="badge bg-dark rounded-circle me-1"><?php echo htmlspecialchars($disc['author_initials']); ?></span> Asked by <?php echo htmlspecialchars($disc['author_name']); ?></span>
                                    <span><i class="bi bi-chat-left-text me-1"></i> <?php echo $disc['answers_count']; ?> answers</span>
                                    <span><?php echo htmlspecialchars($disc['time_ago']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="discussion-callout-card">
                        <div>
                            <div class="feature-icon-wrapper feature-icon-green mb-3">
                                <i class="bi bi-chat-square-dots"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Have something on your mind?</h4>
                            <p class="text-muted small mb-4">Good questions help everyone learn. Add context, show what you tried, and invite a stronger answer.</p>
                        </div>
                        <a href="discussions.php?action=start" class="btn btn-success fw-bold py-2 w-100 shadow-sm">
                            Start a discussion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. STUDY GROUPS (Dynamic Backend Powered) -->
    <section class="py-5" id="study-groups">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
                <div>
                    <span class="section-tag">STUDY GROUPS</span>
                    <h2 class="section-title mb-1">Progress feels easier in the right circle</h2>
                    <p class="section-subtitle mb-0">Find focused communities built around the modules and goals that matter to you.</p>
                </div>
                <a href="study-groups.php" class="text-success fw-bold text-decoration-none small">
                    Browse all groups <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($groups as $grp): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="study-group-box">
                            <div>
                                <div class="group-avatar-initials <?php echo htmlspecialchars($grp['class_color'] ?? 'bg-init-ds'); ?>">
                                    <?php echo htmlspecialchars($grp['avatar_initials'] ?? 'SG'); ?>
                                </div>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($grp['name']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($grp['description']); ?></p>
                            </div>
                            <div>
                                <div class="small text-muted mb-3"><i class="bi bi-people me-1"></i> <?php echo $grp['member_count']; ?> members</div>
                                <a href="study-group-details.php?id=<?php echo (int)($grp['id'] ?? 1); ?>" class="btn btn-outline-secondary btn-sm w-100 fw-semibold">Join group</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 8. UPCOMING SESSIONS (Dynamic Backend Powered) -->
    <section class="py-5 bg-body-tertiary" id="sessions">
        <div class="container-xl">
            <div class="text-center mb-5">
                <span class="section-tag">UPCOMING SESSIONS</span>
                <h2 class="section-title">Make time for focused learning</h2>
                <p class="section-subtitle mx-auto">Join planned sessions, bring your questions, and leave with clearer next steps.</p>
            </div>

            <div class="row g-3 justify-content-center">
                <?php foreach ($sessions as $idx => $sess): ?>
                    <div class="col-lg-10">
                        <div class="session-row-card">
                            <div class="session-date-box <?php echo ($idx === 0) ? 'date-box-dark' : 'date-box-light'; ?>">
                                <span class="day"><?php echo htmlspecialchars($sess['day']); ?></span>
                                <span class="month"><?php echo htmlspecialchars($sess['month']); ?></span>
                            </div>
                            <div class="flex-grow-1">
                                <span class="res-faculty-pill pill-computing mb-1"><?php echo htmlspecialchars($sess['faculty']); ?></span>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($sess['title']); ?></h5>
                                <div class="d-flex align-items-center gap-3 small text-muted">
                                    <span><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($sess['time_details'] ?? '6:30 PM - 8:00 PM'); ?></span>
                                    <span><i class="bi bi-people me-1"></i> <?php echo $sess['enrolled_count']; ?> attending</span>
                                </div>
                            </div>
                            <div>
                                <?php if ($idx === 0): ?>
                                    <a href="session-details.php?id=<?php echo (int)($sess['id'] ?? ($idx + 1)); ?>" class="btn btn-success fw-bold px-4 py-2">Reserve a seat</a>
                                <?php else: ?>
                                    <a href="session-details.php?id=<?php echo (int)($sess['id'] ?? ($idx + 1)); ?>" class="btn btn-outline-secondary btn-sm rounded-circle p-2" title="Details">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 9. READY WHEN YOU ARE -->
    <section class="py-5">
        <div class="container-xl">
            <div class="cta-banner-card">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="text-uppercase fw-bold small text-info" style="letter-spacing: 2px;">READY WHEN YOU ARE</span>
                        <h2 class="display-6 fw-bold my-2 text-white">Your Next Academic Breakthrough Starts Here.</h2>
                        <p class="text-light opacity-75 mb-0" style="max-width: 580px;">
                            Join a community built to help you learn with more clarity, confidence, and connection.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end text-start">
                        <a href="signup.php" class="btn-cta-white mb-2">
                            Create your free account <i class="bi bi-arrow-right"></i>
                        </a>
                        <div class="small text-light opacity-75">No credit card · Built for students</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 10. FOOTER -->
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
                <div>&copy; 2026 NSBM Study Circle. An independent student-run platform. Not affiliated with or endorsed by NSBM Green University.</div>
                <div class="d-flex gap-3">
                    <a href="contact.php" class="text-muted text-decoration-none">Privacy</a>
                    <a href="contact.php" class="text-muted text-decoration-none">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
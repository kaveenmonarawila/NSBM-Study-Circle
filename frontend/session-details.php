<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY SESSION DETAILS PAGE (PDF SCREENSHOTS PAGES 9 - 11)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionId = (int)($_GET['id'] ?? 1);

// Structured session data matching PDF reference
$sessionsData = [
    1 => [
        'id' => 1,
        'title' => 'Algorithms: Final Exam Problem Solving',
        'faculty' => 'Computing',
        'day' => '24',
        'day_name' => 'Saturday',
        'month_year' => 'May 2025',
        'time' => '6:30 PM – 8:00 PM',
        'format' => 'Online · Meeting link after registration',
        'capacity_str' => '18 of 24 places reserved',
        'attending_count' => 18,
        'host_initials' => 'SD',
        'host_name' => 'Sahan Dissanayake',
        'host_role' => 'Data Structures Circle coordinator',
        'description' => 'A focused ninety-minute session working through algorithm analysis, sorting, trees, and graph problems from past papers.',
        'topics' => [
            ['num' => '01', 'text' => 'Quick complexity analysis warm-up'],
            ['num' => '02', 'text' => 'Comparing common sorting approaches'],
            ['num' => '03', 'text' => 'Tree traversal and balancing problems'],
            ['num' => '04', 'text' => 'Graph search exam question'],
            ['num' => '05', 'text' => 'Open questions and revision plan']
        ],
        'what_to_bring' => 'Your lecture formula sheet, a notebook, and one algorithm question you found difficult. A practice worksheet will be shared at the beginning of the session.'
    ],
    2 => [
        'id' => 2,
        'title' => 'Marketing Strategy Case Review',
        'faculty' => 'Business',
        'day' => '26',
        'day_name' => 'Monday',
        'month_year' => 'May 2025',
        'time' => '4:00 PM – 5:30 PM',
        'format' => 'Learning Commons Room 3',
        'capacity_str' => '14 of 20 places reserved',
        'attending_count' => 14,
        'host_initials' => 'NP',
        'host_name' => 'Nethmi Perera',
        'host_role' => 'Business Minds coordinator',
        'description' => 'Comprehensive case study breakdown focusing on SWOT and strategic positioning frameworks.',
        'topics' => [
            ['num' => '01', 'text' => 'Executive summary formatting'],
            ['num' => '02', 'text' => 'Porter Five Forces applied evaluation'],
            ['num' => '03', 'text' => 'Financial metrics integration in marketing cases']
        ],
        'what_to_bring' => 'Printed case study copies and highlighter pens.'
    ],
    3 => [
        'id' => 3,
        'title' => 'Laboratory Report Review',
        'faculty' => 'Science',
        'day' => '28',
        'day_name' => 'Wednesday',
        'month_year' => 'May 2025',
        'time' => '5:30 PM – 7:00 PM',
        'format' => 'Science Laboratory 2',
        'capacity_str' => '12 of 16 places reserved (4 seats left)',
        'attending_count' => 12,
        'host_initials' => 'DJ',
        'host_name' => 'Dinara Jayasinghe',
        'host_role' => 'Science Circle lead',
        'description' => 'Hands-on review of statistical error margins, diagram standardizations, and referencing in experimental write-ups.',
        'topics' => [
            ['num' => '01', 'text' => 'Raw data error propagation analysis'],
            ['num' => '02', 'text' => 'Scientific graph layout standards'],
            ['num' => '03', 'text' => 'Peer critique of lab draft submissions']
        ],
        'what_to_bring' => 'Your draft laboratory write-up and raw measurement charts.'
    ],
    4 => [
        'id' => 4,
        'title' => 'Research Proposal Writing Lab',
        'faculty' => 'Academic Skills',
        'day' => '30',
        'day_name' => 'Friday',
        'month_year' => 'May 2025',
        'time' => '3:00 PM – 4:30 PM',
        'format' => 'Online · Meeting link after registration',
        'capacity_str' => '21 of 30 places reserved',
        'attending_count' => 21,
        'host_initials' => 'RM',
        'host_name' => 'Ravindu Mendis',
        'host_role' => 'Academic Writing Mentor',
        'description' => 'Interactive proposal workshop on formulation of problem statements, hypothesis structuring, and citation mapping.',
        'topics' => [
            ['num' => '01', 'text' => 'Drafting compelling problem statements'],
            ['num' => '02', 'text' => 'Literature matrix organization'],
            ['num' => '03', 'text' => 'Methodology feasibility check']
        ],
        'what_to_bring' => 'Proposed research topic ideas and three peer-reviewed reference papers.'
    ]
];

$session = $sessionsData[$sessionId] ?? $sessionsData[1];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($session['title']); ?> - NSBM Study Circle</title>
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
    <?php $activePage = 'sessions'; include __DIR__ . '/includes/header.php'; ?>

    <!-- ====================================================================
         2. BREADCRUMB ROW (PDF Page 9)
         ==================================================================== -->
    <div class="container-xl">
        <div class="session-breadcrumb-row">
            <a href="sessions.php">Study Sessions</a>
            <span class="session-breadcrumb-sep">/</span>
            <span class="text-dark fw-bold"><?php echo htmlspecialchars($session['title']); ?></span>
        </div>
    </div>

    <!-- ====================================================================
         3. 2-COLUMN MAIN CONTENT & BOOKING SIDEBAR (PDF Pages 9 - 11)
         ==================================================================== -->
    <main class="container-xl pb-5 mb-4">
        <div class="row g-4 justify-content-between">
            <!-- Left Main Information Column (PDF Page 9 & 10) -->
            <div class="col-lg-7">
                <span class="subject-pill-badge subject-pill-computing mb-2">
                    <?php echo htmlspecialchars($session['faculty']); ?>
                </span>
                
                <h1 class="session-details-main-title"><?php echo htmlspecialchars($session['title']); ?></h1>
                
                <p class="session-details-desc">
                    <?php echo htmlspecialchars($session['description']); ?>
                </p>

                <!-- Hosted By Profile (PDF Page 9) -->
                <div class="host-profile-row">
                    <div class="host-avatar-circle">
                        <?php echo htmlspecialchars($session['host_initials']); ?>
                    </div>
                    <div>
                        <div class="host-label-small">Hosted by</div>
                        <div class="host-name-title"><?php echo htmlspecialchars($session['host_name']); ?></div>
                        <div class="host-role-subtitle"><?php echo htmlspecialchars($session['host_role']); ?></div>
                    </div>
                </div>

                <!-- What We'll Cover Section (PDF Page 10) -->
                <h2 class="session-section-heading">What we'll cover</h2>
                <div class="cover-numbered-list">
                    <?php foreach ($session['topics'] as $topic): ?>
                    <div class="cover-topic-row">
                        <span class="cover-number-badge"><?php echo htmlspecialchars($topic['num']); ?></span>
                        <span><?php echo htmlspecialchars($topic['text']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- What To Bring Section (PDF Page 10) -->
                <h2 class="session-section-heading">What to bring</h2>
                <div class="what-to-bring-box">
                    <?php echo htmlspecialchars($session['what_to_bring']); ?>
                </div>

                <!-- Who's Attending Section (PDF Page 10 & 11) -->
                <h2 class="session-section-heading" style="font-size: 1.1rem;">Who's attending</h2>
                <div class="attending-summary-box">
                    <div class="attending-avatars-stack">
                        <span class="attendee-avatar-circle">NP</span>
                        <span class="attendee-avatar-circle attendee-blue">KS</span>
                        <span class="attendee-avatar-circle attendee-orange">AF</span>
                    </div>
                    <span class="attending-text-bold" id="attendingCountDisplay">
                        <?php echo (int)$session['attending_count']; ?> students have reserved a place
                    </span>
                </div>
            </div>

            <!-- Right Booking Information Card (PDF Pages 9 - 11) -->
            <div class="col-lg-5 col-xl-4">
                <div class="booking-info-card">
                    <!-- Date Hero -->
                    <div class="booking-date-hero">
                        <div class="booking-date-large"><?php echo htmlspecialchars($session['day']); ?></div>
                        <div class="booking-date-meta">
                            <div class="booking-day-name"><?php echo htmlspecialchars($session['day_name']); ?></div>
                            <div class="booking-month-year"><?php echo htmlspecialchars($session['month_year']); ?></div>
                        </div>
                    </div>

                    <!-- Specification Rows with Icons -->
                    <div class="booking-spec-row">
                        <div class="booking-spec-label">
                            <i class="bi bi-clock"></i> Time
                        </div>
                        <div class="booking-spec-value"><?php echo htmlspecialchars($session['time']); ?></div>
                    </div>

                    <div class="booking-spec-row">
                        <div class="booking-spec-label">
                            <i class="bi bi-geo-alt"></i> Format
                        </div>
                        <div class="booking-spec-value"><?php echo htmlspecialchars($session['format']); ?></div>
                    </div>

                    <div class="booking-spec-row">
                        <div class="booking-spec-label">
                            <i class="bi bi-people"></i> Capacity
                        </div>
                        <div class="booking-spec-value" id="capacityDisplay"><?php echo htmlspecialchars($session['capacity_str']); ?></div>
                    </div>

                    <!-- Reserve Place Button -->
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="btn-reserve-place" id="reservePlaceBtn">
                            Reserve your place
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-reserve-place" onclick="promptLoginRequired('session_book')">
                            <i class="bi bi-lock-fill me-1"></i> Log in to reserve place
                        </button>
                    <?php endif; ?>

                    <p class="booking-fineprint-text">Free session · Registration required</p>
                </div>
            </div>
        </div>
    </main>

    <!-- ====================================================================
         4. FOOTER (Exact Match to Figma / PDF Reference)
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
    <!-- Sessions JS -->
    <script src="assets/js/sessions.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>


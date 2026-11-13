<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY SESSIONS MAIN PAGE (PDF SCREENSHOTS PAGES 1 - 3, 8)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db_config.php';

$liveSessions = [];
if (isset($conn) && $conn && !$conn->connect_error) {
    try {
        $q = $conn->query("
            SELECT ss.*, COALESCE(u.name, 'NSBM Contributor') AS host_name
            FROM study_sessions ss
            LEFT JOIN users u ON ss.host_id = u.id
            WHERE ss.status IN ('Scheduled', 'Completed')
            ORDER BY ss.session_date ASC
        ");
        if ($q) {
            while ($row = $q->fetch_assoc()) {
                $liveSessions[] = $row;
            }
        }
    } catch (Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Sessions - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
        <!-- Website Favicon: Study Circle logo -->
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
         2. HERO SECTION (Exact Match to PDF Page 1)
         ==================================================================== -->
    <header class="sessions-hero">
        <div class="container-xl">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8">
                    <span class="sessions-hero-label">COLLABORATIVE LEARNING</span>
                    <h1 class="sessions-hero-title">Put focused study time on your calendar</h1>
                    <p class="sessions-hero-desc">
                        Join structured sessions led by students who want to prepare, practise, and progress together.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a href="student/schedule.php" class="btn-create-session text-decoration-none d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-calendar-plus me-2"></i> Create a session
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn-create-session" onclick="promptLoginRequired('session_create')">
                            <i class="bi bi-lock-fill me-1"></i> Create a session
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ====================================================================
         3. SESSIONS FILTER & WEEKLY CALENDAR (Exact Match to PDF Page 1, 8)
         ==================================================================== -->
    <section class="sessions-filter-section">
        <div class="container-xl">
            <!-- Filter Bar: Tabs & Subject Dropdown -->
            <div class="filter-bar-row">
                <!-- Left Tabs: Upcoming, This week, Online -->
                <div class="filter-tabs-pills" role="tablist">
                    <button type="button" class="filter-tab-pill active" data-tab="upcoming">Upcoming</button>
                    <button type="button" class="filter-tab-pill" data-tab="this-week">This week</button>
                    <button type="button" class="filter-tab-pill" data-tab="online">Online</button>
                </div>

                <!-- Right Subject Filter Dropdown (PDF Page 8) -->
                <div class="subject-dropdown-wrap">
                    <button type="button" class="subject-dropdown-btn" id="subjectDropdownBtn">
                        <span id="selectedSubjectLabel">All subjects</span>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 0.8rem;"></i>
                    </button>
                    
                    <div class="subject-dropdown-menu-custom" id="subjectDropdownMenu">
                        <a href="#" class="subject-item-link selected" data-subject="all">All subjects</a>
                        
                        <!-- Computing -->
                        <div class="subject-group-header">Computing</div>
                        <a href="#" class="subject-item-link" data-subject="Computing">All Computing subjects</a>
                        <a href="#" class="subject-item-link" data-subject="Introduction to Computer Science">Introduction to Computer Science</a>
                        <a href="#" class="subject-item-link" data-subject="Programming Fundamentals">Programming Fundamentals</a>
                        <a href="#" class="subject-item-link" data-subject="Mathematics for Computing">Mathematics for Computing</a>
                        <a href="#" class="subject-item-link" data-subject="Computer Systems">Computer Systems</a>
                        <a href="#" class="subject-item-link" data-subject="Web Application Development">Web Application Development</a>
                        <a href="#" class="subject-item-link" data-subject="Database Management Systems">Database Management Systems</a>

                        <!-- Business -->
                        <div class="subject-group-header">Business</div>
                        <a href="#" class="subject-item-link" data-subject="Business">All Business subjects</a>
                        <a href="#" class="subject-item-link" data-subject="Principles of Management">Principles of Management</a>
                        <a href="#" class="subject-item-link" data-subject="Financial Accounting">Financial Accounting</a>
                        <a href="#" class="subject-item-link" data-subject="Business Economics">Business Economics</a>
                        <a href="#" class="subject-item-link" data-subject="Business Mathematics">Business Mathematics</a>
                        <a href="#" class="subject-item-link" data-subject="Marketing Management">Marketing Management</a>
                        <a href="#" class="subject-item-link" data-subject="Business Communication">Business Communication</a>

                        <!-- Science -->
                        <div class="subject-group-header">Science</div>
                        <a href="#" class="subject-item-link" data-subject="Science">All Science subjects</a>
                        <a href="#" class="subject-item-link" data-subject="Biology for Computing">Biology for Computing</a>
                        <a href="#" class="subject-item-link" data-subject="Physics">Physics</a>
                        <a href="#" class="subject-item-link" data-subject="Chemistry">Chemistry</a>

                        <!-- Mathematics -->
                        <div class="subject-group-header">Mathematics</div>
                        <a href="#" class="subject-item-link" data-subject="Mathematics">All Mathematics</a>
                        <a href="#" class="subject-item-link" data-subject="Discrete Mathematics">Discrete Mathematics</a>
                        <a href="#" class="subject-item-link" data-subject="Linear Algebra">Linear Algebra</a>
                        <a href="#" class="subject-item-link" data-subject="Calculus">Calculus</a>

                        <!-- Academic Skills -->
                        <div class="subject-group-header">Academic Skills</div>
                        <a href="#" class="subject-item-link" data-subject="Academic Skills">All Academic Skills</a>
                        <a href="#" class="subject-item-link" data-subject="Academic Writing">Academic Writing</a>
                        <a href="#" class="subject-item-link" data-subject="Research Methods">Research Methods</a>
                        <a href="#" class="subject-item-link" data-subject="Presentation Skills">Presentation Skills</a>
                    </div>
                </div>
            </div>

            <!-- Interactive Real-Time Calendar Component -->
            <div class="calendar-component-wrapper mt-4">
                <!-- Navigation & View Switcher Bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <h4 class="fw-bold mb-0 text-dark" id="publicCalMonthYear" style="font-size: 1.2rem;">November 2026</h4>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" id="pubCalPrevBtn" title="Previous"><i class="bi bi-chevron-left"></i></button>
                            <button type="button" class="btn btn-outline-secondary px-3" id="pubCalTodayBtn">Today</button>
                            <button type="button" class="btn btn-outline-secondary" id="pubCalNextBtn" title="Next"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="pubViewWeekBtn">
                                <i class="bi bi-calendar-week me-1"></i> Week
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="pubViewMonthBtn">
                                <i class="bi bi-calendar3 me-1"></i> Month
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1. Horizontal Weekly Calendar Strip (Dynamic) -->
                <div class="weekly-calendar-strip" id="publicWeeklyStrip">
                    <!-- Populated dynamically by sessions.js -->
                </div>

                <!-- 2. Interactive Monthly Calendar Grid (Toggled) -->
                <div id="publicMonthlyCalendarContainer" class="d-none mt-2">
                    <div class="table-responsive bg-white rounded-3 p-3 border" style="box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);">
                        <table class="calendar-month-table mb-0">
                            <thead>
                                <tr>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                    <th>Sun</th>
                                </tr>
                            </thead>
                            <tbody id="publicCalendarGridBody">
                                <!-- Populated dynamically by sessions.js -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Selected Day Filter Indicator -->
                <div id="publicSelectedDateBar" class="d-flex align-items-center justify-content-between p-2 px-3 mt-3 rounded-3" style="background: rgba(0, 185, 120, 0.08); border: 1px solid rgba(0, 185, 120, 0.25);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check-fill text-success"></i>
                        <span class="small">Filter: <strong id="publicSelectedDateLabel">All Upcoming Sessions</strong></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-success small" id="pubResetFilterBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Show All Upcoming
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         4. VERTICAL SESSION CARDS LIST (Exact Match to PDF Pages 2 & 3)
         ==================================================================== -->
    <main class="sessions-list-container">
        <div class="container-xl" id="sessionsListContainer">

            <?php if (!empty($liveSessions)): ?>
                <?php foreach ($liveSessions as $idx => $s): 
                    $dt = new DateTime($s['session_date']);
                    $dateKey = $dt->format('Y-m-d');
                    $dayNum = $dt->format('d');
                    $monthShort = strtoupper($dt->format('M'));
                    $timeFormatted = $dt->format('g:i A');
                    $isFirst = ($idx === 0);
                    $facLower = strtolower($s['faculty'] ?: 'computing');
                    $pillClass = 'subject-pill-computing';
                    if (strpos($facLower, 'bus') !== false) $pillClass = 'subject-pill-business';
                    elseif (strpos($facLower, 'sci') !== false) $pillClass = 'subject-pill-science';
                    elseif (strpos($facLower, 'eng') !== false) $pillClass = 'subject-pill-engineering';
                    elseif (strpos($facLower, 'acad') !== false) $pillClass = 'subject-pill-academicskills';
                    $isThisWeek = (date('W', strtotime($s['session_date'])) === date('W'));
                    $seatsLeft = (int)$s['capacity'] - (int)$s['enrolled_count'];
                    $seatsText = $seatsLeft <= 5 ? "{$seatsLeft} seats left" : "Seats available";
                    $seatsClass = $seatsLeft <= 5 ? "seats-status-text warning" : "seats-status-text";
                ?>
                <article class="session-card-item" 
                         data-id="<?= (int)$s['id'] ?>"
                         data-date="<?= $dateKey ?>"
                         data-day="<?= (int)$dayNum ?>"
                         data-category="<?= htmlspecialchars($s['faculty']) ?>"
                         data-format="<?= htmlspecialchars($s['format_type'] ?: 'Online') ?>"
                         data-this-week="<?= $isThisWeek ? 'true' : 'false' ?>">
                    <div class="session-card-left">
                        <div class="session-date-box <?= $isFirst ? '' : 'date-light' ?>">
                            <span class="date-box-number"><?= $dayNum ?></span>
                            <span class="date-box-month"><?= $monthShort ?></span>
                        </div>
                        <div>
                            <span class="subject-pill-badge <?= $pillClass ?>"><?= htmlspecialchars($s['faculty']) ?></span>
                            <h2 class="session-item-title"><?= htmlspecialchars($s['title']) ?></h2>
                            <div class="session-item-meta">
                                <span><i class="bi bi-clock me-1"></i> <?= $timeFormatted ?></span>
                                <span><i class="bi bi-people me-1"></i> <?= (int)$s['enrolled_count'] ?> attending</span>
                                <span><?= htmlspecialchars($s['location_details'] ?: ($s['format_type'] ?: 'Online')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="session-card-right">
                        <span class="<?= $seatsClass ?>"><?= $seatsText ?></span>
                        <a href="session-details.php?id=<?= (int)$s['id'] ?>" class="btn-session-view-details">View details</a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>

            <!-- Card 1: Algorithms: Final Exam Problem Solving (PDF Page 2) -->
            <article class="session-card-item" 
                     data-id="1"
                     data-date="2026-05-24"
                     data-day="24"
                     data-category="Computing"
                     data-format="Online"
                     data-this-week="true">
                <div class="session-card-left">
                    <div class="session-date-box">
                        <span class="date-box-number">24</span>
                        <span class="date-box-month">MAY</span>
                    </div>
                    <div>
                        <span class="subject-pill-badge subject-pill-computing">Computing</span>
                        <h2 class="session-item-title">Algorithms: Final Exam Problem Solving</h2>
                        <div class="session-item-meta">
                            <span><i class="bi bi-clock me-1"></i> 6:30 PM – 8:00 PM</span>
                            <span><i class="bi bi-people me-1"></i> 18 attending</span>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
                <div class="session-card-right">
                    <span class="seats-status-text">Seats available</span>
                    <a href="session-details.php?id=1" class="btn-session-view-details">View details</a>
                </div>
            </article>

            <!-- Card 2: Marketing Strategy Case Review (PDF Page 2) -->
            <article class="session-card-item" 
                     data-id="2"
                     data-date="2026-05-26"
                     data-day="26"
                     data-category="Business"
                     data-format="Physical"
                     data-this-week="true">
                <div class="session-card-left">
                    <div class="session-date-box date-light">
                        <span class="date-box-number">26</span>
                        <span class="date-box-month">MAY</span>
                    </div>
                    <div>
                        <span class="subject-pill-badge subject-pill-business">Business</span>
                        <h2 class="session-item-title">Marketing Strategy Case Review</h2>
                        <div class="session-item-meta">
                            <span><i class="bi bi-clock me-1"></i> 4:00 PM – 5:30 PM</span>
                            <span><i class="bi bi-people me-1"></i> 14 attending</span>
                            <span>Learning Commons</span>
                        </div>
                    </div>
                </div>
                <div class="session-card-right">
                    <span class="seats-status-text">Seats available</span>
                    <a href="session-details.php?id=2" class="btn-session-view-details">View details</a>
                </div>
            </article>

            <!-- Card 3: Laboratory Report Review (PDF Page 2) -->
            <article class="session-card-item" 
                     data-id="3"
                     data-date="2026-05-28"
                     data-day="28"
                     data-category="Science"
                     data-format="Physical"
                     data-this-week="true">
                <div class="session-card-left">
                    <div class="session-date-box date-light">
                        <span class="date-box-number">28</span>
                        <span class="date-box-month">MAY</span>
                    </div>
                    <div>
                        <span class="subject-pill-badge subject-pill-science">Science</span>
                        <h2 class="session-item-title">Laboratory Report Review</h2>
                        <div class="session-item-meta">
                            <span><i class="bi bi-clock me-1"></i> 5:30 PM – 7:00 PM</span>
                            <span><i class="bi bi-people me-1"></i> 12 attending</span>
                            <span>Science Laboratory</span>
                        </div>
                    </div>
                </div>
                <div class="session-card-right">
                    <span class="seats-status-text warning">4 seats left</span>
                    <a href="session-details.php?id=3" class="btn-session-view-details">View details</a>
                </div>
            </article>

            <!-- Card 4: Research Proposal Writing Lab (PDF Page 3) -->
            <article class="session-card-item" 
                     data-id="4"
                     data-date="2026-05-30"
                     data-day="30"
                     data-category="Academic Skills"
                     data-format="Online"
                     data-this-week="false">
                <div class="session-card-left">
                    <div class="session-date-box date-light">
                        <span class="date-box-number">30</span>
                        <span class="date-box-month">MAY</span>
                    </div>
                    <div>
                        <span class="subject-pill-badge subject-pill-academicskills">Academic Skills</span>
                        <h2 class="session-item-title">Research Proposal Writing Lab</h2>
                        <div class="session-item-meta">
                            <span><i class="bi bi-clock me-1"></i> 3:00 PM – 4:30 PM</span>
                            <span><i class="bi bi-people me-1"></i> 21 attending</span>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
                <div class="session-card-right">
                    <span class="seats-status-text">Seats available</span>
                    <a href="session-details.php?id=4" class="btn-session-view-details">View details</a>
                </div>
            </article>
            <?php endif; ?>

        </div>
    </main>

    <!-- ====================================================================
         5. CREATE SESSION MODAL
         ==================================================================== -->
    <div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1" id="createSessionModalLabel">Create a Study Session</h5>
                        <p class="text-muted small mb-0">Organize a focused revision or problem-solving session for peers.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createSessionForm">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Session Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Graph Traversal & Dynamic Programming" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">Faculty / Domain *</label>
                                <select name="faculty" class="form-select" required>
                                    <option value="Computing" selected>Computing</option>
                                    <option value="Business">Business</option>
                                    <option value="Science">Science</option>
                                    <option value="Academic Skills">Academic Skills</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">Format *</label>
                                <select name="format" class="form-select" required>
                                    <option value="Online" selected>Online (Teams/Zoom)</option>
                                    <option value="Physical">Physical (Campus Commons)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">Date *</label>
                                <input type="date" name="session_date" class="form-control" value="2026-05-24" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary">Time *</label>
                                <input type="text" name="session_time" class="form-control" placeholder="6:30 PM - 8:00 PM" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Location / Meeting Link Details *</label>
                            <input type="text" name="location_details" class="form-control" placeholder="e.g. Online · Meeting link after registration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Max Capacity (Seats)</label>
                            <input type="number" name="capacity" class="form-control" value="24" min="5" max="100">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary">Session Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief outline of the revision agenda and problems to tackle..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light" style="border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3">Schedule Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         6. FOOTER (Exact Match to Figma / PDF Reference)
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


<?php
require_once __DIR__ . '/../../backend/config/app_config.php'; // SITE_NAME
// NSBM STUDY CIRCLE - Student Portal My Schedule
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'My schedule';

// ----------------------------------------------------------------------------
// The student's REAL sessions from the database: ones they host + ones they
// reserved. Cancelled / rejected sessions are not shown.
// ----------------------------------------------------------------------------
$scheduleUid = (int)($_SESSION['user_id'] ?? 0);
$upcomingSessions = [];
$pastSessions = [];
if ($scheduleUid > 0 && isset($conn) && $conn && !$conn->connect_error) {
    $sq = $conn->prepare("
        SELECT ss.id, ss.title, ss.faculty, ss.session_date, ss.duration_mins, ss.format_type,
               ss.location_details, ss.status, ss.host_id, COALESCE(u.name, 'Student host') AS host_name
        FROM study_sessions ss
        LEFT JOIN users u ON u.id = ss.host_id
        WHERE ss.status <> 'Cancelled'
          AND (ss.host_id = ? OR EXISTS (SELECT 1 FROM study_session_attendees a WHERE a.session_id = ss.id AND a.user_id = ?))
        ORDER BY ss.session_date ASC, ss.id ASC
    ");
    if ($sq) {
        $sq->bind_param("ii", $scheduleUid, $scheduleUid);
        $sq->execute();
        $res = $sq->get_result();
        $nowTs = time();
        while ($row = $res->fetch_assoc()) {
            $row['ts'] = strtotime($row['session_date']);
            $row['is_host'] = ((int)$row['host_id'] === $scheduleUid);
            if ($row['ts'] >= $nowTs) {
                $upcomingSessions[] = $row;
            } else {
                $pastSessions[] = $row;
            }
        }
        $pastSessions = array_reverse($pastSessions); // most recent first
    }
}

function scheduleFacultyBadge($faculty) {
    $f = strtolower((string)$faculty);
    if (strpos($f, 'bus') !== false) return 'student-badge-business';
    if (strpos($f, 'eng') !== false) return 'student-badge-engineering';
    if (strpos($f, 'sci') !== false) return 'student-badge-science';
    return 'student-badge-computing';
}

// There is no .student-badge-engineering rule in student.css, so style it inline
function scheduleBadgeStyle($badgeClass) {
    return $badgeClass === 'student-badge-engineering'
        ? ' style="background:#F5F3FF; color:#7C3AED; font-weight:700; font-size:0.75rem; padding:2px 8px; border-radius:6px;"'
        : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My schedule - Student Dashboard | <?= htmlspecialchars(SITE_NAME) ?></title>
    <script>(function(){const t=localStorage.getItem('study_circle_theme')||localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <!-- Website Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/study-circle-icon.svg">
    <link rel="icon" type="image/svg+xml" href="../assets/images/study-circle-icon.svg">
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

<div class="student-layout">
    <!-- Sidebar Partial -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="student-main-content">
        <!-- Topbar Partial -->
        <?php include __DIR__ . '/includes/topbar.php'; ?>

        <div class="student-content-body">
            <!-- Page Header -->
            <div class="student-page-header">
                <div>
                    <h2 class="student-page-heading">My schedule</h2>
                    <div class="d-flex align-items-center gap-3 my-2" id="sessionTabsNav">
                        <button type="button" class="btn btn-link p-0 schedule-tab-btn active" data-target="upcoming">Upcoming</button>
                        <button type="button" class="btn btn-link p-0 schedule-tab-btn" data-target="calendar">Calendar</button>
                        <button type="button" class="btn btn-link p-0 schedule-tab-btn" data-target="past">Past</button>
                    </div>
                    <p class="student-page-subheading">Keep upcoming study sessions organized in one place.</p>
                </div>
                <div>
                    <button type="button" class="student-btn-green" data-bs-toggle="modal" data-bs-target="#createSessionModal">
                        <i class="bi bi-calendar-plus me-1"></i> Create session
                    </button>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB PANEL 1: UPCOMING SESSIONS                           -->
            <!-- ========================================================= -->
            <div id="panelUpcoming" class="schedule-tab-panel">
                <!-- Weekly Calendar Strip -->
                <div class="student-week-selector" id="studentWeekSelector">
                    <div class="student-day-pill active" data-day="19">
                        <span class="day-name">MON</span>
                        <span class="day-number">19</span>
                        <div style="width: 4px; height: 4px; background: #1D4ED8; border-radius: 50%; margin: 3px auto 0;"></div>
                    </div>
                    <div class="student-day-pill" data-day="20">
                        <span class="day-name">TUE</span>
                        <span class="day-number">20</span>
                    </div>
                    <div class="student-day-pill" data-day="21">
                        <span class="day-name">WED</span>
                        <span class="day-number">21</span>
                    </div>
                    <div class="student-day-pill" data-day="22">
                        <span class="day-name">THU</span>
                        <span class="day-number">22</span>
                    </div>
                    <div class="student-day-pill" data-day="23">
                        <span class="day-name">FRI</span>
                        <span class="day-number">23</span>
                    </div>
                    <div class="student-day-pill" data-day="24">
                        <span class="day-name">SAT</span>
                        <span class="day-number">24</span>
                        <div style="width: 4px; height: 4px; background: var(--nsbm-green); border-radius: 50%; margin: 3px auto 0;"></div>
                    </div>
                    <div class="student-day-pill" data-day="25">
                        <span class="day-name">SUN</span>
                        <span class="day-number">25</span>
                    </div>
                </div>

                <!-- Next on your schedule (sessions you host or reserved, from the database) -->
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0" style="font-size: 0.95rem; color: var(--slate-muted);">Next on your schedule</h5>
                        <span class="badge bg-light text-secondary border px-2 py-1" id="sessionCountBadge"><?= count($upcomingSessions) ?> Session<?= count($upcomingSessions) === 1 ? '' : 's' ?></span>
                    </div>

                    <div id="upcomingSessionsList">
                    <?php if (empty($upcomingSessions)): ?>
                        <div class="student-card mb-3 p-4 text-center text-secondary" id="noUpcomingSessions">
                            <i class="bi bi-calendar2-x fs-3 d-block mb-2"></i>
                            You have no upcoming sessions. Reserve a place on the <a href="../sessions.php" class="fw-semibold">Sessions page</a> or create your own.
                        </div>
                    <?php endif; ?>
                    <?php foreach ($upcomingSessions as $i => $s):
                        $badge = scheduleFacultyBadge($s['faculty']);
                        $place = trim(($s['location_details'] ?? '') !== '' ? $s['location_details'] : ($s['format_type'] ?: 'Online'));
                        $isPhysical = stripos((string)$s['format_type'], 'phys') !== false || stripos((string)$s['format_type'], 'person') !== false;
                    ?>
                        <div class="student-card mb-3 p-3 session-item-card" data-day="<?= (int)date('j', $s['ts']) ?>" data-date="<?= date('Y-m-d', $s['ts']) ?>" data-session-card="<?= (int)$s['id'] ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge <?= $i === 0 ? 'student-date-dark' : 'student-date-light' ?>">
                                    <span class="student-date-num"><?= date('j', $s['ts']) ?></span>
                                    <span class="student-date-month"><?= strtoupper(date('M', $s['ts'])) ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="<?= $badge ?>"<?= scheduleBadgeStyle($badge) ?>><?= htmlspecialchars($s['faculty'] ?: 'Computing') ?></span>
                                    <h4 class="student-item-title mt-1" style="font-size: 1.05rem;"><?= htmlspecialchars($s['title']) ?></h4>
                                    <p class="student-item-subtitle"><i class="bi <?= $isPhysical ? 'bi-geo-alt' : 'bi-clock' ?> me-1"></i> <?= date('D j M Y', $s['ts']) ?> &bull; <?= date('g:i A', $s['ts']) ?> &bull; <?= htmlspecialchars($place) ?><?= $s['is_host'] ? '' : ' &bull; Host: ' . htmlspecialchars($s['host_name']) ?></p>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($s['is_host'] && $s['status'] === 'Review'): ?>
                                        <span class="text-warning-emphasis fw-bold small">Hosting &middot; Awaiting approval</span>
                                    <?php elseif ($s['is_host']): ?>
                                        <span class="text-primary fw-bold small">Hosting</span>
                                    <?php else: ?>
                                        <span class="text-success fw-bold small">Attending</span>
                                    <?php endif; ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" aria-label="Session actions"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 8px;">
                                            <li><a class="dropdown-item small" href="../session-details.php?id=<?= (int)$s['id'] ?>">View Details</a></li>
                                            <?php if ($s['is_host']): ?>
                                                <li><a class="dropdown-item small text-danger" href="javascript:void(0)" data-schedule-action="cancel" data-id="<?= (int)$s['id'] ?>">Cancel Session</a></li>
                                            <?php else: ?>
                                                <li><a class="dropdown-item small text-danger" href="javascript:void(0)" data-schedule-action="leave" data-id="<?= (int)$s['id'] ?>">Leave Session</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB PANEL 2: INTERACTIVE MONTHLY CALENDAR                -->
            <!-- ========================================================= -->
            <div id="panelCalendar" class="schedule-tab-panel d-none">
                <div class="student-card mb-4 p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <h3 class="fw-bold mb-0 text-dark" id="studentCalMonthYear" style="font-size: 1.25rem;">November 2026</h3>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="calPrevBtn" title="Previous Month"><i class="bi bi-chevron-left"></i></button>
                                <button type="button" class="btn btn-outline-secondary px-3" id="calTodayBtn">Today</button>
                                <button type="button" class="btn btn-outline-secondary" id="calNextBtn" title="Next Month"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- Legend Pills -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Computing</span>
                            <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Business</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Engineering</span>
                            <span class="badge bg-info-subtle text-info-emphasis fw-semibold px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Science</span>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="table-responsive">
                        <table class="calendar-month-table">
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
                            <tbody id="studentCalendarGridBody">
                                <!-- Populated dynamically by real-time JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Day-Specific Schedule Inspector -->
                    <div id="studentCalSelectedDayBox" class="cal-schedule-inspector mt-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary fs-5"></i>
                                <h5 class="fw-bold mb-0 text-dark" id="studentSelectedDateTitle">Schedule for Today</h5>
                                <span class="badge bg-primary-subtle text-primary fw-semibold rounded-pill px-2 py-1" id="studentSelectedDateBadge">Today</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="btnScheduleForSelectedDate">
                                <i class="bi bi-plus-circle me-1"></i> Schedule Session
                            </button>
                        </div>
                        <div id="studentSelectedDateEventsList">
                            <!-- Populated with day-specific sessions -->
                        </div>
                    </div>

                    <div class="mt-3 text-muted small text-center">
                        <i class="bi bi-info-circle me-1"></i> Click on any date cell to view that day's scheduled sessions or schedule a new one.
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB PANEL 3: PAST STUDY SESSIONS ARCHIVE                  -->
            <!-- ========================================================= -->
            <div id="panelPast" class="schedule-tab-panel d-none">
                <div class="student-card mb-4 p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h4 class="student-card-title mb-1" style="font-size: 1.1rem;">Past Study Sessions</h4>
                            <p class="student-item-subtitle mb-0">Sessions you hosted or attended.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 fw-semibold"><?= count($pastSessions) ?> Completed</span>
                    </div>

                    <?php if (empty($pastSessions)): ?>
                        <p class="text-secondary text-center mb-0 py-3">No past sessions yet.</p>
                    <?php endif; ?>
                    <?php foreach ($pastSessions as $i => $s):
                        $badge = scheduleFacultyBadge($s['faculty']);
                        $place = trim(($s['location_details'] ?? '') !== '' ? $s['location_details'] : ($s['format_type'] ?: 'Online'));
                    ?>
                    <div class="p-3 rounded border bg-light <?= $i === count($pastSessions) - 1 ? 'mb-0' : 'mb-3' ?>">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge" style="background:#475569; color:#fff;">
                                    <span class="student-date-num"><?= date('d', $s['ts']) ?></span>
                                    <span class="student-date-month"><?= strtoupper(date('M', $s['ts'])) ?></span>
                                </div>
                                <div>
                                    <span class="<?= $badge ?>"<?= scheduleBadgeStyle($badge) ?>><?= htmlspecialchars($s['faculty'] ?: 'Computing') ?></span>
                                    <h5 class="fw-bold text-dark mt-1 mb-1" style="font-size: 1rem;"><?= htmlspecialchars($s['title']) ?></h5>
                                    <p class="text-secondary small mb-0"><i class="bi bi-person me-1"></i>Hosted by <?= $s['is_host'] ? 'you' : htmlspecialchars($s['host_name']) ?> &bull; <?= date('j M Y, g:i A', $s['ts']) ?> &bull; <?= htmlspecialchars($place) ?></p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check2-circle me-1"></i><?= $s['is_host'] ? 'Hosted' : 'Attended' ?></span>
                                <a href="../session-details.php?id=<?= (int)$s['id'] ?>" class="student-btn-outline py-1 px-3 text-decoration-none" style="font-size: 0.85rem;">View details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: CREATE / SCHEDULE SESSION                          -->
<!-- ========================================================= -->
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--student-card-border);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createSessionModalLabel">Schedule a Study Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createSessionForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Session Topic / Title</label>
                        <input type="text" id="csTitle" class="form-control" placeholder="e.g. Algorithms: Past Papers Analysis" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Faculty / Subject Domain</label>
                        <select id="csFaculty" class="form-select" style="border-radius: 8px;">
                            <option value="Computing">Faculty of Computing</option>
                            <option value="Business">Faculty of Business</option>
                            <option value="Engineering">Faculty of Engineering</option>
                            <option value="Science">Faculty of Science</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" id="csDate" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Time</label>
                            <input type="time" id="csTime" class="form-control" value="18:30" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Duration</label>
                            <select id="csDuration" class="form-select" style="border-radius: 8px;">
                                <option value="60 mins">1 Hour</option>
                                <option value="90 mins" selected>1.5 Hours</option>
                                <option value="120 mins">2 Hours</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Max Attendees</label>
                            <input type="number" id="csCapacity" class="form-control" value="25" min="5" max="100">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Meeting Venue / Format</label>
                        <select id="csFormat" class="form-select" style="border-radius: 8px;">
                            <option value="Online &bull; Microsoft Teams">Online (Microsoft Teams)</option>
                            <option value="Online &bull; Zoom">Online (Zoom)</option>
                            <option value="Campus &bull; Learning Commons">Campus - Learning Commons</option>
                            <option value="Campus &bull; Computing Lab 4">Campus - Computing Lab 4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Agenda & Key Topics</label>
                        <textarea id="csDescription" class="form-control" rows="2" placeholder="Briefly describe what will be covered..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="student-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="student-btn-green" id="csSubmitBtn">
                            <i class="bi bi-calendar-check me-1"></i> Schedule Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/student.js"></script>
<?php include __DIR__ . '/../includes/site_disclaimer.php'; ?>
</body>
</html>

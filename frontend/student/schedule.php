<?php
// NSBM STUDY CIRCLE - Student Portal My Schedule
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'My schedule';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My schedule - Student Portal | NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('study_circle_theme')||localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <!-- Website Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">
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
                <div class="student-week-selector">
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

                <!-- Next on your schedule section -->
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0" style="font-size: 0.95rem; color: var(--slate-muted);">Next on your schedule</h5>
                        <span class="badge bg-light text-secondary border px-2 py-1" id="sessionCountBadge">3 Sessions</span>
                    </div>

                    <div id="upcomingSessionsList">
                        <!-- Session Item 1: Database Systems Revision -->
                        <div class="student-card mb-3 p-3 session-item-card" data-day="19">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge student-date-dark">
                                    <span class="student-date-num">19</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="student-badge-computing">Computing</span>
                                    <h4 class="student-item-title mt-1" style="font-size: 1.05rem;">Database Systems Revision</h4>
                                    <p class="student-item-subtitle"><i class="bi bi-clock me-1"></i> 6:00 PM &bull; Online &bull; Microsoft Teams</p>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-success fw-bold small">Attending</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 8px;">
                                            <li><a class="dropdown-item small open-session-modal" href="javascript:void(0)" data-session-id="1">View Details</a></li>
                                            <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Leave Session</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Item 2: Algorithms: Final Exam Problem Solving (Hosting) -->
                        <div class="student-card mb-3 p-3 session-item-card" data-day="24">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge student-date-dark" style="background-color: #0B1B2B;">
                                    <span class="student-date-num">24</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="student-badge-computing">Computing</span>
                                    <h4 class="student-item-title mt-1" style="font-size: 1.05rem;">Algorithms: Final Exam Problem Solving</h4>
                                    <p class="student-item-subtitle"><i class="bi bi-clock me-1"></i> 6:30 PM &bull; Online &bull; Zoom</p>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-primary fw-bold small">Hosting</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 8px;">
                                            <li><a class="dropdown-item small open-session-modal" href="javascript:void(0)" data-session-id="2">View Details</a></li>
                                            <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Cancel Session</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Item 3: Marketing Case Review -->
                        <div class="student-card mb-3 p-3 session-item-card" data-day="26">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge student-date-light">
                                    <span class="student-date-num">26</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="student-badge-business">Business</span>
                                    <h4 class="student-item-title mt-1" style="font-size: 1.05rem;">Marketing Case Review</h4>
                                    <p class="student-item-subtitle"><i class="bi bi-geo-alt me-1"></i> 4:00 PM &bull; Campus &bull; Learning Commons</p>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-success fw-bold small">Attending</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 8px;">
                                            <li><a class="dropdown-item small open-session-modal" href="javascript:void(0)" data-session-id="3">View Details</a></li>
                                            <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Leave Session</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <h4 class="student-card-title mb-1" style="font-size: 1.1rem;">Past Study Sessions Archive</h4>
                            <p class="student-item-subtitle mb-0">Access notes, summaries, and downloadable PDF material from completed sessions.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 fw-semibold">3 Completed</span>
                    </div>

                    <!-- Past Session 1 -->
                    <div class="p-3 rounded border bg-light mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge" style="background:#475569; color:#fff;">
                                    <span class="student-date-num">12</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div>
                                    <span class="student-badge-computing">Computing</span>
                                    <h5 class="fw-bold text-dark mt-1 mb-1" style="font-size: 1rem;">OOP in Java: Inheritance, Polymorphism & Design Patterns</h5>
                                    <p class="text-secondary small mb-0"><i class="bi bi-person me-1"></i>Hosted by Sahan Dissanayake &bull; 5:00 PM &bull; Microsoft Teams</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check2-circle me-1"></i>Attended</span>
                                <button type="button" class="student-btn-green py-1 px-3 download-material-btn" data-title="OOP in Java Design Patterns Notes" data-faculty="Computing" data-format="PDF" style="font-size: 0.85rem;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Download Notes (PDF)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Past Session 2 -->
                    <div class="p-3 rounded border bg-light mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge" style="background:#475569; color:#fff;">
                                    <span class="student-date-num">08</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div>
                                    <span class="student-badge-business">Business</span>
                                    <h5 class="fw-bold text-dark mt-1 mb-1" style="font-size: 1rem;">Macroeconomics: Monetary Policy & Inflation Revision</h5>
                                    <p class="text-secondary small mb-0"><i class="bi bi-person me-1"></i>Hosted by Nisansala Jayawardena &bull; 3:30 PM &bull; Learning Commons</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check2-circle me-1"></i>Attended</span>
                                <button type="button" class="student-btn-green py-1 px-3 download-material-btn" data-title="Macroeconomics Monetary Policy Revision" data-faculty="Business" data-format="PDF" style="font-size: 0.85rem;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Download Notes (PDF)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Past Session 3 -->
                    <div class="p-3 rounded border bg-light mb-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-date-badge" style="background:#475569; color:#fff;">
                                    <span class="student-date-num">02</span>
                                    <span class="student-date-month">MAY</span>
                                </div>
                                <div>
                                    <span class="student-badge-engineering" style="background:#F5F3FF; color:#7C3AED; font-weight:700; font-size:0.75rem; padding:2px 8px; border-radius:6px;">Engineering</span>
                                    <h5 class="fw-bold text-dark mt-1 mb-1" style="font-size: 1rem;">Electrical Circuit Analysis: Mesh & Nodal Methods</h5>
                                    <p class="text-secondary small mb-0"><i class="bi bi-person me-1"></i>Hosted by Dinuka Mendis &bull; 6:00 PM &bull; Zoom</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check2-circle me-1"></i>Attended</span>
                                <button type="button" class="student-btn-green py-1 px-3 download-material-btn" data-title="Electrical Circuit Analysis Worked Solutions" data-faculty="Engineering" data-format="PDF" style="font-size: 0.85rem;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Download Notes (PDF)
                                </button>
                            </div>
                        </div>
                    </div>
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
                            <input type="date" id="csDate" class="form-control" value="2025-05-28" required>
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
</body>
</html>

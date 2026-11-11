<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: ADMIN DASHBOARD (PDF PAGES 1 & 2)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';

$totalStudents = 0;
$pendingApprovals = 0;
$openReports = 0;
$activeStudents = 0;

if (isset($conn) && $conn && !$conn->connect_error) {
    if (safeAdminTableExists($conn, 'users')) {
        $r = @$conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
        if ($r && $row = $r->fetch_row()) $totalStudents = (int)$row[0];

        $r = @$conn->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'Active'");
        if ($r && $row = $r->fetch_row()) $activeStudents = (int)$row[0];
    }
    if (safeAdminTableExists($conn, 'resources')) {
        $r = @$conn->query("SELECT COUNT(*) FROM resources WHERE status = 'Pending'");
        if ($r && $row = $r->fetch_row()) $pendingApprovals = (int)$row[0];
    }
    if (safeAdminTableExists($conn, 'reported_content')) {
        $r = @$conn->query("SELECT COUNT(*) FROM reported_content WHERE status = 'Pending'");
        if ($r && $row = $r->fetch_row()) $openReports = (int)$row[0];
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    <link rel="alternate icon" type="image/jpeg" href="../assets/images/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="admin-main-wrapper">
            <?php include __DIR__ . '/includes/topbar.php'; ?>

            <main class="admin-content">
                <!-- Page Header -->
                <div class="admin-page-header">
                    <div>
                        <h1 class="admin-page-title">Admin dashboard</h1>
                        <p class="admin-page-subtitle">Monitor platform health, moderation queues, and academic activity.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- 4 KPI Cards Row (PDF Page 1) -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <a href="users.php" class="admin-kpi-card text-decoration-none">
                            <div class="admin-kpi-icon kpi-icon-blue">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="admin-kpi-label">Total students</div>
                                <div class="admin-kpi-value"><?= number_format($totalStudents) ?></div>
                                <div class="admin-kpi-subtext">Active in database</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="approvals.php" class="admin-kpi-card text-decoration-none">
                            <div class="admin-kpi-icon kpi-icon-amber">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div>
                                <div class="admin-kpi-label">Pending approvals</div>
                                <div class="admin-kpi-value"><?= number_format($pendingApprovals) ?></div>
                                <div class="admin-kpi-subtext">Awaiting moderation</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="discussions.php" class="admin-kpi-card text-decoration-none">
                            <div class="admin-kpi-icon kpi-icon-blue">
                                <i class="bi bi-chat-left-text"></i>
                            </div>
                            <div>
                                <div class="admin-kpi-label">Open reports</div>
                                <div class="admin-kpi-value"><?= number_format($openReports) ?></div>
                                <div class="admin-kpi-subtext">Content flags</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="reports.php" class="admin-kpi-card text-decoration-none">
                            <div class="admin-kpi-icon kpi-icon-green">
                                <i class="bi bi-bar-chart-line"></i>
                            </div>
                            <div>
                                <div class="admin-kpi-label">Active this week</div>
                                <div class="admin-kpi-value"><?= number_format($activeStudents) ?></div>
                                <div class="admin-kpi-subtext"><?= $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0 ?>% of students</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Platform Activity & Moderation Queue (PDF Page 1) -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Platform activity</h2>
                                    <p class="admin-card-subtitle">Weekly active students and contributions</p>
                                </div>
                                <select class="admin-select" aria-label="Timeframe">
                                    <option selected>Last 8 weeks</option>
                                    <option>Last 12 weeks</option>
                                    <option>Last 6 months</option>
                                </select>
                            </div>

                            <!-- Smooth SVG Area Chart matching Page 1 -->
                            <div class="pt-2">
                                <svg viewBox="0 0 680 235" width="100%" height="235" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="activityAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#0284C7" stop-opacity="0.16"/>
                                            <stop offset="100%" stop-color="#0284C7" stop-opacity="0.0"/>
                                        </linearGradient>
                                    </defs>
                                    <!-- Grid Lines & Y-Axis Labels -->
                                    <text x="4" y="22" fill="#94A3B8" font-size="11" font-family="Plus Jakarta Sans">1.5k</text>
                                    <line x1="38" y1="18" x2="665" y2="18" stroke="#F1F5F9" stroke-dasharray="3 3"/>

                                    <text x="4" y="82" fill="#94A3B8" font-size="11" font-family="Plus Jakarta Sans">1k</text>
                                    <line x1="38" y1="78" x2="665" y2="78" stroke="#F1F5F9" stroke-dasharray="3 3"/>

                                    <text x="4" y="142" fill="#94A3B8" font-size="11" font-family="Plus Jakarta Sans">500</text>
                                    <line x1="38" y1="138" x2="665" y2="138" stroke="#F1F5F9" stroke-dasharray="3 3"/>

                                    <text x="4" y="198" fill="#94A3B8" font-size="11" font-family="Plus Jakarta Sans">0</text>
                                    <line x1="38" y1="194" x2="665" y2="194" stroke="#E2E8F0"/>

                                    <!-- Area Fill -->
                                    <path d="M52,175 C95,162 115,146 142,148 C175,150 205,152 232,144 C265,134 285,116 322,120 C355,124 378,125 412,102 C442,82 468,78 502,86 C535,93 560,82 592,66 C618,52 640,56 655,60 L655,194 L52,194 Z" fill="url(#activityAreaGrad)"/>

                                    <!-- Smooth Line Stroke -->
                                    <path d="M52,175 C95,162 115,146 142,148 C175,150 205,152 232,144 C265,134 285,116 322,120 C355,124 378,125 412,102 C442,82 468,78 502,86 C535,93 560,82 592,66 C618,52 640,56 655,60" stroke="#0369A1" stroke-width="2.6" stroke-linecap="round"/>

                                    <!-- X-Axis Labels -->
                                    <text x="40" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">MAR 24</text>
                                    <text x="125" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">MAR 31</text>
                                    <text x="212" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">APR 07</text>
                                    <text x="298" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">APR 14</text>
                                    <text x="385" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">APR 21</text>
                                    <text x="470" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">APR 28</text>
                                    <text x="556" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">MAY 05</text>
                                    <text x="632" y="220" fill="#94A3B8" font-size="10.5" font-weight="600" font-family="Plus Jakarta Sans">MAY 12</text>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Moderation queue</h2>
                                    <p class="admin-card-subtitle">Items requiring attention</p>
                                </div>
                                <a href="approvals.php" class="text-decoration-none fw-bold" style="font-size:0.78rem;color:#0284C7;">View all</a>
                            </div>

                            <div>
                                <a href="approvals.php" class="moderation-queue-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="mod-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                        <div>
                                            <p class="mod-item-title">Resource approvals</p>
                                            <p class="mod-item-sub">3 urgent</p>
                                        </div>
                                    </div>
                                    <span class="mod-item-count">7</span>
                                </a>

                                <a href="discussions.php" class="moderation-queue-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="mod-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                        <div>
                                            <p class="mod-item-title">Reported discussions</p>
                                            <p class="mod-item-sub">4 high priority</p>
                                        </div>
                                    </div>
                                    <span class="mod-item-count">12</span>
                                </a>

                                <a href="groups.php" class="moderation-queue-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="mod-item-icon"><i class="bi bi-people"></i></div>
                                        <div>
                                            <p class="mod-item-title">Flagged groups</p>
                                            <p class="mod-item-sub">1 requires review</p>
                                        </div>
                                    </div>
                                    <span class="mod-item-count">3</span>
                                </a>

                                <a href="users.php" class="moderation-queue-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="mod-item-icon"><i class="bi bi-people"></i></div>
                                        <div>
                                            <p class="mod-item-title">User appeals</p>
                                            <p class="mod-item-sub">Within SLA</p>
                                        </div>
                                    </div>
                                    <span class="mod-item-count">2</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Administrative Activity & Content Distribution (PDF Page 2) -->
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Recent administrative activity</h2>
                                    <p class="admin-card-subtitle">Latest actions across the platform</p>
                                </div>
                            </div>

                            <div>
                                <a href="resources.php" class="admin-activity-row text-decoration-none">
                                    <div class="admin-avatar-circle">AD</div>
                                    <div>
                                        <p class="admin-activity-title">Approved "Database Systems Exam Guide"</p>
                                        <p class="admin-activity-time">2 min ago</p>
                                    </div>
                                </a>

                                <a href="users.php" class="admin-activity-row text-decoration-none">
                                    <div class="admin-avatar-circle">MK</div>
                                    <div>
                                        <p class="admin-activity-title">Suspended account pending review</p>
                                        <p class="admin-activity-time">24 min ago</p>
                                    </div>
                                </a>

                                <a href="subjects.php" class="admin-activity-row text-decoration-none">
                                    <div class="admin-avatar-circle">AD</div>
                                    <div>
                                        <p class="admin-activity-title">Added module CS3104 · Machine Learning</p>
                                        <p class="admin-activity-time">1 h ago</p>
                                    </div>
                                </a>

                                <a href="discussions.php" class="admin-activity-row text-decoration-none">
                                    <div class="admin-avatar-circle">SN</div>
                                    <div>
                                        <p class="admin-activity-title">Resolved discussion report #1048</p>
                                        <p class="admin-activity-time">2 h ago</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Content distribution</h2>
                                    <p class="admin-card-subtitle">Resources by faculty</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-4 pt-2 flex-wrap">
                                <!-- SVG Donut Chart -->
                                <div class="position-relative" style="width:136px;height:136px;flex-shrink:0;">
                                    <svg viewBox="0 0 36 36" width="136" height="136" style="transform: rotate(-90deg);">
                                        <!-- Computing 46% -->
                                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#16A34A" stroke-width="4.5" stroke-dasharray="40.5 88" stroke-dashoffset="0"></circle>
                                        <!-- Business 29% -->
                                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#0369A1" stroke-width="4.5" stroke-dasharray="25.5 88" stroke-dashoffset="-40.5"></circle>
                                        <!-- Engineering 16% -->
                                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#3B82F6" stroke-width="4.5" stroke-dasharray="14.1 88" stroke-dashoffset="-66"></circle>
                                        <!-- Science 9% -->
                                        <circle cx="18" cy="18" r="14" fill="transparent" stroke="#94A3B8" stroke-width="4.5" stroke-dasharray="8 88" stroke-dashoffset="-80.1"></circle>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <div style="font-size:1.15rem;font-weight:800;color:var(--admin-navy);line-height:1.1;">486</div>
                                        <div style="font-size:0.68rem;color:var(--admin-slate);">resources</div>
                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1">
                                        <span class="d-flex align-items-center gap-2" style="font-size:0.82rem;color:var(--admin-slate);">
                                            <span style="width:7px;height:7px;border-radius:2px;background:#0369A1;display:inline-block;"></span> Computing
                                        </span>
                                        <strong style="font-size:0.82rem;color:var(--admin-navy);">46%</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1">
                                        <span class="d-flex align-items-center gap-2" style="font-size:0.82rem;color:var(--admin-slate);">
                                            <span style="width:7px;height:7px;border-radius:2px;background:#16A34A;display:inline-block;"></span> Business
                                        </span>
                                        <strong style="font-size:0.82rem;color:var(--admin-navy);">29%</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1">
                                        <span class="d-flex align-items-center gap-2" style="font-size:0.82rem;color:var(--admin-slate);">
                                            <span style="width:7px;height:7px;border-radius:2px;background:#3B82F6;display:inline-block;"></span> Engineering
                                        </span>
                                        <strong style="font-size:0.82rem;color:var(--admin-navy);">16%</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="d-flex align-items-center gap-2" style="font-size:0.82rem;color:var(--admin-slate);">
                                            <span style="width:7px;height:7px;border-radius:2px;background:#94A3B8;display:inline-block;"></span> Science
                                        </span>
                                        <strong style="font-size:0.82rem;color:var(--admin-navy);">9%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>

<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: REPORTS & ANALYTICS (PDF PAGES 21 & 22)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports &amp; Analytics - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">Reports and analytics</h1>
                        <p class="admin-page-subtitle">Understand usage, content quality, and community growth.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select class="admin-select" aria-label="Reporting period">
                            <option selected>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This academic year</option>
                        </select>
                        <button type="button" class="btn-admin-outline" data-admin-export="nsbm-analytics-report">Export report</button>
                    </div>
                </div>

                <!-- 4 KPI Cards Row (PDF Page 21) -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <a href="users.php" class="admin-card p-3 px-4 h-100 d-flex flex-column justify-content-between text-decoration-none">
                            <div class="admin-kpi-label mb-2">Registered students</div>
                            <div class="d-flex align-items-baseline justify-content-between">
                                <div class="admin-kpi-value mb-0">2,486</div>
                                <span class="status-pill pill-green">+8.4%</span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="users.php" class="admin-card p-3 px-4 h-100 d-flex flex-column justify-content-between text-decoration-none">
                            <div class="admin-kpi-label mb-2">Weekly active users</div>
                            <div class="d-flex align-items-baseline justify-content-between">
                                <div class="admin-kpi-value mb-0">1,204</div>
                                <span class="status-pill pill-green">+5.2%</span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="resources.php" class="admin-card p-3 px-4 h-100 d-flex flex-column justify-content-between text-decoration-none">
                            <div class="admin-kpi-label mb-2">Published resources</div>
                            <div class="d-flex align-items-baseline justify-content-between">
                                <div class="admin-kpi-value mb-0">486</div>
                                <span class="status-pill pill-green">+12.1%</span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="resources.php" class="admin-card p-3 px-4 h-100 d-flex flex-column justify-content-between text-decoration-none">
                            <div class="admin-kpi-label mb-2">Resource downloads</div>
                            <div class="d-flex align-items-baseline justify-content-between">
                                <div class="admin-kpi-value mb-0">8,942</div>
                                <span class="status-pill pill-green">+18.6%</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Engagement by Week & Resources by Faculty (PDF Page 21) -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-7">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Engagement by week</h2>
                                    <p class="admin-card-subtitle">Active students, downloads, and contributions</p>
                                </div>
                            </div>

                            <div class="weekly-bars-container">
                                <?php
                                $weeksData = [
                                    ['W1', 96, 72],
                                    ['W2', 118, 86],
                                    ['W3', 108, 78],
                                    ['W4', 136, 98],
                                    ['W5', 124, 92],
                                    ['W6', 152, 114],
                                    ['W7', 142, 106],
                                    ['W8', 164, 128],
                                ];
                                foreach ($weeksData as $wk):
                                ?>
                                <div class="week-bar-group">
                                    <div class="week-bars-pair">
                                        <div class="bar-active-students" style="height: <?= $wk[1] ?>px;"></div>
                                        <div class="bar-downloads" style="height: <?= $wk[2] ?>px;"></div>
                                    </div>
                                    <span class="week-label"><?= $wk[0] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex align-items-center justify-content-center gap-4 pt-3" style="font-size:0.76rem;color:var(--admin-slate);">
                                <span class="d-flex align-items-center gap-2">
                                    <span style="width:7px;height:7px;border-radius:2px;background:#0369A1;display:inline-block;"></span> Active students
                                </span>
                                <span class="d-flex align-items-center gap-2">
                                    <span style="width:7px;height:7px;border-radius:2px;background:#BAE6FD;display:inline-block;"></span> Downloads
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Resources by faculty</h2>
                                    <p class="admin-card-subtitle">Published content distribution</p>
                                </div>
                            </div>

                            <div class="pt-1">
                                <div class="faculty-progress-row">
                                    <div class="d-flex justify-content-between" style="font-size:0.82rem;">
                                        <span class="text-secondary">Computing</span>
                                        <strong class="text-dark">224</strong>
                                    </div>
                                    <div class="faculty-progress-track">
                                        <div class="faculty-progress-fill" style="width: 58%;"></div>
                                    </div>
                                </div>

                                <div class="faculty-progress-row">
                                    <div class="d-flex justify-content-between" style="font-size:0.82rem;">
                                        <span class="text-secondary">Business</span>
                                        <strong class="text-dark">141</strong>
                                    </div>
                                    <div class="faculty-progress-track">
                                        <div class="faculty-progress-fill" style="width: 37%;"></div>
                                    </div>
                                </div>

                                <div class="faculty-progress-row">
                                    <div class="d-flex justify-content-between" style="font-size:0.82rem;">
                                        <span class="text-secondary">Engineering</span>
                                        <strong class="text-dark">78</strong>
                                    </div>
                                    <div class="faculty-progress-track">
                                        <div class="faculty-progress-fill" style="width: 21%;"></div>
                                    </div>
                                </div>

                                <div class="faculty-progress-row">
                                    <div class="d-flex justify-content-between" style="font-size:0.82rem;">
                                        <span class="text-secondary">Science</span>
                                        <strong class="text-dark">43</strong>
                                    </div>
                                    <div class="faculty-progress-track">
                                        <div class="faculty-progress-fill" style="width: 12%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Moderation Quality & Top Resources (PDF Page 22) -->
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Moderation quality</h2>
                                    <p class="admin-card-subtitle">Resolution and approval performance</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="mod-quality-box">
                                        <div class="fw-bold text-dark mb-1" style="font-size:1.35rem;">94%</div>
                                        <div class="text-secondary" style="font-size:0.76rem;line-height:1.35;">Reports resolved within SLA</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mod-quality-box">
                                        <div class="fw-bold text-dark mb-1" style="font-size:1.35rem;">1.4d</div>
                                        <div class="text-secondary" style="font-size:0.76rem;line-height:1.35;">Average resource review time</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mod-quality-box">
                                        <div class="fw-bold text-dark mb-1" style="font-size:1.35rem;">3.2%</div>
                                        <div class="text-secondary" style="font-size:0.76rem;line-height:1.35;">Submission rejection rate</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="admin-card h-100">
                            <div class="admin-card-header">
                                <div>
                                    <h2 class="admin-card-title">Top resources</h2>
                                    <p class="admin-card-subtitle">By downloads this period</p>
                                </div>
                            </div>

                            <div>
                                <a href="resources.php" class="d-flex align-items-center justify-content-between py-2 border-bottom text-decoration-none" style="border-color:var(--admin-divider) !important;">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-secondary fw-bold" style="font-size:0.78rem;">01</span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.83rem;">Data Structures — Complete Revision Guide</div>
                                            <div class="text-secondary" style="font-size:0.75rem;">Computing</div>
                                        </div>
                                    </div>
                                    <span class="code-badge-text">1284</span>
                                </a>

                                <a href="resources.php" class="d-flex align-items-center justify-content-between py-2 border-bottom text-decoration-none" style="border-color:var(--admin-divider) !important;">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-secondary fw-bold" style="font-size:0.78rem;">02</span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.83rem;">Financial Accounting Formula Book</div>
                                            <div class="text-secondary" style="font-size:0.75rem;">Business</div>
                                        </div>
                                    </div>
                                    <span class="code-badge-text">971</span>
                                </a>

                                <a href="resources.php" class="d-flex align-items-center justify-content-between pt-2 text-decoration-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-secondary fw-bold" style="font-size:0.78rem;">03</span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.83rem;">Engineering Mechanics — First-Year Guide</div>
                                            <div class="text-secondary" style="font-size:0.75rem;">Engineering</div>
                                        </div>
                                    </div>
                                    <span class="code-badge-text">658</span>
                                </a>
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

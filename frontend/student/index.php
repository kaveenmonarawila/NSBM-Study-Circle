<?php
// NSBM STUDY CIRCLE - Student Portal Dashboard
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Dashboard';

// ============================================================================
// LIVE DATABASE QUERIES (phpMyAdmin / MySQL `nsbm_student_circle`)
// Robust Queries with Safe Table Checks for Fresh & Existing Accounts
// ============================================================================

$uploadedCount = 0;
$approvedMonthCount = 0;
$downloadedCount = 0;
$downloadedWeekCount = 0;
$groupsCount = 0;
$managedGroupsCount = 0;
$sessionsCount = 0;
$nextSessionText = 'No upcoming sessions';

$recommendedResources = [];
$upcomingSessions = [];
$recentDiscussions = [];
$myGroups = [];
$recentActivities = [];

if (isset($conn) && $conn && !$conn->connect_error) {
    // 1. Metric: Uploaded Resources (Safely check table first)
    if (safeTableExists($conn, 'resources') && $currentUserId > 0) {
        $uploadedCount = safeCountQuery($conn, 'resources', "uploaded_by = $currentUserId");
        $approvedMonthCount = safeCountQuery($conn, 'resources', "uploaded_by = $currentUserId AND status = 'Approved' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }

    // 2. Metric: Downloaded Materials (Safely check table first)
    if (safeTableExists($conn, 'resource_downloads') && $currentUserId > 0) {
        $downloadedCount = safeCountQuery($conn, 'resource_downloads', "user_id = $currentUserId");
        $downloadedWeekCount = safeCountQuery($conn, 'resource_downloads', "user_id = $currentUserId AND downloaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    }

    // 3. Metric: Study Groups (Safely check table first)
    if (safeTableExists($conn, 'study_group_members') && $currentUserId > 0) {
        $groupsCount = safeCountQuery($conn, 'study_group_members', "user_id = $currentUserId");
    }
    if (safeTableExists($conn, 'study_groups') && $currentUserId > 0) {
        $managedGroupsCount = safeCountQuery($conn, 'study_groups', "creator_id = $currentUserId");
    }

    // 4. Metric: Upcoming Sessions (Safely check table first)
    if (safeTableExists($conn, 'study_sessions')) {
        $sessionsCount = safeCountQuery($conn, 'study_sessions', "session_date >= NOW() AND status = 'Scheduled'");

        try {
            $nextSessRes = @$conn->query("SELECT session_date, title FROM study_sessions WHERE session_date >= NOW() AND status = 'Scheduled' ORDER BY session_date ASC LIMIT 1");
            if ($nextSessRes && $sRow = $nextSessRes->fetch_assoc()) {
                $sDate = strtotime($sRow['session_date']);
                if (date('Y-m-d') === date('Y-m-d', $sDate)) {
                    $nextSessionText = 'Next session today';
                } else {
                    $nextSessionText = 'Next: ' . date('d M, g:i A', $sDate);
                }
                $nextSessRes->free();
            }
        } catch (Throwable $e) {}
    }

    // 5. Recommended Resources (prioritizing user's faculty, then top downloads)
    if (safeTableExists($conn, 'resources')) {
        try {
            $safeFac = $conn->real_escape_string((string)($currentUserFaculty ?? 'Computing'));
            $rSql = "
                SELECT r.*, u.name AS author_name 
                FROM resources r 
                LEFT JOIN users u ON r.uploaded_by = u.id 
                WHERE r.status = 'Approved'
                ORDER BY (r.faculty = '$safeFac') DESC, r.downloads DESC, r.id DESC 
                LIMIT 3
            ";
            $rRes = @$conn->query($rSql);
            if ($rRes) {
                while ($r = $rRes->fetch_assoc()) {
                    $recommendedResources[] = $r;
                }
                $rRes->free();
            }
        } catch (Throwable $e) {}
    }

    // 6. Upcoming Study Sessions
    if (safeTableExists($conn, 'study_sessions')) {
        try {
            $sSql = "
                SELECT s.*, u.name AS host_name 
                FROM study_sessions s 
                LEFT JOIN users u ON s.host_id = u.id 
                WHERE s.session_date >= NOW() AND s.status = 'Scheduled'
                ORDER BY s.session_date ASC 
                LIMIT 3
            ";
            $sRes = @$conn->query($sSql);
            if ($sRes) {
                while ($s = $sRes->fetch_assoc()) {
                    $upcomingSessions[] = $s;
                }
                $sRes->free();
            }
        } catch (Throwable $e) {}
    }

    // 7. Recent Discussions
    if (safeTableExists($conn, 'discussions')) {
        try {
            $hasAnswers = safeTableExists($conn, 'discussion_answers');
            $hasVotes = safeTableExists($conn, 'discussion_votes');

            $answersSubquery = $hasAnswers ? "(SELECT COUNT(*) FROM discussion_answers a WHERE a.discussion_id = d.id)" : "0";
            $votesSubquery = $hasVotes ? "(SELECT COUNT(*) FROM discussion_votes v WHERE v.discussion_id = d.id)" : "0";

            $dSql = "
                SELECT d.*, u.name AS author_name,
                       $answersSubquery AS real_answers_count,
                       $votesSubquery AS real_helpful_count
                FROM discussions d
                LEFT JOIN users u ON d.author_id = u.id
                WHERE d.is_hidden = 0
                ORDER BY d.created_at DESC 
                LIMIT 3
            ";
            $dRes = @$conn->query($dSql);
            if ($dRes) {
                while ($d = $dRes->fetch_assoc()) {
                    $recentDiscussions[] = $d;
                }
                $dRes->free();
            }
        } catch (Throwable $e) {}
    }

    // 8. My Study Groups (Joined or Featured)
    if (safeTableExists($conn, 'study_groups')) {
        try {
            $hasMembersTable = safeTableExists($conn, 'study_group_members');
            $memberCountSub = $hasMembersTable 
                ? "(SELECT COUNT(*) FROM study_group_members m WHERE m.group_id = g.id)" 
                : "g.member_count";

            if ($hasMembersTable && $currentUserId > 0) {
                $gSql = "
                    SELECT g.*, $memberCountSub AS real_members
                    FROM study_groups g 
                    JOIN study_group_members gm ON g.id = gm.group_id 
                    WHERE gm.user_id = $currentUserId 
                    ORDER BY g.created_at DESC 
                    LIMIT 3
                ";
                $gRes = @$conn->query($gSql);
                if ($gRes && $gRes->num_rows > 0) {
                    while ($g = $gRes->fetch_assoc()) {
                        $myGroups[] = $g;
                    }
                    $gRes->free();
                }
            }

            // Fallback to active featured circles if user hasn't joined any yet
            if (empty($myGroups)) {
                $gRes = @$conn->query("
                    SELECT g.*, $memberCountSub AS real_members
                    FROM study_groups g 
                    WHERE g.status = 'Active' 
                    ORDER BY g.is_featured DESC, g.id ASC 
                    LIMIT 3
                ");
                if ($gRes) {
                    while ($g = $gRes->fetch_assoc()) {
                        $myGroups[] = $g;
                    }
                    $gRes->free();
                }
            }
        } catch (Throwable $e) {}
    }

    // 9. Recent Activity for this Student (Guarded against missing tables)
    // Uploads
    if (safeTableExists($conn, 'resources') && $currentUserId > 0) {
        try {
            $actUp = @$conn->query("SELECT title, created_at FROM resources WHERE uploaded_by = $currentUserId ORDER BY created_at DESC LIMIT 2");
            if ($actUp) {
                while ($r = $actUp->fetch_assoc()) {
                    $recentActivities[] = [
                        'type' => 'upload',
                        'title' => 'Resource uploaded',
                        'desc' => $r['title'],
                        'time' => date('d M', strtotime($r['created_at'])),
                        'icon' => 'bi-cloud-check',
                        'color' => 'student-act-green',
                        'link' => 'uploads.php'
                    ];
                }
                $actUp->free();
            }
        } catch (Throwable $e) {}
    }

    // Downloads
    if (safeTableExists($conn, 'resource_downloads') && safeTableExists($conn, 'resources') && $currentUserId > 0) {
        try {
            $actDl = @$conn->query("
                SELECT r.title, rd.downloaded_at 
                FROM resource_downloads rd 
                JOIN resources r ON rd.resource_id = r.id 
                WHERE rd.user_id = $currentUserId 
                ORDER BY rd.downloaded_at DESC 
                LIMIT 2
            ");
            if ($actDl) {
                while ($r = $actDl->fetch_assoc()) {
                    $recentActivities[] = [
                        'type' => 'download',
                        'title' => 'Material downloaded',
                        'desc' => $r['title'],
                        'time' => date('d M', strtotime($r['downloaded_at'])),
                        'icon' => 'bi-download',
                        'color' => 'student-act-blue',
                        'link' => 'downloads.php'
                    ];
                }
                $actDl->free();
            }
        } catch (Throwable $e) {}
    }

    // Recent Logins from optional `login_logs` table
    if (safeTableExists($conn, 'login_logs') && $currentUserId > 0) {
        try {
            $actLog = @$conn->query("SELECT logged_in_at, action_type FROM login_logs WHERE user_id = $currentUserId ORDER BY logged_in_at DESC LIMIT 2");
            if ($actLog) {
                while ($r = $actLog->fetch_assoc()) {
                    $recentActivities[] = [
                        'type' => 'login',
                        'title' => 'Portal ' . htmlspecialchars($r['action_type'] ?? 'Login'),
                        'desc' => 'Logged into NSBM Study Circle with verified credentials.',
                        'time' => date('d M, g:i A', strtotime($r['logged_in_at'])),
                        'icon' => 'bi-shield-check',
                        'color' => 'student-act-indigo',
                        'link' => 'profile.php'
                    ];
                }
                $actLog->free();
            }
        } catch (Throwable $e) {}
    }
}

// Fallback sample activity if newly created user has no recorded actions yet
if (empty($recentActivities)) {
    $recentActivities[] = [
        'type' => 'welcome',
        'title' => 'Welcome to NSBM Study Circle, ' . htmlspecialchars($userFirstName) . '!',
        'desc' => 'Your student workspace is ready. Explore study notes, ask questions, or join peer circles.',
        'time' => 'Just now',
        'icon' => 'bi-stars',
        'color' => 'student-act-green',
        'link' => 'study-materials.php'
    ];
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Portal | NSBM Study Circle</title>
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
            <!-- Hero Banner (Personalized with Live User Name from Database) -->
            <div class="student-hero-banner">
                <div class="student-hero-tag">YOUR LEARNING SPACE</div>
                <h2 class="student-hero-heading">Welcome back, <?= htmlspecialchars($userFirstName) ?>!</h2>
                <p class="student-hero-text">Continue learning, explore new resources, and connect with your study community.</p>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="study-materials.php" class="student-hero-btn-green">
                        Explore Resources
                    </a>
                    <a href="uploads.php" class="student-hero-btn-outline">
                        Upload Material
                    </a>
                </div>

                <!-- Right 3D style badge artwork -->
                <div class="student-hero-badge-art">
                    <div class="student-hero-card-white">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="student-hero-badge-pill">
                        Learn<br>Together.
                    </div>
                </div>
            </div>

            <!-- 4 Stat Metric Cards (Dynamic Live Counts from MySQL) -->
            <div class="student-stats-row">
                <!-- Card 1: Uploaded Resources -->
                <a href="uploads.php" class="text-decoration-none student-stat-card student-stat-blue">
                    <div class="student-stat-icon-box">
                        <i class="bi bi-upload"></i>
                    </div>
                    <div>
                        <div class="student-stat-value"><?= $uploadedCount ?></div>
                        <div class="student-stat-label">Uploaded Resources</div>
                        <p class="student-stat-subtext"><?= $approvedMonthCount > 0 ? "{$approvedMonthCount} approved this month" : ($uploadedCount > 0 ? "Shared materials" : "Share notes with peers") ?></p>
                    </div>
                </a>

                <!-- Card 2: Downloaded Materials -->
                <a href="downloads.php" class="text-decoration-none student-stat-card student-stat-green">
                    <div class="student-stat-icon-box">
                        <i class="bi bi-download"></i>
                    </div>
                    <div>
                        <div class="student-stat-value"><?= $downloadedCount ?></div>
                        <div class="student-stat-label">Downloaded Materials</div>
                        <p class="student-stat-subtext"><?= $downloadedWeekCount > 0 ? "{$downloadedWeekCount} added this week" : ($downloadedCount > 0 ? "Saved in library" : "Explore module library") ?></p>
                    </div>
                </a>

                <!-- Card 3: Study Groups -->
                <a href="groups.php" class="text-decoration-none student-stat-card student-stat-indigo">
                    <div class="student-stat-icon-box">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="student-stat-value"><?= $groupsCount ?></div>
                        <div class="student-stat-label">Study Groups</div>
                        <p class="student-stat-subtext"><?= $managedGroupsCount > 0 ? "{$managedGroupsCount} circle you manage" : ($groupsCount > 0 ? "Active circles" : "Join active peer groups") ?></p>
                    </div>
                </a>

                <!-- Card 4: Upcoming Sessions -->
                <a href="schedule.php" class="text-decoration-none student-stat-card student-stat-amber">
                    <div class="student-stat-icon-box">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <div class="student-stat-value"><?= $sessionsCount ?></div>
                        <div class="student-stat-label">Upcoming Sessions</div>
                        <p class="student-stat-subtext"><?= htmlspecialchars($nextSessionText) ?></p>
                    </div>
                </a>
            </div>

            <!-- Two Column Row 1: Recommended Resources & Upcoming Study Sessions -->
            <div class="student-grid-2col">
                <!-- Recommended Resources (From MySQL `resources` Table) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">FOR YOU</div>
                            <h3 class="student-card-title">Recommended resources</h3>
                            <p class="student-item-subtitle mt-1">Useful material selected from your faculty (<?= htmlspecialchars($currentUserFaculty) ?>) and top downloads.</p>
                        </div>
                        <a href="study-materials.php" class="student-card-link">View library</a>
                    </div>

                    <?php if (!empty($recommendedResources)): ?>
                        <?php foreach ($recommendedResources as $idx => $rItem): 
                            $facLower = strtolower($rItem['faculty'] ?? 'computing');
                            $badgeClass = 'student-badge-computing';
                            $iconClass = 'student-res-icon-blue';
                            if (strpos($facLower, 'bus') !== false) {
                                $badgeClass = 'student-badge-business';
                                $iconClass = 'student-res-icon-green';
                            } elseif (strpos($facLower, 'sci') !== false) {
                                $badgeClass = 'student-badge-science';
                                $iconClass = 'student-res-icon-teal';
                            } elseif (strpos($facLower, 'eng') !== false) {
                                $badgeClass = 'student-badge-engineering';
                                $iconClass = 'student-res-icon-blue';
                            }
                            $isLast = ($idx === count($recommendedResources) - 1);
                        ?>
                            <div class="student-resource-item <?= $isLast ? 'mb-0' : '' ?>">
                                <div class="student-res-icon <?= $iconClass ?>">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="<?= $badgeClass ?>"><?= htmlspecialchars($rItem['faculty'] ?? 'Computing') ?></span>
                                    <h4 class="student-item-title"><?= htmlspecialchars($rItem['title'] ?? 'Study Material') ?></h4>
                                    <p class="student-item-subtitle"><?= htmlspecialchars($rItem['file_type'] ?? 'PDF') ?> · Shared by <?= htmlspecialchars($rItem['author_name'] ?? 'NSBM Contributor') ?></p>
                                </div>
                                <a href="javascript:void(0)" data-resource-id="<?= (int)$rItem['id'] ?>" class="student-btn-sm-view open-resource-modal">View</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                            <p class="mb-0">No resources available yet. <a href="uploads.php" class="text-success fw-bold">Be the first to upload!</a></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Study Sessions (From MySQL `study_sessions` Table) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">NEXT UP</div>
                            <h3 class="student-card-title">Upcoming study sessions</h3>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <a href="schedule.php?view=calendar" class="student-card-link"><i class="bi bi-calendar3 me-1"></i>Calendar</a>
                            <a href="schedule.php" class="student-card-link">Full schedule</a>
                        </div>
                    </div>

                    <?php if (!empty($upcomingSessions)): ?>
                        <?php foreach ($upcomingSessions as $idx => $sItem): 
                            $dt = strtotime($sItem['session_date'] ?? 'now');
                            $dayNum = date('d', $dt);
                            $monthStr = strtoupper(date('M', $dt));
                            $timeStr = date('g:i A', $dt);
                            $isFirst = ($idx === 0);
                            $dateBadgeClass = $isFirst ? 'student-date-dark' : 'student-date-light';
                            $facLower = strtolower($sItem['faculty'] ?? 'computing');
                            $badgeClass = 'student-badge-computing';
                            if (strpos($facLower, 'bus') !== false) $badgeClass = 'student-badge-business';
                            elseif (strpos($facLower, 'sci') !== false) $badgeClass = 'student-badge-science';
                            $isLast = ($idx === count($upcomingSessions) - 1);
                        ?>
                            <div class="student-session-item <?= $isLast ? 'mb-0' : '' ?>">
                                <div class="student-date-badge <?= $dateBadgeClass ?>">
                                    <span class="student-date-num"><?= $dayNum ?></span>
                                    <span class="student-date-month"><?= $monthStr ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="<?= $badgeClass ?>"><?= htmlspecialchars($sItem['faculty'] ?? 'Computing') ?></span>
                                    <h4 class="student-item-title"><?= htmlspecialchars($sItem['title'] ?? 'Study Session') ?></h4>
                                    <p class="student-item-subtitle"><?= $timeStr ?> · <?= htmlspecialchars($sItem['location_details'] ?? 'Online') ?></p>
                                </div>
                                <a href="javascript:void(0)" data-session-id="<?= (int)$sItem['id'] ?>" class="student-card-link open-session-modal">Details</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-event fs-2 d-block mb-2"></i>
                            <p class="mb-0">No upcoming sessions scheduled. <a href="schedule.php" class="text-success fw-bold">Plan a session</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Two Column Row 2: Recent Discussions & My Study Groups -->
            <div class="student-grid-2col">
                <!-- Recent Discussions (From MySQL `discussions` Table) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">COMMUNITY QUESTIONS</div>
                            <h3 class="student-card-title">Recent discussions</h3>
                        </div>
                        <a href="discussions.php" class="student-card-link">Browse all</a>
                    </div>

                    <?php if (!empty($recentDiscussions)): ?>
                        <?php foreach ($recentDiscussions as $idx => $dItem): 
                            $facLower = strtolower($dItem['faculty'] ?? 'computing');
                            $badgeClass = 'student-badge-computing';
                            if (strpos($facLower, 'bus') !== false) $badgeClass = 'student-badge-business';
                            elseif (strpos($facLower, 'sci') !== false) $badgeClass = 'student-badge-science';
                            $helpful = (int)($dItem['real_helpful_count'] ?? $dItem['upvotes'] ?? 0);
                            $answers = (int)($dItem['real_answers_count'] ?? $dItem['answers_count'] ?? 0);
                            $isLast = ($idx === count($recentDiscussions) - 1);
                        ?>
                            <a href="discussions.php?id=<?= (int)$dItem['id'] ?>" class="text-decoration-none student-discussion-item <?= $isLast ? 'mb-0' : '' ?>">
                                <div class="student-helpful-badge">
                                    <span class="student-helpful-count"><?= $helpful ?></span>
                                    <span class="student-helpful-txt">helpful</span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="<?= $badgeClass ?>"><?= htmlspecialchars($dItem['faculty'] ?? 'General') ?></span>
                                    <h4 class="student-item-title"><?= htmlspecialchars($dItem['title'] ?? 'Discussion') ?></h4>
                                    <p class="student-item-subtitle"><?= $answers ?> answers · <?= !empty($dItem['created_at']) ? date('d M', strtotime($dItem['created_at'])) : 'Recent' ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-dots fs-2 d-block mb-2"></i>
                            <p class="mb-0">No active discussions. <a href="discussions.php" class="text-success fw-bold">Ask a question</a></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- My Study Groups (From MySQL `study_groups` Table) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">YOUR CIRCLES</div>
                            <h3 class="student-card-title">My study groups</h3>
                        </div>
                        <a href="groups.php" class="student-card-link">All groups</a>
                    </div>

                    <?php if (!empty($myGroups)): ?>
                        <?php foreach ($myGroups as $idx => $gItem): 
                            $gName = $gItem['name'] ?? 'Study Circle';
                            $gInit = function_exists('getStudentInitials') ? getStudentInitials($gName) : strtoupper(substr($gName, 0, 2));
                            $memberCount = (int)($gItem['real_members'] ?? $gItem['member_count'] ?? 1);
                            $isLast = ($idx === count($myGroups) - 1);
                        ?>
                            <div class="student-group-row <?= $isLast ? 'mb-0' : '' ?>">
                                <div class="student-circle-avatar student-circle-ds"><?= htmlspecialchars($gInit) ?></div>
                                <div class="flex-grow-1">
                                    <h4 class="student-item-title"><?= htmlspecialchars($gName) ?></h4>
                                    <p class="student-item-subtitle"><i class="bi bi-people me-1"></i> <?= $memberCount ?> members</p>
                                </div>
                                <a href="javascript:void(0)" data-group-id="<?= (int)$gItem['id'] ?>" class="student-btn-sm-view open-group-modal">Open</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            <p class="mb-0">You have not joined any study groups yet. <a href="groups.php" class="text-success fw-bold">Explore groups</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Two Column Row 3: Recent Activity & Quick Actions -->
            <div class="student-grid-2col">
                <!-- Recent Activity (Live User Activity from MySQL) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">ACTIVITY</div>
                            <h3 class="student-card-title">Recent activity</h3>
                        </div>
                        <a href="notifications.php" class="student-card-link">Notifications</a>
                    </div>

                    <?php foreach ($recentActivities as $idx => $act): 
                        $isLast = ($idx === count($recentActivities) - 1);
                    ?>
                        <a href="<?= htmlspecialchars($act['link'] ?? 'index.php') ?>" class="text-decoration-none student-activity-item <?= $isLast ? 'mb-0' : '' ?>">
                            <div class="student-act-icon <?= htmlspecialchars($act['color'] ?? 'student-act-green') ?>">
                                <i class="bi <?= htmlspecialchars($act['icon'] ?? 'bi-stars') ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="student-act-title"><?= htmlspecialchars($act['title'] ?? 'Update') ?></h4>
                                <p class="student-act-desc"><?= htmlspecialchars($act['desc'] ?? '') ?></p>
                            </div>
                            <span class="student-act-time"><?= htmlspecialchars($act['time'] ?? 'Recent') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Actions (2x2 Grid) -->
                <div class="student-card mb-0">
                    <div class="student-card-header">
                        <div>
                            <div class="student-card-eyebrow">SHORTCUTS</div>
                            <h3 class="student-card-title">Quick actions</h3>
                            <p class="student-item-subtitle mt-1">Start your next contribution or collaboration.</p>
                        </div>
                    </div>

                    <div class="student-quick-grid">
                        <!-- Action 1 -->
                        <a href="uploads.php" class="student-quick-card">
                            <div class="student-quick-icon text-primary">
                                <i class="bi bi-upload"></i>
                            </div>
                            <div>
                                <h4 class="student-quick-title">Upload Study Material</h4>
                                <p class="student-quick-sub">Share useful notes</p>
                            </div>
                        </a>

                        <!-- Action 2 -->
                        <a href="discussions.php" class="student-quick-card">
                            <div class="student-quick-icon text-success">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div>
                                <h4 class="student-quick-title">Ask a Question</h4>
                                <p class="student-quick-sub">Get academic help</p>
                            </div>
                        </a>

                        <!-- Action 3 -->
                        <a href="groups.php" class="student-quick-card">
                            <div class="student-quick-icon text-indigo" style="color: #6366F1;">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h4 class="student-quick-title">Create a Study Group</h4>
                                <p class="student-quick-sub">Build your circle</p>
                            </div>
                        </a>

                        <!-- Action 4 -->
                        <a href="schedule.php" class="student-quick-card">
                            <div class="student-quick-icon text-warning">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div>
                                <h4 class="student-quick-title">Schedule a Session</h4>
                                <p class="student-quick-sub">Plan focused study</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Synchronize Authenticated Session User with LocalStorage -->
<script>
    window.__NSBM_CURRENT_USER_ID = <?= (int)$currentUserId ?>;
    localStorage.setItem('nsbm_current_user_id', '<?= (int)$currentUserId ?>');
    localStorage.setItem('nsbm_current_user_name', <?= json_encode($currentUserName) ?>);
</script>

<!-- Bootstrap 5 JS Bundle & Student script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/student.js"></script>
</body>
</html>

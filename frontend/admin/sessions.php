<?php
require_once __DIR__ . '/../../backend/config/app_config.php'; // SITE_NAME
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: STUDY SESSION MANAGEMENT (PDF PAGE 20)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Session Management - <?= htmlspecialchars(SITE_NAME) ?></title>
    <script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="icon" type="image/svg+xml" href="../assets/images/study-circle-icon.svg">
    <link rel="alternate icon" type="image/svg+xml" href="../assets/images/study-circle-icon.svg">
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
                        <h1 class="admin-page-title">Study session management</h1>
                        <p class="admin-page-subtitle">Review upcoming sessions, hosts, and capacity.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search sessions...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="review">Review</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Upcoming date</option>
                        </select>
                    </div>
                </div>

                <!-- Study Sessions Table Card -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>SESSION</th>
                                    <th>DATE / TIME</th>
                                    <th>CAPACITY</th>
                                    <th>STATUS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody data-server-rendered="1">
<?php
// Real study sessions from the database (newest first), including ones students just created
$adminSessions = [];
if (isset($conn) && $conn && !$conn->connect_error) {
    $sq = @$conn->query("
        SELECT ss.id, ss.title, ss.faculty, ss.session_date, ss.capacity, ss.enrolled_count, ss.status,
               ss.format_type, ss.created_at, COALESCE(u.name, 'Unknown host') AS host_name
        FROM study_sessions ss
        LEFT JOIN users u ON ss.host_id = u.id
        ORDER BY ss.created_at DESC, ss.id DESC
    ");
    if ($sq) {
        while ($row = $sq->fetch_assoc()) {
            $adminSessions[] = $row;
        }
    }
}
?>
                            <?php if (empty($adminSessions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No study sessions yet. Sessions created by students will appear here.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($adminSessions as $sess):
                                $ts = strtotime($sess['session_date'] ?? '');
                                $enrolled = (int)($sess['enrolled_count'] ?? 0);
                                $cap = max(1, (int)($sess['capacity'] ?? 24));
                                $pill = $enrolled >= $cap ? 'pill-red' : ($enrolled >= $cap * 0.75 ? 'pill-amber' : 'pill-green');
                            ?>
                                <tr data-session-id="<?= (int)$sess['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-calendar-event"></i></div>
                                            <div>
                                                <span class="fw-bold text-dark"><?= htmlspecialchars($sess['title']) ?></span>
                                                <div class="small text-secondary">Host: <?= htmlspecialchars($sess['host_name']) ?> · <?= htmlspecialchars($sess['faculty'] ?? '') ?> · <?= htmlspecialchars($sess['format_type'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $ts ? htmlspecialchars(date('j M Y · g:i A', $ts)) : '—' ?></td>
                                    <td><span class="status-pill <?= $pill ?>"><?= $enrolled ?> / <?= $cap ?></span></td>
                                    <td class="text-secondary"><?= htmlspecialchars($sess['status'] ?? 'Scheduled') ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-table-footer">
                        <span id="tableRecordCount">Showing 1–5 of 48 records</span>
                        <div class="admin-pagination">
                            <a href="#" class="page-btn">Previous</a>
                            <a href="#" class="page-btn active">1</a>
                            <a href="#" class="page-btn">2</a>
                            <a href="#" class="page-btn">3</a>
                            <a href="#" class="page-btn">Next</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
<?php include __DIR__ . '/../includes/site_disclaimer.php'; ?>
</body>
</html>

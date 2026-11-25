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
                        <p class="admin-page-subtitle">Approve new sessions from students, and manage upcoming sessions, hosts, and capacity.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>
<?php
// Sessions students created that are waiting for a decision (status 'Review')
$pendingSessions = [];
if (isset($conn) && $conn && !$conn->connect_error) {
    $pq = @$conn->query("
        SELECT ss.id, ss.title, ss.description, ss.faculty, ss.session_date, ss.format_type, ss.location_details,
               ss.capacity, ss.created_at, COALESCE(u.name, 'Unknown host') AS host_name, u.email AS host_email
        FROM study_sessions ss
        LEFT JOIN users u ON ss.host_id = u.id
        WHERE ss.status = 'Review'
        ORDER BY ss.created_at ASC, ss.id ASC
    ");
    if ($pq) {
        while ($row = $pq->fetch_assoc()) {
            $pendingSessions[] = $row;
        }
    }
}
?>
                <!-- Sessions awaiting approval -->
                <div class="admin-table-card mb-4" id="pendingSessionsCard">
                    <div class="d-flex align-items-center justify-content-between px-3 pt-3">
                        <span class="fw-bold text-dark">Awaiting approval</span>
                        <span class="status-pill pill-amber" id="pendingSessionsPill"><?= count($pendingSessions) ?> pending</span>
                    </div>
                    <p class="small text-secondary px-3 mb-2">New sessions from students are hidden from the public Sessions page until you approve them.</p>
                    <div class="table-responsive">
                        <table class="admin-table admin-pending-table" id="pendingSessionsTable">
                            <tbody>
                            <?php if (empty($pendingSessions)): ?>
                                <tr><td class="text-center text-secondary py-3">No sessions waiting for approval.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pendingSessions as $ps): $pts = strtotime($ps['session_date'] ?? ''); ?>
                                <tr data-pending-session="<?= (int)$ps['id'] ?>">
                                    <td style="max-width: 420px;">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($ps['title']) ?></div>
                                        <div class="small text-secondary">
                                            <?= $pts ? htmlspecialchars(date('D j M Y · g:i A', $pts)) : '—' ?>
                                            · <?= htmlspecialchars($ps['faculty'] ?? '') ?> · <?= htmlspecialchars($ps['location_details'] ?: ($ps['format_type'] ?? 'Online')) ?>
                                            · <?= (int)$ps['capacity'] ?> seats
                                        </div>
                                        <?php if (trim((string)$ps['description']) !== ''): ?>
                                            <div class="small text-secondary mt-1"><?= htmlspecialchars(mb_strimwidth($ps['description'], 0, 200, '…')) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($ps['host_name']) ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($ps['host_email'] ?? '') ?></div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn-admin-reject" data-session-decision="Cancelled" data-id="<?= (int)$ps['id'] ?>">Reject</button>
                                        <button type="button" class="btn-admin-green" data-session-decision="Scheduled" data-id="<?= (int)$ps['id'] ?>">Approve</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                            <option value="review">Awaiting approval</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
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
                                <tr data-session-id="<?= (int)$sess['id'] ?>" data-session-datetime="<?= $ts ? date('Y-m-d\TH:i', $ts) : '' ?>" data-row-status="<?= htmlspecialchars(strtolower($sess['status'] ?? 'scheduled')) ?>">
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
                                    <td class="text-secondary" data-status-cell><?= htmlspecialchars(($sess['status'] ?? 'Scheduled') === 'Review' ? 'Awaiting approval' : ($sess['status'] ?? 'Scheduled')) ?></td>
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
    <script>
    // Approve / reject a session waiting for review (admin_controller.php?action=moderate_session)
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-session-decision][data-id]');
        if (!btn) return;
        const id = parseInt(btn.dataset.id, 10);
        const status = btn.dataset.sessionDecision; // 'Scheduled' (approve) or 'Cancelled' (reject)
        const row = btn.closest('tr');
        const title = row?.querySelector('.fw-bold')?.textContent.trim() || ('#' + id);
        if (status === 'Cancelled' && !confirm(`Reject "${title}"? It will not be published.`)) return;

        row?.querySelectorAll('button').forEach(b => b.disabled = true);
        const toast = (msg, type) => (window.showAdminToast ? window.showAdminToast(msg, type) : alert(msg));
        try {
            const res = await fetch('../../backend/controllers/admin_controller.php?action=moderate_session', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            let data = null;
            try { data = JSON.parse(await res.text()); } catch (err) { /* non-JSON (e.g. host browser check) */ }
            if (!data || !data.success) throw new Error((data && data.message) || 'Request failed');

            row?.remove();
            // Keep the main table in step
            const mainRow = document.querySelector(`tr[data-session-id="${id}"]`);
            if (mainRow) {
                mainRow.dataset.rowStatus = status.toLowerCase();
                const cell = mainRow.querySelector('[data-status-cell]');
                if (cell) cell.textContent = status;
            }
            const left = document.querySelectorAll('[data-pending-session]').length;
            const pill = document.getElementById('pendingSessionsPill');
            if (pill) pill.textContent = `${left} pending`;
            const badge = document.getElementById('sidebarSessionBadge');
            if (badge) { badge.textContent = left; badge.style.display = left ? '' : 'none'; }
            if (!left) {
                const tbody = document.querySelector('#pendingSessionsTable tbody');
                if (tbody) tbody.innerHTML = '<tr><td class="text-center text-secondary py-3">No sessions waiting for approval.</td></tr>';
            }
            toast(status === 'Scheduled' ? `Approved "${title}". It is now on the public Sessions page.` : `Rejected "${title}".`, status === 'Scheduled' ? 'success' : 'danger');
        } catch (err) {
            row?.querySelectorAll('button').forEach(b => b.disabled = false);
            toast(`Could not update "${title}": ${err.message}. Please refresh and try again.`, 'danger');
        }
    });
    </script>
<?php include __DIR__ . '/../includes/site_disclaimer.php'; ?>
</body>
</html>

<?php
require_once __DIR__ . '/../../backend/config/app_config.php'; // SITE_NAME
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: RESOURCE APPROVALS (PDF PAGES 5 & 6)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Approvals - <?= htmlspecialchars(SITE_NAME) ?></title>
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
<?php
require_once __DIR__ . '/../../backend/helpers/link_helper.php';

// Real approval queue: every resource waiting for review (newest first)
$pendingQueue = [];
if (isset($conn) && $conn && !$conn->connect_error) {
    $qRes = @$conn->query("
        SELECT r.id, r.title, r.description, r.faculty, r.module_code, r.academic_year,
               r.external_url, r.file_path, r.created_at, u.name AS author_name, u.email AS author_email
        FROM resources r
        LEFT JOIN users u ON r.uploaded_by = u.id
        WHERE r.status = 'Pending'
        ORDER BY r.created_at DESC, r.id DESC
    ");
    if ($qRes) {
        while ($row = $qRes->fetch_assoc()) {
            $pendingQueue[] = $row;
        }
    }
}
?>
                <!-- Page Header -->
                <div class="admin-page-header">
                    <div>
                        <h1 class="admin-page-title">Resource approvals</h1>
                        <p class="admin-page-subtitle">New study materials are shared as Google Drive / OneDrive links. Open each link, check it, then approve or reject it. Only approved resources are visible to students.</p>
                    </div>
                    <span class="status-pill pill-amber" id="pendingCountPill"><?= count($pendingQueue) ?> pending</span>
                </div>

                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table" id="approvalQueueTable">
                            <thead>
                                <tr>
                                    <th>RESOURCE</th>
                                    <th>SUBMITTED BY</th>
                                    <th>LINK</th>
                                    <th class="text-end">DECISION</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($pendingQueue)): ?>
                                <tr id="approvalQueueEmpty">
                                    <td colspan="4" class="text-center text-secondary py-4">Nothing to review. New link submissions will appear here.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pendingQueue as $item):
                                $link = validateResourceLink($item['external_url'] ?? '');
                            ?>
                                <tr data-resource-row="<?= (int)$item['id'] ?>">
                                    <td style="max-width: 360px;">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['title']) ?></div>
                                        <div class="small text-secondary">
                                            <?= htmlspecialchars($item['faculty']) ?> · <?= htmlspecialchars($item['module_code'] ?? '') ?> · <?= htmlspecialchars($item['academic_year'] ?? '') ?>
                                            · <?= htmlspecialchars(date('d M Y, H:i', strtotime($item['created_at'] ?? 'now'))) ?>
                                        </div>
                                        <?php if (!empty($item['description'])): ?>
                                            <div class="small text-secondary mt-1"><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 180, '…')) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($item['author_name'] ?? 'Unknown') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($item['author_email'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <?php if ($link['ok']): ?>
                                            <a href="<?= htmlspecialchars($link['url'], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" class="small fw-semibold">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Open on <?= htmlspecialchars($link['provider']) ?>
                                            </a>
                                        <?php elseif (!empty($item['file_path'])): ?>
                                            <span class="small text-secondary">Old file stored on server</span>
                                        <?php else: ?>
                                            <span class="small text-danger">No valid link - reject</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn-admin-reject" data-moderate="Rejected" data-id="<?= (int)$item['id'] ?>">Reject</button>
                                        <button type="button" class="btn-admin-green" data-moderate="Approved" data-id="<?= (int)$item['id'] ?>" <?= $link['ok'] || !empty($item['file_path']) ? '' : 'disabled' ?>>Approve</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
    // Approve / reject one resource by id (admin_controller.php?action=moderate_resource)
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-moderate][data-id]');
        if (!btn) return;
        const id = parseInt(btn.dataset.id, 10);
        const status = btn.dataset.moderate;
        const row = btn.closest('tr');
        const title = row?.querySelector('.fw-bold')?.textContent.trim() || ('#' + id);
        if (status === 'Rejected' && !confirm(`Reject "${title}"? Students will not see it.`)) return;

        row?.querySelectorAll('button').forEach(b => b.disabled = true);
        const toast = (msg, type) => (window.showAdminToast ? window.showAdminToast(msg, type) : alert(msg));
        try {
            const res = await fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            let data = null;
            try { data = JSON.parse(await res.text()); } catch (err) { /* non-JSON (e.g. host browser check) */ }
            if (!data || !data.success) throw new Error((data && data.message) || 'Request failed');

            row?.remove();
            const left = document.querySelectorAll('[data-resource-row]').length;
            const pill = document.getElementById('pendingCountPill');
            if (pill) pill.textContent = `${left} pending`;
            if (!left) {
                const tbody = document.querySelector('#approvalQueueTable tbody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Nothing to review. New link submissions will appear here.</td></tr>';
            }
            toast(status === 'Approved' ? `Approved "${title}". It is now visible to students.` : `Rejected "${title}".`, status === 'Approved' ? 'success' : 'danger');
        } catch (err) {
            row?.querySelectorAll('button').forEach(b => b.disabled = false);
            toast(`Could not update "${title}": ${err.message}. Please refresh and try again.`, 'danger');
        }
    });
    </script>
<?php include __DIR__ . '/../includes/site_disclaimer.php'; ?>
</body>
</html>

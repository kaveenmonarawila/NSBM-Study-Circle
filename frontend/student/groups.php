<?php
// NSBM STUDY CIRCLE - Student Portal My Groups
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'My groups';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My groups - Student Dashboard | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">My groups</h2>
                    <p class="student-page-subheading">Stay connected to the groups you lead and participate in.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="student-btn-outline" onclick="window.showStudentToast('Showing all available study groups across faculties...', 'primary');">
                        <i class="bi bi-compass me-1"></i> Discover groups
                    </button>
                    <button type="button" class="student-btn-green" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        Create group
                    </button>
                </div>
            </div>

            <!-- 3 Group Cards Grid -->
            <div class="student-groups-grid">
                <!-- Group 1: Data Structures Circle (Manager) -->
                <div class="student-group-card">
                    <div class="student-group-card-header">
                        <div class="student-circle-avatar student-circle-ds">DS</div>
                        <span class="badge" style="background-color: #ECFDF5; color: #059669; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">Manager</span>
                    </div>
                    <h3 class="student-card-title mb-2" style="font-size: 1.1rem;">Data Structures Circle</h3>
                    <p class="student-item-subtitle mb-3" style="line-height: 1.5;">
                        Collaborative learning, useful resources, and focused academic conversations.
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex align-items-center justify-content-between mb-3 text-muted small" style="font-size: 0.8rem;">
                            <span><i class="bi bi-people me-1"></i> 48 members</span>
                            <span><i class="bi bi-calendar-event me-1"></i> Next: Sat, 6:30 PM</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0)" data-group-id="1" class="student-btn-outline w-50 justify-content-center py-2 open-group-modal" style="font-size: 0.85rem;">
                                Manage group
                            </a>
                            <a href="javascript:void(0)" data-group-id="1" class="student-btn-green w-50 justify-content-center py-2 open-group-modal" style="font-size: 0.85rem;">
                                Open group
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Science Study Circle (Member) -->
                <div class="student-group-card">
                    <div class="student-group-card-header">
                        <div class="student-circle-avatar student-circle-sc">SC</div>
                        <span class="badge" style="background-color: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">Member</span>
                    </div>
                    <h3 class="student-card-title mb-2" style="font-size: 1.1rem;">Science Study Circle</h3>
                    <p class="student-item-subtitle mb-3" style="line-height: 1.5;">
                        Collaborative learning, useful resources, and focused academic conversations.
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex align-items-center justify-content-between mb-3 text-muted small" style="font-size: 0.8rem;">
                            <span><i class="bi bi-people me-1"></i> 27 members</span>
                            <span><i class="bi bi-chat-left-dots me-1"></i> 3 new posts</span>
                        </div>
                        <a href="javascript:void(0)" data-group-id="2" class="student-btn-green w-100 justify-content-center py-2 open-group-modal" style="font-size: 0.85rem;">
                            Open group
                        </a>
                    </div>
                </div>

                <!-- Group 3: Business Minds (Member) -->
                <div class="student-group-card">
                    <div class="student-group-card-header">
                        <div class="student-circle-avatar student-circle-bm">BM</div>
                        <span class="badge" style="background-color: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">Member</span>
                    </div>
                    <h3 class="student-card-title mb-2" style="font-size: 1.1rem;">Business Minds</h3>
                    <p class="student-item-subtitle mb-3" style="line-height: 1.5;">
                        Collaborative learning, useful resources, and focused academic conversations.
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex align-items-center justify-content-between mb-3 text-muted small" style="font-size: 0.8rem;">
                            <span><i class="bi bi-people me-1"></i> 35 members</span>
                            <span><i class="bi bi-calendar-event me-1"></i> Next: Mon, 4:00 PM</span>
                        </div>
                        <a href="javascript:void(0)" data-group-id="3" class="student-btn-green w-100 justify-content-center py-2 open-group-modal" style="font-size: 0.85rem;">
                            Open group
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Create Group -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--student-card-border);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createGroupModalLabel">Create Study Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createGroupForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Group Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Software Architecture Peer Circle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Faculty</label>
                        <select class="form-select">
                            <option>Faculty of Computing</option>
                            <option>Faculty of Business</option>
                            <option>Faculty of Science</option>
                            <option>Faculty of Engineering</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Explain the focus and goals of this circle..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="student-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="student-btn-green">Create Circle</button>
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

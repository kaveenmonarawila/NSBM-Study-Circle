<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: STUDY GROUP MANAGEMENT (PDF PAGES 18 & 19)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Group Management - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">Study group management</h1>
                        <p class="admin-page-subtitle">Monitor group health, ownership, and policy compliance.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Study Group Invitations Card (PDF Page 18) -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header mb-2">
                        <div>
                            <h2 class="admin-card-title">Study group invitations</h2>
                            <p class="admin-card-subtitle">Review invitations sent to students before group access is granted.</p>
                        </div>
                        <span class="status-pill pill-amber" id="pendingInvitesBadge">2 pending</span>
                    </div>

                    <div>
                        <div class="group-invite-row">
                            <div class="d-flex align-items-center gap-3" style="min-width:240px;">
                                <div class="admin-avatar-circle">KF</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.85rem;">Kasun Fernando</div>
                                    <div class="text-secondary" style="font-size:0.76rem;">kasun.f@students.nsbm.ac.lk</div>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="text-secondary" style="font-size:0.74rem;">Invited to</div>
                                <div class="fw-bold text-dark" style="font-size:0.84rem;">Data Structures Circle</div>
                                <div class="text-secondary" style="font-size:0.76rem;">By Sahan Dissanayake · Today, 09:24</div>
                            </div>

                            <div class="d-flex align-items-center gap-2 invite-actions-cell">
                                <span class="status-pill pill-amber me-1">Pending</span>
                                <button type="button" class="btn-admin-outline" style="height:34px;padding:0 14px;" data-invite-action="reject">Reject</button>
                                <button type="button" class="btn-admin-green" style="height:34px;padding:0 14px;" data-invite-action="accept">Accept</button>
                            </div>
                        </div>

                        <div class="group-invite-row">
                            <div class="d-flex align-items-center gap-3" style="min-width:240px;">
                                <div class="admin-avatar-circle">DJ</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.85rem;">Dinara Jayasinghe</div>
                                    <div class="text-secondary" style="font-size:0.76rem;">dinara.j@example.com</div>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="text-secondary" style="font-size:0.74rem;">Invited to</div>
                                <div class="fw-bold text-dark" style="font-size:0.84rem;">Science Study Circle</div>
                                <div class="text-secondary" style="font-size:0.76rem;">By Nethmi Perera · Yesterday, 16:40</div>
                            </div>

                            <div class="d-flex align-items-center gap-2 invite-actions-cell">
                                <span class="status-pill pill-amber me-1">Pending</span>
                                <button type="button" class="btn-admin-outline" style="height:34px;padding:0 14px;" data-invite-action="reject">Reject</button>
                                <button type="button" class="btn-admin-green" style="height:34px;padding:0 14px;" data-invite-action="accept">Accept</button>
                            </div>
                        </div>

                        <div class="group-invite-row">
                            <div class="d-flex align-items-center gap-3" style="min-width:240px;">
                                <div class="admin-avatar-circle">KS</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.85rem;">Kavindu Silva</div>
                                    <div class="text-secondary" style="font-size:0.76rem;">kavindu.silva@example.com</div>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="text-secondary" style="font-size:0.74rem;">Invited to</div>
                                <div class="fw-bold text-dark" style="font-size:0.84rem;">Business Minds</div>
                                <div class="text-secondary" style="font-size:0.76rem;">By Amaya Fernando · 18 May, 11:15</div>
                            </div>

                            <div class="d-flex align-items-center gap-2 invite-actions-cell">
                                <span class="status-pill pill-green">Accepted</span>
                                <span class="text-secondary" style="font-size:0.8rem;margin-left:14px;">Decision recorded</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Study Groups Section (PDF Page 19) -->
                <div class="mb-3 pt-2">
                    <h2 class="fw-bold text-dark mb-1" style="font-size:1.3rem;">All study groups</h2>
                    <p class="admin-page-subtitle">Monitor active groups, membership, and reports.</p>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search groups...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="active">Active</option>
                            <option value="review">Review</option>
                            <option value="flagged">Flagged</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Most members</option>
                        </select>
                    </div>
                </div>

                <!-- Study Groups Table Card -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>GROUP</th>
                                    <th>MEMBERSHIP</th>
                                    <th>STATUS</th>
                                    <th>REPORTS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-people"></i></div>
                                            <span class="fw-bold text-dark">Data Structures Circle</span>
                                        </div>
                                    </td>
                                    <td>48 members</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td>0 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-people"></i></div>
                                            <span class="fw-bold text-dark">Business Minds</span>
                                        </div>
                                    </td>
                                    <td>35 members</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td>0 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-people"></i></div>
                                            <span class="fw-bold text-dark">Science Study Circle</span>
                                        </div>
                                    </td>
                                    <td>27 members</td>
                                    <td><span class="status-pill pill-amber">Review</span></td>
                                    <td>2 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-people"></i></div>
                                            <span class="fw-bold text-dark">Unofficial Exam Answers</span>
                                        </div>
                                    </td>
                                    <td>8 members</td>
                                    <td><span class="status-pill pill-red">Flagged</span></td>
                                    <td>5 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
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
</body>
</html>

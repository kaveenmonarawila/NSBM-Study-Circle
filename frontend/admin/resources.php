<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: RESOURCE MANAGEMENT (PDF PAGE 7)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">Resource management</h1>
                        <p class="admin-page-subtitle">Manage published, rejected, and archived learning resources.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search resources...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="approved">Approved</option>
                            <option value="archived">Archived</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Most downloaded</option>
                            <option>Oldest first</option>
                        </select>
                        <button type="button" class="btn-admin-green" data-admin-export="nsbm-resources-inventory">Export inventory</button>
                    </div>
                </div>

                <!-- Resources Table Card -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>RESOURCE</th>
                                    <th>SUBJECT</th>
                                    <th>STATUS</th>
                                    <th>DOWNLOADS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <span class="fw-bold text-dark">Data Structures Revision Guide</span>
                                        </div>
                                    </td>
                                    <td>Computing</td>
                                    <td><span class="status-pill pill-green">Approved</span></td>
                                    <td>1,284</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage resource"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <span class="fw-bold text-dark">Accounting Core Concepts</span>
                                        </div>
                                    </td>
                                    <td>Business</td>
                                    <td><span class="status-pill pill-green">Approved</span></td>
                                    <td>864</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage resource"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <span class="fw-bold text-dark">Research Methods Toolkit</span>
                                        </div>
                                    </td>
                                    <td>Science</td>
                                    <td><span class="status-pill pill-amber">Archived</span></td>
                                    <td>621</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage resource"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <span class="fw-bold text-dark">Database Quick Notes</span>
                                        </div>
                                    </td>
                                    <td>Computing</td>
                                    <td><span class="status-pill pill-red">Rejected</span></td>
                                    <td>—</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage resource"><i class="bi bi-three-dots"></i></button>
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

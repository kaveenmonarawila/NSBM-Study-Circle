<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: DISCUSSION MODERATION (PDF PAGE 17)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Moderation - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">Discussion moderation</h1>
                        <p class="admin-page-subtitle">Review reported questions and answers for community safety.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search discussions...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="high priority">High priority</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Most reports</option>
                        </select>
                    </div>
                </div>

                <!-- Reported Discussions Table Card -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>REPORTED CONTENT</th>
                                    <th>REASON</th>
                                    <th>PRIORITY</th>
                                    <th>REPORTS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                            <span class="fw-bold text-dark">"Can someone send the final exam answers?"</span>
                                        </div>
                                    </td>
                                    <td>Academic integrity</td>
                                    <td><span class="status-pill pill-red">High priority</span></td>
                                    <td>4 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                            <span class="fw-bold text-dark">Off-topic promotional answer</span>
                                        </div>
                                    </td>
                                    <td>Spam</td>
                                    <td><span class="status-pill pill-amber">Medium</span></td>
                                    <td>3 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                            <span class="fw-bold text-dark">Unconstructive reply in accounting discussion</span>
                                        </div>
                                    </td>
                                    <td>Conduct</td>
                                    <td><span class="status-pill pill-amber">Medium</span></td>
                                    <td>2 reports</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                            <span class="fw-bold text-dark">Duplicate database question</span>
                                        </div>
                                    </td>
                                    <td>Duplicate</td>
                                    <td><span class="status-pill pill-amber">Low</span></td>
                                    <td>1 report</td>
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

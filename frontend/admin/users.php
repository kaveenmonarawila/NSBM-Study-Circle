<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: USER MANAGEMENT (PDF PAGES 3 & 4)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">User management</h1>
                        <p class="admin-page-subtitle">Search, review, and manage student access.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search by name, email, or student ID...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Oldest first</option>
                            <option>Most contributions</option>
                        </select>
                        <button type="button" class="btn-admin-green" data-admin-export="nsbm-users">Export users</button>
                    </div>
                </div>

                <!-- Users Table Card -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>USER</th>
                                    <th>FACULTY</th>
                                    <th>STATUS</th>
                                    <th>LAST ACTIVE</th>
                                    <th>CONTRIBUTIONS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="admin-avatar-circle">NP</div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.85rem;">Nethmi Perera</div>
                                                <div class="text-secondary" style="font-size:0.76rem;">nethmi.perera@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Computing</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td>19 May 2025</td>
                                    <td>12</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="admin-avatar-circle">KS</div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.85rem;">Kavindu Silva</div>
                                                <div class="text-secondary" style="font-size:0.76rem;">kavindu.silva@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Business</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td>19 May 2025</td>
                                    <td>19</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="admin-avatar-circle">DJ</div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.85rem;">Dinara Jayasinghe</div>
                                                <div class="text-secondary" style="font-size:0.76rem;">dinara.j@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Science</td>
                                    <td><span class="status-pill pill-amber">Pending</span></td>
                                    <td>18 May 2025</td>
                                    <td>26</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="admin-avatar-circle">RM</div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.85rem;">Ravindu Mendis</div>
                                                <div class="text-secondary" style="font-size:0.76rem;">ravindu.m@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Engineering</td>
                                    <td><span class="status-pill pill-red">Suspended</span></td>
                                    <td>16 May 2025</td>
                                    <td>33</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="admin-avatar-circle">AF</div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.85rem;">Amaya Fernando</div>
                                                <div class="text-secondary" style="font-size:0.76rem;">amaya.f@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Computing</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td>15 May 2025</td>
                                    <td>40</td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer (PDF Page 4) -->
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

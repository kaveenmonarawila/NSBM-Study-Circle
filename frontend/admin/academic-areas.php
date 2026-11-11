<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: ACADEMIC AREA MANAGEMENT (PDF PAGES 8 - 12)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Area Management - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">Academic area management</h1>
                        <p class="admin-page-subtitle">Manage Computing, Business, Engineering, and Science.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Summary Stats & Add Button Row (PDF Page 8) -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                    <div class="admin-mini-stat-row">
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num" id="statTotalAreas">4</div>
                            <div class="admin-mini-stat-label">Academic areas</div>
                        </div>
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num">4</div>
                            <div class="admin-mini-stat-label">Faculties<br>represented</div>
                        </div>
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num">24</div>
                            <div class="admin-mini-stat-label">Subjects mapped</div>
                        </div>
                    </div>

                    <button type="button" class="btn-admin-green" data-bs-toggle="modal" data-bs-target="#addAcademicAreaModal">
                        Add academic area
                    </button>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search academic areas...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="active">Active</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Alphabetical</option>
                        </select>
                    </div>
                </div>

                <!-- Academic Areas Table Card (PDF Pages 8 & 9) -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>CODE</th>
                                    <th>ACADEMIC AREA</th>
                                    <th>SUBJECTS</th>
                                    <th>LEVEL</th>
                                    <th>STATUS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="code-badge-text">COM</span></td>
                                    <td>Computing</td>
                                    <td>6 first-year subjects</td>
                                    <td>Year 1</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="code-badge-text">BUS</span></td>
                                    <td>Business</td>
                                    <td>6 first-year subjects</td>
                                    <td>Year 1</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="code-badge-text">ENG</span></td>
                                    <td>Engineering</td>
                                    <td>6 first-year subjects</td>
                                    <td>Year 1</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="code-badge-text">SCI</span></td>
                                    <td>Science</td>
                                    <td>6 first-year subjects</td>
                                    <td>Year 1</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-table-footer">
                        <span id="tableRecordCount">Showing all 4 records</span>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Academic Area Modal (Exact Match to PDF Pages 10 & 12) -->
    <div class="modal fade" id="addAcademicAreaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content admin-modal-content">
                <form id="addAcademicAreaForm">
                    <div class="admin-modal-body">
                        <h5 class="admin-modal-title">Add academic area</h5>
                        <p class="admin-modal-subtitle">Maintain the NSBM Year 1 academic taxonomy used across the platform.</p>

                        <div class="mb-3">
                            <label class="admin-form-label" for="areaCodeInput">Area code</label>
                            <input type="text" id="areaCodeInput" class="admin-form-control" placeholder="e.g. SCI" required>
                        </div>

                        <div class="mb-3">
                            <label class="admin-form-label" for="areaNameInput">Academic area name</label>
                            <input type="text" id="areaNameInput" class="admin-form-control" placeholder="e.g. Science" required>
                        </div>

                        <div>
                            <label class="admin-form-label" for="areaDescInput">Description</label>
                            <textarea id="areaDescInput" class="admin-form-control" rows="3" placeholder="Short description..."></textarea>
                        </div>
                    </div>

                    <div class="admin-modal-footer-box">
                        <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-admin-green">Save area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>

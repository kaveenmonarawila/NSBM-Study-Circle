<?php
// NSBM STUDY CIRCLE - Student Portal My Downloads
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'My downloads';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My downloads - Student Dashboard | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">My downloads</h2>
                    <p class="student-page-subheading">Quickly return to resources you have downloaded.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="student-btn-outline" id="clearDownloadsBtn">
                        <i class="bi bi-clock-history me-1"></i> Clear history
                    </button>
                    <a href="study-materials.php" class="student-btn-green">
                        Explore resources
                    </a>
                </div>
            </div>

            <!-- Table Container -->
            <div class="student-table-container">
                <!-- Filters Bar -->
                <div class="student-table-filters">
                    <div class="student-search-box" style="width: 320px;">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search downloads..." aria-label="Search downloads">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" style="border-radius: 8px; width: 140px; font-size: 0.85rem;">
                            <option>All statuses</option>
                            <option>Downloaded</option>
                            <option>Archived</option>
                        </select>
                        <select class="form-select form-select-sm" style="border-radius: 8px; width: 140px; font-size: 0.85rem;">
                            <option>Newest first</option>
                            <option>Oldest first</option>
                        </select>
                    </div>
                </div>

                <!-- Downloads Table -->
                <div class="table-responsive">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">RESOURCE</th>
                                <th>SUBJECT</th>
                                <th>DOWNLOADED</th>
                                <th>FORMAT</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-res-icon student-res-icon-green">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-resource-id="2" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                Financial Accounting: Core Concepts
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="student-badge-business">Business</span></td>
                                <td class="text-muted">Today</td>
                                <td class="fw-semibold">PDF</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 me-2 download-material-btn" data-resource-id="2" data-format="PDF" style="border-radius: 6px; font-size: 0.78rem;"><i class="bi bi-download me-1"></i>PDF</button><a href="javascript:void(0)" data-resource-id="2" class="student-btn-sm-view me-2 text-decoration-none open-resource-modal">View</a>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-download-row"><i class="bi bi-trash me-1"></i>Remove</button>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-res-icon student-res-icon-teal">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-resource-id="3" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                Scientific Communication Toolkit
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="student-badge-science">Science</span></td>
                                <td class="text-muted">16 May 2025</td>
                                <td class="fw-semibold">DOCX</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 me-2 download-material-btn" data-resource-id="3" data-format="DOCX" style="border-radius: 6px; font-size: 0.78rem;"><i class="bi bi-download me-1"></i>DOCX</button><a href="javascript:void(0)" data-resource-id="3" class="student-btn-sm-view me-2 text-decoration-none open-resource-modal">View</a>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-download-row"><i class="bi bi-trash me-1"></i>Remove</button>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-res-icon student-res-icon-blue">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-resource-id="1" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                Data Structures — Complete Revision Guide
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="student-badge-computing">Computing</span></td>
                                <td class="text-muted">12 May 2025</td>
                                <td class="fw-semibold">PDF</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 me-2 download-material-btn" data-resource-id="1" data-format="PDF" style="border-radius: 6px; font-size: 0.78rem;"><i class="bi bi-download me-1"></i>PDF</button><a href="javascript:void(0)" data-resource-id="1" class="student-btn-sm-view me-2 text-decoration-none open-resource-modal">View</a>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-download-row"><i class="bi bi-trash me-1"></i>Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="d-flex align-items-center justify-content-between p-3 border-top text-muted small">
                    <span>Showing 3 of 3 items</span>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary disabled py-1 px-2" style="font-size: 0.78rem;">Previous</button>
                        <span class="badge bg-primary px-2 py-1" style="font-size: 0.78rem;">1</span>
                        <button class="btn btn-sm btn-outline-secondary disabled py-1 px-2" style="font-size: 0.78rem;">Next</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/student.js"></script>
</body>
</html>

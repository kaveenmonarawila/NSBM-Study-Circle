<?php
// NSBM STUDY CIRCLE - Student Portal My Uploads
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'My uploads';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My uploads - Student Portal | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">My uploads</h2>
                    <p class="student-page-subheading">Review, manage, and track the materials you have shared.</p>
                </div>
                <div>
                    <button type="button" class="student-btn-green" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                        <i class="bi bi-cloud-arrow-up"></i> Upload new
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="student-table-container">
                <!-- Filters Bar -->
                <div class="student-table-filters">
                    <div class="student-search-box" style="width: 320px;">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search uploads..." aria-label="Search uploads">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" style="border-radius: 8px; width: 140px; font-size: 0.85rem;">
                            <option>All statuses</option>
                            <option>Approved</option>
                            <option>Pending</option>
                            <option>Rejected</option>
                        </select>
                        <select class="form-select form-select-sm" style="border-radius: 8px; width: 140px; font-size: 0.85rem;">
                            <option>Newest first</option>
                            <option>Oldest first</option>
                            <option>Most downloads</option>
                        </select>
                    </div>
                </div>

                <!-- Uploads Table -->
                <div class="table-responsive">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">RESOURCE</th>
                                <th>UPLOADED</th>
                                <th>STATUS</th>
                                <th>DOWNLOADS</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
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
                                <td class="text-muted">18 May 2025</td>
                                <td>
                                    <span class="badge" style="background-color: #ECFDF5; color: #059669; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                        Approved
                                    </span>
                                </td>
                                <td class="fw-semibold">1,284</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none me-2 small edit-upload-btn" data-title="Data Structures — Complete Revision Guide"><i class="bi bi-pencil me-1"></i>Edit</button>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-upload-row"><i class="bi bi-trash me-1"></i>Remove</button>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-res-icon student-res-icon-blue">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-resource-id="2" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                Database Normalization Quick Notes
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">12 May 2025</td>
                                <td>
                                    <span class="badge" style="background-color: #FEF3C7; color: #D97706; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                        Pending
                                    </span>
                                </td>
                                <td class="text-muted">—</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none me-2 small edit-upload-btn" data-title="Database Normalization Quick Notes"><i class="bi bi-pencil me-1"></i>Edit</button>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-upload-row"><i class="bi bi-trash me-1"></i>Remove</button>
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
                                            <a href="javascript:void(0)" data-resource-id="3" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                Algorithm Complexity Cheat Sheet
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">03 May 2025</td>
                                <td>
                                    <span class="badge" style="background-color: #ECFDF5; color: #059669; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                        Approved
                                    </span>
                                </td>
                                <td class="fw-semibold">418</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none me-2 small edit-upload-btn" data-title="Algorithm Complexity Cheat Sheet"><i class="bi bi-pencil me-1"></i>Edit</button>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small remove-upload-row"><i class="bi bi-trash me-1"></i>Remove</button>
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

<!-- Modal: Upload Material -->
<div class="modal fade" id="uploadMaterialModal" tabindex="-1" aria-labelledby="uploadMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--student-card-border);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="uploadMaterialModalLabel">Upload Study Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadMaterialForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title / Document Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Distributed Systems Revision Notes" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Faculty</label>
                            <select class="form-select">
                                <option>Faculty of Computing</option>
                                <option>Faculty of Business</option>
                                <option>Faculty of Science</option>
                                <option>Faculty of Engineering</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Academic Year</label>
                            <select class="form-select">
                                <option>Year 1</option>
                                <option>Year 2</option>
                                <option>Year 3</option>
                                <option>Year 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select File (PDF, DOCX, PPTX - Max 10MB)</label>
                        <input type="file" class="form-control" accept=".pdf,.docx,.pptx" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description / Topics Covered</label>
                        <textarea class="form-control" rows="3" placeholder="Briefly describe what students will learn from this material..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="student-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="student-btn-green">Submit for Review</button>
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

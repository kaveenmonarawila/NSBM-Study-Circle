<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE: FIRST-YEAR SUBJECT MANAGEMENT (PDF PAGES 13 - 16)
// ============================================================================
include_once __DIR__ . '/includes/auth_check.php';

$subjectsList = [
    ['COM101', 'Introduction to Computer Science', 'Computing'],
    ['COM102', 'Programming Fundamentals', 'Computing'],
    ['COM103', 'Mathematics for Computing', 'Computing'],
    ['COM104', 'Computer Systems', 'Computing'],
    ['COM105', 'Web Application Development', 'Computing'],
    ['COM106', 'Database Management Systems', 'Computing'],
    ['BUS101', 'Principles of Management', 'Business'],
    ['BUS102', 'Financial Accounting', 'Business'],
    ['BUS103', 'Business Economics', 'Business'],
    ['BUS104', 'Business Mathematics', 'Business'],
    ['BUS105', 'Marketing Management', 'Business'],
    ['BUS106', 'Business Communication', 'Business'],
    ['ENG101', 'Engineering Mathematics I', 'Engineering'],
    ['ENG102', 'Engineering Mechanics', 'Engineering'],
    ['ENG103', 'Engineering Drawing & CAD', 'Engineering'],
    ['ENG104', 'Applied Physics for Engineers', 'Engineering'],
    ['ENG105', 'Electrical Fundamentals', 'Engineering'],
    ['ENG106', 'Programming for Engineers', 'Engineering'],
    ['SCI101', 'Fundamentals of Biology', 'Science'],
    ['SCI102', 'General Chemistry', 'Science'],
    ['SCI103', 'Physics for Science', 'Science'],
    ['SCI104', 'Mathematics for Science', 'Science'],
    ['SCI105', 'Laboratory Techniques', 'Science'],
    ['SCI106', 'Scientific Communication', 'Science'],
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First-Year Subject Management - NSBM Study Circle</title>
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
                        <h1 class="admin-page-title">First-year subject management</h1>
                        <p class="admin-page-subtitle">Maintain the Year 1 subjects assigned to each academic area.</p>
                    </div>
                    <span class="admin-last-updated">Last updated just now</span>
                </div>

                <!-- Summary Stats & Add Button Row (PDF Page 13) -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                    <div class="admin-mini-stat-row">
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num" id="statTotalSubjects">24</div>
                            <div class="admin-mini-stat-label">Year 1 subjects</div>
                        </div>
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num">4</div>
                            <div class="admin-mini-stat-label">Faculties<br>represented</div>
                        </div>
                        <div class="admin-mini-stat-card">
                            <div class="admin-mini-stat-num">1</div>
                            <div class="admin-mini-stat-label">Academic year</div>
                        </div>
                    </div>

                    <button type="button" class="btn-admin-green" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        Add subject
                    </button>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="admin-toolbar-card">
                    <div class="admin-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="text" data-admin-table-search placeholder="Search first-year subjects...">
                    </div>
                    <div class="admin-toolbar-controls">
                        <select class="admin-select" data-admin-status-filter aria-label="Filter by status">
                            <option value="all" selected>All statuses</option>
                            <option value="active">Active</option>
                        </select>
                        <select class="admin-select" aria-label="Sort order">
                            <option selected>Newest first</option>
                            <option>Subject code</option>
                        </select>
                    </div>
                </div>

                <!-- First-Year Subjects Table Card (PDF Pages 13, 14, 15) -->
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>CODE</th>
                                    <th>FIRST-YEAR SUBJECT</th>
                                    <th>AREA</th>
                                    <th>LEVEL</th>
                                    <th>STATUS</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectsList as $subj): ?>
                                <tr>
                                    <td><span class="code-badge-text"><?= htmlspecialchars($subj[0]) ?></span></td>
                                    <td><?= htmlspecialchars($subj[1]) ?></td>
                                    <td><?= htmlspecialchars($subj[2]) ?></td>
                                    <td>Year 1</td>
                                    <td><span class="status-pill pill-green">Active</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-table-footer">
                        <span id="tableRecordCount">Showing all 24 records</span>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add First-Year Subject Modal (Exact Match to PDF Page 16) -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content admin-modal-content">
                <form id="addSubjectForm">
                    <div class="admin-modal-body">
                        <h5 class="admin-modal-title">Add first-year subject</h5>
                        <p class="admin-modal-subtitle">Maintain the NSBM Year 1 academic taxonomy used across the platform.</p>

                        <div class="mb-3">
                            <label class="admin-form-label" for="subjectCodeInput">Subject code</label>
                            <input type="text" id="subjectCodeInput" class="admin-form-control" placeholder="e.g. COM101" required>
                        </div>

                        <div class="mb-3">
                            <label class="admin-form-label" for="subjectNameInput">Subject name</label>
                            <input type="text" id="subjectNameInput" class="admin-form-control" placeholder="e.g. Programming Fundamentals" required>
                        </div>

                        <div class="mb-3">
                            <label class="admin-form-label" for="subjectAreaSelect">Academic area</label>
                            <select id="subjectAreaSelect" class="admin-select w-100" style="height:40px;background-color:var(--admin-input-bg);">
                                <option value="Computing" selected>Computing</option>
                                <option value="Business">Business</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Science">Science</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="admin-form-label" for="subjectYearSelect">Academic year</label>
                            <select id="subjectYearSelect" class="admin-select w-100" style="height:40px;background-color:var(--admin-input-bg);">
                                <option value="Year 1" selected>Year 1</option>
                            </select>
                        </div>

                        <div>
                            <label class="admin-form-label" for="subjectDescInput">Description</label>
                            <textarea id="subjectDescInput" class="admin-form-control" rows="2" placeholder="Short description..."></textarea>
                        </div>
                    </div>

                    <div class="admin-modal-footer-box">
                        <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-admin-green">Save subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>

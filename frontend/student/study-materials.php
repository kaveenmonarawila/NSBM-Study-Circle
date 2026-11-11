<?php
// NSBM STUDY CIRCLE - Student Portal Study Materials
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Study Materials';

// Optional database integration if available
$db_path = __DIR__ . '/../../backend/config/db_config.php';
$db_connected = false;
$db_resources = [];

if (file_exists($db_path)) {
    @include_once $db_path;
    if (isset($conn) && $conn && !$conn->connect_error) {
        $db_connected = true;
        $res = $conn->query("SELECT * FROM resources WHERE status = 'Approved' ORDER BY downloads DESC LIMIT 20");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $db_resources[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - Student Portal | NSBM Study Circle</title>
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
    <style>
        .material-card {
            background: var(--student-card-bg);
            border: 1px solid var(--student-card-border);
            border-radius: 14px;
            padding: 22px;
            box-shadow: var(--student-card-shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            transition: all 0.2s ease;
        }
        .material-card:hover {
            transform: translateY(-2px);
            border-color: #CBD5E1;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }
        .format-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }
        .format-pdf { background: #FEE2E2; color: #DC2626; }
        .format-docx { background: #DBEAFE; color: #2563EB; }
        .format-pptx { background: #FFEDD5; color: #EA580C; }

        .faculty-filter-pill {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--student-card-border);
            background: var(--student-card-bg);
            color: var(--navy-title);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }
        .faculty-filter-pill:hover, .faculty-filter-pill.active {
            background: #0B1B2B;
            color: #FFFFFF;
            border-color: #0B1B2B;
        }
        [data-bs-theme="dark"] .faculty-filter-pill:hover,
        [data-bs-theme="dark"] .faculty-filter-pill.active {
            background: var(--nsbm-green);
            color: #0B1B2B;
            border-color: var(--nsbm-green);
        }
        .uploader-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0F1E36;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
    </style>
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
                    <h2 class="student-page-heading">Study Materials</h2>
                    <p class="student-page-subheading">Access curated lecture notes, exam revision packs, and student-shared guides across all faculties.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="student-btn-outline" onclick="window.location.reload();">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Library
                    </button>
                    <button type="button" class="student-btn-green" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload Material
                    </button>
                </div>
            </div>

            <!-- Faculty Quick Filter Pills -->
            <div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-2" style="white-space: nowrap;">
                <button type="button" class="faculty-filter-pill active" data-faculty="all">
                    <i class="bi bi-grid-fill"></i> All Subjects
                </button>
                <button type="button" class="faculty-filter-pill" data-faculty="Computing">
                    <i class="bi bi-code-slash text-primary"></i> Computing
                </button>
                <button type="button" class="faculty-filter-pill" data-faculty="Business">
                    <i class="bi bi-briefcase text-success"></i> Business
                </button>
                <button type="button" class="faculty-filter-pill" data-faculty="Engineering">
                    <i class="bi bi-gear-wide-connected text-warning"></i> Engineering
                </button>
                <button type="button" class="faculty-filter-pill" data-faculty="Science">
                    <i class="bi bi-radioactive text-info"></i> Science
                </button>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="student-card mb-4 p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-lg-5 col-md-12">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="materialsSearchInput" class="form-control ps-5" placeholder="Search by title, topic, or keyword..." style="border-radius: 10px; font-size: 0.88rem;">
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-4 col-6">
                        <select id="formatFilter" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.85rem; height: 38px;">
                            <option value="all">All formats</option>
                            <option value="PDF">PDF</option>
                            <option value="DOCX">DOCX</option>
                            <option value="PPTX">PPTX</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-sm-4 col-6">
                        <select id="yearFilter" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.85rem; height: 38px;">
                            <option value="all">Any year</option>
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Year 3">Year 3</option>
                            <option value="Year 4">Year 4</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-sm-4 col-12">
                        <select id="sortFilter" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.85rem; height: 38px;">
                            <option value="most_downloaded">Most downloaded</option>
                            <option value="highest_rated">Highest rated</option>
                            <option value="newest">Newest first</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Materials Grid -->
            <div class="row g-4" id="materialsGrid">
                <!-- Material 1: Data Structures Complete Revision Guide -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Computing" data-format="PDF" data-year="Year 2">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark-fill fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-computing">Computing</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Data Structures — Complete Revision Guide
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Clear summaries, worked examples, and exam-focused notes covering the full module syllabus.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar">NP</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Nethmi Perera</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.9</span> · <i class="bi bi-download"></i> 1.2k
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="1" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="1" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 2: Financial Accounting Core Concepts -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Business" data-format="PDF" data-year="Year 1">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-business">Business</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Financial Accounting: Core Concepts
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                A concise lecture companion with key formulas, journal entries, and practice exam questions.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#047857;">KS</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Kavindu Silva</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.8</span> · <i class="bi bi-download"></i> 864
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="2" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="2" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 3: Engineering Mechanics First-Year Guide -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Engineering" data-format="PDF" data-year="Year 1">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark fs-5"></i>
                                </button>
                            </div>
                            <span class="badge" style="background-color: #F5F3FF; color: #7C3AED; font-weight: 700; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px;">Engineering</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Engineering Mechanics — First-Year Guide
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Core mechanics principles, force diagrams, worked examples, and mid-semester revision problems.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#6D28D9;">RM</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Ravindu Mendis</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.7</span> · <i class="bi bi-download"></i> 706
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="3" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="3" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 4: Scientific Communication Toolkit -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Science" data-format="DOCX" data-year="Year 1">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-docx"><i class="bi bi-filetype-docx me-1"></i>DOCX</span>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark-fill fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-science">Science</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Scientific Communication Toolkit
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Practical frameworks for laboratory records, scientific reports, citations, and research presentations.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#0E7490;">DJ</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Dinara Jayasinghe</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.7</span> · <i class="bi bi-download"></i> 621
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="4" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="4" data-format="DOCX" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-word me-1"></i>Download DOCX
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 5: Database Systems Exam Companion -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Computing" data-format="PDF" data-year="Year 2">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-computing">Computing</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Database Systems Exam Companion
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Normalization, SQL queries, transaction isolation, ER-diagrams, and worked past-paper walkthroughs.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#1E40AF;">AF</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Amaya Fernando</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.8</span> · <i class="bi bi-download"></i> 942
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="5" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="5" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 6: Business Statistics Formula Book -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Business" data-format="PDF" data-year="Year 2">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-business">Business</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Business Statistics Formula Book
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                A clean handbook reference for probability, linear regression, variance, and hypothesis testing.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#15803D;">RDS</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Ruvin De Silva</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.6</span> · <i class="bi bi-download"></i> 518
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="6" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="6" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 7: Laboratory Skills Handbook -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Science" data-format="PPTX" data-year="Year 1">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pptx"><i class="bi bi-filetype-pptx me-1"></i>PPTX</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-science">Science</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Laboratory Skills Handbook
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Safe laboratory practice, measurement calibration, titration observation records, and report guidelines.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar" style="background:#0369A1;">SD</div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);">Shenali Dias</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.7</span> · <i class="bi bi-download"></i> 407
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="7" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="7" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material 8: Object-Oriented Programming with Java -->
                <div class="col-lg-4 col-md-6 material-item" data-faculty="Computing" data-format="PDF" data-year="Year 2">
                    <div class="material-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="format-badge format-pdf"><i class="bi bi-filetype-pdf me-1"></i>PDF</span>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Save to bookmarks">
                                    <i class="bi bi-bookmark-fill fs-5"></i>
                                </button>
                            </div>
                            <span class="student-badge-computing">Computing</span>
                            <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                                Object-Oriented Programming with Java
                            </h4>
                            <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                                Polymorphism, inheritance, exception handling, design patterns, and past lab exam solutions.
                            </p>
                        </div>
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="uploader-avatar"><?= htmlspecialchars($currentUserInitials) ?></div>
                                <div class="small">
                                    <div class="text-muted" style="font-size: 0.72rem;">Shared by</div>
                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--navy-title);"><?= htmlspecialchars($currentUserName) ?> (You)</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    <span class="text-warning fw-bold">★ 4.9</span> · <i class="bi bi-download"></i> 1.5k
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" data-resource-id="8" class="student-btn-outline py-1 px-2 open-resource-modal" style="font-size: 0.82rem;">
                                        View
                                    </a>
                                    <button type="button" class="student-btn-green py-1 px-2 download-material-btn" data-resource-id="8" data-format="PDF" style="font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Search Results State (hidden by default) -->
            <div id="noResultsAlert" class="student-card text-center py-5 d-none">
                <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                <h4 class="fw-bold mb-1">No matching study materials found</h4>
                <p class="text-muted small mb-3">Try adjusting your keyword, faculty filter, or format selection.</p>
                <button type="button" class="student-btn-outline" id="resetFiltersBtn">Reset All Filters</button>
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
                        <label class="form-label small fw-bold">Select File (PDF, DOCX, PPTX - Max 50MB)</label>
                        <input type="file" class="form-control" required>
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
<script>
    // Live client-side interactive filtering
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('materialsSearchInput');
        const formatFilter = document.getElementById('formatFilter');
        const yearFilter = document.getElementById('yearFilter');
        const facultyPills = document.querySelectorAll('.faculty-filter-pill');
        const materialItems = document.querySelectorAll('.material-item');
        const noResults = document.getElementById('noResultsAlert');
        const resetBtn = document.getElementById('resetFiltersBtn');

        let currentFaculty = 'all';

        function filterMaterials() {
            const query = searchInput.value.toLowerCase().trim();
            const format = formatFilter.value;
            const year = yearFilter.value;
            let visibleCount = 0;

            materialItems.forEach(item => {
                const itemFaculty = item.getAttribute('data-faculty') || '';
                const itemFormat = item.getAttribute('data-format') || '';
                const itemYear = item.getAttribute('data-year') || '';
                const textContent = item.textContent.toLowerCase();

                const matchesFaculty = (currentFaculty === 'all' || itemFaculty.toLowerCase() === currentFaculty.toLowerCase());
                const matchesFormat = (format === 'all' || itemFormat === format);
                const matchesYear = (year === 'all' || itemYear === year);
                const matchesQuery = (!query || textContent.includes(query));

                if (matchesFaculty && matchesFormat && matchesYear && matchesQuery) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (visibleCount === 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }
        }

        facultyPills.forEach(pill => {
            pill.addEventListener('click', () => {
                facultyPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                currentFaculty = pill.getAttribute('data-faculty');
                filterMaterials();
            });
        });

        if (searchInput) searchInput.addEventListener('input', filterMaterials);
        if (formatFilter) formatFilter.addEventListener('change', filterMaterials);
        if (yearFilter) yearFilter.addEventListener('change', filterMaterials);

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                searchInput.value = '';
                formatFilter.value = 'all';
                yearFilter.value = 'all';
                facultyPills.forEach(p => p.classList.remove('active'));
                facultyPills[0].classList.add('active');
                currentFaculty = 'all';
                filterMaterials();
            });
        }

        // Bookmark toggle
        document.querySelectorAll('.bookmark-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const icon = btn.querySelector('i');
                if (icon.classList.contains('bi-bookmark-fill')) {
                    icon.classList.remove('bi-bookmark-fill');
                    icon.classList.add('bi-bookmark');
                    btn.classList.remove('text-primary');
                    btn.classList.add('text-muted');
                } else {
                    icon.classList.remove('bi-bookmark');
                    icon.classList.add('bi-bookmark-fill');
                    btn.classList.remove('text-muted');
                    btn.classList.add('text-primary');
                }
            });
        });
    });
</script>
</body>
</html>

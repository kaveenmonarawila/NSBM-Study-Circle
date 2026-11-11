<?php
// NSBM STUDY CIRCLE - Student Portal Saved Resources
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Saved resources';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved resources - Student Portal | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">Saved resources</h2>
                    <p class="student-page-subheading">Your personal reading list for later study.</p>
                </div>
            </div>

            <!-- 3 Saved Resource Cards Grid -->
            <div class="student-groups-grid">
                <!-- Card 1 -->
                <div class="student-card mb-0 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="student-res-icon student-res-icon-blue">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Remove bookmark">
                                <i class="bi bi-bookmark-fill fs-5"></i>
                            </button>
                        </div>
                        <span class="student-badge-computing">Computing</span>
                        <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                            Data Structures — Complete Revision Guide
                        </h4>
                        <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                            Clear, student-reviewed learning material ready for your next study block.
                        </p>
                    </div>
                    <div>
                        <div class="text-muted small mb-3" style="font-size: 0.8rem;">
                            <span class="text-warning fw-bold">★ 4.9</span> · 42 pages
                        </div>
                        <a href="javascript:void(0)" data-resource-id="1" class="open-resource-modal student-btn-outline w-100 justify-content-center py-2" style="font-size: 0.85rem;">
                            Open resource
                        </a>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="student-card mb-0 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="student-res-icon student-res-icon-green">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Remove bookmark">
                                <i class="bi bi-bookmark-fill fs-5"></i>
                            </button>
                        </div>
                        <span class="student-badge-business">Business</span>
                        <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                            Financial Accounting: Core Concepts
                        </h4>
                        <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                            Clear, student-reviewed learning material ready for your next study block.
                        </p>
                    </div>
                    <div>
                        <div class="text-muted small mb-3" style="font-size: 0.8rem;">
                            <span class="text-warning fw-bold">★ 4.8</span> · 36 pages
                        </div>
                        <a href="javascript:void(0)" data-resource-id="2" class="open-resource-modal student-btn-outline w-100 justify-content-center py-2" style="font-size: 0.85rem;">
                            Open resource
                        </a>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="student-card mb-0 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="student-res-icon student-res-icon-teal">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 bookmark-toggle" title="Remove bookmark">
                                <i class="bi bi-bookmark-fill fs-5"></i>
                            </button>
                        </div>
                        <span class="student-badge-science">Science</span>
                        <h4 class="student-card-title mt-2 mb-2" style="font-size: 1.05rem;">
                            Scientific Communication Toolkit
                        </h4>
                        <p class="student-item-subtitle mb-4" style="line-height: 1.5;">
                            Clear, student-reviewed learning material ready for your next study block.
                        </p>
                    </div>
                    <div>
                        <div class="text-muted small mb-3" style="font-size: 0.8rem;">
                            <span class="text-warning fw-bold">★ 4.7</span> · DOCX
                        </div>
                        <a href="javascript:void(0)" data-resource-id="3" class="open-resource-modal student-btn-outline w-100 justify-content-center py-2" style="font-size: 0.85rem;">
                            Open resource
                        </a>
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

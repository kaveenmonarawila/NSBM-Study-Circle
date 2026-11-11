<?php
// NSBM STUDY CIRCLE - Student Portal Notifications
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Notifications';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Portal | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">Notifications</h2>
                    <div class="d-flex align-items-center gap-3 my-2">
                        <a href="notifications.php" class="text-decoration-none fw-bold" style="color: var(--navy-title); border-bottom: 2px solid var(--navy-title); padding-bottom: 4px;">All</a>
                        <a href="notifications.php?filter=unread" class="text-decoration-none text-muted" style="padding-bottom: 4px;">Unread</a>
                        <a href="notifications.php?filter=mentions" class="text-decoration-none text-muted" style="padding-bottom: 4px;">Mentions</a>
                    </div>
                    <p class="student-page-subheading">Updates from your resources, questions, groups, and sessions.</p>
                </div>
                <div>
                    <a href="javascript:void(0)" class="student-card-link text-muted small fw-semibold">Mark all as read</a>
                </div>
            </div>

            <!-- Notifications List Container -->
            <div class="student-card p-0">
                <!-- Item 1: New answer on your question -->
                <div class="p-3 border-bottom d-flex align-items-start gap-3">
                    <div class="student-act-icon student-act-blue mt-1">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="student-act-title" style="font-size: 0.95rem;">
                                <a href="discussions.php" class="text-decoration-none text-dark hover-green">New answer on your question</a>
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1 bg-primary"></span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="javascript:void(0)">Mark as read</a></li>
                                        <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="student-act-desc mb-1" style="font-size: 0.85rem;">
                            Sahan answered "How do you choose between merge sort and quicksort?"
                        </p>
                        <span class="text-muted" style="font-size: 0.74rem;">12 minutes ago</span>
                    </div>
                </div>

                <!-- Item 2: Your upload was approved -->
                <div class="p-3 border-bottom d-flex align-items-start gap-3">
                    <div class="student-act-icon student-act-green mt-1">
                        <i class="bi bi-check2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="student-act-title" style="font-size: 0.95rem;">
                                <a href="uploads.php" class="text-decoration-none text-dark hover-green">Your upload was approved</a>
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1 bg-primary"></span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="javascript:void(0)">Mark as read</a></li>
                                        <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="student-act-desc mb-1" style="font-size: 0.85rem;">
                            Algorithm Complexity Cheat Sheet is now available publicly.
                        </p>
                        <span class="text-muted" style="font-size: 0.74rem;">2 hours ago</span>
                    </div>
                </div>

                <!-- Item 3: New group member -->
                <div class="p-3 border-bottom d-flex align-items-start gap-3">
                    <div class="student-act-icon student-act-indigo mt-1">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="student-act-title" style="font-size: 0.95rem;">
                                <a href="groups.php" class="text-decoration-none text-dark hover-green">New group member</a>
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1 bg-primary"></span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="javascript:void(0)">Mark as read</a></li>
                                        <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="student-act-desc mb-1" style="font-size: 0.85rem;">
                            Dinara Jayasinghe joined Data Structures Circle.
                        </p>
                        <span class="text-muted" style="font-size: 0.74rem;">5 hours ago</span>
                    </div>
                </div>

                <!-- Item 4: Session reminder -->
                <div class="p-3 border-bottom d-flex align-items-start gap-3">
                    <div class="student-act-icon student-act-blue mt-1">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="student-act-title" style="font-size: 0.95rem;">
                                <a href="schedule.php" class="text-decoration-none text-dark hover-green">Session reminder</a>
                            </h4>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <p class="student-act-desc mb-1" style="font-size: 0.85rem;">
                            Database Systems Revision begins today at 6:00 PM.
                        </p>
                        <span class="text-muted" style="font-size: 0.74rem;">Yesterday</span>
                    </div>
                </div>

                <!-- Item 5: Saved resource updated -->
                <div class="p-3 d-flex align-items-start gap-3">
                    <div class="student-act-icon student-act-orange mt-1">
                        <i class="bi bi-bookmark"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="student-act-title" style="font-size: 0.95rem;">
                                <a href="saved.php" class="text-decoration-none text-dark hover-green">Saved resource updated</a>
                            </h4>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item small text-danger" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <p class="student-act-desc mb-1" style="font-size: 0.85rem;">
                            UI/UX Research Methods Toolkit has a new version.
                        </p>
                        <span class="text-muted" style="font-size: 0.74rem;">2 days ago</span>
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

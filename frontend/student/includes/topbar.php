<?php
$page_title = !empty($page_title) ? $page_title : 'Dashboard';
$studentName = !empty($currentUserName) ? $currentUserName : (!empty($_SESSION['name']) ? $_SESSION['name'] : 'Student');
$studentInitials = !empty($currentUserInitials) ? $currentUserInitials : (function_exists('getStudentInitials') ? getStudentInitials($studentName) : 'ST');
if (empty($studentInitials)) {
    $studentInitials = 'ST';
}
$studentRole = !empty($currentUserDegree) ? $currentUserDegree : (!empty($_SESSION['degree_program']) ? $_SESSION['degree_program'] : 'NSBM Student Member');
?>
<!-- Student Dashboard Top Bar -->
<header class="student-topbar">
    <div>
        <div class="student-topbar-breadcrumb">STUDENT DASHBOARD</div>
        <h1 class="student-topbar-title"><?= htmlspecialchars($page_title) ?></h1>
    </div>

    <!-- Search Input -->
    <div class="student-search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search resources, discussions, or groups..." aria-label="Search">
    </div>

    <!-- Topbar Actions -->
    <div class="student-topbar-actions">
        <!-- Theme Toggle -->
        <button type="button" class="student-icon-btn theme-toggle-btn" id="studentThemeToggle" title="Toggle Theme">
            <i class="bi bi-moon"></i>
        </button>

        <!-- Notifications Bell -->
        <a href="notifications.php" class="student-icon-btn position-relative" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                <span class="visually-hidden">New alerts</span>
            </span>
        </a>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <div class="student-profile-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="student-avatar"><?= htmlspecialchars($studentInitials) ?></div>
                <div class="student-user-info d-none d-sm-block">
                    <p class="student-user-name"><?= htmlspecialchars($studentName) ?></p>
                    <p class="student-user-role text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($studentRole) ?>"><?= htmlspecialchars($studentRole) ?></p>
                </div>
                <i class="bi bi-chevron-down ms-1 text-muted" style="font-size: 0.75rem;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius: 10px;">
                <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
                <li><a class="dropdown-item py-2" href="settings.php"><i class="bi bi-gear me-2 text-muted"></i> Account Settings</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log out</a></li>
            </ul>
        </div>
    </div>
</header>
<script>
    window.__NSBM_CURRENT_USER = <?= json_encode([
        'id' => $currentUserId ?? 0,
        'name' => $studentName,
        'initials' => $studentInitials,
        'role' => $studentRole,
        'email' => $currentUserEmail ?? ''
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

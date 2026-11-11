<?php
$adminName = $_SESSION['name'] ?? 'Admin User';
$adminRole = $_SESSION['admin_type'] ?? 'Super administrator';
?>
<!-- Admin Console Topbar -->
<header class="admin-topbar">
    <!-- Left Search Input -->
    <div class="admin-topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" id="adminGlobalSearch" placeholder="Search users, resources, or records..." aria-label="Search">
    </div>

    <!-- Right Actions & Admin User Profile -->
    <div class="admin-topbar-actions">
        <button type="button" class="admin-icon-btn" id="adminThemeToggle" title="Toggle theme" aria-label="Toggle theme">
            <i class="bi bi-moon"></i>
        </button>

        <button type="button" class="admin-icon-btn" id="adminNotificationBtn" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </button>

        <div class="dropdown">
            <div class="admin-profile-trigger" data-bs-toggle="dropdown" aria-expanded="false" role="button" tabindex="0">
                <div class="admin-avatar-badge">AD</div>
                <div class="admin-user-meta">
                    <div class="admin-user-name"><?= htmlspecialchars($adminName) ?></div>
                    <div class="admin-user-role"><?= htmlspecialchars($adminRole) ?></div>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end admin-profile-dropdown-menu">
                <li><a class="dropdown-item" href="settings.php">Admin settings</a></li>
                <li><a class="dropdown-item" href="../index.php">Return to website</a></li>
                <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</header>

<?php
// Determine current page for active sidebar state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Admin Console Dark Navy Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <a href="index.php" class="admin-sidebar-brand">
        <div class="admin-brand-icon">
            <img src="../assets/images/logo.svg" alt="NSBM Study Circle" onerror="this.onerror=null; this.src='../assets/images/logo.jpg';">
        </div>
        <div>
            <h6 class="admin-brand-title">NSBM Study Circle</h6>
            <p class="admin-brand-tagline">ADMIN CONSOLE</p>
        </div>
    </a>

    <div class="admin-sidebar-scroll">
        <!-- OVERVIEW -->
        <div class="admin-sidebar-section">
            <span class="admin-sidebar-label">OVERVIEW</span>
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="index.php" class="admin-nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>">
                        <i class="bi bi-grid"></i>
                        <span>Admin Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- PEOPLE -->
        <div class="admin-sidebar-section">
            <span class="admin-sidebar-label">PEOPLE</span>
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="users.php" class="admin-nav-link <?= ($current_page === 'users.php' || $current_page === 'manage_students.php') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>User Management</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ACADEMIC CONTENT -->
        <div class="admin-sidebar-section">
            <span class="admin-sidebar-label">ACADEMIC CONTENT</span>
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="approvals.php" class="admin-nav-link <?= ($current_page === 'approvals.php') ? 'active' : '' ?>">
                        <i class="bi bi-check2"></i>
                        <span>Resource Approvals</span>
                        <span class="admin-nav-badge" id="sidebarApprovalBadge">7</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="resources.php" class="admin-nav-link <?= ($current_page === 'resources.php' || $current_page === 'manage_resources.php') ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Resource Management</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="academic-areas.php" class="admin-nav-link <?= ($current_page === 'academic-areas.php') ? 'active' : '' ?>">
                        <i class="bi bi-book"></i>
                        <span>Academic Areas</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="subjects.php" class="admin-nav-link <?= ($current_page === 'subjects.php') ? 'active' : '' ?>">
                        <i class="bi bi-journal-text"></i>
                        <span>First-Year Subjects</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- COMMUNITY -->
        <div class="admin-sidebar-section">
            <span class="admin-sidebar-label">COMMUNITY</span>
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="discussions.php" class="admin-nav-link <?= ($current_page === 'discussions.php' || $current_page === 'manage_announcements.php') ? 'active' : '' ?>">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Discussion Moderation</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="groups.php" class="admin-nav-link <?= ($current_page === 'groups.php') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>Study Groups</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="sessions.php" class="admin-nav-link <?= ($current_page === 'sessions.php' || $current_page === 'manage_events.php') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-event"></i>
                        <span>Study Sessions</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- SYSTEM -->
        <div class="admin-sidebar-section">
            <span class="admin-sidebar-label">SYSTEM</span>
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="reports.php" class="admin-nav-link <?= ($current_page === 'reports.php') ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Reports &amp; Analytics</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="settings.php" class="admin-nav-link <?= ($current_page === 'settings.php') ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        <span>Admin Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bottom System Status Box (PDF Pages 4, 26, 27) -->
    <div class="admin-sidebar-footer">
        <div class="admin-system-status-card">
            <span class="status-dot"></span>
            <div>
                <div class="status-title">All systems operational</div>
                <div class="status-subtitle">Last checked 2 min ago</div>
            </div>
        </div>
    </div>
</aside>

<?php
// Determine the current page for setting active link in the sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Student Dark Navy Sidebar -->
<aside class="student-sidebar">
    <a href="index.php" class="student-sidebar-brand">
        <div class="student-brand-icon">
            <img src="../assets/images/logo.svg" alt="NSBM Study Circle Logo" onerror="this.onerror=null; this.src='../assets/images/logo.jpg';">
        </div>
        <div>
            <h6 class="student-brand-title">NSBM Study Circle</h6>
            <p class="student-brand-tagline">Learn Together. Grow Together.</p>
        </div>
    </a>

    <!-- LEARNING SECTION -->
    <div class="student-sidebar-section">
        <span class="student-sidebar-label">LEARNING</span>
        <ul class="student-nav-list">
            <li class="student-nav-item">
                <a href="index.php" class="student-nav-link <?= ($current_page == 'index.php' || $current_page == 'index.html') ? 'active' : '' ?>">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="study-materials.php" class="student-nav-link <?= ($current_page == 'study-materials.php' || $current_page == 'study-materials.html' || $current_page == 'resources.php') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Study Materials</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="discussions.php" class="student-nav-link <?= ($current_page == 'discussions.php' || $current_page == 'discussions.html') ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Discussions</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="groups.php" class="student-nav-link <?= ($current_page == 'groups.php') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Study Groups</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="schedule.php" class="student-nav-link <?= ($current_page == 'schedule.php' || $current_page == 'sessions.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Study Sessions</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MY LIBRARY SECTION -->
    <div class="student-sidebar-section">
        <span class="student-sidebar-label">MY LIBRARY</span>
        <ul class="student-nav-list">
            <li class="student-nav-item">
                <a href="uploads.php" class="student-nav-link <?= ($current_page == 'uploads.php') ? 'active' : '' ?>">
                    <i class="bi bi-upload"></i>
                    <span>My Uploads</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="downloads.php" class="student-nav-link <?= ($current_page == 'downloads.php') ? 'active' : '' ?>">
                    <i class="bi bi-download"></i>
                    <span>My Downloads</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="saved.php" class="student-nav-link <?= ($current_page == 'saved.php') ? 'active' : '' ?>">
                    <i class="bi bi-bookmark"></i>
                    <span>Saved Resources</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- ACCOUNT SECTION -->
    <div class="student-sidebar-section">
        <span class="student-sidebar-label">ACCOUNT</span>
        <ul class="student-nav-list">
            <li class="student-nav-item">
                <a href="notifications.php" class="student-nav-link <?= ($current_page == 'notifications.php') ? 'active' : '' ?>">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                    <span class="student-nav-badge">4</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="profile.php" class="student-nav-link <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
                    <i class="bi bi-person"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="student-nav-item">
                <a href="settings.php" class="student-nav-link <?= ($current_page == 'settings.php') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

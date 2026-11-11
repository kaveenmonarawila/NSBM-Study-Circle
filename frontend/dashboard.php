<?php
// NSBM STUDY CIRCLE - Global Dashboard Router
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strictly guard dashboard access for unauthenticated guests
if (empty($_SESSION['user_id'])) {
    header("Location: login.php?error=" . urlencode("Authentication required. Please log in to access the dashboard."));
    exit();
}

$role = $_SESSION['role'] ?? 'student';
if ($role === 'admin') {
    header("Location: admin/index.php");
} else {
    header("Location: student/index.php");
}
exit();


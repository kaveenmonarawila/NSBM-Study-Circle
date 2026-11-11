<?php
// Session Helper - Session management utilities
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStudent() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}
?>


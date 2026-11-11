<?php
// ============================================================================
// NSBM STUDY CIRCLE - CENTRAL LOGOUT HANDLER
// Safely terminates student and admin sessions, logs the event to MySQL,
// and redirects cleanly to the login screen.
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userEmail = $_SESSION['email'] ?? '';
$userName  = $_SESSION['name'] ?? 'User';
$userId    = $_SESSION['user_id'] ?? null;
$userRole  = $_SESSION['role'] ?? 'student';

// Safely log the logout event to MySQL (direct connection, NO external controller calls)
if (!empty($userEmail)) {
    $dbConfig = __DIR__ . '/../backend/config/db_config.php';
    if (file_exists($dbConfig)) {
        @require_once $dbConfig;
        global $conn;
        if (isset($conn) && $conn && !$conn->connect_error) {
            try {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Web Browser', 0, 250);
                $roleClean = ($userRole === 'admin') ? 'admin' : 'student';

                // Validate FK against users table before logging
                $uid = null;
                if (!empty($userId)) {
                    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
                    if ($chk) {
                        $chk->bind_param("i", $userId);
                        $chk->execute();
                        $chk->bind_result($validId);
                        if ($chk->fetch()) {
                            $uid = (int)$validId;
                        }
                        $chk->close();
                    }
                }

                @$conn->query("
                    CREATE TABLE IF NOT EXISTS login_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NULL,
                        name VARCHAR(120) NULL,
                        email VARCHAR(160) NOT NULL,
                        role ENUM('student', 'admin') DEFAULT 'student',
                        action_type ENUM('Login', 'Signup', 'Logout') DEFAULT 'Login',
                        ip_address VARCHAR(45) DEFAULT '127.0.0.1',
                        user_agent VARCHAR(255) NULL,
                        status ENUM('Success', 'Failed') DEFAULT 'Success',
                        logged_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_login_logs_email (email),
                        INDEX idx_login_logs_time (logged_in_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");

                $stmt = $conn->prepare("INSERT INTO login_logs (user_id, name, email, role, action_type, ip_address, user_agent, status, logged_in_at) VALUES (?, ?, ?, ?, 'Logout', ?, ?, 'Success', NOW())");
                if ($stmt) {
                    $stmt->bind_param("isssss", $uid, $userName, $userEmail, $roleClean, $ip, $ua);
                    $stmt->execute();
                    $stmt->close();
                }
            } catch (Throwable $e) {
                // Silently ignore DB errors so logout NEVER fails
            }
        }
    }
}

// Clear all session variables
$_SESSION = [];

// Expire the session cookie in browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
if (session_status() === PHP_SESSION_ACTIVE) {
    @session_destroy();
}

// Determine correct redirect URL to frontend/login.php
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

if (preg_match('#^(.*?/frontend/)#i', $scriptName, $matches)) {
    $redirect = $matches[1] . 'login.php?logged_out=1';
} elseif (strpos($scriptName, '/student/') !== false || strpos($scriptName, '/admin/') !== false) {
    $redirect = '../login.php?logged_out=1';
} else {
    $redirect = 'login.php?logged_out=1';
}

header("Location: " . $redirect);
exit();

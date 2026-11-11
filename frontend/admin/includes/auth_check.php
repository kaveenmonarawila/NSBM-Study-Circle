<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE AUTHENTICATION & ACCESS CONTROL BRIDGE
// Strict Role-Based Security: Prevents Students & Logged-Out Users from Accessing Admin
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../backend/config/db_config.php';

// Safe helper to verify table existence in MySQL
if (!function_exists('safeAdminTableExists')) {
    function safeAdminTableExists($conn, string $tableName): bool {
        static $tablesCache = null;
        if (!isset($conn) || !$conn || $conn->connect_error) {
            return false;
        }
        if ($tablesCache === null) {
            $tablesCache = [];
            try {
                $res = @$conn->query("SHOW TABLES");
                if ($res) {
                    while ($row = $res->fetch_row()) {
                        $tablesCache[strtolower($row[0])] = true;
                    }
                    $res->free();
                }
            } catch (Throwable $e) {
                return false;
            }
        }
        return isset($tablesCache[strtolower($tableName)]);
    }
}

// 1. Guard: Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php?role=admin&error=" . urlencode("Authentication required. Please log in as an administrator."));
    exit();
}

// 2. Guard: Check if user has administrator role (Students are strictly blocked!)
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php?role=admin&error=" . urlencode("Access denied. Administrator privileges required."));
    exit();
}

$currentAdminId = (int)$_SESSION['user_id'];
$currentAdmin = null;

// 3. Verify admin account exists in MySQL and is Active
if (isset($conn) && $conn && !$conn->connect_error && safeAdminTableExists($conn, 'users')) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, role, admin_type, status FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $currentAdminId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                if ($row['status'] === 'Suspended') {
                    $_SESSION = [];
                    session_destroy();
                    header("Location: ../login.php?role=admin&error=" . urlencode("Your administrator account is suspended."));
                    exit();
                }
                $currentAdmin = $row;
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['admin_type'] = $row['admin_type'] ?? 'Super administrator';
            }
            $stmt->close();
        }
    } catch (Throwable $e) {}
}

if (!$currentAdmin && isset($conn) && $conn && !$conn->connect_error) {
    // Admin user was deleted or does not exist in MySQL
    $_SESSION = [];
    session_destroy();
    header("Location: ../login.php?role=admin&error=" . urlencode("Administrator account not found. Please log in again."));
    exit();
}

$currentAdminName = !empty($_SESSION['name']) ? $_SESSION['name'] : 'Administrator';
$currentAdminEmail = !empty($_SESSION['email']) ? $_SESSION['email'] : 'admin@nsbm.lk';
$currentAdminType = !empty($_SESSION['admin_type']) ? $_SESSION['admin_type'] : 'Super administrator';
?>

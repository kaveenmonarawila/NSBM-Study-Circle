<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDENT PORTAL AUTHENTICATION & DATABASE BRIDGE
// Connects every Student Dashboard page to MySQL (`nsbm_student_circle`)
// Robust Session & Variable Fallbacks for Brand New & Existing Users
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../backend/config/db_config.php';

// Safe helper to verify table existence in MySQL
if (!function_exists('safeTableExists')) {
    function safeTableExists($conn, string $tableName): bool {
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

// Safe helper for user initials with guaranteed 2-letter fallback
if (!function_exists('getStudentInitials')) {
    function getStudentInitials(string $name): string {
        $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name));
        if (empty($clean)) {
            return 'ST';
        }
        $parts = preg_split('/\s+/', $clean);
        $parts = array_values(array_filter($parts));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($clean, 0, min(2, strlen($clean))));
    }
}

// Safe query runner for single integer counts
if (!function_exists('safeCountQuery')) {
    function safeCountQuery($conn, string $tableName, string $where = ''): int {
        if (!safeTableExists($conn, $tableName)) {
            return 0;
        }
        try {
            $sql = "SELECT COUNT(*) FROM `$tableName`" . ($where ? " WHERE $where" : "");
            $res = @$conn->query($sql);
            if ($res && $row = $res->fetch_row()) {
                $val = (int)$row[0];
                $res->free();
                return $val;
            }
        } catch (Throwable $e) {
            return 0;
        }
        return 0;
    }
}

// Ensure optional `login_logs` table exists so queries on it never fail
if (isset($conn) && $conn && !$conn->connect_error) {
    if (!safeTableExists($conn, 'login_logs')) {
        try {
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
        } catch (Throwable $e) {}
    }
}

// 1. Guard: Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php?error=" . urlencode("Authentication required. Please log in to your student account."));
    exit();
}

// 2. Fetch fresh live profile details from MySQL `users` table
$currentUserId = (int)$_SESSION['user_id'];
$currentUser = null;

if ($currentUserId > 0 && isset($conn) && $conn && !$conn->connect_error && safeTableExists($conn, 'users')) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, student_id, faculty, degree_program, academic_year, bio, academic_interests, profile_visibility, status FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $currentUserId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                if ($row['status'] === 'Suspended') {
                    $_SESSION = [];
                    session_destroy();
                    header("Location: ../login.php?error=" . urlencode("Your account has been suspended by an administrator."));
                    exit();
                }
                $currentUser = $row;
            }
            $stmt->close();
        }
    } catch (Throwable $e) {}
}

if (!$currentUser && isset($conn) && $conn && !$conn->connect_error) {
    // User does not exist in database
    $_SESSION = [];
    session_destroy();
    header("Location: ../login.php?error=" . urlencode("User session expired. Please log in again."));
    exit();
}

// 3. Guarantee robust, non-empty fallback values for EVERY user attribute
$rawName = trim($currentUser['name'] ?? ($_SESSION['name'] ?? ''));
if (empty($rawName) || strtolower($rawName) === 'undefined') {
    // If name is not set, derive from email or generic title
    $emailCandidate = $currentUser['email'] ?? ($_SESSION['email'] ?? '');
    if (!empty($emailCandidate) && strpos($emailCandidate, '@') !== false) {
        $local = explode('@', $emailCandidate)[0];
        $parts = preg_split('/[._\-]+/', $local);
        $formatted = array_map(fn($p) => ucfirst(strtolower(trim($p))), array_filter($parts));
        $rawName = !empty($formatted) ? implode(' ', $formatted) : 'NSBM Student';
    } else {
        $rawName = 'NSBM Student';
    }
}

$currentUserName = $rawName;
$nameParts = preg_split('/\s+/', $currentUserName);
$userFirstName = !empty($nameParts[0]) ? $nameParts[0] : 'Student';
$currentUserInitials = getStudentInitials($currentUserName);

$currentUserFaculty = !empty($currentUser['faculty']) 
    ? $currentUser['faculty'] 
    : (!empty($_SESSION['faculty']) ? $_SESSION['faculty'] : 'Computing');

$currentUserDegree = !empty($currentUser['degree_program']) 
    ? $currentUser['degree_program'] 
    : (!empty($_SESSION['degree_program']) ? $_SESSION['degree_program'] : 'BSc (Hons) Computer Science · Year 1');

$currentUserEmail = !empty($currentUser['email']) 
    ? $currentUser['email'] 
    : (!empty($_SESSION['email']) ? $_SESSION['email'] : 'student@nsbm.ac.lk');

$currentUserStudentId = !empty($currentUser['student_id']) 
    ? $currentUser['student_id'] 
    : (!empty($_SESSION['student_id']) ? $_SESSION['student_id'] : 'CB' . str_pad((string)$currentUserId, 6, '0', STR_PAD_LEFT));

$currentUserBio = !empty($currentUser['bio']) 
    ? $currentUser['bio'] 
    : (!empty($_SESSION['bio']) ? $_SESSION['bio'] : 'NSBM undergraduate student collaborating and sharing resources on the NSBM Study Circle platform.');

$currentUserInterests = !empty($currentUser['academic_interests']) 
    ? $currentUser['academic_interests'] 
    : (!empty($_SESSION['academic_interests']) ? $_SESSION['academic_interests'] : 'Algorithms, Databases, Web Technologies');

$currentUserVisibility = !empty($currentUser['profile_visibility']) 
    ? $currentUser['profile_visibility'] 
    : (!empty($_SESSION['profile_visibility']) ? $_SESSION['profile_visibility'] : 'NSBM Study Circle members');

$currentUserRole = 'NSBM Student Contributor';

// Ensure complete $currentUser array representation
if (!$currentUser) {
    $currentUser = [
        'id' => $currentUserId,
        'name' => $currentUserName,
        'email' => $currentUserEmail,
        'faculty' => $currentUserFaculty,
        'degree_program' => $currentUserDegree,
        'bio' => $currentUserBio,
        'academic_interests' => $currentUserInterests,
        'profile_visibility' => $currentUserVisibility,
        'student_id' => $currentUserStudentId,
        'status' => 'Active'
    ];
}

// Synchronize all clean, sanitized attributes back into $_SESSION
$_SESSION['user_id'] = $currentUserId;
$_SESSION['name'] = $currentUserName;
$_SESSION['email'] = $currentUserEmail;
$_SESSION['role'] = 'student';
$_SESSION['faculty'] = $currentUserFaculty;
$_SESSION['degree_program'] = $currentUserDegree;
$_SESSION['student_id'] = $currentUserStudentId;
$_SESSION['bio'] = $currentUserBio;
$_SESSION['academic_interests'] = $currentUserInterests;
$_SESSION['profile_visibility'] = $currentUserVisibility;
?>

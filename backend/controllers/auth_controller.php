<?php
// ============================================================================
// NSBM STUDY CIRCLE - AUTH CONTROLLER (100% Live MySQL / phpMyAdmin Persistence)
// Records every Login, Registration, Logout & Password Update in MySQL:
//   - `users` table (creates/updates account & last_active_at)
//   - `login_logs` table (stores every login/signup timestamp, email, role & IP)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_path = __DIR__ . '/../config/db_config.php';
if (file_exists($db_path)) {
    require_once $db_path;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure `login_logs` table exists in MySQL (`nsbm_student_circle`)
function ensureLoginLogsTable() {
    global $conn;
    if (!isset($conn) || $conn->connect_error) return;
    $conn->query("
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
}

function recordLoginEvent($userId, $name, $email, $role = 'student', $actionType = 'Login', $status = 'Success') {
    global $conn;
    if (!isset($conn) || !$conn || $conn->connect_error) return;
    try {
        ensureLoginLogsTable();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Web Browser', 0, 250);
        $uid = $userId ? (int)$userId : null;
        $roleClean = ($role === 'admin') ? 'admin' : 'student';

        $stmt = $conn->prepare("INSERT INTO login_logs (user_id, name, email, role, action_type, ip_address, user_agent, status, logged_in_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("isssssss", $uid, $name, $email, $roleClean, $actionType, $ip, $ua, $status);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $e) {}
}

function deriveNameFromEmail(string $email): string {
    $local = explode('@', $email)[0] ?? 'Student';
    $parts = preg_split('/[._\-]+/', $local);
    $formatted = array_map(fn($p) => ucfirst(strtolower(trim($p))), array_filter($parts));
    return !empty($formatted) ? implode(' ', $formatted) : 'NSBM Student';
}

function isJsonRequest() {
    return (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
           (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
           (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
           (isset($_GET['format']) && $_GET['format'] === 'json');
}

function sendResponse($success, $message, $redirectUrl = null, $extra = []) {
    if (isJsonRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'redirect' => $redirectUrl
        ], $extra));
        exit();
    } else {
        if ($success && $redirectUrl) {
            header("Location: " . $redirectUrl);
            exit();
        } else {
            $referer = $_SERVER['HTTP_REFERER'] ?? '../../frontend/login.php';
            $cleanReferer = preg_replace('/(\?|&)(error|success|msg)=[^&]*/', '', $referer);
            $cleanSeparator = (strpos($cleanReferer, '?') !== false) ? '&' : '?';
            header("Location: " . $cleanReferer . $cleanSeparator . "error=" . urlencode($message));
            exit();
        }
    }
}

$input = $_POST;
$rawBody = file_get_contents('php://input');
if (!empty($rawBody)) {
    $jsonData = json_decode($rawBody, true);
    if (is_array($jsonData)) {
        $input = array_merge($input, $jsonData);
    }
}

$action = $input['action'] ?? ($_GET['action'] ?? 'login');

// ============================================================================
// 1. ACTION: LOGIN (Saves/Updates `users` and inserts into `login_logs`)
// ============================================================================
if ($action === 'login') {
    $identifier = trim($input['email'] ?? ($input['identifier'] ?? ''));
    $password = $input['password'] ?? '';
    $requestedRole = strtolower(trim($input['role'] ?? 'student'));
    if (!in_array($requestedRole, ['student', 'admin'])) {
        $requestedRole = 'student';
    }

    if (empty($identifier) || empty($password)) {
        sendResponse(false, 'Please provide both your Email / Student ID and Password.');
    }

    global $conn;
    if (!isset($conn) || $conn->connect_error) {
        sendResponse(false, 'Database connection is offline. Please verify MySQL is running.');
    }

    ensureLoginLogsTable();

    // ========================================================================
    // PRIMARY DESIGNATED ADMIN CREDENTIALS:
    // email: admin.nsbmstudycircle@gmail.com
    // password: admin@nsbmsc
    // ========================================================================
    if (strcasecmp($identifier, 'admin.nsbmstudycircle@gmail.com') === 0) {
        if ($password !== 'admin@nsbmsc') {
            recordLoginEvent(null, null, $identifier, 'admin', 'Login', 'Failed');
            sendResponse(false, 'Invalid credentials.');
        }

        // Credentials match! Ensure account is active and correctly configured in MySQL `users`
        $chkAdmin = $conn->prepare("SELECT id, name, email, password, role, admin_type, status FROM users WHERE email = 'admin.nsbmstudycircle@gmail.com' LIMIT 1");
        $chkAdmin->execute();
        $adminRes = $chkAdmin->get_result();
        $adminUser = ($adminRes && $adminRes->num_rows === 1) ? $adminRes->fetch_assoc() : null;
        $chkAdmin->close();

        if (!$adminUser) {
            $hashed = password_hash('admin@nsbmsc', PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password, role, admin_type, faculty, status, created_at) VALUES ('System Administrator', 'admin.nsbmstudycircle@gmail.com', ?, 'admin', 'Super administrator', 'Computing', 'Active', NOW())");
            if ($ins) {
                $ins->bind_param("s", $hashed);
                $ins->execute();
                $ins->close();
            }
            $chkAdmin2 = $conn->prepare("SELECT id, name, email, password, role, admin_type, status FROM users WHERE email = 'admin.nsbmstudycircle@gmail.com' LIMIT 1");
            $chkAdmin2->execute();
            $adminUser = $chkAdmin2->get_result()->fetch_assoc();
            $chkAdmin2->close();
        } else if ($adminUser['role'] !== 'admin' || $adminUser['status'] !== 'Active' || !password_verify('admin@nsbmsc', $adminUser['password'])) {
            $hashed = password_hash('admin@nsbmsc', PASSWORD_BCRYPT);
            $conn->query("UPDATE users SET password = '{$hashed}', role = 'admin', admin_type = 'Super administrator', status = 'Active', last_active_at = NOW() WHERE id = " . (int)$adminUser['id']);
            $adminUser['role'] = 'admin';
            $adminUser['status'] = 'Active';
            $adminUser['admin_type'] = 'Super administrator';
        }

        // Record successful admin login in MySQL audit logs
        recordLoginEvent($adminUser['id'], $adminUser['name'], $adminUser['email'], 'admin', 'Login', 'Success');

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$adminUser['id'];
        $_SESSION['name'] = $adminUser['name'];
        $_SESSION['email'] = $adminUser['email'];
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_type'] = $adminUser['admin_type'] ?? 'Super administrator';
        $_SESSION['faculty'] = 'Computing';
        $_SESSION['degree_program'] = 'BSc (Hons) in Information Technology';
        $_SESSION['student_id'] = null;
        $_SESSION['logged_in_at'] = time();

        sendResponse(true, 'Admin login successful!', '../../frontend/admin/index.php', [
            'user' => [
                'id' => (int)$adminUser['id'],
                'name' => $adminUser['name'],
                'email' => $adminUser['email'],
                'role' => 'admin',
                'faculty' => 'Computing'
            ]
        ]);
    }

    // Query existing user by email or student_id
    $stmt = $conn->prepare("SELECT id, name, email, password, role, admin_type, student_id, faculty, degree_program, academic_year, status FROM users WHERE email = ? OR student_id = ? LIMIT 1");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] === 'Suspended') {
            recordLoginEvent($user['id'], $user['name'], $user['email'], $user['role'], 'Login', 'Failed');
            sendResponse(false, 'Your account has been suspended by an administrator.');
        }

        // If logging in via Admin portal tab, ensure user has admin role
        if ($requestedRole === 'admin' && $user['role'] !== 'admin') {
            recordLoginEvent($user['id'], $user['name'], $user['email'], $user['role'], 'Login', 'Failed');
            sendResponse(false, 'Invalid credentials.');
        }

        // Strict password verification (hashed bcrypt or plaintext legacy fallback)
        $passwordValid = password_verify($password, $user['password']) || ($password === $user['password']);
        if (!$passwordValid) {
            recordLoginEvent($user['id'], $user['name'], $user['email'], $user['role'], 'Login', 'Failed');
            sendResponse(false, 'Invalid credentials.');
        }

        // Upgrade legacy plaintext password to secure bcrypt hash
        if ($password === $user['password'] && !password_verify($password, $user['password'])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $upd = $conn->prepare("UPDATE users SET password = ?, last_active_at = NOW() WHERE id = ?");
            if ($upd) {
                $upd->bind_param("si", $newHash, $user['id']);
                $upd->execute();
                $upd->close();
            }
        } else {
            $upd = $conn->prepare("UPDATE users SET last_active_at = NOW() WHERE id = ?");
            if ($upd) {
                $upd->bind_param("i", $user['id']);
                $upd->execute();
                $upd->close();
            }
        }

        // Record login event in `login_logs` table in MySQL
        recordLoginEvent($user['id'], $user['name'], $user['email'], $user['role'], 'Login', 'Success');

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['admin_type'] = $user['admin_type'] ?? ($user['role'] === 'admin' ? 'Super administrator' : null);
        $_SESSION['faculty'] = $user['faculty'] ?? 'Computing';
        $_SESSION['degree_program'] = $user['degree_program'] ?? 'BSc (Hons) Computer Science · Year 3';
        $_SESSION['student_id'] = $user['student_id'] ?? null;
        $_SESSION['bio'] = $user['bio'] ?? '';
        $_SESSION['academic_interests'] = $user['academic_interests'] ?? '';
        $_SESSION['profile_visibility'] = $user['profile_visibility'] ?? 'NSBM Study Circle members';
        $_SESSION['logged_in_at'] = time();

        $redirectUrl = ($user['role'] === 'admin')
            ? '../../frontend/admin/index.php'
            : '../../frontend/student/index.php';

        sendResponse(true, 'Login successful! Recorded in MySQL.', $redirectUrl, [
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'faculty' => $user['faculty']
            ]
        ]);
    } else {
        // Account does not exist in MySQL database
        recordLoginEvent(null, null, $identifier, $requestedRole, 'Login', 'Failed');
        sendResponse(false, 'Invalid credentials.');
    }
}

// ============================================================================
// 2. ACTION: REGISTER (Student Signup -> Inserts into `users` & `login_logs`)
// ============================================================================
else if ($action === 'register') {
    $firstName = trim($input['first_name'] ?? '');
    $lastName = trim($input['last_name'] ?? '');
    $name = trim($input['name'] ?? trim("$firstName $lastName"));
    $email = trim($input['email'] ?? '');
    $studentId = trim($input['student_id'] ?? '');
    if (empty($studentId)) {
        $studentId = 'CB' . rand(100000, 999999);
    }
    $facultyRaw = trim($input['faculty'] ?? 'Computing');
    $faculty = 'Computing';
    foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
        if (stripos($facultyRaw, $fac) !== false) {
            $faculty = $fac;
            break;
        }
    }
    $degreeProgram = trim($input['degree_program'] ?? 'BSc (Hons) Software Engineering');
    $academicYear = trim($input['academic_year'] ?? 'Year 1');
    if (strpos($degreeProgram, '·') === false && !empty($academicYear)) {
        $degreeProgram = "$degreeProgram · $academicYear";
    }
    $password = $input['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        sendResponse(false, 'Please fill in all required registration fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Please enter a valid university email address.');
    }

    if (strlen($password) < 6) {
        sendResponse(false, 'Password must be at least 6 characters in length.');
    }

    global $conn;
    if (!isset($conn) || $conn->connect_error) {
        sendResponse(false, 'Database connection error.');
    }

    ensureLoginLogsTable();

    // Prevent duplicate registrations
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            $checkStmt->close();
            sendResponse(false, 'An account with this email address already exists. Please log in instead.');
        }
        $checkStmt->close();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $requestedRole = strtolower(trim($input['role'] ?? 'student'));
    $roleToSave = ($requestedRole === 'admin') ? 'admin' : 'student';
    $adminType = ($roleToSave === 'admin') ? 'Super administrator' : null;

    // Insert user into `users` table
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, student_id, faculty, degree_program, academic_year, password, role, admin_type, status, last_active_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
    ");
    $stmt->bind_param("sssssssss", $name, $email, $studentId, $faculty, $degreeProgram, $academicYear, $hashedPassword, $roleToSave, $adminType);

    if ($stmt->execute()) {
        $newId = (int)$stmt->insert_id;

        // Record signup in `login_logs` table
        recordLoginEvent($newId, $name, $email, $roleToSave, 'Signup', 'Success');

        session_regenerate_id(true);
        $_SESSION['user_id'] = $newId;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $roleToSave;
        $_SESSION['admin_type'] = $adminType;
        $_SESSION['faculty'] = $faculty;
        $_SESSION['degree_program'] = $degreeProgram;
        $_SESSION['student_id'] = $studentId;
        $_SESSION['bio'] = '';
        $_SESSION['academic_interests'] = '';
        $_SESSION['profile_visibility'] = 'NSBM Study Circle members';
        $_SESSION['logged_in_at'] = time();

        $redirectUrl = ($roleToSave === 'admin') ? '../../frontend/admin/index.php' : '../../frontend/student/index.php';

        sendResponse(true, 'Account created and saved in MySQL!', $redirectUrl, [
            'user' => [
                'id' => $newId,
                'name' => $name,
                'email' => $email,
                'role' => $roleToSave,
                'faculty' => $faculty
            ]
        ]);
    } else {
        sendResponse(false, 'Registration failed: ' . $stmt->error);
    }
}

// ============================================================================
// 3. ACTION: LOGOUT
// ============================================================================
else if ($action === 'logout') {
    if (!empty($_SESSION['email'])) {
        recordLoginEvent($_SESSION['user_id'] ?? null, $_SESSION['name'] ?? 'User', $_SESSION['email'], $_SESSION['role'] ?? 'student', 'Logout', 'Success');
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Determine correct redirect URL dynamically
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (preg_match('#^(.*?/backend/)#i', $scriptName, $matches)) {
        $prefix = preg_replace('#/backend/$#i', '', $matches[1]);
        $redirectUrl = $prefix . '/frontend/login.php?logged_out=1';
    } else {
        $redirectUrl = '../../frontend/login.php?logged_out=1';
    }

    if (isJsonRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Logged out successfully', 'redirect' => $redirectUrl]);
        exit();
    } else {
        header("Location: " . $redirectUrl);
        exit();
    }
}

// ============================================================================
// 4. ACTION: DELETE ACCOUNT
// ============================================================================
else if ($action === 'delete_account' || $action === 'delete') {
    global $conn;
    if (isset($_SESSION['user_id']) && isset($conn) && !$conn->connect_error) {
        $uid = intval($_SESSION['user_id']);
        $conn->query("DELETE FROM users WHERE id = $uid");
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    if (isJsonRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Account permanently deleted.', 'redirect' => '../../frontend/login.php?deleted=1']);
        exit();
    } else {
        header("Location: ../../frontend/login.php?deleted=1");
        exit();
    }
}

// ============================================================================
// 5. ACTION: UPDATE PASSWORD
// ============================================================================
else if ($action === 'update_password') {
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        sendResponse(false, 'Current password and new password are required.');
    }
    if (strlen($newPassword) < 8) {
        sendResponse(false, 'New password must be at least 8 characters long.');
    }

    global $conn;
    if (isset($_SESSION['user_id']) && isset($conn) && !$conn->connect_error) {
        $uid = intval($_SESSION['user_id']);
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $hashed, $uid);
            $stmt->execute();
        }
    }
    sendResponse(true, 'Password updated in MySQL successfully!');
}
?>

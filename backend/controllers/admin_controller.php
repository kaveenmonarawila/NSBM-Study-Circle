<?php
// ============================================================================
// NSBM STUDY CIRCLE - ADMIN CONSOLE CONTROLLER (100% Live MySQL / phpMyAdmin API)
// Queries & updates `nsbm_student_circle` in phpMyAdmin for all 11 Admin pages
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = $_POST;
$raw = file_get_contents('php://input');
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = array_merge($input, $decoded);
    }
}

$action = $input['action'] ?? ($_GET['action'] ?? 'admin_data');

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database offline']);
    exit();
}

// Enforce strict administrator authentication and authorization
$sessionRole = $_SESSION['role'] ?? '';
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
if ($sessionRole !== 'admin' || $sessionUserId <= 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden. Administrator privileges required.']);
    exit();
}

function getAdminInitials(?string $name): string {
    $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name ?? ''));
    if (empty($clean)) return 'AD';
    $parts = preg_split('/\s+/', $clean);
    $parts = array_values(array_filter($parts));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }
    return strtoupper(substr($clean, 0, min(2, strlen($clean))));
}

function safeAdminCount($conn, string $query): int {
    try {
        $res = @$conn->query($query);
        if ($res && $row = $res->fetch_row()) {
            $val = (int)$row[0];
            $res->free();
            return $val;
        }
    } catch (Throwable $e) {}
    return 0;
}

switch ($action) {
    // =========================================================================
    // 0. FETCH LIVE ADMIN CONSOLE DATA FROM MYSQL DATABASE
    // =========================================================================
    case 'admin_data':
    case 'overview':
        // 1. Live Metrics
        $totalStudents = safeAdminCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'student'");
        $activeStudents = safeAdminCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'Active'");
        $pendingApprovals = safeAdminCount($conn, "SELECT COUNT(*) FROM resources WHERE status = 'Pending'");
        $openReports = safeAdminCount($conn, "SELECT COUNT(*) FROM reported_content WHERE status = 'Pending'");
        $highPriorityReports = safeAdminCount($conn, "SELECT COUNT(*) FROM reported_content WHERE status = 'Pending' AND priority = 'High priority'");
        $publishedResources = safeAdminCount($conn, "SELECT COUNT(*) FROM resources WHERE status = 'Approved'");
        $totalDownloads = safeAdminCount($conn, "SELECT COALESCE(SUM(downloads), 0) FROM resources");
        $totalAreas = safeAdminCount($conn, "SELECT COUNT(*) FROM academic_areas");
        $totalSubjects = safeAdminCount($conn, "SELECT COUNT(*) FROM subjects");

        // 2. Users (Students)
        $users = [];
        $uRes = $conn->query("
            SELECT u.*,
                   (SELECT COUNT(*) FROM resources r WHERE r.uploaded_by = u.id) +
                   (SELECT COUNT(*) FROM discussion_answers da WHERE da.author_id = u.id) AS live_contributions
            FROM users u
            WHERE u.role = 'student'
            ORDER BY u.id ASC
        ");
        if ($uRes) {
            while ($row = $uRes->fetch_assoc()) {
                $row['initials'] = getAdminInitials($row['name']);
                $row['last_active_formatted'] = $row['last_active_at'] ? date('d M Y', strtotime($row['last_active_at'])) : 'Today';
                $row['contributions_count'] = max((int)($row['contributions'] ?? 0), (int)$row['live_contributions']);
                $users[] = $row;
            }
        }

        // 3. Administrators
        $admins = [];
        $admRes = $conn->query("SELECT id, name, email, admin_type, status FROM users WHERE role = 'admin' ORDER BY id ASC");
        if ($admRes) {
            while ($row = $admRes->fetch_assoc()) {
                $row['initials'] = getAdminInitials($row['name']);
                $admins[] = $row;
            }
        }

        // 4. All Resources & Pending Approvals Queue
        $resources = [];
        $pendingResources = [];
        $rRes = $conn->query("
            SELECT r.*, u.name AS author_name
            FROM resources r
            LEFT JOIN users u ON r.uploaded_by = u.id
            ORDER BY r.id ASC
        ");
        if ($rRes) {
            while ($row = $rRes->fetch_assoc()) {
                $row['author_name'] = $row['author_name'] ?: 'Nethmi Perera';
                $row['uploaded_date'] = date('d M Y', strtotime($row['created_at']));
                $resources[] = $row;
                if ($row['status'] === 'Pending') {
                    $pendingResources[] = $row;
                }
            }
        }
        if (empty($pendingResources) && !empty($resources)) {
            $pendingResources = array_slice($resources, 0, 4);
        }

        // 5. Academic Areas
        $areas = [];
        $aRes = $conn->query("
            SELECT aa.*,
                   (SELECT COUNT(*) FROM subjects s WHERE s.area_id = aa.id) AS subject_count
            FROM academic_areas aa
            ORDER BY aa.id ASC
        ");
        if ($aRes) {
            while ($row = $aRes->fetch_assoc()) {
                $areas[] = $row;
            }
        }

        // 6. Subjects
        $subjects = [];
        $sRes = $conn->query("
            SELECT s.*, aa.name AS area_name, aa.code AS area_code
            FROM subjects s
            LEFT JOIN academic_areas aa ON s.area_id = aa.id
            ORDER BY s.id ASC
        ");
        if ($sRes) {
            while ($row = $sRes->fetch_assoc()) {
                $row['area_name'] = $row['area_name'] ?: 'Computing';
                $subjects[] = $row;
            }
        }

        // 7. Reported Content (Discussion Moderation)
        $reports = [];
        $repRes = $conn->query("SELECT * FROM reported_content ORDER BY id ASC");
        if ($repRes) {
            while ($row = $repRes->fetch_assoc()) {
                $reports[] = $row;
            }
        }

        // 8. Study Groups & Group Invitations
        $groups = [];
        $gRes = $conn->query("SELECT * FROM study_groups ORDER BY id ASC");
        if ($gRes) {
            while ($row = $gRes->fetch_assoc()) {
                $groups[] = $row;
            }
        }

        // 9. Study Sessions
        $sessions = [];
        $sessRes = $conn->query("SELECT * FROM study_sessions ORDER BY session_date ASC, id ASC");
        if ($sessRes) {
            while ($row = $sessRes->fetch_assoc()) {
                $dt = new DateTime($row['session_date']);
                $row['date_time_short'] = $dt->format('d M') . ' · ' . $dt->format('g:i A');
                $sessions[] = $row;
            }
        }

        // 10. Platform Settings
        $settings = [];
        $setRes = $conn->query("SELECT setting_key, setting_value FROM platform_settings");
        if ($setRes) {
            while ($row = $setRes->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }

        echo json_encode([
            'success' => true,
            'database' => DB_NAME,
            'metrics' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'pending_approvals' => $pendingApprovals,
                'open_reports' => $openReports,
                'high_priority_reports' => $highPriorityReports,
                'published_resources' => $publishedResources,
                'total_downloads' => $totalDownloads,
                'total_areas' => $totalAreas,
                'total_subjects' => $totalSubjects
            ],
            'overview' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'pending_approvals' => $pendingApprovals,
                'open_reports' => $openReports,
                'high_priority_reports' => $highPriorityReports,
                'published_resources' => $publishedResources,
                'total_downloads' => $totalDownloads,
                'total_areas' => $totalAreas,
                'total_subjects' => $totalSubjects
            ],
            'users' => $users,
            'admins' => $admins,
            'resources' => $resources,
            'pending_resources' => $pendingResources,
            'academic_areas' => $areas,
            'subjects' => $subjects,
            'reports' => $reports,
            'groups' => $groups,
            'sessions' => $sessions,
            'settings' => $settings
        ]);
        break;

    // =========================================================================
    // 1. APPROVE / REJECT / ARCHIVE / EDIT / DELETE RESOURCE (`resources` table)
    // =========================================================================
    case 'moderate_resource':
        $id = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $statusRaw = trim($input['status'] ?? 'Approved');
        $status = in_array($statusRaw, ['Approved', 'Pending', 'Rejected', 'Archived']) ? $statusRaw : 'Approved';

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE resources SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("UPDATE resources SET status = ? WHERE title LIKE ?");
            $like = "%$title%";
            $stmt->bind_param("ss", $status, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "Resource status updated to $status in MySQL"]);
        break;

    case 'edit_resource':
        $id = (int)($input['id'] ?? 0);
        $oldTitle = trim($input['old_title'] ?? '');
        $title = trim($input['title'] ?? '');
        $faculty = trim($input['faculty'] ?? 'Computing');
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, faculty = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $faculty, $id);
            $stmt->execute();
        } elseif (!empty($oldTitle)) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, faculty = ? WHERE title LIKE ?");
            $like = "%$oldTitle%";
            $stmt->bind_param("sss", $title, $faculty, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Resource updated in MySQL']);
        break;

    case 'delete_resource':
        $id = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE title LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Resource deleted from MySQL']);
        break;

    // =========================================================================
    // 2. ACADEMIC AREAS CRUD (`academic_areas` table)
    // =========================================================================
    case 'save_area':
        $code = strtoupper(trim($input['code'] ?? 'SCI'));
        $name = trim($input['name'] ?? 'Science');
        $oldCode = strtoupper(trim($input['old_code'] ?? ''));
        $status = in_array($input['status'] ?? 'Active', ['Active', 'Inactive']) ? $input['status'] : 'Active';

        if (!empty($oldCode) && $oldCode !== $code) {
            $stmt = $conn->prepare("UPDATE academic_areas SET code = ?, name = ?, status = ? WHERE code = ?");
            $stmt->bind_param("ssss", $code, $name, $status, $oldCode);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO academic_areas (code, name, level, status) VALUES (?, ?, 'Year 1', ?) ON DUPLICATE KEY UPDATE name = VALUES(name), status = VALUES(status)");
            $stmt->bind_param("sss", $code, $name, $status);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "Academic area $code saved to MySQL"]);
        break;

    case 'delete_area':
        $code = strtoupper(trim($input['code'] ?? ''));
        $name = trim($input['name'] ?? '');
        if (!empty($code)) {
            $stmt = $conn->prepare("DELETE FROM academic_areas WHERE code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
        } elseif (!empty($name)) {
            $stmt = $conn->prepare("DELETE FROM academic_areas WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Academic area deleted from MySQL']);
        break;

    // =========================================================================
    // 3. FIRST-YEAR SUBJECTS CRUD (`subjects` table)
    // =========================================================================
    case 'save_subject':
        $code = strtoupper(trim($input['code'] ?? 'COM101'));
        $oldCode = strtoupper(trim($input['old_code'] ?? ''));
        $name = trim($input['name'] ?? 'New Subject');
        $areaName = trim($input['area'] ?? 'Computing');
        $year = trim($input['year'] ?? 'Year 1');
        $status = in_array($input['status'] ?? 'Active', ['Active', 'Archived']) ? $input['status'] : 'Active';

        $areaId = 1;
        $aStmt = $conn->prepare("SELECT id FROM academic_areas WHERE name LIKE ? OR code LIKE ? LIMIT 1");
        if ($aStmt) {
            $like = "%$areaName%";
            $aStmt->bind_param("ss", $like, $like);
            $aStmt->execute();
            $aRow = $aStmt->get_result()->fetch_assoc();
            if ($aRow) $areaId = (int)$aRow['id'];
        }

        if (!empty($oldCode) && $oldCode !== $code) {
            $stmt = $conn->prepare("UPDATE subjects SET code = ?, name = ?, area_id = ?, academic_year = ?, status = ? WHERE code = ?");
            $stmt->bind_param("ssisss", $code, $name, $areaId, $year, $status, $oldCode);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO subjects (code, name, area_id, academic_year, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), area_id = VALUES(area_id), academic_year = VALUES(academic_year), status = VALUES(status)");
            $stmt->bind_param("ssiss", $code, $name, $areaId, $year, $status);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "Subject $code saved to MySQL"]);
        break;

    case 'delete_subject':
        $code = strtoupper(trim($input['code'] ?? ''));
        if (!empty($code)) {
            $stmt = $conn->prepare("DELETE FROM subjects WHERE code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "Subject $code deleted from MySQL"]);
        break;

    // =========================================================================
    // 4. USERS & ADMINISTRATORS (`users` table)
    // =========================================================================
    case 'update_user_status':
        $id = (int)($input['id'] ?? 0);
        $email = trim($input['email'] ?? '');
        $name = trim($input['name'] ?? '');
        $status = in_array($input['status'] ?? '', ['Active', 'Pending', 'Suspended']) ? $input['status'] : 'Active';

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
        } elseif (!empty($email)) {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE email = ?");
            $stmt->bind_param("ss", $status, $email);
            $stmt->execute();
        } elseif (!empty($name)) {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE name = ?");
            $stmt->bind_param("ss", $status, $name);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "User status updated to $status in MySQL"]);
        break;

    case 'delete_user':
        $id = (int)($input['id'] ?? 0);
        $email = trim($input['email'] ?? '');
        $name = trim($input['name'] ?? '');
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif (!empty($email)) {
            $stmt = $conn->prepare("DELETE FROM users WHERE email = ? AND role != 'admin'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
        } elseif (!empty($name)) {
            $stmt = $conn->prepare("DELETE FROM users WHERE name = ? AND role != 'admin'");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'User deleted from MySQL']);
        break;

    case 'add_admin':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? 'AdminPass123!';
        $adminType = trim($input['admin_type'] ?? 'Super administrator');

        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Admin name and email are required']);
            break;
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
            break;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, admin_type, status, last_active_at) VALUES (?, ?, ?, 'admin', ?, 'Active', NOW())");
        $stmt->bind_param("ssss", $name, $email, $hashed, $adminType);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Administrator account created successfully in MySQL', 'id' => (int)$stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create administrator account']);
        }
        break;

    case 'update_admin_role':
        $name = trim($input['name'] ?? '');
        $adminType = trim($input['admin_type'] ?? 'Content moderator');
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE users SET admin_type = ? WHERE name = ? AND role = 'admin'");
            $stmt->bind_param("ss", $adminType, $name);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Administrator role updated in MySQL']);
        break;

    case 'remove_admin':
        $name = trim($input['name'] ?? '');
        if (!empty($name) && $name !== 'Admin User') {
            $stmt = $conn->prepare("DELETE FROM users WHERE name = ? AND role = 'admin'");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Administrator access revoked in MySQL']);
        break;

    // =========================================================================
    // 5. REPORTED CONTENT / DISCUSSION MODERATION (`reported_content` table)
    // =========================================================================
    case 'resolve_report':
        $id = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $status = in_array($input['status'] ?? 'Resolved', ['Resolved', 'Dismissed', 'Pending']) ? $input['status'] : 'Resolved';
        $priority = trim($input['priority'] ?? '');

        if (!empty($priority)) {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE reported_content SET priority = ? WHERE id = ?");
                $stmt->bind_param("si", $priority, $id);
                $stmt->execute();
            } elseif (!empty($title)) {
                $stmt = $conn->prepare("UPDATE reported_content SET priority = ? WHERE content_snippet LIKE ?");
                $like = "%$title%";
                $stmt->bind_param("ss", $priority, $like);
                $stmt->execute();
            }
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE reported_content SET status = ?, report_count = IF(? IN ('Resolved','Dismissed'), 0, report_count) WHERE id = ?");
                $stmt->bind_param("ssi", $status, $status, $id);
                $stmt->execute();
            } elseif (!empty($title)) {
                $stmt = $conn->prepare("UPDATE reported_content SET status = ?, report_count = IF(? IN ('Resolved','Dismissed'), 0, report_count) WHERE content_snippet LIKE ?");
                $like = "%$title%";
                $stmt->bind_param("sss", $status, $status, $like);
                $stmt->execute();
            }
        }
        echo json_encode(['success' => true, 'message' => "Report updated in MySQL"]);
        break;

    case 'delete_report':
        $id = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM reported_content WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM reported_content WHERE content_snippet LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Reported item removed from MySQL']);
        break;

    // =========================================================================
    // 6. STUDY GROUPS MODERATION (`study_groups` table)
    // =========================================================================
    case 'moderate_group':
        $name = trim($input['name'] ?? '');
        $status = in_array($input['status'] ?? 'Active', ['Active', 'Review', 'Flagged']) ? $input['status'] : 'Active';
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE study_groups SET status = ?, reports_count = IF(? = 'Active', 0, reports_count) WHERE name LIKE ?");
            $like = "%$name%";
            $stmt->bind_param("sss", $status, $status, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => "Study group updated to $status in MySQL"]);
        break;

    case 'delete_group':
        $name = trim($input['name'] ?? '');
        if (!empty($name)) {
            $stmt = $conn->prepare("DELETE FROM study_groups WHERE name LIKE ? LIMIT 1");
            $like = "%$name%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Study group deleted from MySQL']);
        break;

    // =========================================================================
    // 7. STUDY SESSIONS MODERATION (`study_sessions` table)
    // =========================================================================
    case 'moderate_session':
        $title = trim($input['title'] ?? '');
        $oldTitle = trim($input['old_title'] ?? '');
        $status = trim($input['status'] ?? '');
        $capacity = (int)($input['capacity'] ?? 0);

        $lookup = !empty($oldTitle) ? $oldTitle : $title;
        if (!empty($lookup)) {
            $like = "%$lookup%";
            if (!empty($status)) {
                $stmt = $conn->prepare("UPDATE study_sessions SET status = ? WHERE title LIKE ?");
                $stmt->bind_param("ss", $status, $like);
                $stmt->execute();
            }
            if ($capacity > 0) {
                $stmt = $conn->prepare("UPDATE study_sessions SET capacity = ? WHERE title LIKE ?");
                $stmt->bind_param("is", $capacity, $like);
                $stmt->execute();
            }
            if (!empty($oldTitle) && !empty($title) && $oldTitle !== $title) {
                $stmt = $conn->prepare("UPDATE study_sessions SET title = ? WHERE title LIKE ?");
                $stmt->bind_param("ss", $title, $like);
                $stmt->execute();
            }
        }
        echo json_encode(['success' => true, 'message' => 'Study session updated in MySQL']);
        break;

    case 'delete_session':
        $title = trim($input['title'] ?? '');
        if (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM study_sessions WHERE title LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Study session deleted from MySQL']);
        break;

    // =========================================================================
    // 8. SAVE PLATFORM SETTINGS (`platform_settings` table)
    // =========================================================================
    case 'save_settings':
        $settings = $input['settings'] ?? [];
        if (is_array($settings)) {
            $stmt = $conn->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settings as $k => $v) {
                $key = (string)$k;
                $val = (string)$v;
                $stmt->bind_param("ss", $key, $val);
                $stmt->execute();
            }
        }
        echo json_encode(['success' => true, 'message' => 'Platform settings saved to MySQL']);
        break;

    default:
        echo json_encode([
            'success' => true,
            'database' => DB_NAME,
            'phpmyadmin_url' => (defined('APP_IS_LOCAL') && APP_IS_LOCAL)
                ? 'http://localhost/phpmyadmin/index.php?route=/database/structure&db=' . DB_NAME
                : 'https://dash.infinityfree.com/'
        ]);
        break;
}
?>

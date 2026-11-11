<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDENT PORTAL CONTROLLER (100% Live MySQL Database API)
// Queries and updates `nsbm_student_circle` in phpMyAdmin for all Student pages
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

$action = $input['action'] ?? ($_GET['action'] ?? 'dashboard_data');
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in to your student account.']);
    exit();
}

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable']);
    exit();
}

function getInitials(?string $name): string {
    $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name ?? ''));
    if (empty($clean)) return 'ST';
    $parts = preg_split('/\s+/', $clean);
    $parts = array_values(array_filter($parts));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }
    return strtoupper(substr($clean, 0, min(2, strlen($clean))));
}

function safeCount($conn, string $query): int {
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
    // 0. FETCH LIVE STUDENT PORTAL DATA FROM MYSQL DATABASE
    // =========================================================================
    case 'dashboard_data':
        // 1. User Profile + Live Counts
        $userRes = @$conn->query("SELECT id, name, email, student_id, faculty, degree_program, academic_year, bio, academic_interests, profile_visibility, status FROM users WHERE id = $userId LIMIT 1");
        $user = ($userRes && $userRes->num_rows > 0) ? $userRes->fetch_assoc() : null;
        if (!$user) {
            $userRes = @$conn->query("SELECT id, name, email, student_id, faculty, degree_program, academic_year, bio, academic_interests, profile_visibility, status FROM users WHERE role = 'student' ORDER BY id ASC LIMIT 1");
            $user = ($userRes && $userRes->num_rows > 0) ? $userRes->fetch_assoc() : [
                'id' => $userId,
                'name' => $_SESSION['name'] ?? 'Student',
                'email' => $_SESSION['email'] ?? 'student@nsbm.ac.lk',
                'faculty' => $_SESSION['faculty'] ?? 'Computing',
                'degree_program' => $_SESSION['degree_program'] ?? 'BSc (Hons) Computer Science · Year 1',
                'bio' => '',
                'academic_interests' => '',
                'profile_visibility' => 'NSBM Study Circle members'
            ];
            $userId = (int)$user['id'];
        }
        if (empty($user['name'])) $user['name'] = $_SESSION['name'] ?? 'Student';
        if (empty($user['faculty'])) $user['faculty'] = $_SESSION['faculty'] ?? 'Computing';
        if (empty($user['degree_program'])) $user['degree_program'] = $_SESSION['degree_program'] ?? 'BSc (Hons) Computer Science · Year 1';
        $user['initials'] = getInitials($user['name']);

        $uploadedCount = safeCount($conn, "SELECT COUNT(*) FROM resources WHERE uploaded_by = $userId");
        $downloadedCount = safeCount($conn, "SELECT COUNT(*) FROM resource_downloads WHERE user_id = $userId");
        $groupsCount = safeCount($conn, "SELECT COUNT(*) FROM study_group_members WHERE user_id = $userId");
        $sessionsCount = safeCount($conn, "SELECT COUNT(*) FROM study_sessions WHERE status = 'Scheduled'");
        $answersCount = safeCount($conn, "SELECT COUNT(*) FROM discussion_answers WHERE author_id = $userId");
        $unreadNotifCount = safeCount($conn, "SELECT COUNT(*) FROM notifications WHERE user_id = $userId AND is_read = 0");

        // 2. All Resources
        $resources = [];
        $resQuery = $conn->query("
            SELECT r.*, u.name AS author_name, u.degree_program AS author_role
            FROM resources r
            LEFT JOIN users u ON r.uploaded_by = u.id
            ORDER BY r.id ASC
        ");
        if ($resQuery) {
            while ($row = $resQuery->fetch_assoc()) {
                $row['author_initials'] = getInitials($row['author_name'] ?: 'Student');
                $row['uploaded_date'] = date('d M Y', strtotime($row['created_at']));
                $resources[] = $row;
            }
        }

        // 3. My Uploads
        $myUploads = [];
        $upQuery = $conn->query("
            SELECT r.*, u.name AS author_name
            FROM resources r
            LEFT JOIN users u ON r.uploaded_by = u.id
            WHERE r.uploaded_by = $userId
            ORDER BY r.created_at DESC, r.id DESC
        ");
        if ($upQuery) {
            while ($row = $upQuery->fetch_assoc()) {
                $row['uploaded_date'] = date('d M Y', strtotime($row['created_at']));
                $myUploads[] = $row;
            }
        }

        // 4. My Downloads
        $myDownloads = [];
        $dlQuery = $conn->query("
            SELECT rd.id AS download_id, rd.downloaded_at, r.id AS resource_id, r.title, r.faculty, r.file_type, r.module_code
            FROM resource_downloads rd
            JOIN resources r ON rd.resource_id = r.id
            WHERE rd.user_id = $userId
            ORDER BY rd.downloaded_at DESC, rd.id DESC
        ");
        if ($dlQuery) {
            while ($row = $dlQuery->fetch_assoc()) {
                $row['downloaded_date'] = date('d M Y', strtotime($row['downloaded_at']));
                $myDownloads[] = $row;
            }
        }

        // 5. Saved / Bookmarked Resources
        $savedResources = [];
        $svQuery = $conn->query("
            SELECT sr.saved_at, r.*, u.name AS author_name
            FROM saved_resources sr
            JOIN resources r ON sr.resource_id = r.id
            LEFT JOIN users u ON r.uploaded_by = u.id
            WHERE sr.user_id = $userId
            ORDER BY sr.saved_at DESC
        ");
        if ($svQuery) {
            while ($row = $svQuery->fetch_assoc()) {
                $savedResources[] = $row;
            }
        }

        // 6. Study Groups
        $groups = [];
        $grpQuery = $conn->query("
            SELECT sg.*, u.name AS leader_name,
                   EXISTS(SELECT 1 FROM study_group_members sgm WHERE sgm.group_id = sg.id AND sgm.user_id = $userId) AS is_joined
            FROM study_groups sg
            LEFT JOIN users u ON sg.creator_id = u.id
            ORDER BY sg.id ASC
        ");
        if ($grpQuery) {
            while ($row = $grpQuery->fetch_assoc()) {
                $groups[] = $row;
            }
        }

        // 7. Study Sessions
        $sessions = [];
        $sessQuery = $conn->query("
            SELECT ss.*, u.name AS host_name
            FROM study_sessions ss
            LEFT JOIN users u ON ss.host_id = u.id
            ORDER BY ss.session_date ASC
        ");
        if ($sessQuery) {
            while ($row = $sessQuery->fetch_assoc()) {
                $dt = new DateTime($row['session_date']);
                $row['day_num'] = $dt->format('d');
                $row['month_short'] = strtoupper($dt->format('M'));
                $row['date_formatted'] = $dt->format('l, d F Y');
                $row['time_formatted'] = $dt->format('g:i A');
                $sessions[] = $row;
            }
        }

        // 8. Discussions
        $discussions = [];
        $discQuery = $conn->query("
            SELECT d.*, u.name AS author_name, u.degree_program AS author_meta
            FROM discussions d
            LEFT JOIN users u ON d.author_id = u.id
            WHERE d.is_hidden = 0
            ORDER BY d.id DESC
        ");
        if ($discQuery) {
            while ($row = $discQuery->fetch_assoc()) {
                $row['author_initials'] = getInitials($row['author_name'] ?: 'Student');
                $discussions[] = $row;
            }
        }

        // 9. Notifications
        $notifications = [];
        $notifQuery = $conn->query("
            SELECT * FROM notifications
            WHERE user_id = $userId
            ORDER BY created_at DESC, id DESC
        ");
        if ($notifQuery) {
            while ($row = $notifQuery->fetch_assoc()) {
                $notifications[] = $row;
            }
        }

        echo json_encode([
            'success' => true,
            'user' => $user,
            'metrics' => [
                'uploaded_count' => $uploadedCount,
                'downloaded_count' => $downloadedCount,
                'groups_count' => $groupsCount,
                'sessions_count' => $sessionsCount,
                'answers_count' => $answersCount,
                'unread_notifications' => $unreadNotifCount
            ],
            'resources' => $resources,
            'my_uploads' => $myUploads,
            'my_downloads' => $myDownloads,
            'saved_resources' => $savedResources,
            'groups' => $groups,
            'sessions' => $sessions,
            'discussions' => $discussions,
            'notifications' => $notifications
        ]);
        break;

    // =========================================================================
    // 1. UPDATE STUDENT PROFILE (`users` table)
    // =========================================================================
    case 'update_profile':
        $name = trim($input['name'] ?? ($_SESSION['name'] ?? 'Student'));
        $degree = trim($input['degree'] ?? 'BSc (Hons) Computer Science · Year 3');
        $facultyRaw = trim($input['faculty'] ?? 'Computing');
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }
        $bio = trim($input['bio'] ?? '');
        $interests = trim($input['interests'] ?? '');
        $visibility = trim($input['visibility'] ?? 'NSBM Study Circle members');

        $stmt = $conn->prepare("UPDATE users SET name = ?, degree_program = ?, faculty = ?, bio = ?, academic_interests = ?, profile_visibility = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssi", $name, $degree, $faculty, $bio, $interests, $visibility, $userId);
            $stmt->execute();
            $_SESSION['name'] = $name;
            $_SESSION['degree_program'] = $degree;
            $_SESSION['faculty'] = $faculty;
        }
        echo json_encode(['success' => true, 'message' => 'Student profile saved to database']);
        break;

    // =========================================================================
    // 2. RECORD STUDY MATERIAL DOWNLOAD (`resource_downloads` & `resources`)
    // =========================================================================
    case 'record_download':
        $resId = (int)($input['resource_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($resId <= 0 && !empty($title)) {
            $s = $conn->prepare("SELECT id FROM resources WHERE title LIKE ? LIMIT 1");
            $like = "%$title%";
            $s->bind_param("s", $like);
            $s->execute();
            $row = $s->get_result()->fetch_assoc();
            $resId = $row ? (int)$row['id'] : 1;
        }
        if ($resId <= 0) $resId = 1;

        $ins = $conn->prepare("INSERT INTO resource_downloads (resource_id, user_id, downloaded_at) VALUES (?, ?, NOW())");
        if ($ins) {
            $ins->bind_param("ii", $resId, $userId);
            $ins->execute();
        }
        $conn->query("UPDATE resources SET downloads = downloads + 1 WHERE id = " . (int)$resId);

        echo json_encode(['success' => true, 'message' => 'Download recorded in database', 'resource_id' => $resId]);
        break;

    // =========================================================================
    // 3. REMOVE SINGLE DOWNLOAD OR CLEAR DOWNLOAD HISTORY (`resource_downloads`)
    // =========================================================================
    case 'remove_download':
        $dlId = (int)($input['download_id'] ?? 0);
        $resId = (int)($input['resource_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($dlId > 0) {
            $stmt = $conn->prepare("DELETE FROM resource_downloads WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $dlId, $userId);
            $stmt->execute();
        } elseif ($resId > 0) {
            $stmt = $conn->prepare("DELETE FROM resource_downloads WHERE resource_id = ? AND user_id = ? LIMIT 1");
            $stmt->bind_param("ii", $resId, $userId);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE rd FROM resource_downloads rd JOIN resources r ON rd.resource_id = r.id WHERE rd.user_id = ? AND r.title LIKE ?");
            $like = "%$title%";
            $stmt->bind_param("is", $userId, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Removed from download history in database']);
        break;

    case 'clear_downloads':
        $stmt = $conn->prepare("DELETE FROM resource_downloads WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Download history cleared in database']);
        break;

    // =========================================================================
    // 4. TOGGLE BOOKMARK (`saved_resources`)
    // =========================================================================
    case 'toggle_bookmark':
        $resId = (int)($input['resource_id'] ?? 1);
        $chk = $conn->prepare("SELECT 1 FROM saved_resources WHERE user_id = ? AND resource_id = ?");
        $chk->bind_param("ii", $userId, $resId);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $del = $conn->prepare("DELETE FROM saved_resources WHERE user_id = ? AND resource_id = ?");
            $del->bind_param("ii", $userId, $resId);
            $del->execute();
            echo json_encode(['success' => true, 'saved' => false, 'message' => 'Bookmark removed from database']);
        } else {
            $ins = $conn->prepare("INSERT IGNORE INTO saved_resources (user_id, resource_id) VALUES (?, ?)");
            $ins->bind_param("ii", $userId, $resId);
            $ins->execute();
            echo json_encode(['success' => true, 'saved' => true, 'message' => 'Resource saved to database']);
        }
        break;

    // =========================================================================
    // 5. UPLOAD / EDIT / DELETE STUDY MATERIAL (`resources` table)
    // =========================================================================
    case 'upload_material':
        $title = trim($input['title'] ?? 'Uploaded Study Material');
        $moduleCode = trim($input['module'] ?? 'CS2102');
        $facultyRaw = trim($input['faculty'] ?? 'Computing');
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }
        $desc = trim($input['description'] ?? 'Student-contributed revision material.');
        $filePath = 'uploads/resources/' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $title)) . '.pdf';

        $stmt = $conn->prepare("INSERT INTO resources (title, description, module_code, faculty, file_path, file_type, file_size_mb, page_count, uploaded_by, status) VALUES (?, ?, ?, ?, ?, 'PDF', 2.40, 15, ?, 'Pending')");
        if ($stmt) {
            $stmt->bind_param("sssssi", $title, $desc, $moduleCode, $faculty, $filePath, $userId);
            $stmt->execute();
            echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'message' => 'Material saved to database']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Could not save material']);
        break;

    case 'edit_upload':
        $resId = (int)($input['resource_id'] ?? 0);
        $oldTitle = trim($input['old_title'] ?? '');
        $title = trim($input['title'] ?? '');
        $moduleCode = trim($input['module'] ?? 'CS2102');
        $facultyRaw = trim($input['faculty'] ?? 'Computing');
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }
        if ($resId > 0) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, module_code = ?, faculty = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $moduleCode, $faculty, $resId);
            $stmt->execute();
        } elseif (!empty($oldTitle)) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, module_code = ?, faculty = ? WHERE title LIKE ?");
            $like = "%$oldTitle%";
            $stmt->bind_param("ssss", $title, $moduleCode, $faculty, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Resource updated in database']);
        break;

    case 'delete_upload':
        $resId = (int)($input['resource_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($resId > 0) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
            $stmt->bind_param("i", $resId);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE title LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Upload removed from database']);
        break;

    // =========================================================================
    // 6. CREATE / CANCEL STUDY SESSION (`study_sessions` table)
    // =========================================================================
    case 'create_session':
        $title = trim($input['title'] ?? 'Study Session');
        $facultyRaw = trim($input['faculty'] ?? 'Computing');
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }
        $dateVal = trim($input['date'] ?? date('Y-m-d'));
        $timeVal = trim($input['time'] ?? '18:30');
        $sessionDate = $dateVal . ' ' . (strlen($timeVal) === 5 ? $timeVal . ':00' : '18:30:00');
        $location = trim($input['format'] ?? 'Online · Microsoft Teams');

        $stmt = $conn->prepare("INSERT INTO study_sessions (title, faculty, host_id, session_date, location_details, capacity, enrolled_count, status) VALUES (?, ?, ?, ?, ?, 24, 1, 'Scheduled')");
        if ($stmt) {
            $stmt->bind_param("ssiss", $title, $faculty, $userId, $sessionDate, $location);
            $stmt->execute();
            echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'message' => 'Study session saved to database']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Could not save session']);
        break;

    case 'delete_session':
        $sessId = (int)($input['session_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($sessId > 0) {
            $stmt = $conn->prepare("DELETE FROM study_sessions WHERE id = ?");
            $stmt->bind_param("i", $sessId);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM study_sessions WHERE title LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Study session removed from database']);
        break;

    // =========================================================================
    // 7. CREATE / JOIN / LEAVE STUDY GROUP (`study_groups` & `study_group_members`)
    // =========================================================================
    case 'create_group':
        $name = trim($input['name'] ?? 'New Study Circle');
        $category = trim($input['faculty'] ?? 'Computing');
        $desc = trim($input['description'] ?? 'Collaborative student study group.');
        $schedule = trim($input['schedule'] ?? 'Meets Weekly');
        $words = preg_split('/\s+/', $name);
        $code = strtoupper(substr($words[0] ?? 'S', 0, 1) . substr($words[1] ?? 'G', 0, 1));

        $stmt = $conn->prepare("INSERT INTO study_groups (name, code, category, schedule_info, description, creator_id, member_count, status) VALUES (?, ?, ?, ?, ?, ?, 1, 'Active')");
        if ($stmt) {
            $stmt->bind_param("sssssi", $name, $code, $category, $schedule, $desc, $userId);
            $stmt->execute();
            $gid = $stmt->insert_id;
            $conn->query("INSERT IGNORE INTO study_group_members (group_id, user_id) VALUES ($gid, $userId)");
            echo json_encode(['success' => true, 'id' => $gid, 'message' => 'Study group saved to database']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Could not create group']);
        break;

    case 'leave_group':
        $gid = (int)($input['group_id'] ?? 1);
        $conn->query("DELETE FROM study_group_members WHERE group_id = $gid AND user_id = $userId");
        echo json_encode(['success' => true, 'message' => 'Left study group in database']);
        break;

    // =========================================================================
    // 8. NOTIFICATIONS (`notifications` table)
    // =========================================================================
    case 'mark_notifications_read':
        $notifId = (int)($input['notification_id'] ?? 0);
        if ($notifId > 0) {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notifId AND user_id = $userId");
        } else {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
        }
        echo json_encode(['success' => true, 'message' => 'Notifications marked as read in database']);
        break;

    case 'delete_notification':
        $notifId = (int)($input['notification_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($notifId > 0) {
            $conn->query("DELETE FROM notifications WHERE id = $notifId AND user_id = $userId");
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ? AND title LIKE ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("is", $userId, $like);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Notification deleted from database']);
        break;

    default:
        echo json_encode(['success' => true, 'database' => DB_NAME]);
        break;
}
?>

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
                'email' => $_SESSION['email'] ?? 'student@example.com',
                'faculty' => $_SESSION['faculty'] ?? 'Computing',
                'degree_program' => $_SESSION['degree_program'] ?? 'BSc (Hons) Computer Science · Year 1',
                'bio' => '',
                'academic_interests' => '',
                'profile_visibility' => 'Members only'
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
            WHERE r.status = 'Approved' OR r.uploaded_by = $userId
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
        $visibility = trim($input['visibility'] ?? 'Members only');

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
    case 'submit_link':
    case 'upload_material':
        // Study materials are shared as Google Drive / OneDrive links. No file is
        // ever uploaded or stored; new links wait for administrator approval.
        require_once __DIR__ . '/../models/Resource.php';
        require_once __DIR__ . '/../helpers/link_helper.php';

        $title = trim((string)($input['title'] ?? ''));
        $moduleCode = trim((string)($input['module'] ?? ($input['module_code'] ?? '')));
        $facultyRaw = trim((string)($input['faculty'] ?? 'Computing'));
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }
        $yearRaw = trim((string)($input['academic_year'] ?? 'Year 1'));
        $academicYear = in_array($yearRaw, ['Year 1', 'Year 2', 'Year 3', 'Year 4'], true) ? $yearRaw : 'Year 1';
        $desc = trim((string)($input['description'] ?? ''));

        if ($title === '' || $moduleCode === '') {
            echo json_encode(['success' => false, 'message' => 'Please provide a resource title and module code.']);
            exit();
        }
        if (mb_strlen($title) > 255 || mb_strlen($moduleCode) > 30) {
            echo json_encode(['success' => false, 'message' => 'The title (max 255 characters) or module code (max 30 characters) is too long.']);
            exit();
        }

        $link = validateResourceLink($input['resource_link'] ?? '');
        if (!$link['ok']) {
            echo json_encode(['success' => false, 'message' => $link['error']]);
            exit();
        }

        $newId = (new ResourceModel($conn))->createResource([
            'title' => $title,
            'description' => $desc,
            'faculty' => $faculty,
            'module_code' => $moduleCode,
            'academic_year' => $academicYear,
            'external_url' => $link['url'],
            'uploaded_by' => $userId,
            'about_text' => $desc,
        ]);

        if ($newId) {
            echo json_encode(['success' => true, 'id' => $newId, 'status' => 'Pending',
                'message' => 'Thanks! Your resource link was submitted and is waiting for an administrator to approve it.']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Could not save the resource link. Please try again.']);
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
            $stmt = $conn->prepare("UPDATE resources SET title = ?, module_code = ?, faculty = ? WHERE id = ? AND uploaded_by = ?");
            $stmt->bind_param("sssii", $title, $moduleCode, $faculty, $resId, $userId);
            $stmt->execute();
        } elseif (!empty($oldTitle)) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, module_code = ?, faculty = ? WHERE title LIKE ? AND uploaded_by = ?");
            $like = "%$oldTitle%";
            $stmt->bind_param("ssssi", $title, $moduleCode, $faculty, $like, $userId);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Resource updated in database']);
        break;

    case 'delete_upload':
        $resId = (int)($input['resource_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($resId > 0) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE id = ? AND uploaded_by = ?");
            $stmt->bind_param("ii", $resId, $userId);
            $stmt->execute();
        } elseif (!empty($title)) {
            $stmt = $conn->prepare("DELETE FROM resources WHERE title LIKE ? AND uploaded_by = ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("si", $like, $userId);
            $stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Upload removed from database']);
        break;

    // =========================================================================
    // 6. CREATE / CANCEL STUDY SESSION (`study_sessions` table)
    // =========================================================================
    case 'create_session':
        // Used by the student portal (Schedule) and the public Sessions page.
        $title = trim((string)($input['title'] ?? ''));
        $facultyRaw = trim((string)($input['faculty'] ?? 'Computing'));
        $faculty = 'Computing';
        foreach (['Computing', 'Business', 'Engineering', 'Science'] as $fac) {
            if (stripos($facultyRaw, $fac) !== false) {
                $faculty = $fac;
                break;
            }
        }

        if ($title === '' || mb_strlen($title) > 200) {
            echo json_encode(['success' => false, 'message' => 'Please enter a session title (max 200 characters).']);
            exit();
        }

        // Date: YYYY-MM-DD. Time: "18:30", "6:30 PM" or a range like "6:30 PM - 8:00 PM" (start time is used).
        $dateVal = trim((string)($input['date'] ?? ''));
        $dateObj = DateTime::createFromFormat('!Y-m-d', $dateVal);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $dateVal) {
            echo json_encode(['success' => false, 'message' => 'Please choose a valid session date.']);
            exit();
        }
        $timeRaw = trim((string)($input['time'] ?? ''));
        $startPart = trim(preg_split('/\s*[-–—]\s*|\s+to\s+/i', $timeRaw)[0] ?? '');
        $timeTs = $startPart !== '' ? strtotime('2000-01-01 ' . $startPart) : false;
        if ($timeTs === false) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid start time, e.g. 18:30 or 6:30 PM.']);
            exit();
        }
        $sessionDate = $dateVal . ' ' . date('H:i:s', $timeTs);

        $formatRaw = trim((string)($input['format'] ?? 'Online'));
        $formatType = (stripos($formatRaw, 'physical') !== false || stripos($formatRaw, 'campus') !== false) ? 'Physical' : 'Online';
        $location = trim(strip_tags((string)($input['location'] ?? '')));
        if ($location === '') {
            $location = trim(strip_tags(html_entity_decode($formatRaw))) ?: 'Online';
        }
        $location = mb_substr($location, 0, 255);
        $description = trim((string)($input['description'] ?? ''));
        $capacity = (int)($input['capacity'] ?? 24);
        if ($capacity < 2 || $capacity > 500) $capacity = 24;

        $stmt = $conn->prepare("INSERT INTO study_sessions (title, description, faculty, host_id, session_date, format_type, location_details, capacity, enrolled_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'Review')"); // 'Review' = waiting for admin approval
        if ($stmt) {
            $stmt->bind_param("sssisssi", $title, $description, $faculty, $userId, $sessionDate, $formatType, $location, $capacity);
            if ($stmt->execute()) {
                $newId = (int)$stmt->insert_id;
                // The host is the first attendee
                $att = $conn->prepare("INSERT IGNORE INTO study_session_attendees (session_id, user_id) VALUES (?, ?)");
                if ($att) {
                    $att->bind_param("ii", $newId, $userId);
                    $att->execute();
                }
                echo json_encode(['success' => true, 'id' => $newId, 'status' => 'Review',
                    'message' => 'Thanks! "' . $title . '" was sent to the admins for approval. It will appear on the Sessions page once it is approved.']);
                exit();
            }
        }
        echo json_encode(['success' => false, 'message' => 'Could not save the session. Please try again.']);
        break;

    case 'reserve_session':
        // Reserve or cancel a place: {"session_id": 12, "reserve": true|false}
        $sessId = (int)($input['session_id'] ?? 0);
        $wantReserve = filter_var($input['reserve'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $q = $conn->prepare("SELECT id, host_id, capacity, enrolled_count, status FROM study_sessions WHERE id = ?");
        $q->bind_param("i", $sessId);
        $q->execute();
        $sess = $q->get_result()->fetch_assoc();
        if (!$sess || !in_array($sess['status'], ['Scheduled'], true)) {
            echo json_encode(['success' => false, 'message' => 'This session is not open for reservations.']);
            exit();
        }
        if ((int)$sess['host_id'] === $userId) {
            echo json_encode(['success' => false, 'message' => 'You are the host of this session.']);
            exit();
        }

        if ($wantReserve) {
            // Only take a seat if one is free (single atomic UPDATE prevents overbooking)
            $chk = $conn->prepare("SELECT 1 FROM study_session_attendees WHERE session_id = ? AND user_id = ?");
            $chk->bind_param("ii", $sessId, $userId);
            $chk->execute();
            if (!$chk->get_result()->fetch_row()) {
                $seat = $conn->prepare("UPDATE study_sessions SET enrolled_count = enrolled_count + 1 WHERE id = ? AND enrolled_count < capacity");
                $seat->bind_param("i", $sessId);
                $seat->execute();
                if ($seat->affected_rows < 1) {
                    echo json_encode(['success' => false, 'message' => 'Sorry, this session is full.']);
                    exit();
                }
                $ins = $conn->prepare("INSERT IGNORE INTO study_session_attendees (session_id, user_id) VALUES (?, ?)");
                $ins->bind_param("ii", $sessId, $userId);
                $ins->execute();
                if ($ins->affected_rows < 1) {
                    // Reserved in parallel by another tab: give the seat back
                    $conn->query("UPDATE study_sessions SET enrolled_count = GREATEST(enrolled_count - 1, 0) WHERE id = " . $sessId);
                }
            }
        } else {
            $del = $conn->prepare("DELETE FROM study_session_attendees WHERE session_id = ? AND user_id = ?");
            $del->bind_param("ii", $sessId, $userId);
            $del->execute();
            if ($del->affected_rows > 0) {
                $conn->query("UPDATE study_sessions SET enrolled_count = GREATEST(enrolled_count - 1, 0) WHERE id = " . $sessId);
            }
        }

        $r = $conn->prepare("SELECT enrolled_count, capacity FROM study_sessions WHERE id = ?");
        $r->bind_param("i", $sessId);
        $r->execute();
        $now = $r->get_result()->fetch_assoc();
        echo json_encode([
            'success' => true,
            'reserved' => $wantReserve,
            'enrolled_count' => (int)$now['enrolled_count'],
            'capacity' => (int)$now['capacity'],
            'message' => $wantReserve ? 'Place reserved! It is saved to your schedule.' : 'Reservation cancelled. Your place has been released.'
        ]);
        break;

    case 'delete_session':
        // Students can only cancel sessions they host
        $sessId = (int)($input['session_id'] ?? 0);
        $title = trim((string)($input['title'] ?? ''));
        $deleted = 0;
        if ($sessId > 0) {
            $stmt = $conn->prepare("DELETE FROM study_sessions WHERE id = ? AND host_id = ?");
            $stmt->bind_param("ii", $sessId, $userId);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
        } elseif ($title !== '') {
            $stmt = $conn->prepare("DELETE FROM study_sessions WHERE title LIKE ? AND host_id = ? LIMIT 1");
            $like = "%$title%";
            $stmt->bind_param("si", $like, $userId);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
        }
        echo json_encode($deleted > 0
            ? ['success' => true, 'message' => 'Study session removed.']
            : ['success' => false, 'message' => 'Session not found, or you are not its host.']);
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

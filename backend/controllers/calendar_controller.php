<?php
// ============================================================================
// NSBM STUDY CIRCLE - REAL-TIME CALENDAR API CONTROLLER
// Supplies dynamic calendar data, live session dates, and day-specific events
// for both the Public Website (frontend/sessions.php) and Student Dashboard.
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

$action = $_GET['action'] ?? ($_POST['action'] ?? 'get_events');
$todayDate = date('Y-m-d');
$nowYear = (int)date('Y');
$nowMonth = (int)date('m');

// Helper to determine faculty theme class
function getFacultyMeta($faculty) {
    $fac = strtolower($faculty ?: 'computing');
    if (strpos($fac, 'bus') !== false) {
        return ['name' => 'Business', 'color' => '#10B981', 'badge' => 'student-badge-business', 'chip' => 'chip-green'];
    } elseif (strpos($fac, 'eng') !== false) {
        return ['name' => 'Engineering', 'color' => '#F59E0B', 'badge' => 'student-badge-engineering', 'chip' => 'chip-amber'];
    } elseif (strpos($fac, 'sci') !== false) {
        return ['name' => 'Science', 'color' => '#8B5CF6', 'badge' => 'student-badge-science', 'chip' => 'chip-purple'];
    }
    return ['name' => 'Computing', 'color' => '#2563EB', 'badge' => 'student-badge-computing', 'chip' => 'chip-blue'];
}

// ----------------------------------------------------------------------------
// 1. ACTION: GET MONTH EVENTS (Interactive Monthly Calendar)
// ----------------------------------------------------------------------------
if ($action === 'get_events' || $action === 'get_month_events') {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : $nowYear;
    $month = isset($_GET['month']) ? (int)$_GET['month'] : $nowMonth;
    $facultyFilter = trim($_GET['faculty'] ?? 'all');

    if ($month < 1) { $month = 12; $year--; }
    if ($month > 12) { $month = 1; $year++; }

    $monthPadded = sprintf('%02d', $month);
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $daysInMonth = (int)date('t', strtotime("$year-$monthPadded-01"));
    $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $daysInMonth);
    $monthName = date('F', strtotime("$year-$monthPadded-01"));

    $eventsByDate = [];
    $allEvents = [];

    global $conn;
    if (isset($conn) && $conn && !$conn->connect_error) {
        try {
            $sql = "
                SELECT ss.id, ss.title, ss.description, ss.module_code, ss.faculty, 
                       ss.session_date, ss.duration_mins, ss.format_type, ss.location_details, 
                       ss.capacity, ss.enrolled_count, ss.status,
                       COALESCE(u.name, 'NSBM Student Leader') AS host_name
                FROM study_sessions ss
                LEFT JOIN users u ON ss.host_id = u.id
                WHERE ss.session_date BETWEEN ? AND ?
                  AND ss.status IN ('Scheduled', 'Completed')
            ";

            if ($facultyFilter !== 'all' && !empty($facultyFilter)) {
                $sql .= " AND ss.faculty = ?";
                $stmt = $conn->prepare($sql . " ORDER BY ss.session_date ASC");
                $stmt->bind_param("sss", $startDate, $endDate, $facultyFilter);
            } else {
                $stmt = $conn->prepare($sql . " ORDER BY ss.session_date ASC");
                $stmt->bind_param("ss", $startDate, $endDate);
            }

            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $dt = new DateTime($row['session_date']);
                    $dateKey = $dt->format('Y-m-d');
                    $facMeta = getFacultyMeta($row['faculty']);

                    $eventItem = [
                        'id' => (int)$row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'] ?: 'Collaborative academic study and problem solving session.',
                        'module_code' => $row['module_code'] ?: 'GEN100',
                        'faculty' => $row['faculty'] ?: 'Computing',
                        'faculty_meta' => $facMeta,
                        'session_date' => $row['session_date'],
                        'date_key' => $dateKey,
                        'day' => (int)$dt->format('j'),
                        'time' => $dt->format('g:i A'),
                        'duration' => ($row['duration_mins'] ?: 90) . ' mins',
                        'location' => $row['location_details'] ?: 'Online · Microsoft Teams',
                        'format' => $row['format_type'] ?: 'Online',
                        'host_name' => $row['host_name'],
                        'enrolled_count' => (int)($row['enrolled_count'] ?: 0),
                        'capacity' => (int)($row['capacity'] ?: 24),
                        'status' => $row['status']
                    ];

                    if (!isset($eventsByDate[$dateKey])) {
                        $eventsByDate[$dateKey] = [];
                    }
                    $eventsByDate[$dateKey][] = $eventItem;
                    $allEvents[] = $eventItem;
                }
                $stmt->close();
            }
        } catch (Throwable $e) {}
    }

    echo json_encode([
        'success' => true,
        'year' => $year,
        'month' => $month,
        'month_name' => $monthName,
        'days_in_month' => $daysInMonth,
        'today' => $todayDate,
        'first_day_of_week' => (int)date('N', strtotime("$year-$monthPadded-01")), // 1=Mon, 7=Sun
        'events_by_date' => $eventsByDate,
        'total_events' => count($allEvents)
    ]);
    exit();
}

// ----------------------------------------------------------------------------
// 2. ACTION: GET WEEK EVENTS (For Weekly Calendar Strip)
// ----------------------------------------------------------------------------
if ($action === 'get_week_events') {
    $anchorDate = $_GET['anchor_date'] ?? $todayDate;
    $dt = new DateTime($anchorDate);
    // Find Monday of the target week
    $dayOfWeek = (int)$dt->format('N'); // 1 = Monday
    $monday = clone $dt;
    if ($dayOfWeek > 1) {
        $monday->modify('-' . ($dayOfWeek - 1) . ' days');
    }
    $sunday = clone $monday;
    $sunday->modify('+6 days');

    $startDate = $monday->format('Y-m-d') . ' 00:00:00';
    $endDate = $sunday->format('Y-m-d') . ' 23:59:59';

    $daysList = [];
    $cur = clone $monday;
    for ($i = 0; $i < 7; $i++) {
        $dateStr = $cur->format('Y-m-d');
        $daysList[$dateStr] = [
            'date' => $dateStr,
            'day_name' => strtoupper($cur->format('D')),
            'day_num' => (int)$cur->format('j'),
            'month_name' => strtoupper($cur->format('M')),
            'is_today' => ($dateStr === $todayDate),
            'has_events' => false,
            'events' => []
        ];
        $cur->modify('+1 day');
    }

    global $conn;
    if (isset($conn) && $conn && !$conn->connect_error) {
        try {
            $stmt = $conn->prepare("
                SELECT ss.id, ss.title, ss.description, ss.module_code, ss.faculty, 
                       ss.session_date, ss.duration_mins, ss.format_type, ss.location_details, 
                       ss.capacity, ss.enrolled_count, ss.status,
                       COALESCE(u.name, 'NSBM Student Leader') AS host_name
                FROM study_sessions ss
                LEFT JOIN users u ON ss.host_id = u.id
                WHERE ss.session_date BETWEEN ? AND ?
                  AND ss.status IN ('Scheduled', 'Completed')
                ORDER BY ss.session_date ASC
            ");
            if ($stmt) {
                $stmt->bind_param("ss", $startDate, $endDate);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $eventDt = new DateTime($row['session_date']);
                    $dateKey = $eventDt->format('Y-m-d');
                    if (isset($daysList[$dateKey])) {
                        $daysList[$dateKey]['has_events'] = true;
                        $daysList[$dateKey]['events'][] = [
                            'id' => (int)$row['id'],
                            'title' => $row['title'],
                            'faculty' => $row['faculty'] ?: 'Computing',
                            'faculty_meta' => getFacultyMeta($row['faculty']),
                            'time' => $eventDt->format('g:i A'),
                            'location' => $row['location_details'],
                            'format' => $row['format_type']
                        ];
                    }
                }
                $stmt->close();
            }
        } catch (Throwable $e) {}
    }

    echo json_encode([
        'success' => true,
        'today' => $todayDate,
        'monday' => $monday->format('Y-m-d'),
        'sunday' => $sunday->format('Y-m-d'),
        'week_label' => $monday->format('M j') . ' - ' . $sunday->format('M j, Y'),
        'days' => array_values($daysList)
    ]);
    exit();
}

// ----------------------------------------------------------------------------
// 3. ACTION: GET DAY SESSIONS (Clickable Date Detail Inspector)
// ----------------------------------------------------------------------------
if ($action === 'get_day_events') {
    $date = $_GET['date'] ?? $todayDate;
    $startDate = $date . ' 00:00:00';
    $endDate = $date . ' 23:59:59';
    $events = [];

    global $conn;
    if (isset($conn) && $conn && !$conn->connect_error) {
        try {
            $stmt = $conn->prepare("
                SELECT ss.id, ss.title, ss.description, ss.module_code, ss.faculty, 
                       ss.session_date, ss.duration_mins, ss.format_type, ss.location_details, 
                       ss.capacity, ss.enrolled_count, ss.status,
                       COALESCE(u.name, 'NSBM Student Leader') AS host_name
                FROM study_sessions ss
                LEFT JOIN users u ON ss.host_id = u.id
                WHERE ss.session_date BETWEEN ? AND ?
                ORDER BY ss.session_date ASC
            ");
            if ($stmt) {
                $stmt->bind_param("ss", $startDate, $endDate);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $dt = new DateTime($row['session_date']);
                    $events[] = [
                        'id' => (int)$row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'module_code' => $row['module_code'],
                        'faculty' => $row['faculty'],
                        'faculty_meta' => getFacultyMeta($row['faculty']),
                        'time' => $dt->format('g:i A'),
                        'duration' => ($row['duration_mins'] ?: 90) . ' mins',
                        'location' => $row['location_details'],
                        'host_name' => $row['host_name'],
                        'capacity' => (int)$row['capacity'],
                        'enrolled_count' => (int)$row['enrolled_count'],
                        'status' => $row['status']
                    ];
                }
                $stmt->close();
            }
        } catch (Throwable $e) {}
    }

    echo json_encode([
        'success' => true,
        'date' => $date,
        'formatted_date' => date('l, F j, Y', strtotime($date)),
        'events' => $events,
        'count' => count($events)
    ]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown calendar action']);
exit();

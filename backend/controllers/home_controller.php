<?php
// ============================================================================
// NSBM STUDY CIRCLE - HOMEPAGE CONTROLLER & API
// Powers the dynamic landing page: Resources, Discussions, Groups, Sessions
// ============================================================================

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    http_response_code(200);
    exit();
}

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../models/Discussion.php';
require_once __DIR__ . '/../models/StudyGroup.php';
require_once __DIR__ . '/../models/StudySession.php';

$resourceModel = new ResourceModel($conn);
$discussionModel = new DiscussionModel($conn);
$groupModel = new StudyGroupModel($conn);
$sessionModel = new StudySessionModel($conn);

$action = $_GET['action'] ?? ($_POST['action'] ?? 'feed');

// JSON output helper
function jsonOutput($data, $statusCode = 200) {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

switch ($action) {
    // 1. Unified Feed for Homepage First Paint
    case 'feed':
        $resources = $resourceModel->getResources('all', '', 4);
        $discussions = $discussionModel->getTrendingDiscussions(2);
        $groups = $groupModel->getFeaturedGroups(4);
        $sessions = $sessionModel->getUpcomingSessions(3);

        jsonOutput([
            'success' => true,
            'timestamp' => time(),
            'data' => [
                'resources' => $resources,
                'discussions' => $discussions,
                'groups' => $groups,
                'sessions' => $sessions,
                'metrics' => [
                    'total_resources' => 1250,
                    'active_groups' => 48,
                    'questions_answered' => 3850,
                    'weekly_sessions' => 120
                ]
            ]
        ]);
        break;

    // 2. Filtered & Searchable Resources
    case 'resources':
        $faculty = $_GET['faculty'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $results = $resourceModel->getResources($faculty, $search, $limit, $offset);

        jsonOutput([
            'success' => true,
            'count' => count($results),
            'faculty' => $faculty,
            'search' => $search,
            'data' => $results
        ]);
        break;

    // 3. Trending Discussions
    case 'discussions':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $results = $discussionModel->getTrendingDiscussions($limit);

        jsonOutput([
            'success' => true,
            'count' => count($results),
            'data' => $results
        ]);
        break;

    // 4. Study Groups
    case 'groups':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        $results = $groupModel->getFeaturedGroups($limit);

        jsonOutput([
            'success' => true,
            'count' => count($results),
            'data' => $results
        ]);
        break;

    // 5. Upcoming Sessions
    case 'sessions':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
        $results = $sessionModel->getUpcomingSessions($limit);

        jsonOutput([
            'success' => true,
            'count' => count($results),
            'data' => $results
        ]);
        break;

    // 6. Upvote Discussion Action
    case 'upvote':
        $discussionId = (int)($_POST['discussion_id'] ?? ($_GET['id'] ?? 0));
        if ($discussionId > 0) {
            $discussionModel->upvote($discussionId);
            jsonOutput([
                'success' => true,
                'message' => 'Discussion upvoted successfully'
            ]);
        } else {
            jsonOutput([
                'success' => false,
                'message' => 'Invalid discussion ID'
            ], 400);
        }
        break;

    default:
        jsonOutput([
            'success' => false,
            'message' => "Unrecognized action '{$action}'"
        ], 404);
        break;
}
?>

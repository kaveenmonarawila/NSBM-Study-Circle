<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY GROUP CONTROLLER
// RESTful API and JSON Endpoints for Learning Communities
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/StudyGroup.php';

header('Content-Type: application/json; charset=utf-8');

$model = new StudyGroupModel($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Parse JSON payload if sent
$rawBody = file_get_contents('php://input');
$inputData = $_POST;
if (!empty($rawBody)) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $inputData = array_merge($inputData, $decoded);
    }
}
if (isset($inputData['action'])) {
    $action = $inputData['action'];
}

try {
    switch ($action) {
        case 'list':
            $category = $_GET['category'] ?? ($inputData['category'] ?? 'All');
            $search = $_GET['q'] ?? ($inputData['q'] ?? '');
            $limit = (int)($_GET['limit'] ?? ($inputData['limit'] ?? 30));

            $featured = $model->getFeaturedGroup();
            $groups = $model->getGroups($category, $search, $limit);

            echo json_encode([
                'success' => true,
                'category' => $category,
                'search' => $search,
                'featured' => $featured,
                'groups' => $groups,
                'total' => count($groups)
            ]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? ($inputData['id'] ?? 1));
            $currentUserId = $_SESSION['user_id'] ?? null;
            $group = $model->getGroupById($id, $currentUserId);

            if ($group) {
                echo json_encode([
                    'success' => true,
                    'group' => $group
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Study group not found.'
                ]);
            }
            break;

        case 'join':
        case 'join_group':
            $id = (int)($inputData['id'] ?? ($inputData['group_id'] ?? ($_GET['id'] ?? ($_GET['group_id'] ?? 0))));
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in.']);
                exit;
            }

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Study group ID is required.']);
                exit;
            }

            $res = $model->joinGroup($id, $userId);
            echo json_encode($res);
            break;

        case 'leave':
        case 'leave_group':
            $id = (int)($inputData['id'] ?? ($inputData['group_id'] ?? ($_GET['id'] ?? ($_GET['group_id'] ?? 0))));
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in.']);
                exit;
            }

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Study group ID is required.']);
                exit;
            }

            $res = $model->leaveGroup($id, $userId);
            echo json_encode($res);
            break;

        case 'create':
        case 'create_group':
        case 'create_study_group':
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in.']);
                exit;
            }

            $name = trim($inputData['name'] ?? '');
            $description = trim($inputData['description'] ?? '');

            if (empty($name) || empty($description)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Please provide a group name and description.']);
                exit;
            }

            $res = $model->createGroup($inputData, $userId);
            echo json_encode($res);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Unknown action requested.'
            ]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>


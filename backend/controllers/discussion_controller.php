<?php
// ============================================================================
// NSBM STUDY CIRCLE - DISCUSSION CONTROLLER (REST API & Web Handlers)
// Manages academic questions, filtering, answers, upvoting & search
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/Discussion.php';

$discussionModel = new DiscussionModel();

function sendDiscussionResponse($success, $message, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit();
}

$action = $_POST['action'] ?? ($_GET['action'] ?? 'list');
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? 'Student';
$userRole = $_SESSION['role'] ?? 'student';

switch ($action) {

    // 1. List Discussions (Recent, Popular, Unanswered, Category, Search)
    case 'list':
        $filter = $_GET['filter'] ?? 'recent';
        $category = $_GET['category'] ?? 'all';
        $search = trim($_GET['q'] ?? '');
        $limit = (int)($_GET['limit'] ?? 25);
        $offset = (int)($_GET['offset'] ?? 0);

        $discussions = $discussionModel->getFilteredDiscussions($filter, $category, $search, $limit, $offset);
        $counts = $discussionModel->getCategoryCounts();

        sendDiscussionResponse(true, 'Discussions retrieved', [
            'count' => count($discussions),
            'discussions' => $discussions,
            'category_counts' => $counts,
            'current_filter' => $filter,
            'current_category' => $category
        ]);
        break;

    // 2. Get Single Discussion with Answers
    case 'get':
        $id = (int)($_GET['id'] ?? 1);
        $discussion = $discussionModel->getDiscussionById($id);
        if (!$discussion) {
            sendDiscussionResponse(false, 'Discussion not found', [], 404);
        }

        sendDiscussionResponse(true, 'Discussion details retrieved', [
            'discussion' => $discussion
        ]);
        break;

    // 3. Post New Question ("Ask a Question")
    case 'ask':
    case 'create_question':
    case 'post_question':
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            sendDiscussionResponse(false, 'Authentication required. Please log in to post questions.', [], 401);
        }

        $title = trim($_POST['title'] ?? ($input['title'] ?? ''));
        $body = trim($_POST['body'] ?? ($input['body'] ?? ''));
        $category = trim($_POST['category'] ?? ($input['category'] ?? 'Computing'));
        $tags = trim($_POST['tags'] ?? ($input['tags'] ?? 'academic, discussion'));
        $moduleCode = trim($_POST['module_code'] ?? ($input['module_code'] ?? 'COM101'));

        if (empty($title) || empty($body)) {
            sendDiscussionResponse(false, 'Please provide both a question title and explanation.', [], 400);
        }

        $newId = $discussionModel->createDiscussion($title, $body, $category, $tags, $userId, $moduleCode);
        if ($newId) {
            sendDiscussionResponse(true, 'Question posted successfully to the community!', [
                'discussion_id' => $newId,
                'redirect' => "discussion-details.php?id=$newId"
            ]);
        } else {
            sendDiscussionResponse(false, 'Failed to save question to database.', [], 500);
        }
        break;

    // 4. Post Answer
    case 'answer':
    case 'create_answer':
    case 'post_answer':
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            sendDiscussionResponse(false, 'Authentication required. Please log in to post answers.', [], 401);
        }

        $discussionId = (int)($_POST['discussion_id'] ?? ($_POST['id'] ?? 0));
        $body = trim($_POST['body'] ?? '');
        $authorRole = ($userRole === 'admin') ? 'Administrator' : 'Student member · Just now';

        if ($discussionId <= 0 || empty($body)) {
            sendDiscussionResponse(false, 'Please write your answer before submitting.', [], 400);
        }

        if (strlen($body) > 2000) {
            sendDiscussionResponse(false, 'Answer exceeds the 2,000 character limit.', [], 400);
        }

        $ansId = $discussionModel->addAnswer($discussionId, $body, $userId, $authorRole);
        if ($ansId) {
            $updatedAnswers = $discussionModel->getDiscussionAnswers($discussionId);
            sendDiscussionResponse(true, 'Answer posted successfully!', [
                'answer_id' => $ansId,
                'answers_count' => count($updatedAnswers),
                'answers' => $updatedAnswers
            ]);
        } else {
            sendDiscussionResponse(false, 'Failed to save your answer.', [], 500);
        }
        break;

    // 5. Upvote Question (Restricted to Logged-in Students)
    case 'upvote_question':
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            sendDiscussionResponse(false, 'Authentication required. Please log in to vote questions helpful.', ['require_login' => true], 401);
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) sendDiscussionResponse(false, 'Invalid ID', [], 400);

        if ($discussionModel->upvoteDiscussion($id)) {
            sendDiscussionResponse(true, 'Helpful vote recorded!');
        } else {
            sendDiscussionResponse(false, 'Failed to record vote.', [], 500);
        }
        break;

    // 6. Upvote Answer (Restricted to Logged-in Students)
    case 'upvote_answer':
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            sendDiscussionResponse(false, 'Authentication required. Please log in to vote answers helpful.', ['require_login' => true], 401);
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) sendDiscussionResponse(false, 'Invalid ID', [], 400);

        if ($discussionModel->upvoteAnswer($id)) {
            sendDiscussionResponse(true, 'Helpful vote recorded on answer!');
        } else {
            sendDiscussionResponse(false, 'Failed to record vote.', [], 500);
        }
        break;

    default:
        sendDiscussionResponse(false, 'Unknown action', [], 400);
        break;
}
?>


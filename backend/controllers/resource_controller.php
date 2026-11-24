<?php
// ============================================================================
// NSBM STUDY CIRCLE - RESOURCE CONTROLLER (REST API & Web Handlers)
// Manages resource listing, details, downloads, bookmarks, reviews & uploads
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/app_config.php'; // SITE_NAME
require_once __DIR__ . '/../models/Resource.php';

$resourceModel = new ResourceModel();

// Helper to determine if client expects JSON
function isApiJsonRequest() {
    return (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
           (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
           (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
           (isset($_GET['format']) && $_GET['format'] === 'json');
}

function sendApiResponse($success, $message, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit();
}

// File uploads are disabled: study materials are shared as Google Drive /
// OneDrive links (see backend/helpers/link_helper.php). This controller never
// reads $_FILES and never writes files to the server.

$action = $_POST['action'] ?? ($_GET['action'] ?? 'list');
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

switch ($action) {

    // =========================================================================
    // 1. LIST RESOURCES (Filter, Search, Sort)
    // =========================================================================
    case 'list':
        $subject = $_GET['subject'] ?? 'all';
        $format = $_GET['format'] ?? 'all';
        $year = $_GET['year'] ?? 'all';
        $search = trim($_GET['q'] ?? ($_GET['search'] ?? ''));
        $sort = $_GET['sort'] ?? 'most_downloaded';
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = (int)($_GET['offset'] ?? 0);

        $resources = $resourceModel->getFilteredResources($subject, $format, $year, $search, $sort, $limit, $offset);

        // Saved bookmarks for currently logged in student
        $savedIds = [];
        if ($userId) {
            $savedIds = $resourceModel->getUserSavedResourceIds($userId);
        }

        sendApiResponse(true, 'Resources fetched successfully', [
            'count' => count($resources),
            'resources' => $resources,
            'saved_ids' => $savedIds,
            'logged_in_user' => $userId ? [
                'id' => $userId,
                'name' => $_SESSION['name'] ?? 'Student',
                'role' => $userRole
            ] : null
        ]);
        break;

    // =========================================================================
    // 2. GET SINGLE RESOURCE DETAILS
    // =========================================================================
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            sendApiResponse(false, 'Invalid resource identifier', [], 400);
        }

        $resource = $resourceModel->getResourceById($id);
        if (!$resource || !ResourceModel::canView($resource, $userId, $userRole)) {
            // Pending / rejected resources are only visible to their author and admins
            sendApiResponse(false, 'Resource not found', [], 404);
        }

        $isSaved = false;
        if ($userId) {
            $isSaved = $resourceModel->isResourceSaved($id, $userId);
        }

        sendApiResponse(true, 'Resource details fetched', [
            'resource' => $resource,
            'is_saved' => $isSaved,
            'current_user' => $userId ? [
                'id' => $userId,
                'name' => $_SESSION['name'] ?? 'Student',
                'role' => $userRole
            ] : null
        ]);
        break;

    // =========================================================================
    // 3. SECURE FILE DOWNLOAD (Restricted Exclusively to Authenticated Users)
    // =========================================================================
    case 'download':
        // Block unauthenticated guests from downloading files
        if (!$userId) {
            if (isApiJsonRequest()) {
                sendApiResponse(false, 'Authentication required. Please log in to your student account to download study materials.', [
                    'require_login' => true,
                    'redirect' => 'login.php'
                ], 401);
            } else {
                header("Location: ../../frontend/login.php?error=" . urlencode("Authentication required. Please log in to your student account to download study materials."));
                exit();
            }
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            die('Invalid resource request.');
        }

        $resource = $resourceModel->getResourceById($id);
        if (!$resource || !ResourceModel::canView($resource, $userId, $userRole)) {
            die('Resource not found.');
        }

        // Increment download counters in MySQL for authenticated student
        $resourceModel->incrementDownloads($id, $userId);

        // New system: the material lives on Google Drive / OneDrive. Send the
        // browser there (a plain redirect - the file never passes through this server).
        if (!empty($resource['is_link']) && !empty($resource['external_url'])) {
            header('Location: ' . $resource['external_url'], true, 302);
            exit();
        }

        // Old files already stored on the server: still served read-only.
        $filePath = __DIR__ . '/../../' . ($resource['file_path'] ?? '');
        $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $resource['title']) . '.' . strtolower($resource['file_type'] ?? 'pdf');

        if (file_exists($filePath) && is_file($filePath)) {
            // Determine content type
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $contentTypes = [
                'pdf' => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit();
        } else {
            // Generate valid on-the-fly sample document if physical file was missing
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo "%PDF-1.4\n% " . SITE_NAME . " - Study Guide\nTitle: " . $resource['title'] . "\nAuthor: " . ($resource['author_name'] ?? 'Student Community') . "\n%%EOF";
            exit();
        }
        break;

    // =========================================================================
    // 4. BOOKMARK / SAVE RESOURCE TOGGLE
    // =========================================================================
    case 'save':
    case 'bookmark':
        if (!$userId) {
            sendApiResponse(false, 'Authentication required. Please log in to bookmark resources.', [
                'require_login' => true,
                'redirect' => 'login.php'
            ], 401);
        }

        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            sendApiResponse(false, 'Invalid resource identifier', [], 400);
        }

        $isSaved = $resourceModel->toggleBookmark($id, $userId);
        $msg = $isSaved ? 'Resource saved to your bookmarks!' : 'Resource removed from bookmarks.';

        sendApiResponse(true, $msg, [
            'is_saved' => $isSaved,
            'resource_id' => $id
        ]);
        break;

    // =========================================================================
    // 5. SUBMIT RATING & REVIEW
    // =========================================================================
    case 'review':
    case 'rate':
        if (!$userId) {
            sendApiResponse(false, 'Authentication required. Please log in to rate and review resources.', [
                'require_login' => true,
                'redirect' => 'login.php'
            ], 401);
        }

        $id = (int)($_POST['id'] ?? ($_POST['resource_id'] ?? 0));
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        if ($id <= 0) {
            sendApiResponse(false, 'Invalid resource identifier', [], 400);
        }
        if ($rating < 1 || $rating > 5) {
            sendApiResponse(false, 'Rating must be between 1 and 5 stars.', [], 400);
        }
        if (empty($comment)) {
            sendApiResponse(false, 'Please provide written feedback with your rating.', [], 400);
        }

        $result = $resourceModel->addReview($id, $userId, $rating, $comment);
        if ($result) {
            $updatedReviews = $resourceModel->getReviewsByResourceId($id);
            sendApiResponse(true, 'Thank you! Your feedback has been published.', [
                'average_rating' => $result['average_rating'],
                'ratings_count' => $result['ratings_count'],
                'reviews' => $updatedReviews
            ]);
        } else {
            sendApiResponse(false, 'Unable to submit review. Please try again.', [], 500);
        }
        break;

    // =========================================================================
    // 6. SUBMIT A RESOURCE LINK (Google Drive / OneDrive) - NO FILE UPLOADS
    // 'upload' is kept as an alias so older pages still reach this handler.
    // =========================================================================
    case 'submit_link':
    case 'upload':
        if (!$userId) {
            if (isApiJsonRequest()) {
                sendApiResponse(false, 'Authentication required. Please log in to share study materials.', [
                    'require_login' => true,
                    'redirect' => 'login.php'
                ], 401);
            } else {
                header("Location: ../../frontend/login.php?error=" . urlencode("Please log in to share study materials."));
                exit();
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            sendApiResponse(false, 'Please submit the form to share a resource.', [], 405);
        }

        // Anything that still tries to send a file is refused (nothing is ever saved to disk)
        if (!empty($_FILES)) {
            sendApiResponse(false, 'File uploads are no longer accepted. Please paste a Google Drive or OneDrive share link instead.', [], 400);
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $facultyRaw = $_POST['faculty'] ?? ($_SESSION['faculty'] ?? 'Computing');
        $faculty = in_array($facultyRaw, ['Computing', 'Business', 'Engineering', 'Science'], true) ? $facultyRaw : 'Computing';
        $moduleCode = trim($_POST['module_code'] ?? '');
        $yearRaw = $_POST['academic_year'] ?? 'Year 1';
        $academicYear = in_array($yearRaw, ['Year 1', 'Year 2', 'Year 3', 'Year 4'], true) ? $yearRaw : 'Year 1';

        if ($title === '' || $moduleCode === '') {
            sendApiResponse(false, 'Please provide a resource title and module code.', [], 400);
        }
        if (mb_strlen($title) > 255 || mb_strlen($moduleCode) > 30) {
            sendApiResponse(false, 'The title (max 255 characters) or module code (max 30 characters) is too long.', [], 400);
        }

        require_once __DIR__ . '/../helpers/link_helper.php';
        $link = validateResourceLink($_POST['resource_link'] ?? '');
        if (!$link['ok']) {
            sendApiResponse(false, $link['error'], [], 400);
        }

        $newId = $resourceModel->createResource([
            'title' => $title,
            'description' => $description,
            'faculty' => $faculty,
            'module_code' => $moduleCode,
            'academic_year' => $academicYear,
            'external_url' => $link['url'],
            'uploaded_by' => $userId,
            'about_text' => $description,
            'whats_included' => [
                'Student revision notes & syllabus guide',
                'Key formulas, definitions & explanations',
                'Module practice exercises'
            ]
        ]);

        if ($newId) {
            $msg = 'Thanks! Your resource link was submitted and is waiting for an administrator to approve it.';
            if (isApiJsonRequest()) {
                sendApiResponse(true, $msg, [
                    'resource_id' => $newId,
                    'status' => 'Pending'
                ]);
            } else {
                header("Location: ../../frontend/resources.php?submitted=1");
                exit();
            }
        } else {
            sendApiResponse(false, 'Database error while saving the resource link.', [], 500);
        }
        break;

    // =========================================================================
    // 7. REPORT CONTENT
    // =========================================================================
    case 'report':
        $id = (int)($_POST['id'] ?? 0);
        $reason = $_POST['reason'] ?? 'Academic integrity';
        $snippet = trim($_POST['snippet'] ?? ('Reported resource #' . $id));

        if ($id <= 0) {
            sendApiResponse(false, 'Invalid resource ID', [], 400);
        }

        global $conn;
        if ($conn && !$conn->connect_error) {
            $reportedBy = $userId ?: 1;
            $stmt = $conn->prepare("
                INSERT INTO reported_content (content_snippet, target_type, target_id, reason, priority, status, reported_by)
                VALUES (?, 'Resource', ?, ?, 'Medium', 'Open', ?)
            ");
            if ($stmt) {
                $stmt->bind_param("sisi", $snippet, $id, $reason, $reportedBy);
                $stmt->execute();
            }
        }

        sendApiResponse(true, 'Thank you. A moderation report has been submitted to the academic team.');
        break;

    // =========================================================================
    // 8. ADMIN ACTIONS (Approve, Reject, Delete)
    // =========================================================================
    case 'admin_status':
        if ($userRole !== 'admin') {
            sendApiResponse(false, 'Unauthorized admin access', [], 403);
        }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Approved';
        if ($resourceModel->updateStatus($id, $status)) {
            sendApiResponse(true, "Resource #$id status changed to $status");
        } else {
            sendApiResponse(false, 'Failed to update resource status', [], 500);
        }
        break;

    case 'admin_delete':
        if ($userRole !== 'admin') {
            sendApiResponse(false, 'Unauthorized admin access', [], 403);
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($resourceModel->deleteResource($id)) {
            sendApiResponse(true, "Resource #$id deleted successfully");
        } else {
            sendApiResponse(false, 'Failed to delete resource', [], 500);
        }
        break;

    default:
        sendApiResponse(false, 'Unknown action: ' . htmlspecialchars($action), [], 400);
        break;
}
?>

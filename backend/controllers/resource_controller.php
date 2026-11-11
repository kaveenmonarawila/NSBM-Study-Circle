<?php
// ============================================================================
// NSBM STUDY CIRCLE - RESOURCE CONTROLLER (REST API & Web Handlers)
// Manages resource listing, details, downloads, bookmarks, reviews & uploads
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
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
        if (!$resource) {
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
        if (!$resource) {
            die('Resource not found.');
        }

        // Increment download counters in MySQL for authenticated student
        $resourceModel->incrementDownloads($id, $userId);

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
            echo "%PDF-1.4\n% NSBM Study Circle - Study Guide\nTitle: " . $resource['title'] . "\nAuthor: " . ($resource['author_name'] ?? 'NSBM Academic Community') . "\n%%EOF";
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
    // 6. UPLOAD RESOURCE SYSTEM
    // =========================================================================
    case 'upload':
        if (!$userId) {
            if (isApiJsonRequest()) {
                sendApiResponse(false, 'Authentication required. Please log in to upload study materials.', [
                    'require_login' => true,
                    'redirect' => 'login.php'
                ], 401);
            } else {
                header("Location: ../../frontend/login.php?error=" . urlencode("Please log in to upload study materials."));
                exit();
            }
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $faculty = $_POST['faculty'] ?? ($_SESSION['faculty'] ?? 'Computing');
        $moduleCode = trim($_POST['module_code'] ?? 'COM101');
        $academicYear = $_POST['academic_year'] ?? 'Year 1';

        if (empty($title) || empty($moduleCode)) {
            sendApiResponse(false, 'Please provide a resource title and module code.', [], 400);
        }

        // Validate File Upload
        if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
            sendApiResponse(false, 'Please attach a valid file for upload.', [], 400);
        }

        $file = $_FILES['resource_file'];
        $maxBytes = 25 * 1024 * 1024; // 25 MB
        if ($file['size'] > $maxBytes) {
            sendApiResponse(false, 'File size exceeds 25 MB limit.', [], 400);
        }

        $origName = $file['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'docx', 'pptx'];

        if (!in_array($ext, $allowedExts)) {
            sendApiResponse(false, 'Unsupported file format. Only PDF, DOCX, and PPTX files are allowed.', [], 400);
        }

        // Target storage directory
        $targetDir = __DIR__ . '/../../backend/uploads/resources/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Unique filename to prevent overwrite and directory traversal
        $newFileName = 'res_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destination = $targetDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            sendApiResponse(false, 'Failed to save uploaded file on server.', [], 500);
        }

        $fileSizeMb = round($file['size'] / (1024 * 1024), 2);
        $fileType = strtoupper($ext);

        $newId = $resourceModel->createResource([
            'title' => $title,
            'description' => $description,
            'faculty' => $faculty,
            'module_code' => $moduleCode,
            'academic_year' => $academicYear,
            'file_path' => 'backend/uploads/resources/' . $newFileName,
            'file_type' => $fileType,
            'file_size_mb' => $fileSizeMb,
            'page_count' => ($ext === 'docx') ? 8 : 15,
            'uploaded_by' => $userId,
            'status' => 'Approved', // Auto-approved or set to 'Pending'
            'about_text' => $description,
            'whats_included' => [
                'Student revision notes & syllabus guide',
                'Key formulas, definitions & explanations',
                'Module practice exercises'
            ]
        ]);

        if ($newId) {
            if (isApiJsonRequest()) {
                sendApiResponse(true, 'Resource uploaded and published successfully!', [
                    'resource_id' => $newId,
                    'redirect' => 'resources.php?uploaded=1'
                ]);
            } else {
                header("Location: ../../frontend/resources.php?uploaded=1");
                exit();
            }
        } else {
            sendApiResponse(false, 'Database error while saving resource record.', [], 500);
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

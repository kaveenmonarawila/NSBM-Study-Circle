<?php
// ============================================================================
// NSBM STUDY CIRCLE - RESOURCE MODEL
// High-performance MySQL database operations for study notes, guides & materials
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

class ResourceModel {
    private $conn;

    public function __construct($dbConnection = null) {
        global $conn;
        $this->conn = $dbConnection ?? $conn;
    }

    /**
     * Fetch approved resources with advanced filters: subject, resource type, academic year, search & sort
     */
    public function getFilteredResources($subject = 'all', $format = 'all', $year = 'all', $search = '', $sort = 'most_downloaded', $limit = 20, $offset = 0) {
        if (!$this->conn || $this->conn->connect_error) {
            return $this->filterFallbackResources($subject, $format, $year, $search, $sort);
        }

        $query = "SELECT r.id, r.title, r.description, r.faculty, r.module_code, r.academic_year, 
                         r.file_type, r.file_size_mb, r.downloads, r.rating, r.ratings_count, r.created_at,
                         r.about_text, r.whats_included, r.preview_badge, r.preview_heading, r.preview_subheading,
                         u.name AS author_name, u.faculty AS author_faculty, u.degree_program AS author_role
                  FROM resources r
                  LEFT JOIN users u ON r.uploaded_by = u.id
                  WHERE r.status = 'Approved'";
        
        $params = [];
        $types = "";

        // 1. Subject filter
        if ($subject !== 'all' && !empty($subject)) {
            if (in_array(strtolower($subject), ['computing', 'business', 'engineering', 'science'])) {
                $query .= " AND LOWER(r.faculty) = LOWER(?)";
                $params[] = $subject;
                $types .= "s";
            } else {
                $query .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.module_code LIKE ?)";
                $term = "%" . $subject . "%";
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $types .= "sss";
            }
        }

        // 2. Format filter (PDF, DOCX, PPTX)
        if ($format !== 'all' && !empty($format) && strtolower($format) !== 'all formats') {
            $query .= " AND UPPER(r.file_type) = UPPER(?)";
            $params[] = $format;
            $types .= "s";
        }

        // 3. Academic year filter
        if ($year !== 'all' && !empty($year) && strtolower($year) !== 'any year') {
            $query .= " AND LOWER(r.academic_year) = LOWER(?)";
            $params[] = $year;
            $types .= "s";
        }

        // 4. Keyword search (title, description, module_code, author)
        if (!empty($search)) {
            $query .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.module_code LIKE ? OR u.name LIKE ?)";
            $term = "%" . $search . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $types .= "ssss";
        }

        // 5. Sorting
        switch (strtolower(str_replace(' ', '_', $sort))) {
            case 'highest_rated':
                $query .= " ORDER BY r.rating DESC, r.downloads DESC";
                break;
            case 'newest_first':
            case 'newest':
                $query .= " ORDER BY r.id DESC, r.created_at DESC";
                break;
            case 'most_downloaded':
            default:
                $query .= " ORDER BY r.downloads DESC, r.rating DESC";
                break;
        }

        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return $this->filterFallbackResources($subject, $format, $year, $search, $sort);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $resources = [];
        while ($row = $result->fetch_assoc()) {
            $row['author_name'] = $row['author_name'] ?? 'Student Contributor';
            $row['author_initials'] = $this->getInitials($row['author_name']);
            $row['faculty_badge'] = $row['faculty'];
            $row['badge_class'] = 'badge-' . strtolower($row['faculty']);
            $row['rating'] = (float)$row['rating'];
            $row['downloads'] = (int)$row['downloads'];
            $row['file_size_mb'] = $row['file_size_mb'] . ' MB';
            $resources[] = $row;
        }

        return !empty($resources) ? $resources : $this->filterFallbackResources($subject, $format, $year, $search, $sort);
    }

    /**
     * Get detailed resource by ID directly from MySQL
     */
    public function getResourceById($id) {
        $id = (int)$id;

        if ($this->conn && !$this->conn->connect_error) {
            $stmt = $this->conn->prepare("
                SELECT r.*, u.name AS author_name, u.faculty AS author_faculty, u.degree_program AS author_role
                FROM resources r
                LEFT JOIN users u ON r.uploaded_by = u.id
                WHERE r.id = ? LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $row['author_name'] = $row['author_name'] ?? 'Student Contributor';
                    $row['author_initials'] = $this->getInitials($row['author_name']);
                    $row['author_role'] = $row['author_role'] ?? ($row['faculty'] . ' • Year 2');
                    $row['faculty_badge'] = $row['faculty'];
                    $row['badge_class'] = 'badge-' . strtolower($row['faculty']);
                    $row['rating'] = (float)$row['rating'];
                    $row['downloads'] = (int)$row['downloads'];
                    $row['file_size_mb'] = (strpos($row['file_size_mb'], 'MB') === false) ? ($row['file_size_mb'] . ' MB') : $row['file_size_mb'];

                    // Parse what's included JSON or format
                    if (!empty($row['whats_included'])) {
                        $decoded = json_decode($row['whats_included'], true);
                        $row['whats_included'] = is_array($decoded) ? $decoded : explode("\n", $row['whats_included']);
                    } else {
                        $row['whats_included'] = [
                            'Topic summary aligned to the module syllabus',
                            'Visual diagrams and worked examples',
                            'Exam practice questions with guidance',
                            'Comprehensive reference notes'
                        ];
                    }

                    // Preview document details
                    $row['preview_doc'] = [
                        'badge' => $row['preview_badge'] ?: strtoupper($row['faculty']),
                        'heading' => $row['preview_heading'] ?: ($row['title']),
                        'subheading' => $row['preview_subheading'] ?: ($row['description']),
                        'page_indicator' => 'Page 1 of ' . ($row['page_count'] ?: 20)
                    ];

                    // Fetch reviews
                    $row['feedback'] = $this->getReviewsByResourceId($id);
                    return $row;
                }
            }
        }

        // Fallback to in-memory dataset
        $all = $this->getAllFallbackResources();
        foreach ($all as $item) {
            if ((int)$item['id'] === $id) return $item;
        }
        return $all[0];
    }

    /**
     * Create a new resource from Student Upload
     */
    public function createResource($data) {
        if (!$this->conn || $this->conn->connect_error) return false;

        $stmt = $this->conn->prepare("
            INSERT INTO resources (
                title, description, faculty, module_code, academic_year, 
                file_path, file_type, file_size_mb, page_count, uploaded_by, 
                status, about_text, whats_included, preview_badge, preview_heading, preview_subheading
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) return false;

        $title = trim($data['title']);
        $description = trim($data['description'] ?? '');
        $faculty = $data['faculty'] ?? 'Computing';
        $moduleCode = trim($data['module_code'] ?? 'COM101');
        $academicYear = $data['academic_year'] ?? 'Year 1';
        $filePath = $data['file_path'];
        $fileType = strtoupper($data['file_type'] ?? 'PDF');
        $fileSizeMb = (float)($data['file_size_mb'] ?? 1.5);
        $pageCount = (int)($data['page_count'] ?? 12);
        $uploadedBy = (int)$data['uploaded_by'];
        $status = $data['status'] ?? 'Approved'; // Can be 'Pending' if approval required
        $aboutText = $data['about_text'] ?? $description;
        $whatsIncluded = is_array($data['whats_included'] ?? null) ? json_encode($data['whats_included']) : ($data['whats_included'] ?? null);
        $previewBadge = strtoupper($faculty);
        $previewHeading = $title;
        $previewSubheading = $description;

        $stmt->bind_param(
            "ssssssdiisssssss",
            $title, $description, $faculty, $moduleCode, $academicYear,
            $filePath, $fileType, $fileSizeMb, $pageCount, $uploadedBy,
            $status, $aboutText, $whatsIncluded, $previewBadge, $previewHeading, $previewSubheading
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Increment download counter and record in download history
     */
    public function incrementDownloads($resourceId, $userId = null) {
        $resourceId = (int)$resourceId;
        if (!$this->conn || $this->conn->connect_error) return true;

        $stmt = $this->conn->prepare("UPDATE resources SET downloads = downloads + 1 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $resourceId);
            $stmt->execute();
        }

        if ($userId) {
            $userId = (int)$userId;
            $logStmt = $this->conn->prepare("INSERT INTO resource_downloads (resource_id, user_id) VALUES (?, ?)");
            if ($logStmt) {
                $logStmt->bind_param("ii", $resourceId, $userId);
                $logStmt->execute();
            }
        }
        return true;
    }

    /**
     * Bookmark / Save resource toggle
     */
    public function toggleBookmark($resourceId, $userId) {
        $resourceId = (int)$resourceId;
        $userId = (int)$userId;
        if (!$this->conn || $this->conn->connect_error) return false;

        $checkStmt = $this->conn->prepare("SELECT 1 FROM saved_resources WHERE user_id = ? AND resource_id = ? LIMIT 1");
        $checkStmt->bind_param("ii", $userId, $resourceId);
        $checkStmt->execute();
        $isSaved = $checkStmt->get_result()->num_rows > 0;

        if ($isSaved) {
            // Remove bookmark
            $delStmt = $this->conn->prepare("DELETE FROM saved_resources WHERE user_id = ? AND resource_id = ?");
            $delStmt->bind_param("ii", $userId, $resourceId);
            $delStmt->execute();
            return false;
        } else {
            // Add bookmark
            $insStmt = $this->conn->prepare("INSERT INTO saved_resources (user_id, resource_id) VALUES (?, ?)");
            $insStmt->bind_param("ii", $userId, $resourceId);
            $insStmt->execute();
            return true;
        }
    }

    /**
     * Check if a resource is saved by user
     */
    public function isResourceSaved($resourceId, $userId) {
        if (!$this->conn || !$userId) return false;
        $stmt = $this->conn->prepare("SELECT 1 FROM saved_resources WHERE user_id = ? AND resource_id = ? LIMIT 1");
        $stmt->bind_param("ii", $userId, $resourceId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Get list of saved resource IDs for a user
     */
    public function getUserSavedResourceIds($userId) {
        if (!$this->conn || !$userId) return [];
        $stmt = $this->conn->prepare("SELECT resource_id FROM saved_resources WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_assoc()) {
            $ids[] = (int)$row['resource_id'];
        }
        return $ids;
    }

    /**
     * Get full saved resources for user dashboard
     */
    public function getUserSavedResources($userId) {
        if (!$this->conn || !$userId) return [];
        $stmt = $this->conn->prepare("
            SELECT r.*, u.name AS author_name 
            FROM saved_resources s
            JOIN resources r ON s.resource_id = r.id
            LEFT JOIN users u ON r.uploaded_by = u.id
            WHERE s.user_id = ?
            ORDER BY s.saved_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['author_initials'] = $this->getInitials($row['author_name'] ?? 'User');
            $list[] = $row;
        }
        return $list;
    }

    /**
     * Add student rating & review, and recalculate resource average
     */
    public function addReview($resourceId, $userId, $rating, $comment) {
        $resourceId = (int)$resourceId;
        $userId = (int)$userId;
        $rating = max(1, min(5, (int)$rating));
        $comment = trim($comment);

        if (!$this->conn || $this->conn->connect_error) return false;

        $stmt = $this->conn->prepare("
            INSERT INTO resource_reviews (resource_id, user_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP
        ");
        if (!$stmt) return false;

        $stmt->bind_param("iiis", $resourceId, $userId, $rating, $comment);
        if ($stmt->execute()) {
            // Recalculate average rating & count
            $calcStmt = $this->conn->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as cnt 
                FROM resource_reviews 
                WHERE resource_id = ?
            ");
            $calcStmt->bind_param("i", $resourceId);
            $calcStmt->execute();
            $calcRes = $calcStmt->get_result()->fetch_assoc();

            $newAvg = round((float)($calcRes['avg_rating'] ?? 5.0), 1);
            $newCnt = (int)($calcRes['cnt'] ?? 0);

            $updStmt = $this->conn->prepare("UPDATE resources SET rating = ?, ratings_count = ? WHERE id = ?");
            $updStmt->bind_param("dii", $newAvg, $newCnt, $resourceId);
            $updStmt->execute();

            return ['average_rating' => $newAvg, 'ratings_count' => $newCnt];
        }
        return false;
    }

    /**
     * Get reviews by resource ID
     */
    public function getReviewsByResourceId($resourceId) {
        $resourceId = (int)$resourceId;
        if (!$this->conn || $this->conn->connect_error) {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT rv.rating, rv.comment, rv.created_at, u.name, u.faculty
            FROM resource_reviews rv
            JOIN users u ON rv.user_id = u.id
            WHERE rv.resource_id = ?
            ORDER BY rv.created_at DESC
        ");
        if (!$stmt) return [];

        $stmt->bind_param("i", $resourceId);
        $stmt->execute();
        $res = $stmt->get_result();

        $reviews = [];
        while ($row = $res->fetch_assoc()) {
            $reviews[] = [
                'name' => $row['name'],
                'initials' => $this->getInitials($row['name']),
                'rating' => (int)$row['rating'],
                'comment' => $row['comment'],
                'created_at' => date('d M Y', strtotime($row['created_at']))
            ];
        }
        return $reviews;
    }

    /**
     * Get admin resources list with filters
     */
    public function getAdminResources($status = 'all', $faculty = 'all', $search = '') {
        if (!$this->conn || $this->conn->connect_error) return [];

        $query = "SELECT r.*, u.name as author_name, u.email as author_email
                  FROM resources r
                  LEFT JOIN users u ON r.uploaded_by = u.id
                  WHERE 1=1";
        $params = [];
        $types = "";

        if ($status !== 'all' && !empty($status)) {
            $query .= " AND r.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        if ($faculty !== 'all' && !empty($faculty)) {
            $query .= " AND r.faculty = ?";
            $params[] = $faculty;
            $types .= "s";
        }
        if (!empty($search)) {
            $query .= " AND (r.title LIKE ? OR r.module_code LIKE ? OR u.name LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $types .= "sss";
        }

        $query .= " ORDER BY r.id DESC";
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['author_initials'] = $this->getInitials($r['author_name'] ?? 'User');
            $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Update Resource Status (Approve, Reject, Archive)
     */
    public function updateStatus($resourceId, $status) {
        $allowed = ['Pending', 'Approved', 'Rejected', 'Archived'];
        if (!in_array($status, $allowed) || !$this->conn) return false;

        $stmt = $this->conn->prepare("UPDATE resources SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $resourceId);
        return $stmt->execute();
    }

    /**
     * Delete resource
     */
    public function deleteResource($resourceId) {
        if (!$this->conn) return false;
        $resourceId = (int)$resourceId;

        // Fetch file path first
        $stmt = $this->conn->prepare("SELECT file_path FROM resources WHERE id = ?");
        $stmt->bind_param("i", $resourceId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $delStmt = $this->conn->prepare("DELETE FROM resources WHERE id = ?");
        $delStmt->bind_param("i", $resourceId);
        if ($delStmt->execute()) {
            if ($row && !empty($row['file_path'])) {
                $abs = __DIR__ . '/../../' . $row['file_path'];
                if (file_exists($abs)) @unlink($abs);
            }
            return true;
        }
        return false;
    }

    /**
     * Fallback in-memory dataset
     */
    private function filterFallbackResources($subject, $format, $year, $search, $sort) {
        $all = $this->getAllFallbackResources();
        return array_values(array_filter($all, function($r) use ($subject, $format, $year, $search) {
            if ($subject !== 'all' && !empty($subject)) {
                if (strtolower($r['faculty']) !== strtolower($subject)) return false;
            }
            if ($format !== 'all' && !empty($format) && strtolower($format) !== 'all formats') {
                if (strtolower($r['file_type']) !== strtolower($format)) return false;
            }
            if ($year !== 'all' && !empty($year) && strtolower($year) !== 'any year') {
                if (strtolower($r['academic_year']) !== strtolower($year)) return false;
            }
            if (!empty($search)) {
                $m = strtolower($r['title'] . ' ' . $r['description'] . ' ' . $r['module_code']);
                if (strpos($m, strtolower($search)) === false) return false;
            }
            return true;
        }));
    }

    public function getAllFallbackResources() {
        return [
            [
                'id' => 1,
                'title' => 'Data Structures - Complete Revision Guide',
                'description' => 'Clear summaries, worked examples, and exam-focused notes covering the full module.',
                'faculty' => 'Computing',
                'module_code' => 'CS2102',
                'academic_year' => 'Year 2',
                'file_type' => 'PDF',
                'file_size_mb' => '5.8 MB',
                'page_count' => 42,
                'downloads' => 1284,
                'rating' => 4.9,
                'ratings_count' => 38,
                'language' => 'English',
                'author_name' => 'Nethmi Perera',
                'author_initials' => 'NP',
                'author_role' => 'Computing • Year 3',
                'about_text' => 'A structured revision guide covering arrays, linked lists, stacks, queues, trees, graphs, searching, sorting, and algorithm analysis.',
                'whats_included' => [
                    'Ten topic summaries aligned to the module outline',
                    'Visual diagrams for trees and graph traversal',
                    'Twenty-four worked algorithm questions',
                    'Final two-page exam checklist'
                ],
                'preview_doc' => [
                    'badge' => 'DATA STRUCTURES',
                    'heading' => 'Exam Revision Guide',
                    'subheading' => 'Core concepts, visual explanations, and worked examples for confident revision.',
                    'page_indicator' => 'Page 1 of 42'
                ],
                'feedback' => [
                    ['name' => 'Kavindu S.', 'initials' => 'KS', 'rating' => 5, 'comment' => 'Excellent structure. The graph traversal diagrams finally made BFS click for me.']
                ]
            ],
            [
                'id' => 2,
                'title' => 'Financial Accounting: Core Concepts',
                'description' => 'A concise lecture companion with key formulas, journal entries, and practice questions.',
                'faculty' => 'Business',
                'module_code' => 'BUS102',
                'academic_year' => 'Year 1',
                'file_type' => 'PDF',
                'file_size_mb' => '3.4 MB',
                'page_count' => 28,
                'downloads' => 864,
                'rating' => 4.8,
                'ratings_count' => 24,
                'language' => 'English',
                'author_name' => 'Kavindu Silva',
                'author_initials' => 'KS',
                'author_role' => 'Business • Year 2',
                'about_text' => 'Covers double-entry bookkeeping, trial balance preparation, and financial statements.',
                'whats_included' => [
                    'Step-by-step balance sheet worksheets',
                    'Key ratio formulas cheat sheet'
                ],
                'preview_doc' => [
                    'badge' => 'FINANCIAL ACCOUNTING',
                    'heading' => 'Core Concepts & Principles',
                    'subheading' => 'A complete primer on transaction recording.',
                    'page_indicator' => 'Page 1 of 28'
                ],
                'feedback' => []
            ]
        ];
    }

    private function getInitials($name) {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($parts as $p) {
            if (!empty($p)) $initials .= strtoupper($p[0]);
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: 'ST';
    }
}
?>

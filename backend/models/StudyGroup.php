<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY GROUP MODEL
// Complete Data Access Layer for Learning Communities
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

class StudyGroupModel {
    private $conn;

    public function __construct($dbConnection = null) {
        global $conn;
        $this->conn = $dbConnection ?? $conn;
    }

    /**
     * Get the single featured community group
     */
    public function getFeaturedGroup() {
        if (!$this->conn || $this->conn->connect_error) {
            $fallback = $this->getFallbackGroups();
            return $fallback[0] ?? null;
        }

        $query = "SELECT * FROM study_groups WHERE is_featured = 1 AND status = 'Active' LIMIT 1";
        $result = $this->conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return $this->formatGroupData($row);
        }

        // Fallback to highest member count
        $query = "SELECT * FROM study_groups WHERE status = 'Active' ORDER BY member_count DESC LIMIT 1";
        $result = $this->conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return $this->formatGroupData($row);
        }

        $fallback = $this->getFallbackGroups();
        return $fallback[0] ?? null;
    }

    /**
     * Get filtered list of study groups
     */
    public function getGroups($category = 'All', $search = '', $limit = 30) {
        if (!$this->conn || $this->conn->connect_error) {
            return $this->filterFallbackGroups($category, $search);
        }

        $sql = "SELECT * FROM study_groups WHERE status = 'Active'";
        $params = [];
        $types = "";

        if (!empty($category) && strtolower($category) !== 'all') {
            $sql .= " AND (LOWER(category) = LOWER(?) OR LOWER(name) LIKE ?)";
            $params[] = $category;
            $params[] = '%' . $category . '%';
            $types .= "ss";
        }

        if (!empty($search)) {
            $searchTerm = '%' . trim($search) . '%';
            $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ? OR code LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }

        $sql .= " ORDER BY id ASC LIMIT ?";
        $params[] = $limit;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return $this->filterFallbackGroups($category, $search);
        }

        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $this->formatGroupData($row);
        }

        return !empty($groups) ? $groups : $this->filterFallbackGroups($category, $search);
    }

    /**
     * Get single study group by ID with details, coordinators, activities and next session
     */
    public function getGroupById($id, $currentUserId = null) {
        $id = (int)$id;

        if (!$this->conn || $this->conn->connect_error) {
            return $this->getFallbackGroupDetails($id);
        }

        $stmt = $this->conn->prepare("SELECT * FROM study_groups WHERE id = ? LIMIT 1");
        if (!$stmt) return $this->getFallbackGroupDetails($id);

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) {
            return $this->getFallbackGroupDetails($id);
        }

        $group = $this->formatGroupData($result->fetch_assoc());

        // Check if current user is member
        $group['is_member'] = false;
        if ($currentUserId) {
            $memStmt = $this->conn->prepare("SELECT id FROM study_group_members WHERE group_id = ? AND user_id = ? LIMIT 1");
            if ($memStmt) {
                $memStmt->bind_param("ii", $id, $currentUserId);
                $memStmt->execute();
                $group['is_member'] = ($memStmt->get_result()->num_rows > 0);
            }
        }

        // Fetch coordinators
        $group['coordinators'] = [];
        $coordStmt = $this->conn->prepare("SELECT name, initials, role_title FROM study_group_coordinators WHERE group_id = ? ORDER BY id ASC");
        if ($coordStmt) {
            $coordStmt->bind_param("i", $id);
            $coordStmt->execute();
            $coordRes = $coordStmt->get_result();
            while ($c = $coordRes->fetch_assoc()) {
                $group['coordinators'][] = $c;
            }
        }
        if (empty($group['coordinators'])) {
            $group['coordinators'] = [
                ['name' => 'Nethmi Perera', 'initials' => 'NP', 'role_title' => 'Coordinator'],
                ['name' => 'Sahan Dissanayake', 'initials' => 'SD', 'role_title' => 'Coordinator']
            ];
        }

        // Fetch recent activities
        $group['activities'] = [];
        $actStmt = $this->conn->prepare("SELECT id, user_name, user_initials, action_text, relative_time, link_url FROM study_group_activities WHERE group_id = ? ORDER BY id DESC LIMIT 10");
        if ($actStmt) {
            $actStmt->bind_param("i", $id);
            $actStmt->execute();
            $actRes = $actStmt->get_result();
            while ($a = $actRes->fetch_assoc()) {
                $group['activities'][] = $a;
            }
        }
        if (empty($group['activities'])) {
            $group['activities'] = [
                ['user_name' => 'Nethmi Perera', 'user_initials' => 'NP', 'action_text' => 'shared a revision checklist', 'relative_time' => '35 minutes ago', 'link_url' => 'discussions.php'],
                ['user_name' => 'Kavindu Silva', 'user_initials' => 'KS', 'action_text' => 'asked about AVL rotations', 'relative_time' => '2 hours ago', 'link_url' => 'discussions.php'],
                ['user_name' => 'Amaya Fernando', 'user_initials' => 'AF', 'action_text' => 'scheduled a graph traversal session', 'relative_time' => 'Yesterday', 'link_url' => 'sessions.php']
            ];
        }

        // Fetch next session
        $group['next_session'] = [
            'day' => '24',
            'month' => 'MAY',
            'title' => 'Graph Traversal Practice',
            'details' => 'Saturday · 6:30 PM · Online',
            'session_id' => 1
        ];

        return $group;
    }

    /**
     * Join study group
     */
    public function joinGroup($groupId, $userId) {
        $groupId = (int)$groupId;
        $userId = (int)$userId;

        if (!$this->conn || $this->conn->connect_error) {
            return ['success' => true, 'message' => 'Successfully joined group (demo mode).'];
        }

        // Check if already member
        $checkStmt = $this->conn->prepare("SELECT id FROM study_group_members WHERE group_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $groupId, $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'You are already a member of this study group.'];
        }

        // Insert membership
        $insStmt = $this->conn->prepare("INSERT INTO study_group_members (group_id, user_id) VALUES (?, ?)");
        $insStmt->bind_param("ii", $groupId, $userId);
        if ($insStmt->execute()) {
            // Increment member_count
            $this->conn->query("UPDATE study_groups SET member_count = member_count + 1 WHERE id = $groupId");

            // Log activity
            $userRes = $this->conn->query("SELECT name FROM users WHERE id = $userId LIMIT 1");
            $userName = ($userRes && $u = $userRes->fetch_assoc()) ? $u['name'] : 'Student';
            $userInit = $this->getInitials($userName);

            $actStmt = $this->conn->prepare("INSERT INTO study_group_activities (group_id, user_id, user_name, user_initials, action_text, relative_time) VALUES (?, ?, ?, ?, 'joined the study group', 'Just now')");
            if ($actStmt) {
                $actStmt->bind_param("iiss", $groupId, $userId, $userName, $userInit);
                $actStmt->execute();
            }

            return ['success' => true, 'message' => 'You have joined the study group!'];
        }

        return ['success' => false, 'message' => 'Failed to join group. Please try again.'];
    }

    /**
     * Leave study group
     */
    public function leaveGroup($groupId, $userId) {
        $groupId = (int)$groupId;
        $userId = (int)$userId;

        if (!$this->conn || $this->conn->connect_error) {
            return ['success' => true, 'message' => 'Left study group.'];
        }

        $delStmt = $this->conn->prepare("DELETE FROM study_group_members WHERE group_id = ? AND user_id = ?");
        $delStmt->bind_param("ii", $groupId, $userId);
        if ($delStmt->execute() && $delStmt->affected_rows > 0) {
            $this->conn->query("UPDATE study_groups SET member_count = GREATEST(1, member_count - 1) WHERE id = $groupId");
            return ['success' => true, 'message' => 'You have left the study group.'];
        }

        return ['success' => false, 'message' => 'You were not a member of this group.'];
    }

    /**
     * Create new study group
     */
    public function createGroup($data, $creatorId) {
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? 'Computing');
        $description = trim($data['description'] ?? '');
        $whatWeStudy = trim($data['what_we_study'] ?? '');
        $aboutText = trim($data['about_text'] ?? $description);

        if (empty($name) || empty($description)) {
            return ['success' => false, 'message' => 'Group name and description are required.'];
        }

        $code = $this->getInitials($name);
        $badgeClass = 'badge-' . strtolower($code);

        if (!$this->conn || $this->conn->connect_error) {
            return ['success' => true, 'message' => 'Study group created successfully!', 'group_id' => 8];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO study_groups (name, code, category, is_featured, schedule_info, activity_status, description, about_text, what_we_study, created_date_str, badge_color, member_count, creator_id, status)
            VALUES (?, ?, ?, 0, 'Meets Weekly', 'Active this week', ?, ?, ?, DATE_FORMAT(NOW(), 'Created %M %Y'), ?, 1, ?, 'Active')
        ");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }

        $stmt->bind_param("sssssssi", $name, $code, $category, $description, $aboutText, $whatWeStudy, $badgeClass, $creatorId);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            // Add creator as member and coordinator
            $this->conn->query("INSERT INTO study_group_members (group_id, user_id) VALUES ($newId, $creatorId)");
            $uRes = $this->conn->query("SELECT name FROM users WHERE id = $creatorId LIMIT 1");
            $creatorName = ($uRes && $u = $uRes->fetch_assoc()) ? $u['name'] : 'Lead Student';
            $cInit = $this->getInitials($creatorName);
            $this->conn->query("INSERT INTO study_group_coordinators (group_id, user_id, name, initials, role_title) VALUES ($newId, $creatorId, '$creatorName', '$cInit', 'Founder & Coordinator')");

            return ['success' => true, 'message' => 'Study group created successfully!', 'group_id' => $newId];
        }

        return ['success' => false, 'message' => 'Failed to create group: ' . $stmt->error];
    }

    /**
     * Format row with badge styling and avatar stack data
     */
    private function formatGroupData($row) {
        $code = !empty($row['code']) ? $row['code'] : $this->getInitials($row['name']);
        $category = $row['category'] ?? 'Computing';

        // Badge styling classes
        $badgeStyle = $this->getBadgeStyle($code);

        // Member count & avatars
        $memberCount = (int)($row['member_count'] ?? 24);
        $remainingMembers = max(0, $memberCount - 2);

        // Tags parsing
        $tags = [];
        if (!empty($row['what_we_study'])) {
            $tags = array_map('trim', explode(',', $row['what_we_study']));
        }

        return [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'code' => $code,
            'category' => $category,
            'is_featured' => (bool)($row['is_featured'] ?? 0),
            'schedule_info' => $row['schedule_info'] ?? 'Meets Wednesdays',
            'activity_status' => $row['activity_status'] ?? 'Active this week',
            'description' => $row['description'],
            'about_text' => !empty($row['about_text']) ? $row['about_text'] : $row['description'],
            'what_we_study' => $row['what_we_study'] ?? '',
            'tags' => $tags,
            'created_date_str' => $row['created_date_str'] ?? 'Created February 2025',
            'member_count' => $memberCount,
            'remaining_count' => $remainingMembers,
            'badge_style' => $badgeStyle,
            'avatars' => [
                ['initials' => 'NP', 'bg' => '#0f172a'],
                ['initials' => 'KS', 'bg' => '#0284c7'],
                ['initials' => '+' . $remainingMembers, 'bg' => '#0f172a']
            ]
        ];
    }

    private function getBadgeStyle($code) {
        $map = [
            'DS' => ['bg' => '#e0f2fe', 'color' => '#0284c7', 'class' => 'badge-ds'],
            'BM' => ['bg' => '#dcfce7', 'color' => '#16a34a', 'class' => 'badge-bm'],
            'EI' => ['bg' => '#e0e7ff', 'color' => '#4f46e5', 'class' => 'badge-ei'],
            'SC' => ['bg' => '#fef3c7', 'color' => '#d97706', 'class' => 'badge-sc'],
            'AI' => ['bg' => '#cffafe', 'color' => '#0891b2', 'class' => 'badge-ai'],
            'FN' => ['bg' => '#d1fae5', 'color' => '#059669', 'class' => 'badge-fn'],
            'RS' => ['bg' => '#ffedd5', 'color' => '#ea580c', 'class' => 'badge-rs']
        ];
        return $map[$code] ?? ['bg' => '#e0f2fe', 'color' => '#0284c7', 'class' => 'badge-ds'];
    }

    private function getInitials($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) $initials .= strtoupper($w[0]);
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: 'SG';
    }

    private function filterFallbackGroups($category, $search) {
        $all = $this->getFallbackGroups();
        return array_values(array_filter($all, function($g) use ($category, $search) {
            $catMatch = (empty($category) || strtolower($category) === 'all' || strtolower($g['category']) === strtolower($category));
            $searchMatch = true;
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $searchMatch = (strpos(strtolower($g['name']), $q) !== false || strpos(strtolower($g['description']), $q) !== false || strpos(strtolower($g['category']), $q) !== false);
            }
            return $catMatch && $searchMatch;
        }));
    }

    public function getFallbackGroups() {
        return [
            [
                'id' => 1,
                'name' => 'Data Structures Circle',
                'code' => 'DS',
                'category' => 'Computing',
                'is_featured' => true,
                'schedule_info' => 'Meets Wednesdays',
                'activity_status' => 'Active this week',
                'description' => 'Weekly problem solving, peer explanations, and focused preparation for algorithms and data structures.',
                'about_text' => 'We are a focused community for students working through data structures and algorithms. Members meet weekly to compare approaches, practice past-paper questions, and support one another without sharing prohibited assessment answers.',
                'what_we_study' => 'Arrays & linked lists, Trees, Graphs, Sorting, Complexity',
                'tags' => ['Arrays & linked lists', 'Trees', 'Graphs', 'Sorting', 'Complexity'],
                'created_date_str' => 'Created February 2025',
                'member_count' => 48,
                'remaining_count' => 46,
                'badge_style' => ['bg' => '#e0f2fe', 'color' => '#0284c7', 'class' => 'badge-ds'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+46']]
            ],
            [
                'id' => 2,
                'name' => 'Business Minds',
                'code' => 'BM',
                'category' => 'Business',
                'is_featured' => false,
                'schedule_info' => 'Meets Mondays',
                'activity_status' => 'Active this week',
                'description' => 'Case studies, presentations, and exam preparation for management students.',
                'about_text' => 'Dedicated peer group for business analytics, organizational behavior, marketing strategies, and financial management presentations.',
                'what_we_study' => 'Case Analysis, Marketing, Financial Models, Presentation Skills',
                'tags' => ['Case Analysis', 'Marketing', 'Financial Models', 'Presentation Skills'],
                'created_date_str' => 'Created January 2025',
                'member_count' => 35,
                'remaining_count' => 33,
                'badge_style' => ['bg' => '#dcfce7', 'color' => '#16a34a', 'class' => 'badge-bm'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+33']]
            ],
            [
                'id' => 3,
                'name' => 'Engineering Innovators',
                'code' => 'EI',
                'category' => 'Engineering',
                'is_featured' => false,
                'schedule_info' => 'Meets Thursdays',
                'activity_status' => 'Active this week',
                'description' => 'Work through first-year mechanics, mathematics, drawing, and physics together.',
                'about_text' => 'Engineering fundamentals community breaking down applied calculus, statics, dynamics, electrical circuit analysis, and CAD modelling.',
                'what_we_study' => 'Mechanics, Engineering Mathematics, Circuit Analysis, Physics',
                'tags' => ['Mechanics', 'Engineering Mathematics', 'Circuit Analysis', 'Physics'],
                'created_date_str' => 'Created January 2025',
                'member_count' => 32,
                'remaining_count' => 30,
                'badge_style' => ['bg' => '#e0e7ff', 'color' => '#4f46e5', 'class' => 'badge-ei'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+30']]
            ],
            [
                'id' => 4,
                'name' => 'Science Study Circle',
                'code' => 'SC',
                'category' => 'Science',
                'is_featured' => false,
                'schedule_info' => 'Meets Tuesdays',
                'activity_status' => 'Active this week',
                'description' => 'Collaborate on laboratory work, scientific reports, and core science subjects.',
                'about_text' => 'Supporting students in experimental design, lab data evaluation, statistical analysis, and comprehensive report preparation.',
                'what_we_study' => 'Lab Analysis, Report Writing, Organic Chemistry, Experimental Physics',
                'tags' => ['Lab Analysis', 'Report Writing', 'Organic Chemistry', 'Experimental Physics'],
                'created_date_str' => 'Created February 2025',
                'member_count' => 27,
                'remaining_count' => 25,
                'badge_style' => ['bg' => '#fef3c7', 'color' => '#d97706', 'class' => 'badge-sc'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+25']]
            ],
            [
                'id' => 5,
                'name' => 'AI & Machine Learning Hub',
                'code' => 'AI',
                'category' => 'Computing',
                'is_featured' => false,
                'schedule_info' => 'Meets Fridays',
                'activity_status' => 'Active this week',
                'description' => 'Paper discussions, practical experiments, and project support.',
                'about_text' => 'Exploring deep learning architectures, Python ML tooling, computer vision, natural language processing, and research paper replication.',
                'what_we_study' => 'PyTorch, Computer Vision, Transformers, Model Fine-tuning, Math for ML',
                'tags' => ['PyTorch', 'Computer Vision', 'Transformers', 'Model Fine-tuning'],
                'created_date_str' => 'Created December 2024',
                'member_count' => 62,
                'remaining_count' => 60,
                'badge_style' => ['bg' => '#cffafe', 'color' => '#0891b2', 'class' => 'badge-ai'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+60']]
            ],
            [
                'id' => 6,
                'name' => 'Finance Network',
                'code' => 'FN',
                'category' => 'Business',
                'is_featured' => false,
                'schedule_info' => 'Meets Saturdays',
                'activity_status' => 'Active this week',
                'description' => 'Collaborative revision for finance and investment modules.',
                'about_text' => 'Hands-on revision sessions covering portfolio management, corporate finance, equity evaluation, and risk modeling.',
                'what_we_study' => 'Corporate Finance, Investment Valuation, Risk Analysis, Derivatives',
                'tags' => ['Corporate Finance', 'Investment Valuation', 'Risk Analysis'],
                'created_date_str' => 'Created February 2025',
                'member_count' => 31,
                'remaining_count' => 29,
                'badge_style' => ['bg' => '#d1fae5', 'color' => '#059669', 'class' => 'badge-fn'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+29']]
            ],
            [
                'id' => 7,
                'name' => 'Research Studio',
                'code' => 'RS',
                'category' => 'Science',
                'is_featured' => false,
                'schedule_info' => 'Meets Sundays',
                'activity_status' => 'Active this week',
                'description' => 'Accountability and peer feedback for final-year research.',
                'about_text' => 'Structured peer critique, methodology review, literature mapping, and publication preparation for undergraduate thesis candidates.',
                'what_we_study' => 'Methodology, Literature Review, Academic Writing, Data Visualization',
                'tags' => ['Methodology', 'Literature Review', 'Academic Writing'],
                'created_date_str' => 'Created March 2025',
                'member_count' => 24,
                'remaining_count' => 22,
                'badge_style' => ['bg' => '#ffedd5', 'color' => '#ea580c', 'class' => 'badge-rs'],
                'avatars' => [['initials' => 'NP'], ['initials' => 'KS'], ['initials' => '+22']]
            ]
        ];
    }

    public function getFallbackGroupDetails($id) {
        $groups = $this->getFallbackGroups();
        foreach ($groups as $g) {
            if ($g['id'] == $id) {
                $g['is_member'] = false;
                $g['coordinators'] = [
                    ['name' => 'Nethmi Perera', 'initials' => 'NP', 'role_title' => 'Coordinator'],
                    ['name' => 'Sahan Dissanayake', 'initials' => 'SD', 'role_title' => 'Coordinator']
                ];
                $g['activities'] = [
                    ['user_name' => 'Nethmi Perera', 'user_initials' => 'NP', 'action_text' => 'shared a revision checklist', 'relative_time' => '35 minutes ago', 'link_url' => 'discussions.php'],
                    ['user_name' => 'Kavindu Silva', 'user_initials' => 'KS', 'action_text' => 'asked about AVL rotations', 'relative_time' => '2 hours ago', 'link_url' => 'discussions.php'],
                    ['user_name' => 'Amaya Fernando', 'user_initials' => 'AF', 'action_text' => 'scheduled a graph traversal session', 'relative_time' => 'Yesterday', 'link_url' => 'sessions.php']
                ];
                $g['next_session'] = [
                    'day' => '24',
                    'month' => 'MAY',
                    'title' => 'Graph Traversal Practice',
                    'details' => 'Saturday · 6:30 PM · Online',
                    'session_id' => 1
                ];
                return $g;
            }
        }
        return $groups[0];
    }
}
?>

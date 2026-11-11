<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY GROUP DATABASE MIGRATION & SEEDER
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Running Study Group Migration...\n";

    // 1. Check & add columns to study_groups table
    $columnsToAdd = [
        'code' => "VARCHAR(10) DEFAULT 'SG' AFTER name",
        'category' => "VARCHAR(50) DEFAULT 'Computing' AFTER code",
        'is_featured' => "TINYINT(1) DEFAULT 0 AFTER category",
        'schedule_info' => "VARCHAR(100) DEFAULT 'Meets Wednesdays' AFTER is_featured",
        'activity_status' => "VARCHAR(50) DEFAULT 'Active this week' AFTER schedule_info",
        'about_text' => "TEXT NULL AFTER description",
        'what_we_study' => "VARCHAR(255) NULL AFTER about_text",
        'created_date_str' => "VARCHAR(50) DEFAULT 'Created February 2025' AFTER what_we_study",
        'badge_color' => "VARCHAR(30) DEFAULT 'badge-ds' AFTER created_date_str"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM study_groups LIKE '$col'")->fetch();
        if (!$checkCol) {
            $pdo->exec("ALTER TABLE study_groups ADD COLUMN $col $definition");
            echo "Added column '$col' to study_groups.\n";
        }
    }

    // 2. Create study_group_members table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS study_group_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL,
            user_id INT NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_group_user (group_id, user_id),
            FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'study_group_members' verified.\n";

    // 3. Create study_group_activities table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS study_group_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL,
            user_id INT NULL,
            user_name VARCHAR(100) NOT NULL,
            user_initials VARCHAR(10) NOT NULL,
            action_text VARCHAR(255) NOT NULL,
            relative_time VARCHAR(100) NOT NULL,
            link_url VARCHAR(255) DEFAULT '#',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'study_group_activities' verified.\n";

    // 4. Create study_group_coordinators table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS study_group_coordinators (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL,
            user_id INT NULL,
            name VARCHAR(100) NOT NULL,
            initials VARCHAR(10) NOT NULL,
            role_title VARCHAR(100) DEFAULT 'Coordinator',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'study_group_coordinators' verified.\n";

    // 5. Seed or Update the 7 study groups exactly as shown in the PDF reference
    $groups = [
        [
            'id' => 1,
            'name' => 'Data Structures Circle',
            'code' => 'DS',
            'category' => 'Computing',
            'is_featured' => 1,
            'schedule_info' => 'Meets Wednesdays',
            'activity_status' => 'Active this week',
            'description' => 'Weekly problem solving, peer explanations, and focused preparation for algorithms and data structures.',
            'about_text' => 'We are a focused community for students working through data structures and algorithms. Members meet weekly to compare approaches, practice past-paper questions, and support one another without sharing prohibited assessment answers.',
            'what_we_study' => 'Arrays & linked lists, Trees, Graphs, Sorting, Complexity',
            'created_date_str' => 'Created February 2025',
            'badge_color' => 'badge-ds',
            'member_count' => 48
        ],
        [
            'id' => 2,
            'name' => 'Business Minds',
            'code' => 'BM',
            'category' => 'Business',
            'is_featured' => 0,
            'schedule_info' => 'Meets Mondays',
            'activity_status' => 'Active this week',
            'description' => 'Case studies, presentations, and exam preparation for management students.',
            'about_text' => 'Dedicated peer group for business analytics, organizational behavior, marketing strategies, and financial management presentations.',
            'what_we_study' => 'Case Analysis, Marketing, Financial Models, Presentation Skills',
            'created_date_str' => 'Created January 2025',
            'badge_color' => 'badge-bm',
            'member_count' => 35
        ],
        [
            'id' => 3,
            'name' => 'Engineering Innovators',
            'code' => 'EI',
            'category' => 'Engineering',
            'is_featured' => 0,
            'schedule_info' => 'Meets Thursdays',
            'activity_status' => 'Active this week',
            'description' => 'Work through first-year mechanics, mathematics, drawing, and physics together.',
            'about_text' => 'Engineering fundamentals community breaking down applied calculus, statics, dynamics, electrical circuit analysis, and CAD modelling.',
            'what_we_study' => 'Mechanics, Engineering Mathematics, Circuit Analysis, Physics',
            'created_date_str' => 'Created January 2025',
            'badge_color' => 'badge-ei',
            'member_count' => 32
        ],
        [
            'id' => 4,
            'name' => 'Science Study Circle',
            'code' => 'SC',
            'category' => 'Science',
            'is_featured' => 0,
            'schedule_info' => 'Meets Tuesdays',
            'activity_status' => 'Active this week',
            'description' => 'Collaborate on laboratory work, scientific reports, and core science subjects.',
            'about_text' => 'Supporting students in experimental design, lab data evaluation, statistical analysis, and comprehensive report preparation.',
            'what_we_study' => 'Lab Analysis, Report Writing, Organic Chemistry, Experimental Physics',
            'created_date_str' => 'Created February 2025',
            'badge_color' => 'badge-sc',
            'member_count' => 27
        ],
        [
            'id' => 5,
            'name' => 'AI & Machine Learning Hub',
            'code' => 'AI',
            'category' => 'Computing',
            'is_featured' => 0,
            'schedule_info' => 'Meets Fridays',
            'activity_status' => 'Active this week',
            'description' => 'Paper discussions, practical experiments, and project support.',
            'about_text' => 'Exploring deep learning architectures, Python ML tooling, computer vision, natural language processing, and research paper replication.',
            'what_we_study' => 'PyTorch, Computer Vision, Transformers, Model Fine-tuning, Math for ML',
            'created_date_str' => 'Created December 2024',
            'badge_color' => 'badge-ai',
            'member_count' => 62
        ],
        [
            'id' => 6,
            'name' => 'Finance Network',
            'code' => 'FN',
            'category' => 'Business',
            'is_featured' => 0,
            'schedule_info' => 'Meets Saturdays',
            'activity_status' => 'Active this week',
            'description' => 'Collaborative revision for finance and investment modules.',
            'about_text' => 'Hands-on revision sessions covering portfolio management, corporate finance, equity evaluation, and risk modeling.',
            'what_we_study' => 'Corporate Finance, Investment Valuation, Risk Analysis, Derivatives',
            'created_date_str' => 'Created February 2025',
            'badge_color' => 'badge-fn',
            'member_count' => 31
        ],
        [
            'id' => 7,
            'name' => 'Research Studio',
            'code' => 'RS',
            'category' => 'Science',
            'is_featured' => 0,
            'schedule_info' => 'Meets Sundays',
            'activity_status' => 'Active this week',
            'description' => 'Accountability and peer feedback for final-year research.',
            'about_text' => 'Structured peer critique, methodology review, literature mapping, and publication preparation for undergraduate thesis candidates.',
            'what_we_study' => 'Methodology, Literature Review, Academic Writing, Data Visualization',
            'created_date_str' => 'Created March 2025',
            'badge_color' => 'badge-rs',
            'member_count' => 24
        ]
    ];

    $upsertStmt = $pdo->prepare("
        INSERT INTO study_groups (id, name, code, category, is_featured, schedule_info, activity_status, description, about_text, what_we_study, created_date_str, badge_color, member_count, creator_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 2, 'Active')
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            code = VALUES(code),
            category = VALUES(category),
            is_featured = VALUES(is_featured),
            schedule_info = VALUES(schedule_info),
            activity_status = VALUES(activity_status),
            description = VALUES(description),
            about_text = VALUES(about_text),
            what_we_study = VALUES(what_we_study),
            created_date_str = VALUES(created_date_str),
            badge_color = VALUES(badge_color),
            member_count = VALUES(member_count),
            status = 'Active'
    ");

    foreach ($groups as $g) {
        $upsertStmt->execute([
            $g['id'],
            $g['name'],
            $g['code'],
            $g['category'],
            $g['is_featured'],
            $g['schedule_info'],
            $g['activity_status'],
            $g['description'],
            $g['about_text'],
            $g['what_we_study'],
            $g['created_date_str'],
            $g['badge_color'],
            $g['member_count']
        ]);
        echo "Seeded/Updated group: {$g['name']} ({$g['code']})\n";
    }

    // 6. Seed Coordinators for Data Structures Circle (Group #1)
    $pdo->exec("DELETE FROM study_group_coordinators WHERE group_id = 1");
    $coordStmt = $pdo->prepare("INSERT INTO study_group_coordinators (group_id, user_id, name, initials, role_title) VALUES (?, ?, ?, ?, ?)");
    $coordStmt->execute([1, 3, 'Nethmi Perera', 'NP', 'Coordinator']);
    $coordStmt->execute([1, 4, 'Sahan Dissanayake', 'SD', 'Coordinator']);
    echo "Seeded coordinators for Data Structures Circle.\n";

    // 7. Seed Recent Activities for Data Structures Circle (Group #1)
    $pdo->exec("DELETE FROM study_group_activities WHERE group_id = 1");
    $actStmt = $pdo->prepare("INSERT INTO study_group_activities (group_id, user_id, user_name, user_initials, action_text, relative_time, link_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $actStmt->execute([1, 3, 'Nethmi Perera', 'NP', 'shared a revision checklist', '35 minutes ago', 'discussions.php']);
    $actStmt->execute([1, 4, 'Kavindu Silva', 'KS', 'asked about AVL rotations', '2 hours ago', 'discussions.php']);
    $actStmt->execute([1, null, 'Amaya Fernando', 'AF', 'scheduled a graph traversal session', 'Yesterday', 'sessions.php']);
    echo "Seeded activities for Data Structures Circle.\n";

    // 8. Ensure the next session exists in study_sessions
    $sessionCheck = $pdo->query("SELECT id FROM study_sessions WHERE title LIKE '%Graph Traversal%' LIMIT 1")->fetch();
    if (!$sessionCheck) {
        $pdo->exec("
            INSERT INTO study_sessions (title, description, faculty, host_id, session_date, location_details, capacity, enrolled_count, status)
            VALUES (
                'Graph Traversal Practice',
                'Comprehensive practice on BFS, DFS, Dijkstra, and cycle detection algorithms.',
                'Computing',
                2,
                '2026-05-24 18:30:00',
                'Saturday · 6:30 PM · Online',
                30,
                22,
                'Scheduled'
            )
        ");
        echo "Seeded next session 'Graph Traversal Practice'.\n";
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
// ============================================================================
// NSBM STUDY CIRCLE - DISCUSSION SYSTEM DATABASE MIGRATION
// Creates categories, votes, replies, updates schema & indexes
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

global $conn;
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
}

echo "=== STARTING DISCUSSION SYSTEM MIGRATION ===" . PHP_EOL;

// 1. Categories Table
$conn->query("
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "[OK] Table 'categories' verified." . PHP_EOL;

// Seed initial categories
$initialCategories = [
    ['name' => 'Computing', 'desc' => 'Software engineering, algorithms, networks and systems architecture'],
    ['name' => 'Business', 'desc' => 'Management, marketing strategy, economics and organizational behaviour'],
    ['name' => 'Engineering', 'desc' => 'Mechanics, electrical circuits, thermodynamics and materials science'],
    ['name' => 'Science', 'desc' => 'Physical sciences, chemistry, biomedical and research methodology'],
    ['name' => 'Accounting', 'desc' => 'Financial accounting, cash flow, cost management and auditing'],
    ['name' => 'Academic Skills', 'desc' => 'Literature reviews, citations, essay structure and exam techniques'],
    ['name' => 'Databases', 'desc' => 'SQL querying, relational design, normalization and indexing']
];

$stmtCat = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description=VALUES(description)");
foreach ($initialCategories as $c) {
    $stmtCat->bind_param("ss", $c['name'], $c['desc']);
    $stmtCat->execute();
}
echo "[OK] Seeded 7 categories." . PHP_EOL;

// 2. Enhance discussions table columns
$discCols = [];
$res = $conn->query("SHOW COLUMNS FROM discussions");
while ($r = $res->fetch_assoc()) $discCols[] = $r['Field'];

if (!in_array('status', $discCols)) {
    $conn->query("ALTER TABLE discussions ADD COLUMN status ENUM('Open', 'Closed', 'Resolved') DEFAULT 'Open' AFTER is_solved");
    echo "[OK] Added 'status' column to discussions." . PHP_EOL;
}
if (!in_array('updated_at', $discCols)) {
    $conn->query("ALTER TABLE discussions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    echo "[OK] Added 'updated_at' column to discussions." . PHP_EOL;
}

// 3. Enhance discussion_answers table columns
$ansCols = [];
$resAns = $conn->query("SHOW COLUMNS FROM discussion_answers");
while ($r = $resAns->fetch_assoc()) $ansCols[] = $r['Field'];

if (!in_array('updated_at', $ansCols)) {
    $conn->query("ALTER TABLE discussion_answers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    echo "[OK] Added 'updated_at' column to discussion_answers." . PHP_EOL;
}

// 4. Discussion Votes Table (Helpful Votes with Unique Constraint per target)
$conn->query("
    CREATE TABLE IF NOT EXISTS discussion_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        discussion_id INT NULL,
        answer_id INT NULL,
        vote_type ENUM('helpful') DEFAULT 'helpful',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
        FOREIGN KEY (answer_id) REFERENCES discussion_answers(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_discussion_vote (user_id, discussion_id),
        UNIQUE KEY unique_user_answer_vote (user_id, answer_id),
        INDEX idx_discussion_votes (discussion_id),
        INDEX idx_answer_votes (answer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "[OK] Table 'discussion_votes' verified." . PHP_EOL;

// 5. Discussion Replies Table (Replies to Answers)
$conn->query("
    CREATE TABLE IF NOT EXISTS discussion_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        answer_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (answer_id) REFERENCES discussion_answers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_answer_replies (answer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "[OK] Table 'discussion_replies' verified." . PHP_EOL;

// 6. Seed Sample Discussion Replies
$replies = [
    [
        'answer_id' => 1,
        'user_id' => 2, // Alex Silva
        'body' => "That makes a lot of sense! So for sorting objects in memory where stability is critical (like names ordered by age), merge sort or TimSort is always preferred."
    ],
    [
        'answer_id' => 1,
        'user_id' => 3, // Nethmi Perera
        'body' => "Exactly right, Alex. Java and Python both default to TimSort (a hybrid of merge sort and insertion sort) for this exact stability property."
    ]
];

$stmtRep = $conn->prepare("INSERT INTO discussion_replies (answer_id, user_id, body) VALUES (?, ?, ?)");
foreach ($replies as $rep) {
    // Check if duplicate exists
    $chk = $conn->prepare("SELECT id FROM discussion_replies WHERE answer_id = ? AND user_id = ? AND body = ? LIMIT 1");
    $chk->bind_param("iis", $rep['answer_id'], $rep['user_id'], $rep['body']);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        $stmtRep->bind_param("iis", $rep['answer_id'], $rep['user_id'], $rep['body']);
        $stmtRep->execute();
    }
}
echo "[OK] Seeded discussion replies." . PHP_EOL;

// 7. Seed Initial Votes for accurate initial counts
$initialVotes = [
    ['user_id' => 3, 'disc_id' => 1, 'ans_id' => null],
    ['user_id' => 4, 'disc_id' => 1, 'ans_id' => null],
    ['user_id' => 5, 'disc_id' => 1, 'ans_id' => null],
    ['user_id' => 2, 'disc_id' => null, 'ans_id' => 1],
    ['user_id' => 4, 'disc_id' => null, 'ans_id' => 1],
    ['user_id' => 5, 'disc_id' => null, 'ans_id' => 2]
];

foreach ($initialVotes as $v) {
    if ($v['disc_id']) {
        $vStmt = $conn->prepare("INSERT IGNORE INTO discussion_votes (user_id, discussion_id) VALUES (?, ?)");
        $vStmt->bind_param("ii", $v['user_id'], $v['disc_id']);
        $vStmt->execute();
    } else if ($v['ans_id']) {
        $vStmt = $conn->prepare("INSERT IGNORE INTO discussion_votes (user_id, answer_id) VALUES (?, ?)");
        $vStmt->bind_param("ii", $v['user_id'], $v['ans_id']);
        $vStmt->execute();
    }
}
echo "[OK] Seeded initial helpful votes." . PHP_EOL;

echo "=== MIGRATION COMPLETE SUCCESSFULLY! ===" . PHP_EOL;
?>

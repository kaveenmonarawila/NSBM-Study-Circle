<?php
require_once __DIR__ . '/../config/db_config.php';

global $conn;
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
}

echo "Updating Discussions Schema..." . PHP_EOL;

// 1. Add extra columns to discussions table if not exist
$columnsToAdd = [
    'category' => "VARCHAR(100) DEFAULT 'Computing' AFTER faculty",
    'tags' => "VARCHAR(255) DEFAULT 'algorithms, sorting' AFTER module_code",
    'views_count' => "INT DEFAULT 45 AFTER answers_count"
];

$existingCols = [];
$res = $conn->query("SHOW COLUMNS FROM discussions");
while ($row = $res->fetch_assoc()) {
    $existingCols[] = $row['Field'];
}

foreach ($columnsToAdd as $col => $definition) {
    if (!in_array($col, $existingCols)) {
        $sql = "ALTER TABLE discussions ADD COLUMN $col $definition";
        if ($conn->query($sql)) {
            echo "Added column '$col' to discussions." . PHP_EOL;
        } else {
            echo "Error adding column '$col': " . $conn->error . PHP_EOL;
        }
    }
}

// 2. Add author_role to discussion_answers if not exists
$ansCols = [];
$resAns = $conn->query("SHOW COLUMNS FROM discussion_answers");
while ($row = $resAns->fetch_assoc()) {
    $ansCols[] = $row['Field'];
}
if (!in_array('author_role', $ansCols)) {
    $conn->query("ALTER TABLE discussion_answers ADD COLUMN author_role VARCHAR(100) DEFAULT 'Student member' AFTER author_id");
    echo "Added column 'author_role' to discussion_answers." . PHP_EOL;
}

// 3. Seed exact discussions from PDF screenshots
$discussions = [
    [
        'id' => 1,
        'title' => 'How do you choose between merge sort and quicksort in practice?',
        'body' => 'I understand that both have average O(n log n) time complexity, while quicksort can degrade to O(n²). But when building an actual application, how should memory constraints, data order, and stability influence the choice?',
        'faculty' => 'Computing',
        'category' => 'Computing',
        'module_code' => 'CS2102',
        'tags' => 'algorithms, sorting, complexity',
        'author_id' => 2, // Akila Perera / Alex Silva
        'upvotes' => 18,
        'answers_count' => 3,
        'views_count' => 126
    ],
    [
        'id' => 2,
        'title' => 'Can someone explain the difference between cash flow and profit?',
        'body' => 'I\'m revising this topic and would appreciate a simple explanation with a practical example covering non-cash expenses, working capital, and revenue timing.',
        'faculty' => 'Business',
        'category' => 'Accounting',
        'module_code' => 'BUS102',
        'tags' => 'accounting, cashflow, finance',
        'author_id' => 4, // Kavindu Silva
        'upvotes' => 15,
        'answers_count' => 8,
        'views_count' => 98
    ],
    [
        'id' => 3,
        'title' => 'How do you document a reliable laboratory experiment?',
        'body' => 'I understand the theory, but I am unsure which constraints matter most in a real implementation when recording raw data, error propagation, and reagent calibration.',
        'faculty' => 'Science',
        'category' => 'Science',
        'module_code' => 'SCI104',
        'tags' => 'laboratory, methodology, research',
        'author_id' => 6, // Dinara Jayasinghe
        'upvotes' => 11,
        'answers_count' => 6,
        'views_count' => 74
    ],
    [
        'id' => 4,
        'title' => 'Why does normalization sometimes increase query complexity?',
        'body' => 'I\'m revising this topic and would appreciate a simple explanation with a practical example of when 3NF/BCNF joins hurt read latency compared to intentional denormalization.',
        'faculty' => 'Computing',
        'category' => 'Databases',
        'module_code' => 'CS2105',
        'tags' => 'databases, sql, normalization',
        'author_id' => 3, // Nethmi Perera
        'upvotes' => 9,
        'answers_count' => 4,
        'views_count' => 62
    ],
    [
        'id' => 5,
        'title' => 'Best way to structure a literature review for a research proposal?',
        'body' => 'I understand the theory, but I am unsure which constraints matter most in a real implementation when synthesizing thematic versus chronological literature analysis.',
        'faculty' => 'Computing',
        'category' => 'Academic Skills',
        'module_code' => 'COM106',
        'tags' => 'research, academic-writing, methodology',
        'author_id' => 5, // Ravindu Mendis
        'upvotes' => 7,
        'answers_count' => 3,
        'views_count' => 53
    ],
    [
        'id' => 6,
        'title' => 'How do you determine beam deflection under distributed loads?',
        'body' => 'Working through second-order differential equation methods and Macaulay’s method for simply supported beams with point and uniform loads.',
        'faculty' => 'Engineering',
        'category' => 'Engineering',
        'module_code' => 'ENG103',
        'tags' => 'mechanics, structures, calculus',
        'author_id' => 5,
        'upvotes' => 14,
        'answers_count' => 5,
        'views_count' => 88
    ]
];

$stmtDisc = $conn->prepare("
    INSERT INTO discussions (id, title, body, faculty, category, module_code, tags, author_id, upvotes, answers_count, views_count)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        title=VALUES(title), body=VALUES(body), faculty=VALUES(faculty), category=VALUES(category),
        module_code=VALUES(module_code), tags=VALUES(tags), upvotes=VALUES(upvotes),
        answers_count=VALUES(answers_count), views_count=VALUES(views_count)
");

foreach ($discussions as $d) {
    $stmtDisc->bind_param(
        "issssssiiii",
        $d['id'], $d['title'], $d['body'], $d['faculty'], $d['category'],
        $d['module_code'], $d['tags'], $d['author_id'], $d['upvotes'], $d['answers_count'], $d['views_count']
    );
    $stmtDisc->execute();
}
echo "Seeded " . count($discussions) . " discussions." . PHP_EOL;

// 4. Seed exact answers for Discussion 1 (from PDF Pages 6 & 7)
$answers = [
    [
        'id' => 1,
        'discussion_id' => 1,
        'author_id' => 3, // Sahan Dissanayake / Nethmi
        'author_role' => 'Top contributor · 1h ago',
        'body' => "In most application code, start with the language's standard sort because it is heavily optimized. If you need stability or predictable worst-case behavior, merge sort is easier to reason about. Quicksort is attractive for arrays because it is cache-friendly and usually works in-place.",
        'upvotes' => 12,
        'is_solution' => 1
    ],
    [
        'id' => 2,
        'discussion_id' => 1,
        'author_id' => 6, // Nimali Madushani
        'author_role' => 'Student member · 2h ago',
        'body' => "Memory is the clearest trade-off: classic merge sort needs additional O(n) storage, whereas quicksort can use O(log n) stack space on average. For linked lists, merge sort is often the natural choice because splitting and merging do not require array copies.",
        'upvotes' => 9,
        'is_solution' => 0
    ],
    [
        'id' => 3,
        'discussion_id' => 1,
        'author_id' => 4, // Student member
        'author_role' => 'Student member · 3h ago',
        'body' => "Also consider whether equal items must preserve their original order. That stability requirement often matters when sorting records by multiple fields.",
        'upvotes' => 6,
        'is_solution' => 0
    ]
];

$stmtAns = $conn->prepare("
    INSERT INTO discussion_answers (id, discussion_id, author_id, author_role, body, upvotes, is_solution)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        body=VALUES(body), upvotes=VALUES(upvotes), is_solution=VALUES(is_solution), author_role=VALUES(author_role)
");

foreach ($answers as $a) {
    $stmtAns->bind_param("iisssii", $a['id'], $a['discussion_id'], $a['author_id'], $a['author_role'], $a['body'], $a['upvotes'], $a['is_solution']);
    $stmtAns->execute();
}
echo "Seeded " . count($answers) . " answers for Discussion 1." . PHP_EOL;

echo "DISCUSSIONS SEEDING COMPLETE!" . PHP_EOL;


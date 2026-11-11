<?php
// ============================================================================
// NSBM STUDY CIRCLE - DATABASE MIGRATION & SEED FOR RESOURCES
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

global $conn;
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
}

echo "Applying Schema Updates..." . PHP_EOL;

// 1. Add extra columns to resources table if not exist
$columnsToAdd = [
    'page_count' => "INT DEFAULT 1 AFTER file_size_mb",
    'language' => "VARCHAR(50) DEFAULT 'English' AFTER page_count",
    'ratings_count' => "INT DEFAULT 0 AFTER rating",
    'about_text' => "TEXT NULL AFTER description",
    'whats_included' => "TEXT NULL AFTER about_text",
    'preview_badge' => "VARCHAR(100) NULL AFTER whats_included",
    'preview_heading' => "VARCHAR(255) NULL AFTER preview_badge",
    'preview_subheading' => "VARCHAR(255) NULL AFTER preview_heading"
];

$existingCols = [];
$res = $conn->query("SHOW COLUMNS FROM resources");
while ($row = $res->fetch_assoc()) {
    $existingCols[] = $row['Field'];
}

foreach ($columnsToAdd as $col => $definition) {
    if (!in_array($col, $existingCols)) {
        $sql = "ALTER TABLE resources ADD COLUMN $col $definition";
        if ($conn->query($sql)) {
            echo "Added column '$col' to resources." . PHP_EOL;
        } else {
            echo "Error adding column '$col': " . $conn->error . PHP_EOL;
        }
    }
}

// 2. Create resource_reviews table if not exists
$createReviewsSql = "
CREATE TABLE IF NOT EXISTS resource_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_resource_review (user_id, resource_id)
);";

if ($conn->query($createReviewsSql)) {
    echo "Table 'resource_reviews' ready." . PHP_EOL;
} else {
    echo "Error creating table 'resource_reviews': " . $conn->error . PHP_EOL;
}

// 3. Populate first-year subjects
$subjects = [
    // Computing
    ['code' => 'COM101', 'name' => 'Intro to Programming', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'Core programming logic in Python and C'],
    ['code' => 'COM102', 'name' => 'Computer Systems', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'Hardware, architecture and operating systems'],
    ['code' => 'COM103', 'name' => 'Algorithms & Logic', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'Algorithmic thinking and problem solving'],
    ['code' => 'COM104', 'name' => 'Web Development', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'HTML, CSS, JavaScript, and responsive design'],
    ['code' => 'COM105', 'name' => 'Discrete Mathematics', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'Sets, graphs, combinatorics, and proof methods'],
    ['code' => 'COM106', 'name' => 'Database Principles', 'area_id' => 1, 'year' => 'Year 1', 'desc' => 'Relational database theory and SQL'],
    ['code' => 'CS2102', 'name' => 'Data Structures', 'area_id' => 1, 'year' => 'Year 2', 'desc' => 'Trees, graphs, heaps, and advanced data structures'],
    ['code' => 'CS2105', 'name' => 'Database Systems', 'area_id' => 1, 'year' => 'Year 2', 'desc' => 'Normalization, indexing, and transactions'],

    // Business
    ['code' => 'BUS101', 'name' => 'Principles of Management', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Managerial functions, strategy, and leadership'],
    ['code' => 'BUS102', 'name' => 'Financial Accounting', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Double-entry bookkeeping and financial statements'],
    ['code' => 'BUS103', 'name' => 'Marketing Fundamentals', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Market research, consumer behaviour, and 4Ps'],
    ['code' => 'BUS104', 'name' => 'Business Economics', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Micro and macroeconomics principles'],
    ['code' => 'BUS105', 'name' => 'Business Statistics', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Probability, hypothesis testing, and regression'],
    ['code' => 'BUS106', 'name' => 'Organizational Behaviour', 'area_id' => 2, 'year' => 'Year 1', 'desc' => 'Team dynamics, culture, and motivation'],

    // Engineering
    ['code' => 'ENG101', 'name' => 'Engineering Mathematics I', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Calculus, linear algebra, and complex numbers'],
    ['code' => 'ENG102', 'name' => 'Engineering Physics', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Mechanics, optics, and thermodynamics'],
    ['code' => 'ENG103', 'name' => 'Mechanics of Materials', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Stress, strain, torsion, and beam bending'],
    ['code' => 'ENG104', 'name' => 'CAD & Drafting Basics', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Orthographic projection and 3D modeling'],
    ['code' => 'ENG105', 'name' => 'Materials Science', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Metals, polymers, ceramics, and material properties'],
    ['code' => 'ENG106', 'name' => 'Electrical Circuit Theory', 'area_id' => 3, 'year' => 'Year 1', 'desc' => 'Ohm’s law, Kirchhoff’s laws, and AC/DC circuits'],

    // Science
    ['code' => 'SCI101', 'name' => 'General Chemistry I', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Atomic structure, bonding, and stoichiometry'],
    ['code' => 'SCI102', 'name' => 'Cell Biology & Genetics', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Cell structure, mitosis, meiosis, and heredity'],
    ['code' => 'SCI103', 'name' => 'Applied Statistics', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Biostatistics and experimental data analysis'],
    ['code' => 'SCI104', 'name' => 'Laboratory Techniques', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Spectrophotometry, titration, and microscope work'],
    ['code' => 'SCI105', 'name' => 'Physics for Sciences', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Kinematics, wave motion, and fluids'],
    ['code' => 'SCI106', 'name' => 'Scientific Communication', 'area_id' => 4, 'year' => 'Year 1', 'desc' => 'Scientific writing, citations, and presentations']
];

$stmtSub = $conn->prepare("INSERT INTO subjects (code, name, area_id, academic_year, description, status) VALUES (?, ?, ?, ?, ?, 'Active') ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)");
foreach ($subjects as $s) {
    $stmtSub->bind_param("ssiss", $s['code'], $s['name'], $s['area_id'], $s['year'], $s['desc']);
    $stmtSub->execute();
}
echo "Populated " . count($subjects) . " subjects into MySQL." . PHP_EOL;

// 4. Seed 7 Comprehensive Resources matching UI
$allResources = [
    [
        'id' => 1,
        'title' => 'Data Structures - Complete Revision Guide',
        'description' => 'Clear summaries, worked examples, and exam-focused notes covering the full module.',
        'faculty' => 'Computing',
        'module_code' => 'CS2102',
        'academic_year' => 'Year 2',
        'file_path' => 'uploads/resources/data_structures_revision_guide.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 5.80,
        'page_count' => 42,
        'downloads' => 1284,
        'rating' => 4.9,
        'ratings_count' => 38,
        'language' => 'English',
        'uploaded_by' => 3, // Nethmi Perera
        'status' => 'Approved',
        'about_text' => 'A structured revision guide covering arrays, linked lists, stacks, queues, trees, graphs, searching, sorting, and algorithm analysis. Each chapter includes concise explanations followed by worked examples and quick-check questions.',
        'whats_included' => json_encode([
            'Ten topic summaries aligned to the module outline',
            'Visual diagrams for trees and graph traversal',
            'Twenty-four worked algorithm questions',
            'Final two-page exam checklist'
        ]),
        'preview_badge' => 'DATA STRUCTURES',
        'preview_heading' => 'Exam Revision Guide',
        'preview_subheading' => 'Core concepts, visual explanations, and worked examples for confident revision.'
    ],
    [
        'id' => 2,
        'title' => 'Financial Accounting: Core Concepts',
        'description' => 'A concise lecture companion with key formulas, journal entries, and practice questions.',
        'faculty' => 'Business',
        'module_code' => 'BUS102',
        'academic_year' => 'Year 1',
        'file_path' => 'uploads/resources/financial_accounting_core_concepts.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 3.40,
        'page_count' => 28,
        'downloads' => 864,
        'rating' => 4.8,
        'ratings_count' => 24,
        'language' => 'English',
        'uploaded_by' => 4, // Kavindu Silva
        'status' => 'Approved',
        'about_text' => 'Covers double-entry bookkeeping, trial balance preparation, profit and loss calculations, balance sheet structures, and cash flow reconciling with end-of-chapter practice examples.',
        'whats_included' => json_encode([
            'Step-by-step balance sheet and income statement worksheets',
            'Key ratio formulas cheat sheet',
            'Ten worked cash flow adjustments'
        ]),
        'preview_badge' => 'FINANCIAL ACCOUNTING',
        'preview_heading' => 'Core Concepts & Principles',
        'preview_subheading' => 'A complete primer on transaction recording and financial statement compilation.'
    ],
    [
        'id' => 3,
        'title' => 'Business Statistics Formula Book',
        'description' => 'A clean reference for probability, regression, and hypothesis testing.',
        'faculty' => 'Business',
        'module_code' => 'BUS105',
        'academic_year' => 'Year 1',
        'file_path' => 'uploads/resources/business_statistics_formula_book.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 1.80,
        'page_count' => 14,
        'downloads' => 518,
        'rating' => 4.6,
        'ratings_count' => 15,
        'language' => 'English',
        'uploaded_by' => 4,
        'status' => 'Approved',
        'about_text' => 'Quick lookup companion containing all critical formulas for standard deviation, normal distributions, confidence intervals, ANOVA tables, and linear regression analysis.',
        'whats_included' => json_encode([
            'All first-year statistics formulas organized by topic',
            'Standard normal z-table and t-table reference appendices',
            'Hypothesis test decision tree'
        ]),
        'preview_badge' => 'BUSINESS STATISTICS',
        'preview_heading' => 'Formula Reference Book',
        'preview_subheading' => 'Probability distributions, test statistics, and regression equations.'
    ],
    [
        'id' => 4,
        'title' => 'Engineering Mechanics - First-Year Guide',
        'description' => 'Core mechanics principles, force diagrams, worked examples, and revision questions.',
        'faculty' => 'Engineering',
        'module_code' => 'ENG103',
        'academic_year' => 'Year 1',
        'file_path' => 'uploads/resources/engineering_mechanics_guide.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 6.20,
        'page_count' => 50,
        'downloads' => 706,
        'rating' => 4.7,
        'ratings_count' => 19,
        'language' => 'English',
        'uploaded_by' => 5, // Ravindu Mendis
        'status' => 'Approved',
        'about_text' => 'Statics and dynamics fundamentals covering equilibrium of particles, free-body diagrams, truss analysis, moments of inertia, and kinematics of rigid bodies.',
        'whats_included' => json_encode([
            'Detailed method of joints and sections truss examples',
            'Friction and virtual work review sheets',
            'Fifteen fully solved exam problems'
        ]),
        'preview_badge' => 'ENGINEERING MECHANICS',
        'preview_heading' => 'Statics & Dynamics Guide',
        'preview_subheading' => 'Comprehensive force resolution and equilibrium problem walkthroughs.'
    ],
    [
        'id' => 5,
        'title' => 'Scientific Communication: Lab Report Template',
        'description' => 'A standard reporting layout for laboratory experiments and academic write-ups.',
        'faculty' => 'Science',
        'module_code' => 'SCI106',
        'academic_year' => 'Year 1',
        'file_path' => 'uploads/resources/lab_report_template.docx',
        'file_type' => 'DOCX',
        'file_size_mb' => 1.20,
        'page_count' => 8,
        'downloads' => 420,
        'rating' => 4.5,
        'ratings_count' => 11,
        'language' => 'English',
        'uploaded_by' => 6, // Dinara Jayasinghe
        'status' => 'Approved',
        'about_text' => 'Standard template formatting for introduction, methodology, materials, data tables, error calculations, results discussion, and APA 7th style citations.',
        'whats_included' => json_encode([
            'Pre-formatted Microsoft Word style template (.docx)',
            'Plagiarism and citation guidelines checklist',
            'Sample graphs and tabular layout styling'
        ]),
        'preview_badge' => 'SCIENTIFIC COMMUNICATION',
        'preview_heading' => 'Lab Report Template',
        'preview_subheading' => 'Academic report formatting, table guidelines, and citation standards.'
    ],
    [
        'id' => 6,
        'title' => 'Database Systems - SQL & Normalization Companion',
        'description' => 'Relational database concepts, SQL query exercises, and step-by-step 1NF to BCNF normalization.',
        'faculty' => 'Computing',
        'module_code' => 'CS2105',
        'academic_year' => 'Year 2',
        'file_path' => 'uploads/resources/sql_normalization_companion.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 4.10,
        'page_count' => 36,
        'downloads' => 942,
        'rating' => 4.8,
        'ratings_count' => 31,
        'language' => 'English',
        'uploaded_by' => 2, // Alex Silva
        'status' => 'Approved',
        'about_text' => 'A hands-on reference guide covering relational schema design, functional dependencies, 1NF, 2NF, 3NF, BCNF decomposition, and practical complex JOIN and aggregation queries in MySQL/PostgreSQL.',
        'whats_included' => json_encode([
            'Full normalization walkthrough with 8 realistic scenarios',
            'Fifty practical SQL practice queries with solutions',
            'Transaction ACID properties & indexing tips'
        ]),
        'preview_badge' => 'DATABASE SYSTEMS',
        'preview_heading' => 'SQL & Normalization Guide',
        'preview_subheading' => 'Relational schemas, functional dependencies, and advanced SQL querying.'
    ],
    [
        'id' => 7,
        'title' => 'Laboratory Skills & Safety Handbook',
        'description' => 'Safety protocols, chemical hazard classifications, and apparatus handling procedures for science labs.',
        'faculty' => 'Science',
        'module_code' => 'SCI104',
        'academic_year' => 'Year 1',
        'file_path' => 'uploads/resources/lab_skills_safety_handbook.pdf',
        'file_type' => 'PDF',
        'file_size_mb' => 2.70,
        'page_count' => 22,
        'downloads' => 312,
        'rating' => 4.6,
        'ratings_count' => 9,
        'language' => 'English',
        'uploaded_by' => 6,
        'status' => 'Approved',
        'about_text' => 'Essential handbook for undergraduate students outlining safety protocols, WHMIS/GHS symbols, proper disposal of reagents, emergency procedures, and equipment sterilization.',
        'whats_included' => json_encode([
            'Emergency first-aid and eyewash procedures',
            'GHS chemical hazard symbols quick chart',
            'Apparatus calibration guidelines'
        ]),
        'preview_badge' => 'SCIENCE FACULTY',
        'preview_heading' => 'Laboratory Skills Handbook',
        'preview_subheading' => 'Safety protocols, measurement accuracy, and laboratory etiquette.'
    ]
];

$stmtRes = $conn->prepare("
    INSERT INTO resources (
        id, title, description, faculty, module_code, academic_year, file_path, file_type, 
        file_size_mb, page_count, language, downloads, rating, ratings_count, uploaded_by, 
        status, about_text, whats_included, preview_badge, preview_heading, preview_subheading
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?
    ) ON DUPLICATE KEY UPDATE 
        title=VALUES(title), description=VALUES(description), faculty=VALUES(faculty),
        module_code=VALUES(module_code), academic_year=VALUES(academic_year),
        file_path=VALUES(file_path), file_type=VALUES(file_type), file_size_mb=VALUES(file_size_mb),
        page_count=VALUES(page_count), language=VALUES(language), downloads=VALUES(downloads),
        rating=VALUES(rating), ratings_count=VALUES(ratings_count), status=VALUES(status),
        about_text=VALUES(about_text), whats_included=VALUES(whats_included),
        preview_badge=VALUES(preview_badge), preview_heading=VALUES(preview_heading),
        preview_subheading=VALUES(preview_subheading)
");

foreach ($allResources as $r) {
    $stmtRes->bind_param(
        "isssssssdisidiissssss",
        $r['id'], $r['title'], $r['description'], $r['faculty'], $r['module_code'], $r['academic_year'],
        $r['file_path'], $r['file_type'], $r['file_size_mb'], $r['page_count'], $r['language'],
        $r['downloads'], $r['rating'], $r['ratings_count'], $r['uploaded_by'], $r['status'],
        $r['about_text'], $r['whats_included'], $r['preview_badge'], $r['preview_heading'], $r['preview_subheading']
    );
    $stmtRes->execute();
}
echo "Seeded " . count($allResources) . " resources into MySQL." . PHP_EOL;

// 5. Seed Initial Reviews into resource_reviews
$reviews = [
    ['res_id' => 1, 'user_id' => 4, 'rating' => 5, 'comment' => 'Excellent structure. The graph traversal diagrams finally made BFS and DFS click for me.'],
    ['res_id' => 1, 'user_id' => 2, 'rating' => 5, 'comment' => 'Saved me so much time before mid-terms. The Big-O cheatsheet is great.'],
    ['res_id' => 2, 'user_id' => 2, 'rating' => 5, 'comment' => 'Very clear ledger examples. Helped me tremendously for mid-terms.'],
    ['res_id' => 3, 'user_id' => 3, 'rating' => 5, 'comment' => 'Compact and easy to revise during exam week.'],
    ['res_id' => 4, 'user_id' => 2, 'rating' => 5, 'comment' => 'The free-body diagram breakdown is second to none.'],
    ['res_id' => 5, 'user_id' => 3, 'rating' => 5, 'comment' => 'Saved me so much time formatting my first biology lab submission.'],
    ['res_id' => 6, 'user_id' => 4, 'rating' => 5, 'comment' => 'The normalization decomposition steps are explained far better than our textbook.'],
    ['res_id' => 7, 'user_id' => 5, 'rating' => 5, 'comment' => 'Must-read before the practical lab exams.']
];

$stmtRev = $conn->prepare("INSERT INTO resource_reviews (resource_id, user_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)");
foreach ($reviews as $rev) {
    $stmtRev->bind_param("iiis", $rev['res_id'], $rev['user_id'], $rev['rating'], $rev['comment']);
    $stmtRev->execute();
}
echo "Seeded " . count($reviews) . " reviews into MySQL." . PHP_EOL;

// Ensure upload directory exists
$uploadDir = __DIR__ . '/../../backend/uploads/resources';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "Created uploads directory: $uploadDir" . PHP_EOL;
}

// Create sample physical documents if not present so downloads deliver real files
foreach ($allResources as $r) {
    $targetFile = __DIR__ . '/../../' . $r['file_path'];
    $dir = dirname($targetFile);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    if (!file_exists($targetFile)) {
        file_put_contents($targetFile, "%PDF-1.4\n% NSBM Study Circle: " . $r['title'] . "\nAuthor: " . $r['uploaded_by'] . "\nFaculty: " . $r['faculty'] . "\nDescription: " . $r['description'] . "\n%%EOF");
    }
}
echo "Physical resource files initialized." . PHP_EOL;

echo "MIGRATION COMPLETE!" . PHP_EOL;


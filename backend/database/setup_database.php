<?php
// ============================================================================
// NSBM STUDY CIRCLE - MASTER DATABASE INSTALLER & SEEDER
// Creates database `nsbm_student_circle`, builds/upgrades all 24 tables, and seeds data
// ============================================================================

define('SETUP_DB_HOST', '127.0.0.1');
define('SETUP_DB_PORT', 3306);
define('SETUP_DB_USER', 'root');
define('SETUP_DB_PASS', '');
define('SETUP_DB_NAME', 'nsbm_student_circle');

$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "====================================================================\n";
echo " NSBM STUDY CIRCLE - DATABASE SETUP & INITIALIZATION\n";
echo "====================================================================\n\n";

function ensureColumnExists(PDO $pdo, string $table, string $column, string $definition): void {
    $tableCheck = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetchColumn();
    if (!$tableCheck) {
        return;
    }
    $colCheck = $pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column))->fetchColumn();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        echo "[UPGRADE] Added column `$column` to `$table`.\n";
    }
}

try {
    // 1. Connect to MySQL server (without selecting a database first)
    $dsnServer = "mysql:host=" . SETUP_DB_HOST . ";port=" . SETUP_DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsnServer, SETUP_DB_USER, SETUP_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . SETUP_DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `" . SETUP_DB_NAME . "`;");
    echo "[OK] Database `" . SETUP_DB_NAME . "` created / selected.\n";

    // 3. Upgrade any pre-existing tables with new columns before running full SQL schema + seed
    ensureColumnExists($pdo, 'users', 'academic_year', "VARCHAR(20) DEFAULT 'Year 3' AFTER degree_program");
    ensureColumnExists($pdo, 'resources', 'about_text', "TEXT NULL AFTER description");
    ensureColumnExists($pdo, 'resources', 'whats_included', "TEXT NULL AFTER about_text");
    ensureColumnExists($pdo, 'resources', 'preview_badge', "VARCHAR(100) NULL AFTER whats_included");
    ensureColumnExists($pdo, 'resources', 'preview_heading', "VARCHAR(255) NULL AFTER preview_badge");
    ensureColumnExists($pdo, 'resources', 'preview_subheading', "VARCHAR(255) NULL AFTER preview_heading");
    ensureColumnExists($pdo, 'resources', 'module_code', "VARCHAR(30) NULL AFTER subject_id");
    ensureColumnExists($pdo, 'resources', 'page_count', "INT DEFAULT 1 AFTER file_size_mb");
    ensureColumnExists($pdo, 'resources', 'language', "VARCHAR(50) DEFAULT 'English' AFTER page_count");
    ensureColumnExists($pdo, 'resources', 'ratings_count', "INT DEFAULT 0 AFTER rating");

    ensureColumnExists($pdo, 'discussions', 'category', "VARCHAR(100) DEFAULT 'Computing' AFTER faculty");
    ensureColumnExists($pdo, 'discussions', 'module_code', "VARCHAR(30) NULL AFTER category");
    ensureColumnExists($pdo, 'discussions', 'tags', "VARCHAR(255) DEFAULT 'algorithms, sorting' AFTER module_code");
    ensureColumnExists($pdo, 'discussions', 'views_count', "INT DEFAULT 45 AFTER answers_count");
    ensureColumnExists($pdo, 'discussions', 'status', "ENUM('Open', 'Closed', 'Resolved') DEFAULT 'Open' AFTER is_solved");
    ensureColumnExists($pdo, 'discussions', 'updated_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");

    ensureColumnExists($pdo, 'discussion_answers', 'author_role', "VARCHAR(100) DEFAULT 'Student member' AFTER author_id");
    ensureColumnExists($pdo, 'discussion_answers', 'updated_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");

    ensureColumnExists($pdo, 'study_groups', 'code', "VARCHAR(10) DEFAULT 'SG' AFTER name");
    ensureColumnExists($pdo, 'study_groups', 'category', "VARCHAR(50) DEFAULT 'Computing' AFTER code");
    ensureColumnExists($pdo, 'study_groups', 'is_featured', "TINYINT(1) DEFAULT 0 AFTER category");
    ensureColumnExists($pdo, 'study_groups', 'schedule_info', "VARCHAR(100) DEFAULT 'Meets Wednesdays' AFTER is_featured");
    ensureColumnExists($pdo, 'study_groups', 'activity_status', "VARCHAR(50) DEFAULT 'Active this week' AFTER schedule_info");
    ensureColumnExists($pdo, 'study_groups', 'about_text', "TEXT NULL AFTER description");
    ensureColumnExists($pdo, 'study_groups', 'what_we_study', "VARCHAR(255) NULL AFTER about_text");
    ensureColumnExists($pdo, 'study_groups', 'created_date_str', "VARCHAR(50) DEFAULT 'Created February 2025' AFTER what_we_study");
    ensureColumnExists($pdo, 'study_groups', 'badge_color', "VARCHAR(30) DEFAULT 'badge-ds' AFTER created_date_str");

    ensureColumnExists($pdo, 'study_sessions', 'module_code', "VARCHAR(30) DEFAULT 'CS2102' AFTER description");
    ensureColumnExists($pdo, 'study_sessions', 'duration_mins', "INT DEFAULT 90 AFTER session_date");
    ensureColumnExists($pdo, 'study_sessions', 'format_type', "VARCHAR(50) DEFAULT 'Online' AFTER duration_mins");
    ensureColumnExists($pdo, 'study_sessions', 'meeting_link', "VARCHAR(255) DEFAULT 'https://teams.microsoft.com/l/meetup-join/nsbm-study-circle' AFTER format_type");
    ensureColumnExists($pdo, 'study_sessions', 'agenda', "TEXT NULL AFTER location_details");

    ensureColumnExists($pdo, 'notifications', 'title', "VARCHAR(180) NOT NULL DEFAULT 'Notification' AFTER user_id");
    ensureColumnExists($pdo, 'notifications', 'message', "TEXT NULL AFTER title");
    ensureColumnExists($pdo, 'notifications', 'category', "ENUM('upload', 'discussion', 'group', 'session', 'system') DEFAULT 'system' AFTER message");
    ensureColumnExists($pdo, 'notifications', 'link_url', "VARCHAR(255) DEFAULT 'index.php' AFTER category");
    ensureColumnExists($pdo, 'notifications', 'is_read', "TINYINT(1) DEFAULT 0 AFTER link_url");

    // 4. Execute master SQL file using mysqli->multi_query
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(SETUP_DB_HOST, SETUP_DB_USER, SETUP_DB_PASS, SETUP_DB_NAME, SETUP_DB_PORT);
    $mysqli->set_charset("utf8mb4");

    $sqlFile = __DIR__ . '/nsbm_student_circle.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL schema file not found at: " . $sqlFile);
    }

    $sqlContent = file_get_contents($sqlFile);
    if ($mysqli->multi_query($sqlContent)) {
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }
    $mysqli->close();
    echo "[OK] Executed `nsbm_student_circle.sql` schema & seed statements.\n";

    // 5. Initialize physical sample files in uploads/resources if not present
    $resRows = $pdo->query("SELECT title, faculty, description, uploaded_by, file_path FROM resources")->fetchAll();
    foreach ($resRows as $r) {
        $targetFile = __DIR__ . '/../../' . $r['file_path'];
        $dir = dirname($targetFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($targetFile)) {
            file_put_contents($targetFile, "%PDF-1.4\n% NSBM Study Circle: " . $r['title'] . "\nFaculty: " . $r['faculty'] . "\nDescription: " . $r['description'] . "\n%%EOF");
        }
    }
    echo "[OK] Initialized physical study material files in `uploads/resources/`.\n";

    // 6. Verify all tables and print row counts
    echo "\n--------------------------------------------------------------------\n";
    echo sprintf("%-34s | %-12s\n", "TABLE NAME", "ROW COUNT");
    echo "--------------------------------------------------------------------\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo sprintf("%-34s | %-12d\n", $table, $count);
    }
    echo "--------------------------------------------------------------------\n";
    echo "Total Tables Verified: " . count($tables) . "\n";
    echo "Database Setup Completed Successfully!\n";

} catch (Throwable $e) {
    echo "[ERROR] Database Setup Failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>

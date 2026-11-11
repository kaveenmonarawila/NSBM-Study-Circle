<?php
// ============================================================================
// NSBM STUDY CIRCLE - ROBUST DATABASE CONFIGURATION (phpMyAdmin / MySQL)
// Connected to http://localhost/phpmyadmin -> Database: `nsbm_student_circle`
// Provides both $conn (MySQLi) and $pdo (PDO) connections across all modules
// ============================================================================

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', 3306);
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'nsbm_student_circle');

global $conn, $pdo;
$conn = null;
$pdo = null;

try {
    mysqli_report(MYSQLI_REPORT_OFF);

    // Try connecting to 127.0.0.1 first, then fallback to localhost if needed
    $hosts = [DB_HOST];
    if (DB_HOST === '127.0.0.1') {
        $hosts[] = 'localhost';
    }

    foreach ($hosts as $host) {
        $conn = mysqli_init();
        if (!$conn) continue;
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);

        // Connect directly to MySQL
        if (@$conn->real_connect($host, DB_USER, DB_PASS, '', DB_PORT)) {
            $conn->set_charset("utf8mb4");

            // Ensure database `nsbm_student_circle` exists
            if (!@$conn->select_db(DB_NAME)) {
                $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $conn->select_db(DB_NAME);

                $sqlPath = __DIR__ . '/../database/nsbm_student_circle.sql';
                if (file_exists($sqlPath)) {
                    $sql = file_get_contents($sqlPath);
                    if ($conn->multi_query($sql)) {
                        do {
                            if ($res = $conn->store_result()) {
                                $res->free();
                            }
                        } while ($conn->more_results() && $conn->next_result());
                    }
                }
            }

            // Initialize PDO connection
            $dsn = "mysql:host=" . $host . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            break; // Successfully connected!
        } else {
            $conn = null;
        }
    }
} catch (Throwable $e) {
    $conn = null;
    $pdo = null;
}
?>

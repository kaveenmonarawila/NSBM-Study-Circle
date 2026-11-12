<?php
// ============================================================================
// NSBM STUDY CIRCLE - ROBUST DATABASE CONFIGURATION (phpMyAdmin / MySQL)
// Auto-detects the environment:
//   - LOCAL  (XAMPP / php -S on localhost, CLI): root@127.0.0.1, database `nsbm_student_circle`
//   - ONLINE (InfinityFree): credentials loaded from db_credentials.php (never committed)
// If db_credentials.php is missing, the local XAMPP settings are used.
// Provides both $conn (MySQLi) and $pdo (PDO) connections across all modules
// ============================================================================

// --- Environment detection ----------------------------------------------------
if (!defined('APP_IS_LOCAL')) {
    $requestHost = strtolower($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
    $requestHost = trim(preg_replace('/:\d+$/', '', $requestHost), '[]'); // strip port and IPv6 brackets
    $isLocalHost = (PHP_SAPI === 'cli')
        || $requestHost === ''
        || $requestHost === 'localhost'
        || $requestHost === '::1'
        || strpos($requestHost, '127.') === 0
        || substr($requestHost, -10) === '.localhost';

    $credentialsFile = __DIR__ . '/db_credentials.php';
    $onlineCredentials = (!$isLocalHost && is_file($credentialsFile)) ? require $credentialsFile : null;

    define('APP_IS_LOCAL', !is_array($onlineCredentials));
}

// Never show PHP errors to visitors on the live site (they can leak paths/SQL)
if (!APP_IS_LOCAL) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

if (!APP_IS_LOCAL && isset($onlineCredentials)) {
    // ONLINE (InfinityFree) - values come from db_credentials.php
    if (!defined('DB_HOST')) define('DB_HOST', $onlineCredentials['host']);
    if (!defined('DB_PORT')) define('DB_PORT', (int)($onlineCredentials['port'] ?? 3306));
    if (!defined('DB_USER')) define('DB_USER', $onlineCredentials['user']);
    if (!defined('DB_PASS')) define('DB_PASS', $onlineCredentials['pass']);
    if (!defined('DB_NAME')) define('DB_NAME', $onlineCredentials['name']);
} else {
    // LOCAL (XAMPP)
    if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
    if (!defined('DB_PORT')) define('DB_PORT', 3306);
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'nsbm_student_circle');
}
unset($onlineCredentials);

global $conn, $pdo;
$conn = null;
$pdo = null;

try {
    mysqli_report(MYSQLI_REPORT_OFF);

    // Try connecting to 127.0.0.1 first, then fallback to localhost if needed (local only)
    $hosts = [DB_HOST];
    if (DB_HOST === '127.0.0.1') {
        $hosts[] = 'localhost';
    }

    foreach ($hosts as $host) {
        $conn = mysqli_init();
        if (!$conn) continue;
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, APP_IS_LOCAL ? 3 : 10);

        // Connect directly to MySQL
        if (@$conn->real_connect($host, DB_USER, DB_PASS, '', DB_PORT)) {
            $conn->set_charset("utf8mb4");

            if (!@$conn->select_db(DB_NAME)) {
                // Local only: create `nsbm_student_circle` and import the schema on first run.
                // (InfinityFree does not allow CREATE DATABASE - import nsbm_infinityfree.sql in phpMyAdmin instead.)
                if (!APP_IS_LOCAL) {
                    $conn = null;
                    break;
                }
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

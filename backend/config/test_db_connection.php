<?php
// NSBM STUDY CIRCLE - Database Connection Health Check
require_once __DIR__ . '/db_config.php';

header('Content-Type: application/json; charset=utf-8');

$status = [
    'connected' => false,
    'database' => DB_NAME,
    'host' => DB_HOST,
    'port' => DB_PORT,
    'user' => DB_USER,
    'mysqli_status' => 'disconnected',
    'pdo_status' => 'disconnected',
    'tables_count' => 0,
    'tables' => [],
    'summary' => []
];

if (isset($conn) && $conn && !$conn->connect_error) {
    $status['connected'] = true;
    $status['mysqli_status'] = 'connected (' . $conn->server_info . ')';

    $tableRes = $conn->query("SHOW TABLES");
    if ($tableRes) {
        while ($row = $tableRes->fetch_row()) {
            $tableName = $row[0];
            $status['tables'][] = $tableName;
            $cntRes = $conn->query("SELECT COUNT(*) FROM `$tableName`");
            $status['summary'][$tableName] = $cntRes ? (int)$cntRes->fetch_row()[0] : 0;
        }
        $status['tables_count'] = count($status['tables']);
    }
}

if (isset($pdo) && $pdo) {
    $status['pdo_status'] = 'connected';
}

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


<?php
require_once __DIR__ . '/../config/db_config.php';
global $conn;

if (!$conn || $conn->connect_error) {
    die("Database connection error: " . ($conn ? $conn->connect_error : 'Not initialized'));
}

$email = 'admin.nsbmstudycircle@gmail.com';
$password = 'admin@nsbmsc';
$hash = password_hash($password, PASSWORD_BCRYPT);
$name = 'System Administrator';
$role = 'admin';
$adminType = 'Super administrator';
$faculty = 'Computing';
$status = 'Active';

// Check if user already exists
$chk = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$chk->bind_param("s", $email);
$chk->execute();
$res = $chk->get_result();

if ($res && $row = $res->fetch_assoc()) {
    $uid = (int)$row['id'];
    $upd = $conn->prepare("UPDATE users SET name = ?, password = ?, role = 'admin', admin_type = ?, faculty = ?, status = 'Active', last_active_at = NOW() WHERE id = ?");
    $upd->bind_param("ssssi", $name, $hash, $adminType, $faculty, $uid);
    $upd->execute();
    echo "UPDATED existing admin user ID: $uid\n";
    $upd->close();
} else {
    $ins = $conn->prepare("INSERT INTO users (name, email, password, role, admin_type, faculty, status, created_at) VALUES (?, ?, ?, 'admin', ?, ?, 'Active', NOW())");
    $ins->bind_param("sssss", $name, $email, $hash, $adminType, $faculty);
    $ins->execute();
    $newId = $conn->insert_id;
    echo "INSERTED new admin user ID: $newId\n";
    $ins->close();
}
$chk->close();


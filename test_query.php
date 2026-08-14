<?php
require 'config/db.php';
echo "--- USERS ---\n";
$stmt = $pdo->query('SELECT id, username, role FROM users');
print_r($stmt->fetchAll());
echo "--- SCHEDULES ---\n";
$stmt = $pdo->query('SELECT id, user_id, status, message FROM schedules ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll());
?>

<?php
require 'config/db.php';
echo "--- USERS ---\n";
print_r($pdo->query('SELECT id, username, role FROM users')->fetchAll());
echo "--- SCHEDULES ---\n";
print_r($pdo->query('SELECT id, user_id, status FROM schedules ORDER BY id DESC LIMIT 5')->fetchAll());
?>

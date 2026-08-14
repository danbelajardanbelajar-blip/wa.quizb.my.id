<?php
require 'config/db.php';

try {
    // 1. Fix blank statuses
    $stmt1 = $pdo->query("UPDATE schedules SET status = 'PENDING' WHERE status IS NULL OR status = ''");
    $status_fixed = $stmt1->rowCount();

    // 2. Fix Unknown senders (user_id that doesn't exist in users table or is NULL/0)
    // First, find all valid user IDs
    $valid_users = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $valid_users_str = implode(',', $valid_users);
    
    // Update any schedule whose user_id is not in the valid list to belong to admin (id 1)
    $stmt2 = $pdo->query("UPDATE schedules SET user_id = 1 WHERE user_id IS NULL OR user_id NOT IN ($valid_users_str)");
    $sender_fixed = $stmt2->rowCount();

    echo "<h3>Perbaikan Selesai!</h3>";
    echo "<p>- $status_fixed jadwal telah diperbaiki statusnya menjadi PENDING.</p>";
    echo "<p>- $sender_fixed jadwal telah diperbaiki kolom Sender-nya (dikembalikan ke Admin).</p>";
    echo "<p><br><a href='index.php' style='padding: 10px 15px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Dashboard</a></p>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
require 'config/db.php';

try {
    // 1. Pastikan kolom status adalah VARCHAR dan defaultnya PENDING
    $pdo->exec("ALTER TABLE schedules MODIFY status VARCHAR(50) DEFAULT 'PENDING'");
    
    // 2. Perbaiki status yang terlanjur kosong atau NULL menjadi PENDING
    $stmt1 = $pdo->query("UPDATE schedules SET status = 'PENDING' WHERE status IS NULL OR status = ''");
    $status_fixed = $stmt1->rowCount();

    // 3. Perbaiki pengirim Unknown (jika user_id tidak ada di tabel users)
    // Gunakan query UPDATE JOIN untuk memastikan validitas
    // MySQL/MariaDB specific update join
    $pdo->exec("UPDATE schedules s 
                LEFT JOIN users u ON s.user_id = u.id 
                SET s.user_id = 1 
                WHERE u.id IS NULL OR s.user_id IS NULL");
    
    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9;'>";
    echo "<h2 style='color: #4f46e5;'>Perbaikan Database Selesai!</h2>";
    echo "<p>Kami telah memperbarui struktur tabel Anda agar lebih stabil.</p>";
    echo "<ul>";
    echo "<li><strong>$status_fixed</strong> jadwal yang kosong telah diubah menjadi PENDING.</li>";
    echo "<li>Pengirim 'Unknown' telah dikembalikan ke Admin.</li>";
    echo "</ul>";
    echo "<p style='margin-top: 30px;'><a href='index.php' style='padding: 10px 15px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Dashboard</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

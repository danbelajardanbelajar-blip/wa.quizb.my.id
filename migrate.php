<?php
// File: migrate.php
require 'config/db.php';

try {
    // Menjalankan perintah penambahan kolom
    $sql = "ALTER TABLE schedules 
            ADD COLUMN is_loop BOOLEAN DEFAULT 0, 
            ADD COLUMN loop_interval VARCHAR(20) DEFAULT NULL";
            
    $pdo->exec($sql);
    
    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    echo "<h2 style='color: green;'>✅ Migrasi Berhasil!</h2>";
    echo "<p>Kolom <strong>is_loop</strong> dan <strong>loop_interval</strong> telah sukses ditambahkan ke tabel <strong>schedules</strong>.</p>";
    echo "<p style='color: red;'><strong>PENTING:</strong> Segera hapus file <code>migrate.php</code> ini demi keamanan server Anda.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    
    // Mengecek apakah error disebabkan karena kolom sudah ada
    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "<h2 style='color: blue;'>ℹ️ Tidak Ada Perubahan</h2>";
        echo "<p>Kolom tersebut sudah pernah ditambahkan sebelumnya di database Anda. Sistem aman digunakan.</p>";
        echo "<p style='color: red;'><strong>PENTING:</strong> Segera hapus file <code>migrate.php</code> ini demi keamanan server Anda.</p>";
    } else {
        echo "<h2 style='color: red;'>❌ Gagal!</h2>";
        echo "<p>Terjadi kesalahan pada database: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
}
?>

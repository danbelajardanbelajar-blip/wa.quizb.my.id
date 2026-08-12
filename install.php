<?php
require_once 'config/db.php';

echo "<h2>Database Installer</h2>";

try {
    // 1. Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )
    ");
    echo "<p>✅ Tabel 'users' berhasil dibuat/sudah ada.</p>";

    // 2. Create schedules table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            scheduled_time DATETIME NOT NULL,
            status ENUM('PENDING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Tabel 'schedules' berhasil dibuat/sudah ada.</p>";

    // 3. Insert default admin
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $defaultPasswordHash = '$2y$10$wT.fK8.JzG.9L1E.g1G4.OU3QJ/Z5.M.v/N/7QvL.C3J.H.p.T.W.'; // password: password
        $insert = $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', :password)");
        $insert->execute([':password' => $defaultPasswordHash]);
        echo "<p>✅ Akun admin default berhasil dibuat (User: admin, Pass: password).</p>";
    } else {
        echo "<p>ℹ️ Akun admin sudah ada, dilewati.</p>";
    }

    echo "<h3>🎉 Instalasi selesai! Silakan HAPUS file install.php ini demi keamanan!</h3>";
    echo "<p><a href='login.php'>Menuju ke halaman Login</a></p>";

} catch (PDOException $e) {
    echo "<p>❌ <b>Terjadi Kesalahan:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Pastikan Anda sudah mengedit config/db.php dengan username dan password cPanel Anda.</p>";
}
?>

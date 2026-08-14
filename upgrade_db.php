<?php
// upgrade_db.php
// Script to upgrade database schema for Multi-Role Dashboard

require 'config/db.php';

try {
    // 1. Add columns to 'users' table if they don't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user'");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS api_key VARCHAR(255) DEFAULT NULL");
    
    // Make api_key unique
    try {
        $pdo->exec("ALTER TABLE users ADD UNIQUE (api_key)");
    } catch (PDOException $e) {
        // Ignore if index already exists
    }

    // Update existing user(s) to be admin
    $pdo->exec("UPDATE users SET role = 'admin' WHERE id = 1");

    // 2. Add 'user_id' column to 'schedules' table
    $pdo->exec("ALTER TABLE schedules ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1");
    
    // Update existing schedules to belong to admin (user_id = 1)
    $pdo->exec("UPDATE schedules SET user_id = 1 WHERE user_id IS NULL");

    // Drop the old api_users table if it exists since we merged it
    $pdo->exec("DROP TABLE IF EXISTS api_users");

    echo "<h3>Success! Database schema upgraded for Multi-Role support.</h3>";
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";

} catch (PDOException $e) {
    die("Database Upgrade Error: " . $e->getMessage());
}
?>

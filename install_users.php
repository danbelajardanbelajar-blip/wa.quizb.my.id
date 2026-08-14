<?php
// install_users.php
// Run this file once in your browser to create the api_users table

require 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS api_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Add default test user if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM api_users");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $defaultKey = 'WA-API-SECRET-KEY';
        $insert = $pdo->prepare("INSERT INTO api_users (username, api_key) VALUES (?, ?)");
        $insert->execute(['Default User', $defaultKey]);
        echo "<h3>Success! Table 'api_users' created and default user inserted.</h3>";
    } else {
        echo "<h3>Table 'api_users' already exists and contains data.</h3>";
    }
    
    echo "<p><a href='users.php'>Go to API Users Dashboard</a></p>";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

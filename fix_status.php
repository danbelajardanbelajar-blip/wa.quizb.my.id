<?php
require 'config/db.php';
try {
    $stmt = $pdo->query("UPDATE schedules SET status = 'PENDING' WHERE status IS NULL OR status = ''");
    echo "<h3>Success! Updated " . $stmt->rowCount() . " rows that had blank status.</h3>";
    echo "<p><a href='index.php'>Go back to Dashboard</a></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

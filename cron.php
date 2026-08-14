<?php
// cron.php - Run this via CronJob every minute
require 'config/db.php';

$fcm_file = 'config/fcm.json';
if (!file_exists($fcm_file)) {
    die("FCM Config not found.");
}

$fcm_config = json_decode(file_get_contents($fcm_file), true);
$server_key = $fcm_config['server_key'] ?? '';
$device_token = $fcm_config['device_token'] ?? '';

if (empty($server_key) || empty($device_token)) {
    die("FCM Server Key or Device Token is not set in Settings.");
}

// Get pending schedules where time has passed
// SQLite uses string comparison for dates, so date('Y-m-d\TH:i') format works
$now = date('Y-m-d\TH:i');

$stmt = $pdo->prepare("SELECT * FROM schedules WHERE status = 'PENDING' AND scheduled_time <= :now");
$stmt->execute(['now' => $now]);
$schedules = $stmt->fetchAll();

if (empty($schedules)) {
    echo "No pending schedules at this time ($now).\n";
    exit;
}

// FCM V1 API is more complex (requires OAuth2), so we're using the Legacy HTTP API here.
// IMPORTANT: The Firebase Server Key (Legacy) from Firebase Console > Cloud Messaging is required.
$url = 'https://fcm.googleapis.com/fcm/send';
$headers = array(
    'Authorization: key=' . $server_key,
    'Content-Type: application/json'
);

$success_count = 0;

foreach ($schedules as $schedule) {
    $fields = array(
        'to' => $device_token,
        'priority' => 'high',
        'data' => array(
            'schedule_id' => (string) $schedule['id'],
            'phone' => $schedule['phone_number'],
            'message' => $schedule['message']
        )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($result !== FALSE && $http_code == 200) {
        // Mark as PROCESSING so we don't trigger it again
        $updateStmt = $pdo->prepare("UPDATE schedules SET status = 'PROCESSING' WHERE id = :id");
        $updateStmt->execute(['id' => $schedule['id']]);
        $success_count++;
        echo "Successfully triggered push for Schedule ID: " . $schedule['id'] . "\n";
    } else {
        echo "Failed to trigger push for Schedule ID: " . $schedule['id'] . ". Result: " . $result . "\n";
    }
}

echo "Cron completed. Sent $success_count pushes.\n";
?>

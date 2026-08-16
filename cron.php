<?php
// cron.php - Run this via CronJob every minute
require 'config/db.php';

$log_file = __DIR__ . '/cron.log';
function write_log($msg) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $msg\n", FILE_APPEND);
    echo "[$timestamp] $msg<br>\n";
}

write_log("Cron job started via " . ($_SERVER['REMOTE_ADDR'] ?? 'CLI'));

$fcm_file = 'config/fcm.json';
if (!file_exists($fcm_file)) {
    write_log("FCM Config not found.");
    exit;
}

$fcm_config = json_decode(file_get_contents($fcm_file), true);
$device_token = $fcm_config['device_token'] ?? '';

if (empty($device_token)) {
    write_log("Device Token is not set in Settings.");
    exit;
}

// FCM v1 requires a service account
$service_account_path = __DIR__ . '/config/service-account.json';
if (!file_exists($service_account_path)) {
    write_log("service-account.json not found in config directory.");
    exit;
}

function base64UrlEncode($text) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
}

function getFcmAccessToken($path) {
    $json = file_get_contents($path);
    $keyInfo = json_decode($json, true);
    if (!isset($keyInfo['private_key'])) return false;

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $claim = json_encode([
        'iss' => $keyInfo['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => $keyInfo['token_uri'],
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $signatureInput = base64UrlEncode($header) . '.' . base64UrlEncode($claim);
    $signature = '';
    openssl_sign($signatureInput, $signature, $keyInfo['private_key'], 'SHA256');
    $jwt = $signatureInput . '.' . base64UrlEncode($signature);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $keyInfo['token_uri']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return [
        'access_token' => $data['access_token'] ?? null,
        'project_id' => $keyInfo['project_id'] ?? null
    ];
}

$tokenInfo = getFcmAccessToken($service_account_path);
if (!$tokenInfo || !$tokenInfo['access_token']) {
    write_log("Failed to generate FCM Access Token from service account.");
    exit;
}

// Get pending schedules where time has passed
$now = date('Y-m-d\TH:i');
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE status = 'PENDING' AND scheduled_time <= :now");
$stmt->execute(['now' => $now]);
$schedules = $stmt->fetchAll();

if (empty($schedules)) {
    write_log("No pending schedules at this time ($now).");
    exit;
}

$url = 'https://fcm.googleapis.com/v1/projects/' . $tokenInfo['project_id'] . '/messages:send';
$headers = array(
    'Authorization: Bearer ' . $tokenInfo['access_token'],
    'Content-Type: application/json'
);

$success_count = 0;

foreach ($schedules as $schedule) {
    $fields = array(
        'message' => array(
            'token' => $device_token,
            'android' => array(
                'priority' => 'high'
            ),
            'data' => array(
                'schedule_id' => (string) $schedule['id'],
                'phone' => $schedule['phone_number'],
                'message' => $schedule['message']
            )
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
        $updateStmt = $pdo->prepare("UPDATE schedules SET status = 'PROCESSING' WHERE id = :id");
        $updateStmt->execute(['id' => $schedule['id']]);
        $success_count++;
        write_log("Successfully triggered push for Schedule ID: " . $schedule['id']);
    } else {
        write_log("Failed to trigger push for Schedule ID: " . $schedule['id'] . ". Result: " . $result);
    }
}

write_log("Cron completed. Sent $success_count pushes.");
?>

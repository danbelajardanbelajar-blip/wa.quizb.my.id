<?php
// api/send.php
// Public endpoint for adding schedules via API Key.

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, x-api-key");
    exit(0);
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require '../config/db.php';

// Validate Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only POST method is allowed']);
    exit;
}

// Check API Key
$headers = apache_request_headers();
$apiKey = $headers['x-api-key'] ?? $headers['X-API-KEY'] ?? $headers['X-Api-Key'] ?? null;

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Missing x-api-key header']);
    exit;
}

$valid_keys = require '../config/api_keys.php';
if (!in_array($apiKey, $valid_keys)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

// Parse Input
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST; // Fallback to form data
}

$phone = $data['phone_number'] ?? '';
$message = $data['message'] ?? '';
$time = $data['scheduled_time'] ?? '';

// Basic validation
if (empty($phone) || empty($message) || empty($time)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: phone_number, message, scheduled_time']);
    exit;
}

// Insert Schedule
$stmt = $pdo->prepare("INSERT INTO schedules (phone_number, message, scheduled_time, status) VALUES (:phone, :message, :time, 'PENDING')");
if ($stmt->execute(['phone' => $phone, 'message' => $message, 'time' => $time])) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Schedule successfully created', 
        'data' => [
            'id' => $pdo->lastInsertId(),
            'phone_number' => $phone,
            'scheduled_time' => $time
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: Failed to create schedule']);
}
?>

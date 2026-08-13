<?php
session_start();
header('Content-Type: application/json');

// Check Session for Web UI
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$fcm_file = '../config/fcm.json';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (file_exists($fcm_file)) {
            $data = json_decode(file_get_contents($fcm_file), true);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'success', 'data' => ['server_key' => '', 'device_token' => '']]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        $server_key = $data['server_key'] ?? '';
        $device_token = $data['device_token'] ?? '';
        
        $fcm_data = [
            'server_key' => $server_key,
            'device_token' => $device_token
        ];
        
        if (file_put_contents($fcm_file, json_encode($fcm_data, JSON_PRETTY_PRINT))) {
            echo json_encode(['status' => 'success', 'message' => 'FCM Settings saved']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save FCM settings']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?>

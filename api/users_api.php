<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

// Only logged in web users can manage API users
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Helper to generate a random API key
function generateApiKey() {
    return 'wa-key-' . bin2hex(random_bytes(16));
}

switch ($method) {
    case 'GET':
        // Fetch all users
        $stmt = $pdo->query("SELECT * FROM api_users ORDER BY id DESC");
        $users = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $users]);
        break;

    case 'POST':
        // Add new user
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;
        
        $username = $data['username'] ?? '';
        
        if (empty($username)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username is required']);
            exit;
        }
        
        $apiKey = generateApiKey();
        
        $stmt = $pdo->prepare("INSERT INTO api_users (username, api_key) VALUES (:username, :api_key)");
        if ($stmt->execute(['username' => $username, 'api_key' => $apiKey])) {
            echo json_encode(['status' => 'success', 'message' => 'User created']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
        }
        break;

    case 'DELETE':
        // Delete user
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM api_users WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?>

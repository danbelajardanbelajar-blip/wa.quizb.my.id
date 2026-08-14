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

// Only admin can access this API
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

switch ($method) {
    case 'GET':
        // Fetch all users except admin
        $stmt = $pdo->query("SELECT id, username, api_key, role FROM users WHERE role = 'user' ORDER BY id DESC");
        $users = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $users]);
        break;

    case 'POST':
        // Add new user
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;
        
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and Password are required']);
            exit;
        }
        
        $apiKey = generateApiKey();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, api_key, role) VALUES (:username, :password, :api_key, 'user')");
        if ($stmt->execute(['username' => $username, 'password' => $hashedPassword, 'api_key' => $apiKey])) {
            echo json_encode(['status' => 'success', 'message' => 'User created']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user (might be duplicate)']);
        }
        break;

    case 'DELETE':
        // Delete user
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? null;
        
        if (!$id || $id == 1) { // Prevent deleting admin
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }
        
        // Also delete their schedules
        $pdo->prepare("DELETE FROM schedules WHERE user_id = :id")->execute(['id' => $id]);
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'user'");
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

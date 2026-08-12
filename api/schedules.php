<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

// Helper to check authentication
function isAuthenticated() {
    // Check API token from header for APK
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        global $pdo;
        $token = $matches[1];
        $stmt = $pdo->prepare("SELECT id FROM users WHERE api_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        if ($stmt->fetch()) {
            return true;
        }
    }
    
    // Check Session for Web UI
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    
    return false;
}

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch schedules
        $status = $_GET['status'] ?? null;
        if ($status) {
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE status = :status ORDER BY scheduled_time ASC");
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = $pdo->query("SELECT * FROM schedules ORDER BY scheduled_time DESC");
        }
        $schedules = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $schedules]);
        break;

    case 'POST':
        // Add schedule
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST; // Fallback to form data
        }
        
        $phone = $data['phone_number'] ?? '';
        $message = $data['message'] ?? '';
        $time = $data['scheduled_time'] ?? '';
        
        if (empty($phone) || empty($message) || empty($time)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO schedules (phone_number, message, scheduled_time) VALUES (:phone, :message, :time)");
        if ($stmt->execute(['phone' => $phone, 'message' => $message, 'time' => $time])) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule created', 'id' => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create schedule']);
        }
        break;

    case 'PUT':
        // Update schedule (edit or change status)
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            exit;
        }
        
        // Build dynamic update query based on provided fields
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['phone_number'])) {
            $fields[] = "phone_number = :phone";
            $params['phone'] = $data['phone_number'];
        }
        if (isset($data['message'])) {
            $fields[] = "message = :message";
            $params['message'] = $data['message'];
        }
        if (isset($data['scheduled_time'])) {
            $fields[] = "scheduled_time = :time";
            $params['time'] = $data['scheduled_time'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params['status'] = $data['status'];
        }
        
        if (empty($fields)) {
            echo json_encode(['status' => 'success', 'message' => 'No changes made']);
            exit;
        }
        
        $query = "UPDATE schedules SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute($params)) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule updated']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update schedule']);
        }
        break;

    case 'DELETE':
        // Delete schedule
        $data = json_decode(file_get_contents('php://input'), true);
        // If not in body, check query string
        $id = $data['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete schedule']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?>

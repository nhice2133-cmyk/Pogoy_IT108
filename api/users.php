<?php
require_once '../config/config.php';
requireAdmin();

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            $user_type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $sql = "SELECT id, username, email, full_name, phone, user_type, registration_number, license_number, status, created_at FROM users WHERE 1=1";
            $params = [];
            
            if (!empty($user_type)) {
                $sql .= " AND user_type = ?";
                $params[] = $user_type;
            }
            
            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'data' => $users]);
        }
        
        if ($action === 'get' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address, user_type, registration_number, license_number, status, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                jsonResponse(['error' => 'User not found'], 404);
            }
            
            jsonResponse(['success' => true, 'data' => $user]);
        }


        if ($action === 'admin_stats') {
            try {
                $stats = [];
                
                // Total users
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
                $stmt->execute();
                $stats['total_users'] = $stmt->fetchColumn();
                
                // Active fishers
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'fisher' AND status = 'active'");
                $stmt->execute();
                $stats['active_fishers'] = $stmt->fetchColumn();
                
                jsonResponse(['success' => true, 'stats' => $stats]);
            } catch (PDOException $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }
        break;
        
    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            
            $updates = [];
            $params = [];
            
            if (isset($data['full_name'])) { $updates[] = "full_name = ?"; $params[] = sanitize($data['full_name']); }
            if (isset($data['phone'])) { $updates[] = "phone = ?"; $params[] = sanitize($data['phone']); }
            if (isset($data['address'])) { $updates[] = "address = ?"; $params[] = sanitize($data['address']); }
            if (isset($data['user_type'])) { $updates[] = "user_type = ?"; $params[] = sanitize($data['user_type']); }
            if (isset($data['registration_number'])) { $updates[] = "registration_number = ?"; $params[] = sanitize($data['registration_number']); }
            if (isset($data['license_number'])) { $updates[] = "license_number = ?"; $params[] = sanitize($data['license_number']); }
            if (isset($data['status'])) { $updates[] = "status = ?"; $params[] = sanitize($data['status']); }
            
            if (empty($updates)) {
                jsonResponse(['error' => 'No fields to update'], 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute($params)) {
                jsonResponse(['success' => true, 'message' => 'User updated']);
            } else {
                jsonResponse(['error' => 'Failed to update user'], 500);
            }
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);



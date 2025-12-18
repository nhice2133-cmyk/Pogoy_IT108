<?php
require_once '../config/config.php';
requireAuth();

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            if ($user_type === 'admin' || $user_type === 'officer') {
                $stmt = $conn->prepare("
                    SELECT v.*, u.full_name as owner_name
                    FROM vessels v
                    LEFT JOIN users u ON v.owner_id = u.id
                    ORDER BY v.vessel_name
                ");
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("
                    SELECT * FROM vessels
                    WHERE owner_id = ?
                    ORDER BY vessel_name
                ");
                $stmt->execute([$user_id]);
            }
            
            $vessels = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $vessels]);
        }
        
        if ($action === 'get' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            $stmt = $conn->prepare("
                SELECT v.*, u.full_name as owner_name
                FROM vessels v
                LEFT JOIN users u ON v.owner_id = u.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $vessel = $stmt->fetch();
            
            if (!$vessel) {
                jsonResponse(['error' => 'Vessel not found'], 404);
            }
            
            if ($user_type === 'fisher' && $vessel['owner_id'] != $user_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            jsonResponse(['success' => true, 'data' => $vessel]);
        }
        break;
        
    case 'POST':
        if ($action === 'create') {
            $data = json_decode(file_get_contents('php://input'), true);
            $owner_id = $_SESSION['user_id'];
            
            $vessel_name = sanitize($data['vessel_name'] ?? '');
            $vessel_type = !empty($data['vessel_type']) ? sanitize($data['vessel_type']) : null;
            $registration_number = !empty($data['registration_number']) ? sanitize($data['registration_number']) : null;
            $length = !empty($data['length']) ? floatval($data['length']) : null;
            $tonnage = !empty($data['tonnage']) ? floatval($data['tonnage']) : null;
            $engine_power = !empty($data['engine_power']) ? floatval($data['engine_power']) : null;
            $year_built = !empty($data['year_built']) ? intval($data['year_built']) : null;
            
            if (empty($vessel_name)) {
                jsonResponse(['error' => 'Vessel name is required'], 400);
            }
            
            $stmt = $conn->prepare("
                INSERT INTO vessels (owner_id, vessel_name, vessel_type, registration_number, length, tonnage, engine_power, year_built)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$owner_id, $vessel_name, $vessel_type, $registration_number, $length, $tonnage, $engine_power, $year_built])) {
                $id = $conn->lastInsertId();
                jsonResponse(['success' => true, 'message' => 'Vessel registered', 'id' => $id]);
            } else {
                jsonResponse(['error' => 'Failed to register vessel'], 500);
            }
        }
        break;
        
    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            $stmt = $conn->prepare("SELECT owner_id FROM vessels WHERE id = ?");
            $stmt->execute([$id]);
            $vessel = $stmt->fetch();
            
            if (!$vessel) {
                jsonResponse(['error' => 'Vessel not found'], 404);
            }
            
            if ($user_type === 'fisher' && $vessel['owner_id'] != $user_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $updates = [];
            $params = [];
            
            if (isset($data['vessel_name'])) { $updates[] = "vessel_name = ?"; $params[] = sanitize($data['vessel_name']); }
            if (isset($data['vessel_type'])) { $updates[] = "vessel_type = ?"; $params[] = sanitize($data['vessel_type']); }
            if (isset($data['registration_number'])) { $updates[] = "registration_number = ?"; $params[] = sanitize($data['registration_number']); }
            if (isset($data['length'])) { $updates[] = "length = ?"; $params[] = floatval($data['length']); }
            if (isset($data['tonnage'])) { $updates[] = "tonnage = ?"; $params[] = floatval($data['tonnage']); }
            if (isset($data['engine_power'])) { $updates[] = "engine_power = ?"; $params[] = floatval($data['engine_power']); }
            if (isset($data['year_built'])) { $updates[] = "year_built = ?"; $params[] = intval($data['year_built']); }
            if (isset($data['status'])) { $updates[] = "status = ?"; $params[] = sanitize($data['status']); }
            
            if (empty($updates)) {
                jsonResponse(['error' => 'No fields to update'], 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE vessels SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute($params)) {
                jsonResponse(['success' => true, 'message' => 'Vessel updated']);
            } else {
                jsonResponse(['error' => 'Failed to update vessel'], 500);
            }
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            $stmt = $conn->prepare("SELECT owner_id FROM vessels WHERE id = ?");
            $stmt->execute([$id]);
            $vessel = $stmt->fetch();
            
            if (!$vessel) {
                jsonResponse(['error' => 'Vessel not found'], 404);
            }
            
            if ($user_type === 'fisher' && $vessel['owner_id'] != $user_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $stmt = $conn->prepare("DELETE FROM vessels WHERE id = ?");
            if ($stmt->execute([$id])) {
                jsonResponse(['success' => true, 'message' => 'Vessel deleted']);
            } else {
                jsonResponse(['error' => 'Failed to delete vessel'], 500);
            }
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);



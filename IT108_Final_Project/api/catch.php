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
            $fisher_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];

            // Admin and officers can see all, fishers see only their own
            if ($user_type === 'admin' || $user_type === 'officer') {
                $status = $_GET['status'] ?? '';
                $filter_fisher_id = isset($_GET['fisher_id']) ? intval($_GET['fisher_id']) : null;

                $sql = "
                    SELECT c.*, u.full_name as fisher_name, v.vessel_name, fg.gear_name, z.zone_name
                    FROM catch_records c
                    LEFT JOIN users u ON c.fisher_id = u.id
                    LEFT JOIN vessels v ON c.vessel_id = v.id
                    LEFT JOIN fishing_gear fg ON c.gear_id = fg.id
                    LEFT JOIN fishing_zones z ON c.zone_id = z.id
                    LEFT JOIN fish_species fs ON c.species = fs.common_name
                    WHERE 1=1
                ";

                $params = [];
                if (!empty($status)) {
                    $sql .= " AND c.status = ?";
                    $params[] = $status;
                }

                if ($filter_fisher_id) {
                    $sql .= " AND c.fisher_id = ?";
                    $params[] = $filter_fisher_id;
                }

                $sql .= " ORDER BY c.catch_date DESC, c.catch_time DESC LIMIT 100";

                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $conn->prepare("
                    SELECT c.*, v.vessel_name, fg.gear_name, z.zone_name
                    FROM catch_records c
                    LEFT JOIN vessels v ON c.vessel_id = v.id
                    LEFT JOIN fishing_gear fg ON c.gear_id = fg.id
                    LEFT JOIN fishing_zones z ON c.zone_id = z.id
                    LEFT JOIN fish_species fs ON c.species = fs.common_name
                    WHERE c.fisher_id = ?
                    ORDER BY c.catch_date DESC, c.catch_time DESC
                ");
                $stmt->execute([$fisher_id]);
            }

            $records = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $records]);
        }

        if ($action === 'get' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $fisher_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];

            $stmt = $conn->prepare("
                SELECT c.*, u.full_name as fisher_name, v.vessel_name, fg.gear_name, z.zone_name
                FROM catch_records c
                LEFT JOIN users u ON c.fisher_id = u.id
                LEFT JOIN vessels v ON c.vessel_id = v.id
                LEFT JOIN fishing_gear fg ON c.gear_id = fg.id
                LEFT JOIN fishing_zones z ON c.zone_id = z.id
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $record = $stmt->fetch();

            if (!$record) {
                jsonResponse(['error' => 'Record not found'], 404);
            }

            // Fishers can only view their own records
            if ($user_type === 'fisher' && $record['fisher_id'] != $fisher_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }

            jsonResponse(['success' => true, 'data' => $record]);
        }

        if ($action === 'stats') {
            $fisher_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];

            // Get statistics
            if ($user_type === 'admin' || $user_type === 'officer') {
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total_catches,
                        SUM(quantity) as total_quantity,
                        SUM(total_value) as total_value,
                        COUNT(DISTINCT fisher_id) as total_fishers,
                        COUNT(DISTINCT DATE(catch_date)) as fishing_days
                    FROM catch_records
                    WHERE status = 'verified'
                ");
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total_catches,
                        SUM(quantity) as total_quantity,
                        SUM(total_value) as total_value,
                        COUNT(DISTINCT DATE(catch_date)) as fishing_days
                    FROM catch_records
                    WHERE fisher_id = ? AND status = 'verified'
                ");
                $stmt->execute([$fisher_id]);
            }

            $stats = $stmt->fetch();

            // Get monthly data for chart
            $stmt = $conn->prepare("
                SELECT 
                    DATE_FORMAT(catch_date, '%Y-%m') as month,
                    SUM(quantity) as quantity,
                    SUM(total_value) as value
                FROM catch_records
                WHERE status = 'verified' " .
                ($user_type === 'fisher' ? "AND fisher_id = ? " : "") . "
                GROUP BY DATE_FORMAT(catch_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");

            if ($user_type === 'fisher') {
                $stmt->execute([$fisher_id]);
            } else {
                $stmt->execute();
            }

            $monthly = $stmt->fetchAll();

            jsonResponse(['success' => true, 'stats' => $stats, 'monthly' => $monthly]);
        }

        if ($action === 'admin_stats') {
            requireAdmin();

            try {
                $stats = [];

                // Pending approvals
                $stmt = $conn->prepare("SELECT COUNT(*) FROM catch_records WHERE status = 'pending'");
                $stmt->execute();
                $stats['pending_approvals'] = $stmt->fetchColumn();

                // Today's catches
                $stmt = $conn->prepare("SELECT COUNT(*) FROM catch_records WHERE DATE(catch_date) = CURDATE()");
                $stmt->execute();
                $stats['todays_catches'] = $stmt->fetchColumn();

                jsonResponse(['success' => true, 'stats' => $stats]);
            } catch (PDOException $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }
        break;

    case 'POST':
        if ($action === 'create') {
            $data = json_decode(file_get_contents('php://input'), true);
            $fisher_id = $_SESSION['user_id'];

            $vessel_id = !empty($data['vessel_id']) ? intval($data['vessel_id']) : null;
            $gear_id = !empty($data['gear_id']) ? intval($data['gear_id']) : null;
            $zone_id = !empty($data['zone_id']) ? intval($data['zone_id']) : null;
            $catch_date = sanitize($data['catch_date'] ?? date('Y-m-d'));
            $catch_time = sanitize($data['catch_time'] ?? date('H:i:s'));
            $species = sanitize($data['species'] ?? '');
            $quantity = floatval($data['quantity'] ?? 0);
            $unit = sanitize($data['unit'] ?? 'kg');
            $price_per_unit = floatval($data['price_per_unit'] ?? 0);
            $catch_location = sanitize($data['catch_location'] ?? '');
            $weather_conditions = sanitize($data['weather_conditions'] ?? '');
            $notes = sanitize($data['notes'] ?? '');

            if (empty($species) || $quantity <= 0) {
                jsonResponse(['error' => 'Species and quantity are required'], 400);
            }

            $total_value = $quantity * $price_per_unit;

            $stmt = $conn->prepare("
                INSERT INTO catch_records 
                (fisher_id, vessel_id, gear_id, zone_id, catch_date, catch_time, species, quantity, unit, price_per_unit, total_value, catch_location, weather_conditions, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$fisher_id, $vessel_id, $gear_id, $zone_id, $catch_date, $catch_time, $species, $quantity, $unit, $price_per_unit, $total_value, $catch_location, $weather_conditions, $notes])) {
                $id = $conn->lastInsertId();
                jsonResponse(['success' => true, 'message' => 'Catch record created', 'id' => $id]);
            } else {
                jsonResponse(['error' => 'Failed to create catch record'], 500);
            }
        }
        break;

    case 'PUT':
        if ($action === 'update' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $fisher_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];

            // Check ownership or admin access
            $stmt = $conn->prepare("SELECT fisher_id FROM catch_records WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch();

            if (!$record) {
                jsonResponse(['error' => 'Record not found'], 404);
            }

            if ($user_type === 'fisher' && $record['fisher_id'] != $fisher_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $vessel_id = isset($data['vessel_id']) ? (empty($data['vessel_id']) ? null : intval($data['vessel_id'])) : null;
            $gear_id = isset($data['gear_id']) ? (empty($data['gear_id']) ? null : intval($data['gear_id'])) : null;
            $zone_id = isset($data['zone_id']) ? (empty($data['zone_id']) ? null : intval($data['zone_id'])) : null;
            $catch_date = sanitize($data['catch_date'] ?? '');
            $catch_time = sanitize($data['catch_time'] ?? '');
            $species = sanitize($data['species'] ?? '');
            $quantity = isset($data['quantity']) ? floatval($data['quantity']) : null;
            $unit = sanitize($data['unit'] ?? '');
            $price_per_unit = isset($data['price_per_unit']) ? floatval($data['price_per_unit']) : null;
            $catch_location = sanitize($data['catch_location'] ?? '');
            $weather_conditions = sanitize($data['weather_conditions'] ?? '');
            $notes = sanitize($data['notes'] ?? '');

            $updates = [];
            $params = [];

            if (!empty($catch_date)) {
                $updates[] = "catch_date = ?";
                $params[] = $catch_date;
            }
            if (!empty($catch_time)) {
                $updates[] = "catch_time = ?";
                $params[] = $catch_time;
            }
            if (!empty($species)) {
                $updates[] = "species = ?";
                $params[] = $species;
            }
            if ($quantity !== null) {
                $updates[] = "quantity = ?";
                $params[] = $quantity;
            }
            if (!empty($unit)) {
                $updates[] = "unit = ?";
                $params[] = $unit;
            }
            if ($price_per_unit !== null) {
                $updates[] = "price_per_unit = ?";
                $params[] = $price_per_unit;
            }
            if ($vessel_id !== null) {
                $updates[] = "vessel_id = ?";
                $params[] = $vessel_id;
            }
            if ($gear_id !== null) {
                $updates[] = "gear_id = ?";
                $params[] = $gear_id;
            }
            if ($zone_id !== null) {
                $updates[] = "zone_id = ?";
                $params[] = $zone_id;
            }
            if ($catch_location !== null) {
                $updates[] = "catch_location = ?";
                $params[] = $catch_location;
            }
            if ($weather_conditions !== null) {
                $updates[] = "weather_conditions = ?";
                $params[] = $weather_conditions;
            }
            if ($notes !== null) {
                $updates[] = "notes = ?";
                $params[] = $notes;
            }

            if ($quantity !== null && $price_per_unit !== null) {
                $updates[] = "total_value = ?";
                $params[] = $quantity * $price_per_unit;
            }

            if (empty($updates)) {
                jsonResponse(['error' => 'No fields to update'], 400);
            }

            $params[] = $id;
            $sql = "UPDATE catch_records SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute($params)) {
                jsonResponse(['success' => true, 'message' => 'Catch record updated']);
            } else {
                jsonResponse(['error' => 'Failed to update catch record'], 500);
            }
        }

        if ($action === 'verify' && isset($_GET['id'])) {
            requireAdmin();

            $id = intval($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $status = sanitize($data['status'] ?? 'verified');

            $stmt = $conn->prepare("UPDATE catch_records SET status = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");

            if ($stmt->execute([$status, $_SESSION['user_id'], $id])) {
                jsonResponse(['success' => true, 'message' => 'Catch record verified']);
            } else {
                jsonResponse(['error' => 'Failed to verify catch record'], 500);
            }
        }
        break;

    case 'DELETE':
        if ($action === 'delete' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $fisher_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];

            // Check ownership or admin access
            $stmt = $conn->prepare("SELECT fisher_id FROM catch_records WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch();

            if (!$record) {
                jsonResponse(['error' => 'Record not found'], 404);
            }

            if ($user_type === 'fisher' && $record['fisher_id'] != $fisher_id) {
                jsonResponse(['error' => 'Unauthorized'], 403);
            }

            $stmt = $conn->prepare("DELETE FROM catch_records WHERE id = ?");
            if ($stmt->execute([$id])) {
                jsonResponse(['success' => true, 'message' => 'Catch record deleted']);
            } else {
                jsonResponse(['error' => 'Failed to delete catch record'], 500);
            }
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);


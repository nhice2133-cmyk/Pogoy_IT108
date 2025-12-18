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
            $stmt = $conn->prepare("SELECT * FROM fishing_gear WHERE status = 'active' ORDER BY gear_name");
            $stmt->execute();
            $gear = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $gear]);
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);



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
            $stmt = $conn->prepare("SELECT * FROM fishing_zones ORDER BY zone_name");
            $stmt->execute();
            $zones = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $zones]);
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);



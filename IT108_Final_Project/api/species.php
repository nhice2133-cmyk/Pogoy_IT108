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
            $stmt = $conn->prepare("SELECT * FROM fish_species WHERE status = 'allowed' ORDER BY common_name");
            $stmt->execute();
            $species = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $species]);
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);



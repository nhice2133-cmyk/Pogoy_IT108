<?php
// Application Configuration
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Base URL
define('BASE_URL', 'http://localhost/IT108_system/');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('API_PATH', ROOT_PATH . '/api');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Security
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('PASSWORD_MIN_LENGTH', 8);

// Application settings
define('APP_NAME', 'Smart Fisheries Management System');
define('CITY_NAME', 'Cabadbaran City');

// Include database
require_once ROOT_PATH . '/config/database.php';

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_type'] !== 'admin') {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
}



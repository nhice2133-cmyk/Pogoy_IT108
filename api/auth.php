<?php
require_once '../config/config.php';

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = sanitize($data['username'] ?? '');
            $password = $data['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                jsonResponse(['error' => 'Username and password are required'], 400);
            }
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                jsonResponse(['error' => 'User not found or inactive'], 401);
            }
            
            // Check password
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['full_name'] = $user['full_name'];
                
                jsonResponse([
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'user_type' => $user['user_type'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                // Log for debugging (remove in production)
                error_log("Login failed for username: $username - Password verification failed");
                jsonResponse(['error' => 'Invalid username or password'], 401);
            }
        }
        
        if ($action === 'register') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = sanitize($data['username'] ?? '');
            $email = sanitize($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $full_name = sanitize($data['full_name'] ?? '');
            $phone = sanitize($data['phone'] ?? '');
            $address = sanitize($data['address'] ?? '');
            $registration_number = sanitize($data['registration_number'] ?? '');
            
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                jsonResponse(['error' => 'Required fields are missing'], 400);
            }
            
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
            }
            
            // Check if username or email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                jsonResponse(['error' => 'Username or email already exists'], 400);
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, address, registration_number, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'fisher')");
            
            if ($stmt->execute([$username, $email, $password_hash, $full_name, $phone, $address, $registration_number])) {
                jsonResponse(['success' => true, 'message' => 'Registration successful']);
            } else {
                jsonResponse(['error' => 'Registration failed'], 500);
            }
        }
        break;
        
    case 'GET':
    case 'POST':
        if ($action === 'logout') {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Clear all session variables
            $_SESSION = array();
            
            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-3600, '/');
                setcookie(session_name(), '', time()-3600);
            }
            
            // Destroy the session
            session_destroy();
            
            // Clear session cookie headers
            header_remove('Set-Cookie');
            
            jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
        }
        
        if ($action === 'check') {
            if (isLoggedIn()) {
                jsonResponse([
                    'logged_in' => true,
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'username' => $_SESSION['username'],
                        'full_name' => $_SESSION['full_name'],
                        'user_type' => $_SESSION['user_type']
                    ]
                ]);
            } else {
                jsonResponse(['logged_in' => false]);
            }
        }
        break;
}

jsonResponse(['error' => 'Invalid request'], 400);


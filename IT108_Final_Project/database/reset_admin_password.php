<?php
/**
 * Script to reset admin password
 * Run this from command line: php database/reset_admin_password.php
 * Or access via browser: http://localhost/IT108_system/database/reset_admin_password.php
 */

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// New password
$new_password = 'admin123';

// Generate hash
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin user
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$result = $stmt->execute([$password_hash]);

if ($result) {
    echo "Admin password has been reset successfully!\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Hash: " . $password_hash . "\n";
} else {
    echo "Error resetting password.\n";
}



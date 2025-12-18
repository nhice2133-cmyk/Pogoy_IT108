<?php
/**
 * Script to fix admin password
 * Run this once: http://localhost/IT108_system/fix_admin_password.php
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Generate new password hash for admin123
$new_password = 'admin123';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin user
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$result = $stmt->execute([$password_hash]);

if ($result) {
    echo "<h2>✅ Admin password has been reset successfully!</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>New Hash:</strong> " . $password_hash . "</p>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    
    // Verify the password works
    if (password_verify($new_password, $password_hash)) {
        echo "<p style='color: green;'>✅ Password verification successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed!</p>";
    }
} else {
    echo "<h2>❌ Error resetting password.</h2>";
    echo "<p>Make sure the database is set up and the admin user exists.</p>";
}



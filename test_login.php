<?php
// Simple test script to check login functionality
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

echo "<h2>Login System Test</h2>";

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connected</p>";
    
    // Test user class
    $user = new User($db);
    echo "<p style='color: green;'>✓ User class loaded</p>";
    
    // Test auth class
    $auth = new Auth($user);
    echo "<p style='color: green;'>✓ Auth class loaded</p>";
    
    // Test demo login
    echo "<h3>Testing Demo Login</h3>";
    if ($auth->login('admin', 'admin123')) {
        echo "<p style='color: green;'>✓ Demo admin login successful</p>";
        $currentUser = $auth->getCurrentUser();
        echo "<p>Logged in as: " . htmlspecialchars($currentUser['username']) . " (Role: " . htmlspecialchars($currentUser['role']) . ")</p>";
        $auth->logout();
        echo "<p style='color: green;'>✓ Logout successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Demo admin login failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}
?>

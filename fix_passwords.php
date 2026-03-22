<?php
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

echo "<h2>Fixing User Passwords</h2>\n";

try {
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $query = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $result1 = $stmt->execute([$adminHash]);
    echo "Admin password updated: " . ($result1 ? 'SUCCESS' : 'FAILED') . "<br>\n";
    
    $userHash = password_hash('user123', PASSWORD_DEFAULT);
    $query = "UPDATE users SET password_hash = ? WHERE username = 'testuser'";
    $stmt = $db->prepare($query);
    $result2 = $stmt->execute([$userHash]);
    echo "Test user password updated: " . ($result2 ? 'SUCCESS' : 'FAILED') . "<br>\n";
    
    if ($result1 && $result2) {
        echo "<p style='color: green;'><strong>All passwords have been fixed! You can now log in with:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>admin / admin123</li>\n";
        echo "<li>testuser / user123</li>\n";
        echo "</ul>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>

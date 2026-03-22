<?php
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

echo "<h2>Password Debug Information</h2>\n";

// Check what's actually in the database
$query = "SELECT id, username, password_hash FROM users WHERE username IN ('admin', 'testuser')";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll();

foreach ($users as $user) {
    echo "<h3>User: " . htmlspecialchars($user['username']) . "</h3>\n";
    echo "ID: " . $user['id'] . "<br>\n";
    echo "Password Hash: " . htmlspecialchars($user['password_hash']) . "<br>\n";
    echo "Hash Length: " . strlen($user['password_hash']) . "<br>\n";
    
    // Test password verification
    $testPassword = ($user['username'] === 'admin') ? 'admin123' : 'user123';
    echo "Testing password: " . $testPassword . "<br>\n";
    echo "Password verify result: " . (password_verify($testPassword, $user['password_hash']) ? 'TRUE' : 'FALSE') . "<br>\n";
    
    // Show what the hash should look like
    $correctHash = password_hash($testPassword, PASSWORD_DEFAULT);
    echo "Correct hash example: " . $correctHash . "<br>\n";
    echo "<hr>\n";
}

echo "<h3>Creating New Hashed Passwords</h3>\n";
echo "admin123 hash: " . password_hash('admin123', PASSWORD_DEFAULT) . "<br>\n";
echo "user123 hash: " . password_hash('user123', PASSWORD_DEFAULT) . "<br>\n";
?>

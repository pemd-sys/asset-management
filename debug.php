<?php
// Debug script to check system status
echo "<h2>System Debug Information</h2>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if PDO is available
if (extension_loaded('pdo')) {
    echo "<p><strong>PDO:</strong> Available</p>";
    if (extension_loaded('pdo_mysql')) {
        echo "<p><strong>PDO MySQL:</strong> Available</p>";
    } else {
        echo "<p><strong>PDO MySQL:</strong> <span style='color: red;'>NOT Available</span></p>";
    }
} else {
    echo "<p><strong>PDO:</strong> <span style='color: red;'>NOT Available</span></p>";
}

// Test database connection
echo "<h3>Database Connection Test</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test if tables exist
        $tables = ['users', 'brands', 'products', 'user_sessions'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Table '$table' missing or error: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test class loading
echo "<h3>Class Loading Test</h3>";
$classes = ['User', 'Auth', 'Product', 'Brand'];
foreach ($classes as $className) {
    try {
        require_once "classes/$className.php";
        echo "<p style='color: green;'>✓ Class '$className' loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Class '$className' failed to load: " . $e->getMessage() . "</p>";
    }
}

// Test session functionality
echo "<h3>Session Test</h3>";
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "<p style='color: green;'>✓ Sessions are working</p>";
    unset($_SESSION['test']);
} else {
    echo "<p style='color: red;'>✗ Sessions not working</p>";
}

echo "<h3>File Permissions</h3>";
$files = ['config/database.php', 'classes/', 'includes/'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        echo "<p>$file: " . substr(sprintf('%o', $perms), -4) . "</p>";
    } else {
        echo "<p style='color: red;'>$file: File not found</p>";
    }
}
?>

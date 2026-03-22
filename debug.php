<?php
// Enhanced debug script to check system status
echo "<h2>System Debug Information</h2>";
echo "<style>
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if PDO is available
if (extension_loaded('pdo')) {
    echo "<p class='success'><strong>PDO:</strong> ✓ Available</p>";
    if (extension_loaded('pdo_mysql')) {
        echo "<p class='success'><strong>PDO MySQL:</strong> ✓ Available</p>";
    } else {
        echo "<p class='error'><strong>PDO MySQL:</strong> ✗ NOT Available</p>";
    }
} else {
    echo "<p class='error'><strong>PDO:</strong> ✗ NOT Available</p>";
}

// Test database connection
echo "<h3>Database Connection Test</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p class='success'>✓ Database connection successful</p>";
        
        // Test if tables exist
        $tables = ['users', 'user_sessions', 'brands', 'categories', 'products', 'product_specifications'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<p class='success'>✓ Table '$table' exists with $count records</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Table '$table' missing or error: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<h4>Sample Data Check</h4>";
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $adminCount = $stmt->fetchColumn();
            if ($adminCount > 0) {
                echo "<p class='success'>✓ Admin user exists</p>";
            } else {
                echo "<p class='warning'>⚠ No admin user found</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Could not check admin user: " . $e->getMessage() . "</p>";
        }
        
        echo "<h4>Schema Validation</h4>";
        
        // Check brands table schema
        try {
            $stmt = $db->query("DESCRIBE brands");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $expectedBrandColumns = ['id', 'name', 'logo_url', 'website'];
            $missingColumns = array_diff($expectedBrandColumns, $columns);
            $extraColumns = array_diff($columns, $expectedBrandColumns);
            
            if (empty($missingColumns) && empty($extraColumns)) {
                echo "<p class='success'>✓ Brands table schema is correct</p>";
            } else {
                if (!empty($missingColumns)) {
                    echo "<p class='error'>✗ Brands table missing columns: " . implode(', ', $missingColumns) . "</p>";
                }
                if (!empty($extraColumns)) {
                    echo "<p class='warning'>⚠ Brands table has extra columns: " . implode(', ', $extraColumns) . "</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Could not validate brands schema: " . $e->getMessage() . "</p>";
        }
        
        // Check users table schema
        try {
            $stmt = $db->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $expectedUserColumns = ['id', 'username', 'email', 'password_hash', 'role', 'created_at'];
            $missingColumns = array_diff($expectedUserColumns, $columns);
            $extraColumns = array_diff($columns, $expectedUserColumns);
            
            if (empty($missingColumns) && empty($extraColumns)) {
                echo "<p class='success'>✓ Users table schema is correct</p>";
            } else {
                if (!empty($missingColumns)) {
                    echo "<p class='error'>✗ Users table missing columns: " . implode(', ', $missingColumns) . "</p>";
                }
                if (!empty($extraColumns)) {
                    echo "<p class='warning'>⚠ Users table has extra columns: " . implode(', ', $extraColumns) . "</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Could not validate users schema: " . $e->getMessage() . "</p>";
        }
        
        // Test sample data insertion compatibility
        echo "<h4>Data Insertion Test</h4>";
        try {
            // Test if we can insert a sample brand (without actually inserting)
            $stmt = $db->prepare("SELECT * FROM brands LIMIT 0");
            $stmt->execute();
            $meta = $stmt->getColumnMeta(0);
            if ($meta) {
                echo "<p class='success'>✓ Brands table structure allows data insertion</p>";
            }
            
            // Check if seed data matches table structure
            $stmt = $db->query("SELECT COUNT(*) FROM brands");
            $brandCount = $stmt->fetchColumn();
            if ($brandCount >= 10) {
                echo "<p class='success'>✓ Sample brand data appears to be loaded ($brandCount brands)</p>";
            } else {
                echo "<p class='warning'>⚠ Limited brand data found ($brandCount brands) - may need to run seed script</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Data insertion test failed: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p class='error'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test class loading
echo "<h3>Class Loading Test</h3>";
$classes = ['User', 'Auth', 'Product', 'Brand'];
foreach ($classes as $className) {
    try {
        if (file_exists("classes/$className.php")) {
            require_once "classes/$className.php";
            echo "<p class='success'>✓ Class '$className' loaded successfully</p>";
        } else {
            echo "<p class='error'>✗ Class file 'classes/$className.php' not found</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Class '$className' failed to load: " . $e->getMessage() . "</p>";
    }
}

// Test session functionality
echo "<h3>Session Test</h3>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "<p class='success'>✓ Sessions are working</p>";
    unset($_SESSION['test']);
} else {
    echo "<p class='error'>✗ Sessions not working</p>";
}

echo "<h3>File Permissions & Structure</h3>";
$paths = [
    'config/database.php' => 'file',
    'classes/' => 'directory', 
    'includes/' => 'directory',
    'scripts/' => 'directory',
    'login.php' => 'file',
    'index.php' => 'file'
];

foreach ($paths as $path => $type) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $readable = is_readable($path) ? '✓' : '✗';
        echo "<p>$path ($type): " . substr(sprintf('%o', $perms), -4) . " $readable</p>";
    } else {
        echo "<p class='error'>$path: File/Directory not found</p>";
    }
}

echo "<h3>Next Steps</h3>";
echo "<p><strong>If all tables exist:</strong> You can now use login.php with admin/admin123 or testuser/user123</p>";
echo "<p><strong>If tables are missing:</strong> Run the SQL scripts in this order:</p>";
echo "<ol>";
echo "<li>scripts/01_create_database.sql</li>";
echo "<li>scripts/03_create_users_table.sql</li>";
echo "<li>scripts/02_seed_data.sql</li>";
echo "</ol>";
?>

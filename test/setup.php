<?php
// Database setup and seeding script
require_once 'config/database.php';

echo "<h2>Database Setup</h2>\n";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
        
        // Read and execute schema file
        $schemaSQL = file_get_contents('scripts/01_create_database.sql');
        if ($schemaSQL) {
            $db->exec($schemaSQL);
            echo "<p style='color: green;'>✓ Database schema created</p>\n";
        }
        
        // Read and execute seed data file
        $seedSQL = file_get_contents('scripts/02_seed_data.sql');
        if ($seedSQL) {
            $db->exec($seedSQL);
            echo "<p style='color: green;'>✓ Sample data inserted</p>\n";
        }
        
        echo "<p><strong>Setup completed successfully!</strong></p>\n";
        echo "<p><a href='index.php'>View Oscilloscope Catalog</a></p>\n";
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
}
?>

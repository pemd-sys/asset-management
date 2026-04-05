<?php
require_once __DIR__ . '/config.php';

/**
 * Database Connection Class
 * 
 * Provides a single point for database connections using PDO.
 * Uses configuration from config.php for credentials.
 */
class Database {
    private $conn;
    private static $instance = null;

    /**
     * Get database connection
     * Uses singleton pattern to reuse connections
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch(PDOException $exception) {
                error_log("Database connection error: " . $exception->getMessage());
                
                // Show detailed error only in debug mode
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    die("Database connection failed: " . $exception->getMessage());
                } else {
                    die("Database connection failed. Please try again later.");
                }
            }
        }
        
        return $this->conn;
    }

    /**
     * Get singleton instance of Database
     * Use this when you need a shared connection across the app
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
}
?>

<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'oscilloscope_catalog';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Test the connection with a simple query
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            die("Database connection failed. Please check your database configuration and ensure MySQL is running. Error: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
}
?>

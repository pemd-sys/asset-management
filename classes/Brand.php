<?php
class Brand {
    private $conn;
    private $table_name = "brands";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getBrandCounts() {
        $query = "SELECT b.name, COUNT(p.id) as product_count 
                  FROM " . $this->table_name . " b
                  LEFT JOIN products p ON b.id = p.brand_id
                  GROUP BY b.id, b.name
                  ORDER BY b.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>

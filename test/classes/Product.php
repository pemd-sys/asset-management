<?php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($filters = []) {
        $query = "SELECT p.*, b.name as brand_name, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN brands b ON p.brand_id = b.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['brand'])) {
            $query .= " AND b.name IN (" . str_repeat('?,', count($filters['brand']) - 1) . "?)";
            $params = array_merge($params, $filters['brand']);
        }
        
        if (!empty($filters['bandwidth'])) {
            $query .= " AND p.bandwidth IN (" . str_repeat('?,', count($filters['bandwidth']) - 1) . "?)";
            $params = array_merge($params, $filters['bandwidth']);
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.model LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add sorting
        $sortBy = $filters['sort'] ?? 'name';
        $sortOrder = $filters['order'] ?? 'ASC';
        
        switch($sortBy) {
            case 'price_low':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $query .= " ORDER BY p.price DESC";
                break;
            case 'brand':
                $query .= " ORDER BY b.name ASC";
                break;
            case 'popular':
                $query .= " ORDER BY p.review_count DESC, p.rating DESC";
                break;
            default:
                $query .= " ORDER BY p.name ASC";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT p.*, b.name as brand_name, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN brands b ON p.brand_id = b.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    public function getCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply same filters as getAll method
        if (!empty($filters['brand'])) {
            $query .= " AND b.name IN (" . str_repeat('?,', count($filters['brand']) - 1) . "?)";
            $params = array_merge($params, $filters['brand']);
        }
        
        if (!empty($filters['bandwidth'])) {
            $query .= " AND p.bandwidth IN (" . str_repeat('?,', count($filters['bandwidth']) - 1) . "?)";
            $params = array_merge($params, $filters['bandwidth']);
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.model LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>

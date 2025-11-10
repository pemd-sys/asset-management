<?php
class User {
    private $conn;
    private $table_name = "users";
    private $sessions_table = "user_sessions";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($username, $password) {
        $query = "SELECT id, username, email, password_hash, first_name, last_name, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE (username = ? OR email = ?) AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }

    public function register($username, $email, $password, $firstName = '', $lastName = '') {
        // Check if username or email already exists
        if ($this->userExists($username, $email)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, first_name, last_name) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$username, $email, $passwordHash, $firstName, $lastName])) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    public function userExists($username, $email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
    }

    public function getById($id) {
        $query = "SELECT id, username, email, first_name, last_name, role, is_active, last_login, created_at 
                  FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createSession($userId, $ipAddress = '', $userAgent = '') {
        // Generate secure session token
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $query = "INSERT INTO " . $this->sessions_table . " 
                  (user_id, session_token, expires_at, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$userId, $sessionToken, $expiresAt, $ipAddress, $userAgent])) {
            return $sessionToken;
        }
        
        return false;
    }

    public function validateSession($sessionToken) {
        $query = "SELECT s.user_id, u.username, u.email, u.first_name, u.last_name, u.role 
                  FROM " . $this->sessions_table . " s
                  JOIN " . $this->table_name . " u ON s.user_id = u.id
                  WHERE s.session_token = ? AND s.expires_at > NOW() AND u.is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sessionToken]);
        return $stmt->fetch();
    }

    public function destroySession($sessionToken) {
        $query = "DELETE FROM " . $this->sessions_table . " WHERE session_token = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$sessionToken]);
    }

    public function cleanExpiredSessions() {
        $query = "DELETE FROM " . $this->sessions_table . " WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    private function updateLastLogin($userId) {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
    }
}
?>

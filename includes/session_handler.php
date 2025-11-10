<?php
// Session configuration and cleanup handler
class SessionHandler {
    private $user;
    
    public function __construct($user) {
        $this->user = $user;
        $this->configureSession();
        $this->cleanupExpiredSessions();
    }
    
    private function configureSession() {
        // Configure session settings for security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
        ini_set('session.cookie_samesite', 'Lax');
        
        // Set session lifetime (30 minutes of inactivity)
        ini_set('session.gc_maxlifetime', 1800);
        ini_set('session.cookie_lifetime', 0); // Session cookie
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    private function cleanupExpiredSessions() {
        // Clean up expired sessions from database (run occasionally)
        if (rand(1, 100) <= 5) { // 5% chance to run cleanup
            $this->user->cleanExpiredSessions();
        }
    }
}
?>

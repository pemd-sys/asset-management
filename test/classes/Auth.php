<?php
class Auth {
    private $user;
    private $sessionCookieName = 'electrostore_session';
    private $sessionCookieExpiry = 2592000; // 30 days

    public function __construct($user) {
        $this->user = $user;
        $this->startSession();
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password, $rememberMe = false) {
        $userData = $this->user->authenticate($username, $password);
        
        if ($userData) {
            // Set session data
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['logged_in'] = true;
            
            // Create persistent session if remember me is checked
            if ($rememberMe) {
                $sessionToken = $this->user->createSession(
                    $userData['id'],
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                );
                
                if ($sessionToken) {
                    setcookie(
                        $this->sessionCookieName,
                        $sessionToken,
                        time() + $this->sessionCookieExpiry,
                        '/',
                        '',
                        false,
                        true // HttpOnly
                    );
                }
            }
            
            return true;
        }
        
        return false;
    }

    public function logout() {
        // Destroy persistent session if exists
        if (isset($_COOKIE[$this->sessionCookieName])) {
            $this->user->destroySession($_COOKIE[$this->sessionCookieName]);
            setcookie($this->sessionCookieName, '', time() - 3600, '/');
        }
        
        // Clear session data
        session_unset();
        session_destroy();
        
        return true;
    }

    public function isLoggedIn() {
        // Check session first
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            return true;
        }
        
        // Check persistent session cookie
        if (isset($_COOKIE[$this->sessionCookieName])) {
            $sessionData = $this->user->validateSession($_COOKIE[$this->sessionCookieName]);
            
            if ($sessionData) {
                // Restore session data
                $_SESSION['user_id'] = $sessionData['user_id'];
                $_SESSION['username'] = $sessionData['username'];
                $_SESSION['role'] = $sessionData['role'];
                $_SESSION['logged_in'] = true;
                
                return true;
            } else {
                // Invalid session, clear cookie
                setcookie($this->sessionCookieName, '', time() - 3600, '/');
            }
        }
        
        return false;
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        
        return null;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php?error=access_denied');
            exit();
        }
    }
}
?>

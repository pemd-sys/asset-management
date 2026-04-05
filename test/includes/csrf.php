<?php
/**
 * CSRF Protection Helper
 * 
 * Simple functions to generate and verify CSRF tokens.
 * Prevents cross-site request forgery attacks on forms.
 */

/**
 * Generate a CSRF token and store it in the session
 * 
 * @return string The generated token
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate a new token if one doesn't exist or has expired
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) 
        || (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output a hidden CSRF input field for forms
 * 
 * @return string HTML hidden input element
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}
?>

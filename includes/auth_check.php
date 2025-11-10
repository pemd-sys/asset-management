<?php
// Common authentication check for protected pages
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/session_handler.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize auth system
$user = new User($db);
$auth = new Auth($user);

// Initialize session handler
$sessionHandler = new SessionHandler($user);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    // Store the requested page for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php?message=login_required');
    exit();
}

// Get current user data
$currentUser = $auth->getCurrentUser();
?>

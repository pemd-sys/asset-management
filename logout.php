<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize auth system
$user = new User($db);
$auth = new Auth($user);

// Logout user
$auth->logout();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit();
?>

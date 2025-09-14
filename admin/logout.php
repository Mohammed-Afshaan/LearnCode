<?php
/**
 * Admin Logout
 * Handles admin logout and session cleanup
 */

// Start session
session_start();

// Include required files
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

// Log the logout action
if (isset($_SESSION['username'])) {
    error_log("Admin logout: " . $_SESSION['username'] . " logged out at " . date('Y-m-d H:i:s'));
}

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Start a new session for flash messages
session_start();

// Set success message
$_SESSION['flash_messages'][] = [
    'type' => 'success',
    'message' => 'You have been successfully logged out.'
];

// Redirect to login page
redirect('../pages/login.php');
?>
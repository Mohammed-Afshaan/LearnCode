<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Clear from database
    $user_id = getCurrentUserId();
    if ($user_id) {
        $query = "UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?";
        executeQuery($query, 'i', [$user_id]);
    }
    
    // Clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Log the logout action
$user_info = getCurrentUser();
if ($user_info) {
    logError("User logout: " . $user_info['username'] . " (ID: " . $user_info['id'] . ")");
}

// Logout user (destroy session)
logoutUser();

// Set success message for next page load
setFlashMessage('success', 'You have been successfully logged out.');

// Redirect to home page
redirect('../index.php');
?>
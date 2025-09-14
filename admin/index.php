<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

// Redirect to dashboard
redirect('dashboard.php');
?>
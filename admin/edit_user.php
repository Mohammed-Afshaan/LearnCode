<?php
/**
 * Edit User
 * Admin interface for editing user accounts
 */

// Start session
session_start();

// Include required files
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../pages/login.php');
}

$errors = [];
$success = false;
$user = null;

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['flash_messages'][] = [
        'type' => 'error',
        'message' => 'Invalid user ID.'
    ];
    redirect('manage_users.php');
}

// Get user details
$conn = getDBConnection();
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_messages'][] = [
        'type' => 'error',
        'message' => 'User not found.'
    ];
    redirect('manage_users.php');
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $email_verified = isset($_POST['email_verified']) ? 1 : 0;
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($full_name) < 2) {
            $errors[] = 'Full name must be at least 2 characters long.';
        }
        
        // Check if username/email already exists (excluding current user)
        if (empty($errors)) {
            $check_query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param('ssi', $username, $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = 'Username or email already exists.';
            }
            $check_stmt->close();
        }
        
        // Validate password if provided
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $errors[] = 'Passwords do not match.';
            } else {
                $password_validation = validatePassword($new_password);
                if (!$password_validation['valid']) {
                    $errors[] = $password_validation['message'];
                }
            }
        }
        
        // Prevent admin from removing their own admin status
        if ($user_id == $_SESSION['user_id'] && $is_admin == 0) {
            $errors[] = 'You cannot remove your own admin privileges.';
        }
        
        // Update user if no errors
        if (empty($errors)) {
            if (!empty($new_password)) {
                // Update with password
                $hashed_password = hashPassword($new_password);
                $update_query = "UPDATE users SET username = ?, email = ?, full_name = ?, 
                                password = ?, is_admin = ?, is_active = ?, email_verified = ?, 
                                updated_at = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('ssssiiii', $username, $email, $full_name, 
                                       $hashed_password, $is_admin, $is_active, $email_verified, $user_id);
            } else {
                // Update without password
                $update_query = "UPDATE users SET username = ?, email = ?, full_name = ?, 
                                is_admin = ?, is_active = ?, email_verified = ?, 
                                updated_at = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('sssiii', $username, $email, $full_name, 
                                       $is_admin, $is_active, $email_verified, $user_id);
            }
            
            if ($update_stmt->execute()) {
                // Update user data for display
                $user['username'] = $username;
                $user['email'] = $email;
                $user['full_name'] = $full_name;
                $user['is_admin'] = $is_admin;
                $user['is_active'] = $is_active;
                $user['email_verified'] = $email_verified;
                
                $success = true;
                
                // If current user updated their own profile, update session
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['is_admin'] = $is_admin;
                }
            } else {
                $errors[] = 'Error updating user: ' . $conn->error;
            }
            $update_stmt->close();
        }
    }
}

// Get user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM topics WHERE author_id = ?) as total_topics,
    (SELECT COUNT(*) FROM user_progress WHERE user_id = ?) as total_progress,
    (SELECT COUNT(*) FROM user_favorites WHERE user_id = ?) as total_favorites,
    (SELECT COUNT(*) FROM comments WHERE user_id = ?) as total_comments";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param('iiii', $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin - <?php echo SITE_NAME; ?></title>
   <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }
        
        .main-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #007bff;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .user-status {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-align: center;
        }
        
        .status-active {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-admin {
            background: #d4edda;
            color: #155724;
        }
        
        .status-verified {
            background: #fff3cd;
            color: #856404;
        }
        
        .user-stats {
            margin-top: 20px;
        }
        
        .user-stats h4 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-icon {
            color: #007bff;
            width: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-group input:invalid {
            border-color: #dc3545;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .checkbox-item:hover {
            background: #e9ecef;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }
        
        .checkbox-description {
            font-size: 0.9rem;
            color: #666;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: #d1edff;
            border-color: #007bff;
            color: #004085;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .required {
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-user-edit"></i> Edit User</h1>
        </div>
        
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Display success message -->
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        User has been updated successfully!
                    </div>
                <?php endif; ?>
                
                <!-- Display errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input type="text" id="username" name="username" required 
                                       value="<?php echo htmlspecialchars($user['username']); ?>">
                                <div class="help-text">Username must be 3+ characters, letters, numbers, and underscores only</div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="full_name" name="full_name" required 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                    </div>
                    
                    <!-- Password Change -->
                    <div class="form-section">
                        <h3><i class="fas fa-key"></i> Change Password</h3>
                        <p class="help-text">Leave blank to keep current password</p>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                                <div class="help-text">Must be 8+ characters with uppercase, lowercase, number, and special character</div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Settings -->
                    <div class="form-section">
                        <h3><i class="fas fa-cogs"></i> Account Settings</h3>
                        
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="is_admin" name="is_admin" 
                                       <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                                <label for="is_admin">
                                    <strong>Administrator</strong>
                                    <div class="checkbox-description">Can access admin panel and manage content</div>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="is_active" name="is_active" 
                                       <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <label for="is_active">
                                    <strong>Active Account</strong>
                                    <div class="checkbox-description">User can log in and access the site</div>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="email_verified" name="email_verified" 
                                       <?php echo $user['email_verified'] ? 'checked' : ''; ?>>
                                <label for="email_verified">
                                    <strong>Email Verified</strong>
                                    <div class="checkbox-description">Email address has been verified</div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="manage_users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash"></i> Delete User
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <div class="user-status">
                        <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <?php if ($user['is_admin']): ?>
                            <span class="status-badge status-admin">Administrator</span>
                        <?php endif; ?>
                        <span class="status-badge status-verified">
                            <?php echo $user['email_verified'] ? 'Email Verified' : 'Email Not Verified'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="user-stats">
                    <h4><i class="fas fa-chart-bar"></i> User Statistics</h4>
                    
                    <div class="stat-item">
                        <span><i class="fas fa-book stat-icon"></i> Topics Created</span>
                        <strong><?php echo number_format($stats['total_topics'] ?: 0); ?></strong>
                    </div>
                    
                    <div class="stat-item">
                        <span><i class="fas fa-tasks stat-icon"></i> Progress Entries</span>
                        <strong><?php echo number_format($stats['total_progress'] ?: 0); ?></strong>
                    </div>
                    
                    <div class="stat-item">
                        <span><i class="fas fa-heart stat-icon"></i> Favorites</span>
                        <strong><?php echo number_format($stats['total_favorites'] ?: 0); ?></strong>
                    </div>
                    
                    <div class="stat-item">
                        <span><i class="fas fa-comments stat-icon"></i> Comments</span>
                        <strong><?php echo number_format($stats['total_comments'] ?: 0); ?></strong>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9rem; color: #666;">
                    <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                    <p><strong>Last Login:</strong> 
                        <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePasswords() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
        });
    </script>
</body>
</html>
<?php
/**
 * Delete User
 * Admin interface for deleting users with confirmation
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

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['flash_messages'][] = [
        'type' => 'error',
        'message' => 'You cannot delete your own account.'
    ];
    redirect('manage_users.php');
}

// Get user details
$conn = getDBConnection();
$query = "SELECT u.*, 
          COUNT(DISTINCT t.id) as topic_count,
          COUNT(DISTINCT up.id) as progress_count,
          COUNT(DISTINCT uf.id) as favorites_count,
          COUNT(DISTINCT c.id) as comments_count
          FROM users u
          LEFT JOIN topics t ON u.id = t.author_id
          LEFT JOIN user_progress up ON u.id = up.user_id
          LEFT JOIN user_favorites uf ON u.id = uf.user_id
          LEFT JOIN comments c ON u.id = c.user_id
          WHERE u.id = ?
          GROUP BY u.id";

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

// Handle form submission (actual deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Check if confirmation was provided
        if (($_POST['confirm_delete'] ?? '') !== 'DELETE') {
            $errors[] = 'Please type "DELETE" to confirm deletion.';
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Delete user (CASCADE will handle related records)
                $delete_query = "DELETE FROM users WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bind_param('i', $user_id);
                
                if (!$delete_stmt->execute()) {
                    throw new Exception('Error deleting user: ' . $conn->error);
                }
                
                $delete_stmt->close();
                $conn->commit();
                
                $_SESSION['flash_messages'][] = [
                    'type' => 'success',
                    'message' => 'User "' . $user['username'] . '" has been deleted successfully.'
                ];
                
                redirect('manage_users.php');
                
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = $e->getMessage();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - Admin - <?php echo SITE_NAME; ?></title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
        
        .danger-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            border: 2px solid #dc3545;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #6c757d;
        }
        
        .user-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .user-basic h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .user-email {
            color: #666;
            margin-bottom: 10px;
        }
        
        .user-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        
        .badge-active {
            background: #28a745;
            color: white;
        }
        
        .badge-inactive {
            background: #6c757d;
            color: white;
        }
        
        .badge-verified {
            background: #17a2b8;
            color: white;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .user-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .meta-item i {
            color: #6c757d;
            width: 16px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .warning-box h4 {
            color: #856404;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .warning-box ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #856404;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #dc3545;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid;
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
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .danger-container {
                padding: 20px;
            }
            
            .user-header {
                flex-direction: column;
                text-align: center;
            }
            
            .user-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .user-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
     <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-user-times"></i> Delete User</h1>
        </div>
        
        <div class="danger-container">
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
            
            <!-- User Information -->
            <div class="user-info">
                <div class="user-header">
                    <div class="user-avatar">
                        <?php if ($user['profile_image']): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                 alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-basic">
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="user-badges">
                            <?php if ($user['is_admin']): ?>
                                <span class="badge badge-admin">Administrator</span>
                            <?php endif; ?>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Inactive</span>
                            <?php endif; ?>
                            <?php if ($user['email_verified']): ?>
                                <span class="badge badge-verified">Verified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="user-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span>Username: <?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Last Login: <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-hashtag"></i>
                        <span>User ID: <?php echo $user['id']; ?></span>
                    </div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($user['topic_count']); ?></div>
                        <div class="stat-label">Topics Created</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($user['progress_count']); ?></div>
                        <div class="stat-label">Progress Records</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($user['favorites_count']); ?></div>
                        <div class="stat-label">Favorites</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($user['comments_count']); ?></div>
                        <div class="stat-label">Comments</div>
                    </div>
                </div>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!</h4>
                <p>Deleting this user will permanently remove:</p>
                <ul>
                    <li>The user account and all personal information</li>
                    <li>All topics created by this user (<?php echo $user['topic_count']; ?> topics)</li>
                    <li>All user progress tracking (<?php echo $user['progress_count']; ?> records)</li>
                    <li>All user favorites (<?php echo $user['favorites_count']; ?> items)</li>
                    <li>All comments made by this user (<?php echo $user['comments_count']; ?> comments)</li>
                    <li>All view statistics associated with this user</li>
                </ul>
                <?php if ($user['topic_count'] > 0): ?>
                    <p><strong style="color: #dc3545;">This user has created topics that will be lost forever!</strong></p>
                <?php endif; ?>
            </div>
            
            <!-- Deletion Form -->
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="confirm_delete">Type "DELETE" to confirm <span class="required">*</span></label>
                    <input type="text" id="confirm_delete" name="confirm_delete" required 
                           placeholder="Type DELETE here">
                    <div class="help-text">You must type "DELETE" (in capital letters) to confirm the deletion</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you absolutely sure you want to delete this user? This cannot be undone!')">
                        <i class="fas fa-user-times"></i> Delete User Permanently
                    </button>
                    <a href="manage_users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel & Go Back
                    </a>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Instead
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
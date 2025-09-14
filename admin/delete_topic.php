<?php
/**
 * Delete Topic
 * Admin interface for deleting topics with confirmation
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
$topic = null;

// Get topic ID from URL
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($topic_id <= 0) {
    $_SESSION['flash_messages'][] = [
        'type' => 'error',
        'message' => 'Invalid topic ID.'
    ];
    redirect('dashboard.php');
}

// Get topic details
$conn = getDBConnection();
$query = "SELECT t.*, u.username as author_name 
          FROM topics t 
          LEFT JOIN users u ON t.author_id = u.id 
          WHERE t.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_messages'][] = [
        'type' => 'error',
        'message' => 'Topic not found.'
    ];
    redirect('dashboard.php');
}

$topic = $result->fetch_assoc();
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
            // Delete the topic
            $delete_query = "DELETE FROM topics WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param('i', $topic_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['flash_messages'][] = [
                    'type' => 'success',
                    'message' => 'Topic "' . $topic['title'] . '" has been deleted successfully.'
                ];
                $delete_stmt->close();
                $conn->close();
                redirect('dashboard.php');
            } else {
                $errors[] = 'Error deleting topic: ' . $conn->error;
            }
            $delete_stmt->close();
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
    <title>Delete Topic - Admin - <?php echo SITE_NAME; ?></title>
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
        
        .topic-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #6c757d;
        }
        
        .topic-info h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .topic-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
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
            
            .topic-meta {
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
            <h1><i class="fas fa-trash-alt"></i> Delete Topic</h1>
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
            
            <!-- Topic Information -->
            <div class="topic-info">
                <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                
                <div class="topic-meta">
                    <div class="meta-item">
                        <i class="fas fa-code"></i>
                        <span><?php echo htmlspecialchars($topic['language']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-signal"></i>
                        <span><?php echo htmlspecialchars($topic['difficulty']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($topic['author_name'] ?? 'Unknown'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($topic['view_count']); ?> views</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-<?php echo $topic['is_published'] ? 'check' : 'times'; ?>"></i>
                        <span><?php echo $topic['is_published'] ? 'Published' : 'Draft'; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($topic['description'])): ?>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($topic['description']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($topic['tags'])): ?>
                    <p><strong>Tags:</strong> <?php echo htmlspecialchars($topic['tags']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!</h4>
                <p>Deleting this topic will permanently remove:</p>
                <ul>
                    <li>The topic and all its content</li>
                    <li>All user progress tracking for this topic</li>
                    <li>All user favorites for this topic</li>
                    <li>All comments on this topic</li>
                    <li>All view statistics for this topic</li>
                </ul>
                <p><strong>Make sure you really want to delete this topic before proceeding.</strong></p>
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
                            onclick="return confirm('Are you absolutely sure you want to delete this topic? This cannot be undone!')">
                        <i class="fas fa-trash-alt"></i> Delete Topic Permanently
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel & Go Back
                    </a>
                    <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Instead
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
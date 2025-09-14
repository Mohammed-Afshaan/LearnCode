<?php
/**
 * Edit Topic
 * Admin interface for editing existing topics
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
$query = "SELECT * FROM topics WHERE id = ?";
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input data
        $title = sanitize_input($_POST['title'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $content = $_POST['content'] ?? ''; // Don't sanitize HTML content
        $code_snippet = $_POST['code_snippet'] ?? '';
        $language = sanitize_input($_POST['language'] ?? '');
        $difficulty = sanitize_input($_POST['difficulty'] ?? 'Beginner');
        $tags = sanitize_input($_POST['tags'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        // Validation
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($description)) {
            $errors[] = 'Description is required.';
        }
        
        if (empty($language)) {
            $errors[] = 'Language is required.';
        }
        
        if (!in_array($difficulty, ['Beginner', 'Intermediate', 'Advanced'])) {
            $errors[] = 'Invalid difficulty level.';
        }
        
        // If no errors, update the topic
        if (empty($errors)) {
            // Generate slug from title if it changed
            $slug = $topic['slug']; // Keep existing slug by default
            
            if ($title !== $topic['title']) {
                $new_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                
                // Check if new slug already exists (excluding current topic)
                $original_slug = $new_slug;
                $counter = 1;
                while (true) {
                    $check_query = "SELECT id FROM topics WHERE slug = ? AND id != ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param('si', $new_slug, $topic_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows == 0) {
                        $slug = $new_slug;
                        break;
                    }
                    
                    $new_slug = $original_slug . '-' . $counter;
                    $counter++;
                    $check_stmt->close();
                }
            }
            
            // Update topic
            $update_query = "UPDATE topics SET 
                           title = ?, slug = ?, description = ?, content = ?, code_snippet = ?, 
                           language = ?, difficulty = ?, tags = ?, is_featured = ?, is_published = ?,
                           updated_at = NOW()
                           WHERE id = ?";
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('ssssssssiiii', 
                $title, $slug, $description, $content, $code_snippet, 
                $language, $difficulty, $tags, $is_featured, $is_published, $topic_id
            );
            
            if ($update_stmt->execute()) {
                $success = true;
                
                // Update the topic array with new data for display
                $topic['title'] = $title;
                $topic['slug'] = $slug;
                $topic['description'] = $description;
                $topic['content'] = $content;
                $topic['code_snippet'] = $code_snippet;
                $topic['language'] = $language;
                $topic['difficulty'] = $difficulty;
                $topic['tags'] = $tags;
                $topic['is_featured'] = $is_featured;
                $topic['is_published'] = $is_published;
                
                $_SESSION['flash_messages'][] = [
                    'type' => 'success',
                    'message' => 'Topic updated successfully!'
                ];
            } else {
                $errors[] = 'Error updating topic: ' . $conn->error;
            }
            
            $update_stmt->close();
        }
    }
}

// Get available languages for dropdown (from existing topics)
$lang_query = "SELECT DISTINCT language FROM topics ORDER BY language";
$lang_result = $conn->query($lang_query);
$available_languages = [];

if ($lang_result) {
    while ($row = $lang_result->fetch_assoc()) {
        $available_languages[] = $row['language'];
    }
}

// Common languages if none exist
if (empty($available_languages)) {
    $available_languages = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Python', 'Java', 'C++', 'SQL'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Topic - Admin - <?php echo SITE_NAME; ?></title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .topic-info-bar {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .topic-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .meta-item i {
            width: 16px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            font-family: inherit;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
            border-left: 4px solid #28a745;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
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
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .topic-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Edit Topic</h1>
        </div>
        
        <div class="form-container">
            <!-- Topic Info Bar -->
            <div class="topic-info-bar">
                <div class="topic-meta">
                    <div class="meta-item">
                        <i class="fas fa-hashtag"></i>
                        <span>ID: <?php echo $topic['id']; ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Created: <?php echo date('M j, Y', strtotime($topic['created_at'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($topic['view_count']); ?> views</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-link"></i>
                        <span>Slug: <?php echo htmlspecialchars($topic['slug']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Display success message -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Topic has been updated successfully!
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
            
            <!-- Edit Topic Form -->
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($topic['title']); ?>">
                    <div class="help-text">Enter a descriptive title for your topic</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($topic['description']); ?></textarea>
                    <div class="help-text">Brief description of what this topic covers</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="language">Language <span class="required">*</span></label>
                        <select id="language" name="language" required>
                            <option value="">Select Language</option>
                            <?php foreach ($available_languages as $lang): ?>
                                <option value="<?php echo htmlspecialchars($lang); ?>" 
                                        <?php echo ($topic['language'] === $lang) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lang); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty">
                            <option value="Beginner" <?php echo ($topic['difficulty'] === 'Beginner') ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo ($topic['difficulty'] === 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo ($topic['difficulty'] === 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" 
                           value="<?php echo htmlspecialchars($topic['tags']); ?>">
                    <div class="help-text">Comma-separated tags (e.g., loops, functions, arrays)</div>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="10"><?php echo htmlspecialchars($topic['content']); ?></textarea>
                    <div class="help-text">Full content of the topic. You can use HTML formatting.</div>
                </div>
                
                <div class="form-group">
                    <label for="code_snippet">Code Snippet</label>
                    <textarea id="code_snippet" name="code_snippet" rows="8" style="font-family: monospace;"><?php echo htmlspecialchars($topic['code_snippet']); ?></textarea>
                    <div class="help-text">Example code for this topic</div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               <?php echo $topic['is_featured'] ? 'checked' : ''; ?>>
                        <label for="is_featured">Featured Topic</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_published" name="is_published" value="1" 
                               <?php echo $topic['is_published'] ? 'checked' : ''; ?>>
                        <label for="is_published">Published</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Update Topic
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="delete_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this topic?')">
                        <i class="fas fa-trash"></i> Delete Topic
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
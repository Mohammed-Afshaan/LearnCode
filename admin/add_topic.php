<?php
/**
 * Add Topic
 * Admin interface for creating new topics
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
        
        // If no errors, create the topic
        if (empty($errors)) {
            $conn = getDBConnection();
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            // Check if slug already exists and make it unique
            $original_slug = $slug;
            $counter = 1;
            while (true) {
                $check_query = "SELECT id FROM topics WHERE slug = ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param('s', $slug);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    break;
                }
                
                $slug = $original_slug . '-' . $counter;
                $counter++;
                $stmt->close();
            }
            
            // Insert new topic
            $query = "INSERT INTO topics (title, slug, description, content, code_snippet, language, 
                      difficulty, tags, is_featured, is_published, author_id, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $author_id = $_SESSION['user_id'];
            
            $stmt->bind_param('ssssssssiiii', 
                $title, $slug, $description, $content, $code_snippet, 
                $language, $difficulty, $tags, $is_featured, $is_published, $author_id
            );
            
            if ($stmt->execute()) {
                $topic_id = $conn->insert_id;
                $success = true;
                
                // Clear form data
                $title = $description = $content = $code_snippet = $language = $tags = '';
                $difficulty = 'Beginner';
                $is_featured = $is_published = 0;
                
                $_SESSION['flash_messages'][] = [
                    'type' => 'success',
                    'message' => 'Topic created successfully! Topic ID: ' . $topic_id
                ];
            } else {
                $errors[] = 'Error creating topic: ' . $conn->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// Get available languages for dropdown (from existing topics)
$conn = getDBConnection();
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
    <title>Add Topic - Admin - <?php echo SITE_NAME; ?></title>
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
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-plus-circle"></i> Add New Topic</h1>
        </div>
        
        <div class="form-container">
            <!-- Display success message -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Topic has been created successfully!
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
            
            <!-- Add Topic Form -->
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($title ?? ''); ?>">
                    <div class="help-text">Enter a descriptive title for your topic</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <div class="help-text">Brief description of what this topic covers</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="language">Language <span class="required">*</span></label>
                        <select id="language" name="language" required>
                            <option value="">Select Language</option>
                            <?php foreach ($available_languages as $lang): ?>
                                <option value="<?php echo htmlspecialchars($lang); ?>" 
                                        <?php echo (isset($language) && $language === $lang) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lang); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty">
                            <option value="Beginner" <?php echo (isset($difficulty) && $difficulty === 'Beginner') ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo (isset($difficulty) && $difficulty === 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo (isset($difficulty) && $difficulty === 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" 
                           value="<?php echo htmlspecialchars($tags ?? ''); ?>">
                    <div class="help-text">Comma-separated tags (e.g., loops, functions, arrays)</div>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="10"><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                    <div class="help-text">Full content of the topic. You can use HTML formatting.</div>
                </div>
                
                <div class="form-group">
                    <label for="code_snippet">Code Snippet</label>
                    <textarea id="code_snippet" name="code_snippet" rows="8" style="font-family: monospace;"><?php echo htmlspecialchars($code_snippet ?? ''); ?></textarea>
                    <div class="help-text">Example code for this topic</div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               <?php echo (isset($is_featured) && $is_featured) ? 'checked' : ''; ?>>
                        <label for="is_featured">Featured Topic</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_published" name="is_published" value="1" 
                               <?php echo (!isset($is_published) || $is_published) ? 'checked' : ''; ?>>
                        <label for="is_published">Publish Immediately</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Create Topic
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
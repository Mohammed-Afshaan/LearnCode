<?php
/**
 * Add User
 * Admin interface for creating new users
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
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
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
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Check if username or email already exists
        if (empty($errors)) {
            $conn = getDBConnection();
            
            $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = 'Username or email already exists.';
            }
            $stmt->close();
        }
        
        // If no errors, create the user
        if (empty($errors)) {
            // Hash password
            $hashed_password = hashPassword($password);
            
            // Insert new user
            $query = "INSERT INTO users (username, email, password, full_name, is_admin, is_active, email_verified, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssii', $username, $email, $hashed_password, $full_name, $is_admin, $is_active);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $success = true;
                
                // Clear form data
                $username = $email = $full_name = $password = $confirm_password = '';
                $is_admin = $is_active = 0;
                
                $_SESSION['flash_messages'][] = [
                    'type' => 'success',
                    'message' => 'User created successfully! User ID: ' . $user_id
                ];
            } else {
                $errors[] = 'Error creating user: ' . $conn->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin - <?php echo SITE_NAME; ?></title>
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
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
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
        
        .password-strength {
            margin-top: 5px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-fair { background-color: #ffc107; }
        .strength-good { background-color: #28a745; }
        .strength-strong { background-color: #20c997; }
        
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
            <h1><i class="fas fa-user-plus"></i> Add New User</h1>
        </div>
        
        <div class="form-container">
            <!-- Display success message -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    User has been created successfully!
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
            
            <!-- Add User Form -->
            <form method="POST" action="" id="addUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        <div class="help-text">3+ characters, letters, numbers, and underscores only</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <div class="help-text">Valid email address</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                    <div class="help-text">User's display name</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div id="strengthText" class="help-text">Minimum 8 characters</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div id="passwordMatch" class="help-text">Re-enter the password</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" 
                               <?php echo (isset($is_admin) && $is_admin) ? 'checked' : ''; ?>>
                        <label for="is_admin">Administrator</label>
                    </div>
                    <div class="help-text">Administrators can manage users and content</div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               <?php echo (!isset($is_active) || $is_active) ? 'checked' : ''; ?>>
                        <label for="is_active">Active Account</label>
                    </div>
                    <div class="help-text">Inactive users cannot log in</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                    <a href="manage_users.php" class="btn btn-secondary">
                        <i class="fas fa-users"></i> View Users
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = 'Very weak';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const percentage = (strength / 5) * 100;
            strengthFill.style.width = percentage + '%';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthFill.className = 'strength-fill strength-weak';
                    text = 'Very weak';
                    break;
                case 2:
                    strengthFill.className = 'strength-fill strength-weak';
                    text = 'Weak';
                    break;
                case 3:
                    strengthFill.className = 'strength-fill strength-fair';
                    text = 'Fair';
                    break;
                case 4:
                    strengthFill.className = 'strength-fill strength-good';
                    text = 'Good';
                    break;
                case 5:
                    strengthFill.className = 'strength-fill strength-strong';
                    text = 'Strong';
                    break;
            }
            
            strengthText.textContent = text;
        });
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchText.textContent = 'Re-enter the password';
                matchText.style.color = '#666';
            } else if (password === confirmPassword) {
                matchText.textContent = 'Passwords match';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.style.color = '#dc3545';
            }
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
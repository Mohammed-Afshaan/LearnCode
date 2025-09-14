<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$form_data = [
    'full_name' => '',
    'username' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $form_data = [
        'full_name' => sanitize_input($_POST['full_name'] ?? ''),
        'username' => sanitize_input($_POST['username'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? '')
    ];
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Rate limiting check
    if (!checkRateLimit($_SERVER['REMOTE_ADDR'] . '_register', 3, 3600)) {
        $errors[] = 'Too many registration attempts. Please try again later.';
    }
    
    // Validate required fields
    if (empty($form_data['full_name'])) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($form_data['full_name']) < 2) {
        $errors[] = 'Full name must be at least 2 characters long.';
    }
    
    if (empty($form_data['username'])) {
        $errors[] = 'Username is required.';
    } elseif (strlen($form_data['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $form_data['username'])) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($form_data['email'])) {
        $errors[] = 'Email address is required.';
    } elseif (!validateEmail($form_data['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } else {
        $password_validation = validatePassword($password);
        if (!$password_validation['valid']) {
            $errors[] = $password_validation['message'];
        }
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!$agree_terms) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $result = executeQuery($check_query, 'ss', [$form_data['username'], $form_data['email']]);
        
        if ($result && $result->num_rows > 0) {
            $existing_user = $result->fetch_assoc();
            $errors[] = 'Username or email already exists. Please choose different ones.';
        }
    }
    
    // Create user if no errors
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        $verification_token = bin2hex(random_bytes(32));

        // Use manual insert to get insert_id before closing connection
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, verification_token, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param('sssss', $form_data['username'], $form_data['email'], $hashed_password, $form_data['full_name'], $verification_token);
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $stmt->close();

                // Send welcome email (optional)
                sendWelcomeEmail($form_data['email'], $form_data['username']);

                // Set success message
                setFlashMessage('success', 'Registration successful! Please check your email to verify your account.');

                // For demo, automatically verify and login
                $update_query = "UPDATE users SET email_verified = 1 WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                if ($update_stmt) {
                    $update_stmt->bind_param('i', $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Login the user
                loginUser($user_id, [
                    'username' => $form_data['username'],
                    'email' => $form_data['email'],
                    'full_name' => $form_data['full_name'],
                    'is_admin' => 0
                ]);

                $conn->close();
                redirect('dashboard.php');
            } else {
                $errors[] = 'Registration failed. Please try again. Error: ' . $stmt->error;
                $stmt->close();
                $conn->close();
            }
        } else {
            $errors[] = 'Registration failed. Please try again. Error: ' . $conn->error;
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
    <title>Sign Up - W3Clone</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .register-container {
            min-height: calc(100vh - 70px);
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
        }
        .register-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 100%;
            padding: 2.5rem;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-logo {
            font-size: 2rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
        }
        .error-list {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #fecaca;
        }
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        .strength-weak { color: #dc2626; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #10b981; }
        .password-requirements {
            font-size: 0.75rem;
            color: #666666;
            margin-top: 0.5rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .requirement.met {
            color: #10b981;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="register-container flex items-center justify-center p-6">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <i class="fas fa-user-plus"></i>
                    Join W3Clone
                </div>
                <p class="text-gray-600">Create your account and start learning today</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Please fix the following errors:</strong>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        value="<?= htmlspecialchars($form_data['full_name']) ?>"
                        class="form-input w-full" 
                        placeholder="Enter your full name"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-at"></i>
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= htmlspecialchars($form_data['username']) ?>"
                        class="form-input w-full" 
                        placeholder="Choose a username"
                        required
                        pattern="[a-zA-Z0-9_]+"
                        minlength="3"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        Only letters, numbers, and underscores. Minimum 3 characters.
                    </p>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($form_data['email']) ?>"
                        class="form-input w-full" 
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input w-full pr-12" 
                            placeholder="Create a strong password"
                            required
                            minlength="8"
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            onclick="togglePassword('password')"
                        >
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                    <div class="password-requirements">
                        <div class="requirement" id="req-length">
                            <i class="fas fa-times"></i>
                            At least 8 characters
                        </div>
                        <div class="requirement" id="req-upper">
                            <i class="fas fa-times"></i>
                            One uppercase letter
                        </div>
                        <div class="requirement" id="req-lower">
                            <i class="fas fa-times"></i>
                            One lowercase letter
                        </div>
                        <div class="requirement" id="req-number">
                            <i class="fas fa-times"></i>
                            One number
                        </div>
                        <div class="requirement" id="req-special">
                            <i class="fas fa-times"></i>
                            One special character
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input w-full pr-12" 
                            placeholder="Confirm your password"
                            required
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            onclick="togglePassword('confirm_password')"
                        >
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                    <div id="passwordMatch" class="text-sm mt-1"></div>
                </div>

                <div class="form-group">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="agree_terms" 
                            class="form-checkbox mt-1" 
                            required
                        >
                        <span class="text-sm text-gray-600">
                            I agree to the 
                            <a href="terms.php" class="text-black font-semibold hover:underline" target="_blank">
                                Terms of Service
                            </a> 
                            and 
                            <a href="privacy.php" class="text-black font-semibold hover:underline" target="_blank">
                                Privacy Policy
                            </a>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 text-lg" id="submitBtn">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>

            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-black font-semibold hover:underline">
                        Sign in here
                    </a>
                </p>
            </div>

            <!-- Benefits Section -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-star text-yellow-500"></i>
                    Why join W3Clone?
                </h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li><i class="fas fa-check text-green-500"></i> Access to 100+ tutorials</li>
                    <li><i class="fas fa-check text-green-500"></i> Track your learning progress</li>
                    <li><i class="fas fa-check text-green-500"></i> Save your favorite lessons</li>
                    <li><i class="fas fa-check text-green-500"></i> Join our learning community</li>
                </ul>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const iconId = fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2';
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength(password) {
            const requirements = {
                'req-length': password.length >= 8,
                'req-upper': /[A-Z]/.test(password),
                'req-lower': /[a-z]/.test(password),
                'req-number': /[0-9]/.test(password),
                'req-special': /[^A-Za-z0-9]/.test(password)
            };

            let score = 0;
            for (const [reqId, met] of Object.entries(requirements)) {
                const element = document.getElementById(reqId);
                const icon = element.querySelector('i');
                
                if (met) {
                    element.classList.add('met');
                    icon.className = 'fas fa-check';
                    score++;
                } else {
                    element.classList.remove('met');
                    icon.className = 'fas fa-times';
                }
            }

            const strengthDiv = document.getElementById('passwordStrength');
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
            } else if (score < 3) {
                strengthDiv.innerHTML = '<span class="strength-weak"><i class="fas fa-exclamation-triangle"></i> Weak password</span>';
            } else if (score < 5) {
                strengthDiv.innerHTML = '<span class="strength-medium"><i class="fas fa-exclamation-circle"></i> Medium strength</span>';
            } else {
                strengthDiv.innerHTML = '<span class="strength-strong"><i class="fas fa-shield-alt"></i> Strong password</span>';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
            } else if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="text-green-600"><i class="fas fa-check"></i> Passwords match</span>';
            } else {
                matchDiv.innerHTML = '<span class="text-red-600"><i class="fas fa-times"></i> Passwords do not match</span>';
            }
        }

        // Add event listeners
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
        });
    </script>

</body>
</html>
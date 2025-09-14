<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    redirect('dashboard.php');
}

// Redirect regular users to their dashboard
if (isLoggedIn() && !isAdmin()) {
    redirect('../pages/dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    }
    // Enhanced rate limiting for admin login
    elseif (!checkRateLimit($_SERVER['REMOTE_ADDR'] . '_admin_login', 3, 1800)) {
        $error = 'Too many admin login attempts. Please try again in 30 minutes.';
        logError("Multiple failed admin login attempts from IP: " . $_SERVER['REMOTE_ADDR']);
    }
    // Validate input
    elseif (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    }
    elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    }
    else {
        // Check admin credentials
        $query = "SELECT id, username, email, password, full_name, is_admin, is_active, email_verified 
                  FROM users WHERE email = ? AND is_admin = 1 LIMIT 1";
        $result = executeQuery($query, 's', [$email]);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (!$user['is_active']) {
                $error = 'Admin account has been deactivated.';
                logError("Login attempt on deactivated admin account: $email");
            }
            elseif (verifyPassword($password, $user['password'])) {
                // Successful admin login
                loginUser($user['id'], $user);
                
                // Log admin login
                logError("Admin login successful: " . $user['username'] . " from IP: " . $_SERVER['REMOTE_ADDR']);
                
                // Redirect to admin dashboard
                redirect('dashboard.php');
            }
            else {
                $error = 'Invalid email or password.';
                logError("Failed admin login attempt for email: $email from IP: " . $_SERVER['REMOTE_ADDR']);
            }
        }
        else {
            $error = 'Invalid admin credentials.';
            logError("Admin login attempt with non-existent email: $email from IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LearnCode</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
        }
        .admin-login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            padding: 3rem;
        }
        .admin-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .admin-subtitle {
            color: #666666;
            text-align: center;
            margin-bottom: 2rem;
        }
        .error-alert {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .demo-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .security-notice {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="admin-login-container flex items-center justify-center p-6">
        <div class="admin-login-card">
            
            <div class="admin-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="admin-logo">Admin Panel</h1>
            <p class="admin-subtitle">Secure access to W3Clone administration</p>

            <?php if ($error): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Demo Credentials -->
            <div class="demo-info">
                <h4 class="font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Demo Admin Access
                </h4>
                <p class="text-blue-700">
                    <strong>Email:</strong> admin@w3clone.com<br>
                    <strong>Password:</strong> admin123
                </p>
            </div>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="email" class="admin-form-label">
                        <i class="fas fa-envelope mr-2"></i>
                        Admin Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($email) ?>"
                        class="admin-form-input" 
                        placeholder="Enter your admin email"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="admin-form-label">
                        <i class="fas fa-lock mr-2"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="admin-form-input pr-12" 
                            placeholder="Enter your password"
                            required
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            onclick="togglePassword()"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-admin btn-admin-primary w-full py-3 text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Access Admin Panel
                </button>
            </form>

            <div class="security-notice">
                <i class="fas fa-shield-alt text-yellow-600 mr-2"></i>
                <strong>Security Notice:</strong> This is a restricted area. All access attempts are logged and monitored.
            </div>

            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <a href="../index.php" class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Main Site
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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

        // Auto-fill demo credentials on click
        document.querySelector('.demo-info').addEventListener('click', function() {
            document.getElementById('email').value = 'admin@w3clone.com';
            document.getElementById('password').value = 'admin123';
        });

        // Add some security-focused UI behavior
        let failedAttempts = 0;
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email.includes('admin') && !email.includes('w3clone.com')) {
                failedAttempts++;
                if (failedAttempts >= 2) {
                    alert('Multiple invalid attempts detected. This incident will be reported.');
                }
            }
        });
    </script>

</body>
</html>
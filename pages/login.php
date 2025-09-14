<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    }
    // Rate limiting check
    elseif (!checkRateLimit($_SERVER['REMOTE_ADDR'] . '_login', 5, 900)) {
        $error = 'Too many login attempts. Please try again in 15 minutes.';
    }
    // Validate input
    elseif (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    }
    elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    }
    else {
        // Check user credentials
        $query = "SELECT id, username, email, password, full_name, is_admin, is_active, email_verified 
                  FROM users WHERE email = ? LIMIT 1";
        $result = executeQuery($query, 's', [$email]);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (!$user['is_active']) {
                $error = 'Your account has been deactivated. Please contact support.';
            }
            elseif (!$user['email_verified']) {
                $error = 'Please verify your email address before logging in.';
            }
            elseif (verifyPassword($password, $user['password'])) {
                // Successful login
                loginUser($user['id'], $user);
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store remember token in database
                    $update_query = "UPDATE users SET remember_token = ?, remember_expires = FROM_UNIXTIME(?) WHERE id = ?";
                    executeQuery($update_query, 'sii', [$token, $expiry, $user['id']]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                }
                
                // Redirect to intended page or dashboard
                if ($user['is_admin']) {
                    session_write_close();
                    redirect('../admin/dashboard.php');
                } else {
                    $redirect_url = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_after_login']);
                    session_write_close();
                    redirect($redirect_url);
                }
            }
            else {
                $error = 'Invalid email or password.';
            }
        }
        else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - W3Clone</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            min-height: calc(100vh - 70px);
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo {
            font-size: 2rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
        }
        .error-message {
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
        .demo-credentials {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .demo-credentials h4 {
            color: #0369a1;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .demo-credentials ul {
            color: #0c4a6e;
            margin: 0;
            padding-left: 1rem;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="login-container flex items-center justify-center p-6">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-code"></i>
                    Welcome Back
                </div>
                <p class="text-gray-600">Sign in to continue your learning journey</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($email) ?>"
                        class="form-input w-full" 
                        placeholder="Enter your email"
                        required
                        autofocus
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

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="form-checkbox">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-black hover:underline">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>

            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-black font-semibold hover:underline">
                        Sign up here
                    </a>
                </p>
            </div>

            <!-- Social Login Options (Future Enhancement) -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button class="btn btn-outline py-2 text-sm" disabled>
                        <i class="fab fa-google mr-2"></i>
                        Google
                    </button>
                    <button class="btn btn-outline py-2 text-sm" disabled>
                        <i class="fab fa-github mr-2"></i>
                        GitHub
                    </button>
                </div>
                <p class="text-xs text-center text-gray-500 mt-2">
                    Social login coming soon
                </p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

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

        // Auto-fill demo credentials
        function fillDemo(type) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            if (type === 'admin') {
                emailInput.value = 'admin@w3clone.com';
                passwordInput.value = 'admin123';
            } else {
                emailInput.value = 'john@example.com';
                passwordInput.value = 'user123';
            }
        }

        // Add click handlers to demo credentials
        document.addEventListener('DOMContentLoaded', function() {
            const demoCredentials = document.querySelector('.demo-credentials ul');
            if (demoCredentials) {
                demoCredentials.addEventListener('click', function(e) {
                    const li = e.target.closest('li');
                    if (li) {
                        const text = li.textContent;
                        if (text.includes('admin@w3clone.com')) {
                            fillDemo('admin');
                        } else if (text.includes('john@example.com')) {
                            fillDemo('user');
                        }
                    }
                });
            }
        });
    </script>

</body>
</html>
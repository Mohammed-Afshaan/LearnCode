<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'profile.php';
    redirect('login.php');
}

$user = getCurrentUser();
$user_progress = getUserProgress($user['id']);

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid security token. Please try again.';
        }
        
        // Validate input
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Check if email is taken by another user
        if (empty($errors) && $email !== $user['email']) {
            $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $result = executeQuery($check_query, 'si', [$email, $user['id']]);
            if ($result && $result->num_rows > 0) {
                $errors[] = 'Email address is already taken.';
            }
        }
        
        // Update profile
        if (empty($errors)) {
            $update_query = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
            $result = executeQuery($update_query, 'ssi', [$full_name, $email, $user['id']]);
            
            if ($result) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $user['full_name'] = $full_name;
                $user['email'] = $email;
                $success = true;
                setFlashMessage('success', 'Profile updated successfully!');
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid security token. Please try again.';
        }
        
        // Validate input
        if (empty($current_password)) {
            $errors[] = 'Current password is required.';
        }
        
        if (empty($new_password)) {
            $errors[] = 'New password is required.';
        } else {
            $password_validation = validatePassword($new_password);
            if (!$password_validation['valid']) {
                $errors[] = $password_validation['message'];
            }
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        }
        
        // Verify current password
        if (empty($errors)) {
            $query = "SELECT password FROM users WHERE id = ?";
            $result = executeQuery($query, 'i', [$user['id']]);
            
            if ($result) {
                $stored_password = $result->fetch_assoc()['password'];
                if (!verifyPassword($current_password, $stored_password)) {
                    $errors[] = 'Current password is incorrect.';
                }
            }
        }
        
        // Update password
        if (empty($errors)) {
            $hashed_password = hashPassword($new_password);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $result = executeQuery($update_query, 'si', [$hashed_password, $user['id']]);
            
            if ($result) {
                $success = true;
                setFlashMessage('success', 'Password changed successfully!');
            } else {
                $errors[] = 'Failed to change password. Please try again.';
            }
        }
    }
}

// Get user's recent activities
$recent_query = "SELECT t.title, t.slug, t.language, up.status, up.last_accessed 
                 FROM user_progress up 
                 JOIN topics t ON up.topic_id = t.id 
                 WHERE up.user_id = ? 
                 ORDER BY up.last_accessed DESC 
                 LIMIT 5";
$recent_activities = [];
$result = executeQuery($recent_query, 'i', [$user['id']]);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}

// Get user's favorite topics
$favorites_query = "SELECT t.title, t.slug, t.language, uf.created_at 
                    FROM user_favorites uf 
                    JOIN topics t ON uf.topic_id = t.id 
                    WHERE uf.user_id = ? 
                    ORDER BY uf.created_at DESC 
                    LIMIT 5";
$favorites = [];
$result = executeQuery($favorites_query, 'i', [$user['id']]);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - W3Clone</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="flex min-h-screen bg-gray-50">
        
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-sm">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <span class="text-2xl font-bold text-gray-600">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p class="text-gray-600">@<?= htmlspecialchars($user['username']) ?></p>
                </div>
                
                <nav class="space-y-2">
                    <a href="#profile" class="profile-tab active flex items-center px-4 py-2 text-black bg-gray-100 rounded-lg">
                        <i class="fas fa-user mr-3"></i>
                        Profile Settings
                    </a>
                    <a href="#security" class="profile-tab flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-shield-alt mr-3"></i>
                        Security
                    </a>
                    <a href="#progress" class="profile-tab flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-chart-line mr-3"></i>
                        Learning Progress
                    </a>
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            
            <?php echo displayFlashMessages(); ?>

            <!-- Profile Settings Tab -->
            <div id="profile-content" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-2xl font-bold mb-6">Profile Settings</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <ul class="list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input 
                                    type="text" 
                                    id="full_name" 
                                    name="full_name" 
                                    value="<?= htmlspecialchars($user['full_name']) ?>"
                                    class="form-input w-full" 
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    value="<?= htmlspecialchars($user['username']) ?>"
                                    class="form-input w-full bg-gray-100" 
                                    disabled
                                >
                                <p class="text-sm text-gray-500 mt-1">Username cannot be changed</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($user['email']) ?>"
                                class="form-input w-full" 
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Member Since</label>
                            <p class="text-gray-600"><?= formatDate($user['created_at'], 'F j, Y') ?></p>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary px-6 py-2">
                            <i class="fas fa-save mr-2"></i>
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-content" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-2xl font-bold mb-6">Change Password</h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="form-input w-full" 
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="form-input w-full" 
                                required
                                minlength="8"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input w-full" 
                                required
                            >
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary px-6 py-2">
                            <i class="fas fa-key mr-2"></i>
                            Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Progress Tab -->
            <div id="progress-content" class="tab-content hidden">
                <div class="grid gap-8">
                    
                    <!-- Progress Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-8">
                        <h2 class="text-2xl font-bold mb-6">Learning Statistics</h2>
                        <div class="grid md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2"><?= $user_progress['completed'] ?></div>
                                <div class="text-gray-600">Completed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600 mb-2"><?= $user_progress['in_progress'] ?></div>
                                <div class="text-gray-600">In Progress</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-red-600 mb-2"><?= $user_progress['favorites'] ?></div>
                                <div class="text-gray-600">Favorites</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600 mb-2"><?= $user_progress['time_spent'] ?>h</div>
                                <div class="text-gray-600">Time Spent</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white rounded-lg shadow-sm p-8">
                        <h3 class="text-xl font-bold mb-4">Recent Activities</h3>
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-gray-600">No recent activities. <a href="topics.php" class="text-black font-medium hover:underline">Start learning!</a></p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h4 class="font-medium"><?= htmlspecialchars($activity['title']) ?></h4>
                                            <p class="text-sm text-gray-600">
                                                <?= htmlspecialchars($activity['language']) ?> • 
                                                Status: <span class="capitalize"><?= htmlspecialchars($activity['status']) ?></span>
                                            </p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= timeAgo($activity['last_accessed']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Favorites -->
                    <div class="bg-white rounded-lg shadow-sm p-8">
                        <h3 class="text-xl font-bold mb-4">Favorite Topics</h3>
                        <?php if (empty($favorites)): ?>
                            <p class="text-gray-600">No favorite topics yet.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($favorites as $favorite): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h4 class="font-medium"><?= htmlspecialchars($favorite['title']) ?></h4>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($favorite['language']) ?></p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Added <?= timeAgo($favorite['created_at']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Tab switching
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.profile-tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href').substring(1);
                    
                    // Remove active class from all tabs
                    tabs.forEach(t => {
                        t.classList.remove('active', 'text-black', 'bg-gray-100');
                        t.classList.add('text-gray-600');
                    });
                    
                    // Add active class to clicked tab
                    this.classList.add('active', 'text-black', 'bg-gray-100');
                    this.classList.remove('text-gray-600');
                    
                    // Hide all content
                    contents.forEach(c => c.classList.add('hidden'));
                    
                    // Show target content
                    document.getElementById(target + '-content').classList.remove('hidden');
                });
            });
        });
    </script>

</body>
</html>
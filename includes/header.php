<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['username'] : '';
$is_admin = $is_logged_in && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<header class="header">
    <div class="container">
        
        <!-- Brand/Logo -->
        <div class="nav-brand">
            <a href="<?= ($current_dir === 'pages') ? '../index.php' : 'index.php' ?>" class="logo">
                <i class="fas fa-code"></i>
                LearnCode
            </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navigation Menu -->
        <nav class="nav-menu" id="navMenu">
            <ul>
                <li>
                    <a href="<?= ($current_dir === 'pages') ? '../index.php' : 'index.php' ?>" 
                       class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ($current_dir === 'pages') ? 'topics.php' : 'pages/topics.php' ?>" 
                       class="<?= ($current_page === 'topics.php') ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>Tutorials</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ($current_dir === 'pages') ? 'references.php' : 'pages/references.php' ?>" 
                       class="<?= ($current_page === 'references.php') ? 'active' : '' ?>">
                        <i class="fas fa-bookmark"></i>
                        <span>References</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ($current_dir === 'pages') ? 'exercises.php' : 'pages/exercises.php' ?>" 
                       class="<?= ($current_page === 'exercises.php') ? 'active' : '' ?>">
                        <i class="fas fa-dumbbell"></i>
                        <span>Exercises</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ($current_dir === 'pages') ? 'about.php' : 'pages/about.php' ?>" 
                       class="<?= ($current_page === 'about.php') ? 'active' : '' ?>">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Authentication Links -->
        <div class="nav-auth">
            <?php if ($is_logged_in): ?>
                <!-- Logged In User -->
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                    <div class="user-dropdown">
                        <a href="<?= ($current_dir === 'pages') ? 'profile.php' : 'pages/profile.php' ?>" class="dropdown-link">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                        <a href="<?= ($current_dir === 'pages') ? 'dashboard.php' : 'pages/dashboard.php' ?>" class="dropdown-link">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <?php if ($is_admin): ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= ($current_dir === 'pages') ? '../admin/dashboard.php' : 'admin/dashboard.php' ?>" class="dropdown-link admin-link">
                                <i class="fas fa-cog"></i>
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= ($current_dir === 'pages') ? 'logout.php' : 'pages/logout.php' ?>" class="dropdown-link logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Not Logged In -->
                <a href="<?= ($current_dir === 'pages') ? 'login.php' : 'pages/login.php' ?>" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Log In
                </a>
                <a href="<?= ($current_dir === 'pages') ? 'register.php' : 'pages/register.php' ?>" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Add some additional CSS for dropdown menu -->
<style>
.user-menu {
    position: relative;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.3s ease;
    background: transparent;
    border: 1px solid #e5e5e5;
}

.user-info:hover {
    background: #f5f5f5;
}

.user-name {
    font-weight: 500;
    color: #000000;
}

.dropdown-icon {
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.user-menu:hover .dropdown-icon {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.user-menu:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #333333;
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-link:hover {
    background: #f8f8f8;
    color: #000000;
    text-decoration: none;
}

.dropdown-link i {
    width: 16px;
    text-align: center;
}

.admin-link {
    color: #7c3aed;
}

.admin-link:hover {
    background: #f3f4f6;
    color: #7c3aed;
}

.logout-link {
    color: #dc2626;
}

.logout-link:hover {
    background: #fef2f2;
    color: #dc2626;
}

.dropdown-divider {
    height: 1px;
    background: #e5e5e5;
    margin: 0.5rem 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border-bottom: 1px solid #e5e5e5;
        padding: 1rem;
        display: none;
    }
    
    .nav-menu.open {
        display: block;
    }
    
    .nav-menu ul {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .nav-auth {
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
        width: 100%;
    }
    
    .user-dropdown {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border: none;
        border-top: 1px solid #e5e5e5;
        margin-top: 0.5rem;
    }
    
    .btn-login,
    .btn-register {
        text-align: center;
        width: 100%;
    }
}
</style>

<!-- JavaScript for mobile menu toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('open');
            
            // Change hamburger to X
            const icon = this.querySelector('i');
            if (navMenu.classList.contains('open')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('open');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
});
</script>
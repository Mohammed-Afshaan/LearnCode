<?php
// Get current page info for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['username'] : '';
$is_admin = $is_logged_in && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Determine base URL based on current directory
$base_url = ($current_dir === 'pages') ? '' : 'pages/';
$admin_url = ($current_dir === 'pages') ? '../admin/' : 'admin/';
$home_url = ($current_dir === 'pages') ? '../index.php' : 'index.php';

// Define navigation items
$nav_items = [
    'home' => [
        'label' => 'Home',
        'url' => $home_url,
        'icon' => 'fas fa-home',
        'active' => $current_page === 'index.php'
    ],
    'tutorials' => [
        'label' => 'Tutorials',
        'url' => $base_url . 'topics.php',
        'icon' => 'fas fa-book',
        'active' => in_array($current_page, ['topics.php', 'topic-detail.php']),
        'submenu' => [
            'all' => ['label' => 'All Tutorials', 'url' => $base_url . 'topics.php'],
            'html' => ['label' => 'HTML', 'url' => $base_url . 'topics.php?language=HTML'],
            'css' => ['label' => 'CSS', 'url' => $base_url . 'topics.php?language=CSS'],
            'javascript' => ['label' => 'JavaScript', 'url' => $base_url . 'topics.php?language=JavaScript'],
            'php' => ['label' => 'PHP', 'url' => $base_url . 'topics.php?language=PHP'],
            'python' => ['label' => 'Python', 'url' => $base_url . 'topics.php?language=Python']
        ]
    ],
    'references' => [
        'label' => 'References',
        'url' => $base_url . 'references.php',
        'icon' => 'fas fa-bookmark',
        'active' => $current_page === 'references.php',
        'submenu' => [
            'html-ref' => ['label' => 'HTML Reference', 'url' => $base_url . 'reference.php?topic=html'],
            'css-ref' => ['label' => 'CSS Reference', 'url' => $base_url . 'reference.php?topic=css'],
            'js-ref' => ['label' => 'JavaScript Reference', 'url' => $base_url . 'reference.php?topic=javascript'],
            'php-ref' => ['label' => 'PHP Reference', 'url' => $base_url . 'reference.php?topic=php']
        ]
    ],
    'exercises' => [
        'label' => 'Exercises',
        'url' => $base_url . 'exercises.php',
        'icon' => 'fas fa-dumbbell',
        'active' => $current_page === 'exercises.php'
    ],
    'tools' => [
        'label' => 'Tools',
        'url' => $base_url . 'tools.php',
        'icon' => 'fas fa-tools',
        'active' => $current_page === 'tools.php',
        'submenu' => [
            'code-editor' => ['label' => 'Code Editor', 'url' => $base_url . 'editor.php'],
            'color-picker' => ['label' => 'Color Picker', 'url' => $base_url . 'color-picker.php'],
            'html-validator' => ['label' => 'HTML Validator', 'url' => $base_url . 'validator.php'],
            'css-minifier' => ['label' => 'CSS Minifier', 'url' => $base_url . 'minifier.php']
        ]
    ]
];

// Add conditional navigation items
if ($is_logged_in) {
    $nav_items['dashboard'] = [
        'label' => 'Dashboard',
        'url' => $base_url . 'dashboard.php',
        'icon' => 'fas fa-tachometer-alt',
        'active' => $current_page === 'dashboard.php'
    ];
}
?>

<nav class="main-nav" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        
        <!-- Primary Navigation -->
        <ul class="nav-primary">
            <?php foreach ($nav_items as $key => $item): ?>
                <li class="nav-item <?= isset($item['submenu']) ? 'has-submenu' : '' ?> <?= $item['active'] ? 'active' : '' ?>">
                    <a href="<?= $item['url'] ?>" class="nav-link <?= $item['active'] ? 'active' : '' ?>" 
                       <?= isset($item['submenu']) ? 'aria-haspopup="true" aria-expanded="false"' : '' ?>>
                        <i class="<?= $item['icon'] ?>"></i>
                        <span class="nav-text"><?= $item['label'] ?></span>
                        <?php if (isset($item['submenu'])): ?>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isset($item['submenu'])): ?>
                        <ul class="nav-submenu">
                            <?php foreach ($item['submenu'] as $sub_key => $sub_item): ?>
                                <li class="nav-subitem">
                                    <a href="<?= $sub_item['url'] ?>" class="nav-sublink">
                                        <?= $sub_item['label'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Search Box -->
        <div class="nav-search">
            <form action="<?= $base_url ?>search.php" method="GET" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="q" placeholder="Search tutorials..." 
                           class="search-input" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="search-button" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- User Menu -->
        <div class="nav-user">
            <?php if ($is_logged_in): ?>
                <!-- Logged In User -->
                <div class="user-menu-wrapper">
                    <div class="user-avatar">
                        <?php if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($_SESSION['profile_image']) ?>" 
                                 alt="<?= htmlspecialchars($user_name) ?>" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($user_name, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-info">
                        <span class="user-greeting">Hello,</span>
                        <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                        <?php if ($is_admin): ?>
                            <span class="user-badge admin">Admin</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-dropdown">
                        <a href="<?= $base_url ?>profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="<?= $base_url ?>dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="<?= $base_url ?>favorites.php" class="dropdown-item">
                            <i class="fas fa-heart"></i>
                            <span>Favorites</span>
                        </a>
                        <a href="<?= $base_url ?>settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        
                        <?php if ($is_admin): ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= $admin_url ?>dashboard.php" class="dropdown-item admin-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Admin Panel</span>
                            </a>
                        <?php endif; ?>
                        
                        <div class="dropdown-divider"></div>
                        <a href="<?= $base_url ?>logout.php" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Not Logged In -->
                <div class="auth-buttons">
                    <a href="<?= $base_url ?>login.php" class="auth-btn login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Log In</span>
                    </a>
                    <a href="<?= $base_url ?>register.php" class="auth-btn register-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Sign Up</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay"></div>
</nav>

<!-- Navigation Styles -->
<style>
.main-nav {
    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    height: 70px;
    gap: 2rem;
}

.nav-primary {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.5rem;
    flex: 1;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: #666666;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.nav-link:hover,
.nav-link.active {
    color: #000000;
    background: #f8f8f8;
}

.nav-link.active {
    background: #000000;
    color: #ffffff;
}

.nav-arrow {
    font-size: 0.75rem;
    transition: transform 0.3s ease;
}

.nav-item:hover .nav-arrow {
    transform: rotate(180deg);
}

.nav-submenu {
    position: absolute;
    top: 100%;
    left: 0;
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 100;
    list-style: none;
    margin: 0;
    padding: 0.5rem 0;
}

.nav-item:hover .nav-submenu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.nav-sublink {
    display: block;
    padding: 0.75rem 1rem;
    color: #666666;
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-sublink:hover {
    color: #000000;
    background: #f8f8f8;
}

.nav-search {
    flex-shrink: 0;
}

.search-input-group {
    display: flex;
    align-items: center;
    background: #f8f8f8;
    border-radius: 8px;
    overflow: hidden;
}

.search-input {
    width: 250px;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    font-size: 0.9rem;
    outline: none;
}

.search-button {
    padding: 0.75rem 1rem;
    background: #000000;
    color: #ffffff;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

.search-button:hover {
    background: #333333;
}

.nav-user {
    flex-shrink: 0;
}

.user-menu-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.user-menu-wrapper:hover {
    background: #f8f8f8;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: #000000;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.user-greeting {
    font-size: 0.75rem;
    color: #999999;
    line-height: 1;
}

.user-name {
    font-size: 0.9rem;
    color: #000000;
    font-weight: 500;
    line-height: 1;
}

.user-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 0.2rem;
}

.user-badge.admin {
    background: #000000;
    color: #ffffff;
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
    z-index: 100;
    margin-top: 0.5rem;
}

.user-menu-wrapper:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #666666;
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    color: #000000;
    background: #f8f8f8;
}

.dropdown-item i {
    width: 16px;
    text-align: center;
}

.admin-item {
    color: #7c3aed;
}

.admin-item:hover {
    color: #7c3aed;
    background: #f3f4f6;
}

.logout-item {
    color: #dc2626;
}

.logout-item:hover {
    color: #dc2626;
    background: #fef2f2;
}

.dropdown-divider {
    height: 1px;
    background: #e5e5e5;
    margin: 0.5rem 0;
}

.auth-buttons {
    display: flex;
    gap: 0.5rem;
}

.auth-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.login-btn {
    color: #000000;
    border: 1px solid #e5e5e5;
}

.login-btn:hover {
    background: #f8f8f8;
}

.register-btn {
    background: #000000;
    color: #ffffff;
}

.register-btn:hover {
    background: #333333;
}

.mobile-nav-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background: none;
    border: none;
    cursor: pointer;
    gap: 4px;
}

.hamburger-line {
    width: 20px;
    height: 2px;
    background: #000000;
    transition: all 0.3s ease;
}

.mobile-nav-overlay {
    display: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .nav-container {
        padding: 0 1rem;
        height: 60px;
        gap: 1rem;
    }

    .nav-primary {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        flex-direction: column;
        background: #ffffff;
        border-bottom: 1px solid #e5e5e5;
        padding: 1rem 0;
        transform: translateY(-100%);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .nav-primary.open {
        transform: translateY(0);
    }

    .nav-item {
        width: 100%;
    }

    .nav-link {
        justify-content: flex-start;
        padding: 1rem 1.5rem;
    }

    .nav-submenu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border: none;
        background: #f8f8f8;
        margin: 0;
    }

    .nav-search {
        order: -1;
        flex: 1;
        max-width: none;
    }

    .search-input {
        width: 100%;
    }

    .mobile-nav-toggle {
        display: flex;
    }

    .user-dropdown {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border-top: 1px solid #e5e5e5;
        margin-top: 0.5rem;
        border-radius: 0;
    }

    .mobile-nav-overlay {
        display: block;
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
    }

    .mobile-nav-overlay.open {
        opacity: 1;
        visibility: visible;
    }
}
</style>

<!-- Navigation JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const navPrimary = document.querySelector('.nav-primary');
    const mobileOverlay = document.querySelector('.mobile-nav-overlay');
    
    if (mobileToggle && navPrimary) {
        mobileToggle.addEventListener('click', function() {
            const isOpen = navPrimary.classList.contains('open');
            
            if (isOpen) {
                navPrimary.classList.remove('open');
                mobileOverlay.classList.remove('open');
                this.setAttribute('aria-expanded', 'false');
            } else {
                navPrimary.classList.add('open');
                mobileOverlay.classList.add('open');
                this.setAttribute('aria-expanded', 'true');
            }
        });
        
        mobileOverlay.addEventListener('click', function() {
            navPrimary.classList.remove('open');
            mobileOverlay.classList.remove('open');
            mobileToggle.setAttribute('aria-expanded', 'false');
        });
    }
    
    // Search functionality
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    
    if (searchForm && searchInput) {
        // Add search suggestions (future enhancement)
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                // Implement search suggestions here
                console.log('Search for:', query);
            }
        });
    }
});
</script>
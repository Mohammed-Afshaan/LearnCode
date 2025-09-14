<?php
// Get current page info for active highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$language_filter = $_GET['language'] ?? '';

// Check if we're in a subdirectory
$base_url = ($current_dir === 'pages') ? '' : 'pages/';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        
        <!-- Quick Links Section -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-rocket"></i>
                Quick Start
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>topics.php" class="<?= ($current_page === 'topics.php' && !$language_filter) ? 'active' : '' ?>">
                        <i class="fas fa-list"></i>
                        <span>All Tutorials</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?featured=1" class="<?= (isset($_GET['featured'])) ? 'active' : '' ?>">
                        <i class="fas fa-star"></i>
                        <span>Featured</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?difficulty=Beginner" class="<?= ($_GET['difficulty'] ?? '') === 'Beginner' ? 'active' : '' ?>">
                        <i class="fas fa-seedling"></i>
                        <span>Beginner</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?difficulty=Advanced" class="<?= ($_GET['difficulty'] ?? '') === 'Advanced' ? 'active' : '' ?>">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Advanced</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Programming Languages -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-code"></i>
                Languages
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>topics.php?language=HTML" class="<?= ($language_filter === 'HTML') ? 'active' : '' ?>">
                        <i class="fab fa-html5" style="color: #e34f26;"></i>
                        <span>HTML</span>
                        <span class="count">15</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=CSS" class="<?= ($language_filter === 'CSS') ? 'active' : '' ?>">
                        <i class="fab fa-css3-alt" style="color: #1572b6;"></i>
                        <span>CSS</span>
                        <span class="count">12</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=JavaScript" class="<?= ($language_filter === 'JavaScript') ? 'active' : '' ?>">
                        <i class="fab fa-js-square" style="color: #f7df1e;"></i>
                        <span>JavaScript</span>
                        <span class="count">20</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=PHP" class="<?= ($language_filter === 'PHP') ? 'active' : '' ?>">
                        <i class="fab fa-php" style="color: #777bb4;"></i>
                        <span>PHP</span>
                        <span class="count">18</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=Python" class="<?= ($language_filter === 'Python') ? 'active' : '' ?>">
                        <i class="fab fa-python" style="color: #3776ab;"></i>
                        <span>Python</span>
                        <span class="count">16</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=SQL" class="<?= ($language_filter === 'SQL') ? 'active' : '' ?>">
                        <i class="fas fa-database" style="color: #00758f;"></i>
                        <span>SQL</span>
                        <span class="count">10</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Frameworks & Libraries -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-layer-group"></i>
                Frameworks
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>topics.php?language=React" class="<?= ($language_filter === 'React') ? 'active' : '' ?>">
                        <i class="fab fa-react" style="color: #61dafb;"></i>
                        <span>React</span>
                        <span class="count">8</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=Vue" class="<?= ($language_filter === 'Vue') ? 'active' : '' ?>">
                        <i class="fab fa-vuejs" style="color: #4fc08d;"></i>
                        <span>Vue.js</span>
                        <span class="count">6</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=Node" class="<?= ($language_filter === 'Node') ? 'active' : '' ?>">
                        <i class="fab fa-node-js" style="color: #339933;"></i>
                        <span>Node.js</span>
                        <span class="count">12</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?language=Laravel" class="<?= ($language_filter === 'Laravel') ? 'active' : '' ?>">
                        <i class="fab fa-laravel" style="color: #ff2d20;"></i>
                        <span>Laravel</span>
                        <span class="count">9</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tools & Resources -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-tools"></i>
                Tools
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>topics.php?category=git" class="<?= ($_GET['category'] ?? '') === 'git' ? 'active' : '' ?>">
                        <i class="fab fa-git-alt" style="color: #f05032;"></i>
                        <span>Git & GitHub</span>
                        <span class="count">5</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?category=vscode" class="<?= ($_GET['category'] ?? '') === 'vscode' ? 'active' : '' ?>">
                        <i class="fas fa-code" style="color: #007acc;"></i>
                        <span>VS Code</span>
                        <span class="count">4</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?category=deployment" class="<?= ($_GET['category'] ?? '') === 'deployment' ? 'active' : '' ?>">
                        <i class="fas fa-cloud-upload-alt" style="color: #ff9900;"></i>
                        <span>Deployment</span>
                        <span class="count">7</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>topics.php?category=testing" class="<?= ($_GET['category'] ?? '') === 'testing' ? 'active' : '' ?>">
                        <i class="fas fa-vial" style="color: #8cc84b;"></i>
                        <span>Testing</span>
                        <span class="count">6</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Learning Paths -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-route"></i>
                Learning Paths
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>path.php?path=frontend" class="<?= ($_GET['path'] ?? '') === 'frontend' ? 'active' : '' ?>">
                        <i class="fas fa-palette"></i>
                        <span>Frontend Developer</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 45%;"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>path.php?path=backend" class="<?= ($_GET['path'] ?? '') === 'backend' ? 'active' : '' ?>">
                        <i class="fas fa-server"></i>
                        <span>Backend Developer</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 30%;"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>path.php?path=fullstack" class="<?= ($_GET['path'] ?? '') === 'fullstack' ? 'active' : '' ?>">
                        <i class="fas fa-layer-group"></i>
                        <span>Full Stack</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 25%;"></div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- User Progress (only for logged in users) -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-chart-line"></i>
                Your Progress
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>favorites.php">
                        <i class="fas fa-heart"></i>
                        <span>Favorites</span>
                        <span class="count">3</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>completed.php">
                        <i class="fas fa-check-circle"></i>
                        <span>Completed</span>
                        <span class="count">12</span>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Help & Support -->
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <i class="fas fa-question-circle"></i>
                Support
            </h3>
            <ul class="sidebar-links">
                <li>
                    <a href="<?= $base_url ?>faq.php">
                        <i class="fas fa-question"></i>
                        <span>FAQ</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>contact.php">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>feedback.php">
                        <i class="fas fa-comment-alt"></i>
                        <span>Feedback</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-stats">
            <div class="stat-item">
                <strong>125+</strong>
                <span>Tutorials</span>
            </div>
            <div class="stat-item">
                <strong>50K+</strong>
                <span>Students</span>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Additional CSS for Sidebar -->
<style>
.sidebar {
    width: 280px;
    background: #ffffff;
    border-right: 1px solid #e5e5e5;
    height: calc(100vh - 70px);
    position: sticky;
    top: 70px;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-nav {
    padding: 1rem 0;
}

.sidebar-section {
    margin-bottom: 2rem;
}

.sidebar-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #000000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.5rem 1.5rem;
    margin-bottom: 0.5rem;
}

.sidebar-links {
    list-style: none;
    margin: 0;
    padding: 0;
}

.sidebar-links li {
    margin-bottom: 2px;
}

.sidebar-links a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: #666666;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    position: relative;
}

.sidebar-links a:hover,
.sidebar-links a.active {
    color: #000000;
    background: #f8f8f8;
    border-left-color: #000000;
}

.sidebar-links a i {
    width: 18px;
    text-align: center;
    font-size: 1rem;
}

.sidebar-links a .count {
    margin-left: auto;
    background: #e5e5e5;
    color: #666666;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
}

.sidebar-links a:hover .count,
.sidebar-links a.active .count {
    background: #000000;
    color: #ffffff;
}

.progress-bar {
    width: 100%;
    height: 3px;
    background: #e5e5e5;
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #000000;
    transition: width 0.3s ease;
}

.sidebar-footer {
    border-top: 1px solid #e5e5e5;
    padding: 1rem 1.5rem;
    background: #f8f8f8;
}

.sidebar-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    text-align: center;
}

.stat-item strong {
    display: block;
    font-size: 1.125rem;
    color: #000000;
}

.stat-item span {
    font-size: 0.75rem;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 70px;
    left: 0;
    width: 100%;
    height: calc(100vh - 70px);
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 70px;
        left: 0;
        width: 280px;
        height: calc(100vh - 70px);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .sidebar-overlay.open {
        display: block;
    }
    
    .sidebar-title {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
    
    .sidebar-links a {
        padding: 0.5rem 1rem;
    }
    
    .sidebar-footer {
        padding: 1rem;
    }
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track {
    background: #f8f8f8;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #e5e5e5;
    border-radius: 4px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #cccccc;
}
</style>

<!-- JavaScript for Mobile Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar on mobile
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('open');
    }
    
    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        });
    }
    
    // Add sidebar toggle to mobile menu (if exists)
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    if (mobileMenuToggle && window.innerWidth <= 768) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
    }
});
</script>
<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

$slug = sanitize_input($_GET['slug'] ?? '');
$topic = null;
$related_topics = [];
$user_progress = null;
$is_favorite = false;

if (empty($slug)) {
    redirect('topics.php');
}

// Get topic details
$query = "SELECT t.*, u.username as author_name, u.full_name as author_full_name 
          FROM topics t 
          LEFT JOIN users u ON t.author_id = u.id 
          WHERE t.slug = ? AND t.is_published = 1";
$result = executeQuery($query, 's', [$slug]);

if (!$result || $result->num_rows === 0) {
    redirect('topics.php');
}

$topic = $result->fetch_assoc();

// Update view count
$update_views = "UPDATE topics SET view_count = view_count + 1 WHERE id = ?";
executeQuery($update_views, 'i', [$topic['id']]);

// Record view if user is logged in
if (isLoggedIn()) {
    $user_id = getCurrentUserId();
    
    // Get user progress for this topic
    $progress_query = "SELECT * FROM user_progress WHERE user_id = ? AND topic_id = ?";
    $progress_result = executeQuery($progress_query, 'ii', [$user_id, $topic['id']]);
    
    if ($progress_result && $progress_result->num_rows > 0) {
        $user_progress = $progress_result->fetch_assoc();
        // Update last accessed
        $update_progress = "UPDATE user_progress SET last_accessed = NOW() WHERE user_id = ? AND topic_id = ?";
        executeQuery($update_progress, 'ii', [$user_id, $topic['id']]);
    } else {
        // Create new progress record
        $create_progress = "INSERT INTO user_progress (user_id, topic_id, status, last_accessed) VALUES (?, ?, 'not_started', NOW())";
        executeQuery($create_progress, 'iis', [$user_id, $topic['id'], 'not_started']);
    }
    
    // Check if topic is in favorites
    $favorite_query = "SELECT id FROM user_favorites WHERE user_id = ? AND topic_id = ?";
    $favorite_result = executeQuery($favorite_query, 'ii', [$user_id, $topic['id']]);
    $is_favorite = $favorite_result && $favorite_result->num_rows > 0;
    
    // Record topic view for analytics
    $view_query = "INSERT INTO topic_views (topic_id, user_id, ip_address, user_agent, viewed_at) VALUES (?, ?, ?, ?, NOW())";
    executeQuery($view_query, 'iiss', [$topic['id'], $user_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']);
}

// Get related topics
$related_query = "SELECT id, title, slug, language, difficulty, view_count 
                  FROM topics 
                  WHERE language = ? AND id != ? AND is_published = 1 
                  ORDER BY view_count DESC, created_at DESC 
                  LIMIT 5";
$related_result = executeQuery($related_query, 'si', [$topic['language'], $topic['id']]);

if ($related_result) {
    while ($row = $related_result->fetch_assoc()) {
        $related_topics[] = $row;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }
    
    $user_id = getCurrentUserId();
    
    switch ($_POST['action']) {
        case 'toggle_favorite':
            if ($is_favorite) {
                $query = "DELETE FROM user_favorites WHERE user_id = ? AND topic_id = ?";
                $result = executeQuery($query, 'ii', [$user_id, $topic['id']]);
                echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from favorites']);
            } else {
                $query = "INSERT INTO user_favorites (user_id, topic_id) VALUES (?, ?)";
                $result = executeQuery($query, 'ii', [$user_id, $topic['id']]);
                echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to favorites']);
            }
            exit;
            
        case 'update_progress':
            $status = sanitize_input($_POST['status'] ?? 'in_progress');
            $percentage = (int)($_POST['percentage'] ?? 0);
            
            if ($user_progress) {
                $query = "UPDATE user_progress SET status = ?, progress_percentage = ?, last_accessed = NOW() WHERE user_id = ? AND topic_id = ?";
                executeQuery($query, 'siii', [$status, $percentage, $user_id, $topic['id']]);
            } else {
                $query = "INSERT INTO user_progress (user_id, topic_id, status, progress_percentage, last_accessed) VALUES (?, ?, ?, ?, NOW())";
                executeQuery($query, 'iisi', [$user_id, $topic['id'], $status, $percentage]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Progress updated']);
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($topic['title']) ?> - W3Clone</title>
    <meta name="description" content="<?= htmlspecialchars(excerpt($topic['description'], 160)) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($topic['tags']) ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($topic['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(excerpt($topic['description'], 160)) ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= getCurrentURL() ?>">
    
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism-tomorrow.min.css">
    
    <style>
        .topic-content {
            line-height: 1.8;
        }
        .topic-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 2rem 0 1rem;
        }
        .topic-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.5rem 0 0.75rem;
        }
        .topic-content p {
            margin-bottom: 1rem;
        }
        .topic-content ul, .topic-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        .topic-content li {
            margin-bottom: 0.5rem;
        }
        .code-container {
            position: relative;
        }
        .copy-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .copy-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .progress-bar {
            height: 8px;
            background: #e5e5e5;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto px-6 py-8">
        <div class="grid lg:grid-cols-4 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-3">
                
                <!-- Topic Header -->
                <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                    <div class="flex flex-wrap justify-between items-start mb-6">
                        <div class="flex flex-wrap items-center gap-3 mb-4 lg:mb-0">
                            <span class="px-4 py-2 text-sm font-semibold bg-purple-100 text-purple-700 rounded-full">
                                <?= htmlspecialchars($topic['language']) ?>
                            </span>
                            <span class="px-4 py-2 text-sm bg-green-100 text-green-700 rounded-full">
                                <?= htmlspecialchars($topic['difficulty']) ?>
                            </span>
                            <?php if ($topic['is_featured']): ?>
                                <span class="px-4 py-2 text-sm bg-yellow-100 text-yellow-700 rounded-full">
                                    <i class="fas fa-star mr-1"></i>
                                    Featured
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span>
                                <i class="far fa-eye mr-1"></i>
                                <?= number_format($topic['view_count']) ?> views
                            </span>
                            <span>
                                <i class="far fa-clock mr-1"></i>
                                <?= formatDate($topic['created_at']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <h1 class="text-4xl font-bold text-gray-800 mb-4">
                        <?= htmlspecialchars($topic['title']) ?>
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-6 leading-relaxed">
                        <?= htmlspecialchars($topic['description']) ?>
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($topic['author_full_name'] ?: $topic['author_name']) ?></p>
                                    <p class="text-sm text-gray-600">Author</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="flex items-center gap-2">
                                <button id="favoriteBtn" class="px-4 py-2 rounded-lg border transition-colors <?= $is_favorite ? 'bg-red-50 text-red-600 border-red-200' : 'bg-gray-50 text-gray-600 border-gray-200' ?>">
                                    <i class="<?= $is_favorite ? 'fas fa-heart' : 'far fa-heart' ?> mr-2"></i>
                                    <?= $is_favorite ? 'Remove from Favorites' : 'Add to Favorites' ?>
                                </button>
                                
                                <div class="relative">
                                    <button id="progressBtn" class="px-4 py-2 bg-green-50 text-green-600 border border-green-200 rounded-lg">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <?= $user_progress ? ucfirst($user_progress['status']) : 'Mark as Started' ?>
                                    </button>
                                    
                                    <div id="progressMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                        <button class="progress-option w-full text-left px-4 py-2 hover:bg-gray-50" data-status="not_started">
                                            <i class="fas fa-circle text-gray-400 mr-2"></i>
                                            Not Started
                                        </button>
                                        <button class="progress-option w-full text-left px-4 py-2 hover:bg-gray-50" data-status="in_progress">
                                            <i class="fas fa-play-circle text-blue-500 mr-2"></i>
                                            In Progress
                                        </button>
                                        <button class="progress-option w-full text-left px-4 py-2 hover:bg-gray-50" data-status="completed">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            Completed
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isLoggedIn() && $user_progress && $user_progress['progress_percentage'] > 0): ?>
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Your Progress</span>
                                <span class="text-sm text-gray-600"><?= $user_progress['progress_percentage'] ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $user_progress['progress_percentage'] ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Topic Content -->
                <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                    <?php if (!empty($topic['content'])): ?>
                        <div class="topic-content prose max-w-none">
                            <?= nl2br(htmlspecialchars($topic['content'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($topic['code_snippet'])): ?>
                        <div class="mt-8">
                            <h3 class="text-xl font-bold mb-4">
                                <i class="fas fa-code mr-2"></i>
                                Code Example
                            </h3>
                            <div class="code-container">
                                <button class="copy-button" onclick="copyCode('mainCode')">
                                    <i class="far fa-copy"></i>
                                </button>
                                <pre id="mainCode" class="language-<?= strtolower($topic['language']) ?>"><code><?= htmlspecialchars($topic['code_snippet']) ?></code></pre>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Navigation -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center">
                        <a href="topics.php" class="flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Topics
                        </a>
                        
                        <div class="flex items-center gap-4">
                            <button onclick="window.print()" class="flex items-center text-gray-600 hover:text-gray-800">
                                <i class="fas fa-print mr-2"></i>
                                Print
                            </button>
                            <button onclick="shareArticle()" class="flex items-center text-gray-600 hover:text-gray-800">
                                <i class="fas fa-share mr-2"></i>
                                Share
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- Table of Contents (if content has headings) -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-list mr-2"></i>
                        Quick Navigation
                    </h3>
                    <nav class="space-y-2">
                        <a href="#" class="block text-sm text-gray-600 hover:text-gray-800 py-1">Overview</a>
                        <a href="#" class="block text-sm text-gray-600 hover:text-gray-800 py-1">Code Example</a>
                        <a href="#related" class="block text-sm text-gray-600 hover:text-gray-800 py-1">Related Topics</a>
                    </nav>
                </div>

                <!-- Related Topics -->
                <?php if (!empty($related_topics)): ?>
                    <div id="related" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="fas fa-link mr-2"></i>
                            Related Topics
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($related_topics as $related): ?>
                                <a href="topic-detail.php?slug=<?= htmlspecialchars($related['slug']) ?>" 
                                   class="block p-3 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-colors">
                                    <div class="font-medium text-sm mb-1">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-600">
                                        <span><?= htmlspecialchars($related['difficulty']) ?></span>
                                        <span><?= number_format($related['view_count']) ?> views</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Learning Path -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                    <h3 class="font-bold text-lg mb-4 text-purple-800">
                        <i class="fas fa-route mr-2"></i>
                        Continue Learning
                    </h3>
                    <p class="text-purple-700 text-sm mb-4">
                        Master <?= htmlspecialchars($topic['language']) ?> with our comprehensive learning path.
                    </p>
                    <a href="topics.php?language=<?= urlencode($topic['language']) ?>" 
                       class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        View All <?= htmlspecialchars($topic['language']) ?> Topics
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Prism.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>

    <script>
        // Copy code functionality
        function copyCode(elementId) {
            const codeElement = document.getElementById(elementId);
            const text = codeElement.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                const button = codeElement.parentElement.querySelector('.copy-button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.style.background = 'rgba(34, 197, 94, 0.8)';
                
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.style.background = 'rgba(255, 255, 255, 0.1)';
                }, 2000);
            });
        }

        // Share functionality
        function shareArticle() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= addslashes($topic['title']) ?>',
                    text: '<?= addslashes(excerpt($topic['description'], 100)) ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        }

        <?php if (isLoggedIn()): ?>
        // Favorite functionality
        document.getElementById('favoriteBtn').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle_favorite'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const btn = this;
                    const icon = btn.querySelector('i');
                    
                    if (data.action === 'added') {
                        icon.className = 'fas fa-heart mr-2';
                        btn.className = 'px-4 py-2 rounded-lg border transition-colors bg-red-50 text-red-600 border-red-200';
                        btn.innerHTML = '<i class="fas fa-heart mr-2"></i>Remove from Favorites';
                    } else {
                        icon.className = 'far fa-heart mr-2';
                        btn.className = 'px-4 py-2 rounded-lg border transition-colors bg-gray-50 text-gray-600 border-gray-200';
                        btn.innerHTML = '<i class="far fa-heart mr-2"></i>Add to Favorites';
                    }
                }
            });
        });

        // Progress functionality
        const progressBtn = document.getElementById('progressBtn');
        const progressMenu = document.getElementById('progressMenu');
        
        progressBtn.addEventListener('click', function() {
            progressMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!progressBtn.contains(e.target) && !progressMenu.contains(e.target)) {
                progressMenu.classList.add('hidden');
            }
        });

        document.querySelectorAll('.progress-option').forEach(option => {
            option.addEventListener('click', function() {
                const status = this.dataset.status;
                const percentage = status === 'completed' ? 100 : (status === 'in_progress' ? 50 : 0);
                
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_progress&status=${status}&percentage=${percentage}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusText = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        progressBtn.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${statusText}`;
                        progressMenu.classList.add('hidden');
                        
                        // Update progress bar if it exists
                        const progressFill = document.querySelector('.progress-fill');
                        if (progressFill) {
                            progressFill.style.width = percentage + '%';
                        }
                    }
                });
            });
        });
        <?php endif; ?>

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Reading progress indicator
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            
            // You can add a progress bar at the top if needed
            // document.getElementById('reading-progress').style.width = scrolled + '%';
        });

        // Print styles
        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.copy-button').forEach(btn => btn.style.display = 'none');
        });

        window.addEventListener('afterprint', function() {
            document.querySelectorAll('.copy-button').forEach(btn => btn.style.display = 'block');
        });
    </script>

</body>
</html>
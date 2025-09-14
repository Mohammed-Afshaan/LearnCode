<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'dashboard.php';
    redirect('login.php');
}

$user = getCurrentUser();
$user_progress = getUserProgress($user['id']);

// Recent activities
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

// Favorite topics
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
    <title>User Dashboard - W3Clone</title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 14px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.08);
            padding: 2rem 1.5rem;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .dashboard-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .dashboard-section h2 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .recent-list {
            list-style: none;
            padding: 0;
        }
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-info h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 1.1rem;
        }
        .recent-info p {
            margin: 0;
            color: #666;
            font-size: 0.95rem;
        }
        .recent-date {
            color: #999;
            font-size: 0.85rem;
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
        @media (max-width: 768px) {
            .dashboard-header h1 { font-size: 2rem; }
            .stat-number { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Welcome, <?= htmlspecialchars($user['full_name']) ?>!</h1>
            <p>Track your learning progress, resume tutorials, and manage your account.</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $user_progress['completed'] ?? 0 ?></div>
                <div class="stat-label">Completed Lessons</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user_progress['in_progress'] ?? 0 ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user_progress['favorites'] ?? 0 ?></div>
                <div class="stat-label">Favorites</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user_progress['time_spent'] ?? 0 ?> min</div>
                <div class="stat-label">Time Spent</div>
            </div>
        </div>

        <!-- Progress by Language -->
        <?php if (!empty($user_progress['by_language'])): ?>
        <div class="dashboard-section">
            <h2><i class="fas fa-chart-pie"></i> Progress by Language</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($user_progress['by_language'] as $lang): ?>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold"><?= htmlspecialchars($lang['language']) ?></span>
                            <span><?= $lang['completed'] ?>/<?= $lang['total'] ?> (<?= $lang['percentage'] ?>%)</span>
                        </div>
                        <div class="progress-bar mb-2">
                            <div class="progress-fill" style="width: <?= $lang['percentage'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity -->
        <div class="dashboard-section">
            <h2><i class="fas fa-history"></i> Recent Activity</h2>
            <?php if (!empty($recent_activities)): ?>
                <ul class="recent-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <li class="recent-item">
                            <div class="recent-info">
                                <h4>
                                    <a href="topic_detail.php?slug=<?= urlencode($activity['slug']) ?>" class="text-purple-700 hover:underline">
                                        <?= htmlspecialchars($activity['title']) ?>
                                    </a>
                                </h4>
                                <p>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs"><?= htmlspecialchars($activity['language']) ?></span>
                                    <span class="ml-2 text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $activity['status'])) ?></span>
                                </p>
                            </div>
                            <div class="recent-date">
                                <?= date('M j, Y', strtotime($activity['last_accessed'])) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-600">No recent activity yet. Start learning a topic!</p>
            <?php endif; ?>
        </div>

        <!-- Favorites -->
        <div class="dashboard-section">
            <h2><i class="fas fa-star"></i> Favorite Lessons</h2>
            <?php if (!empty($favorites)): ?>
                <ul class="recent-list">
                    <?php foreach ($favorites as $fav): ?>
                        <li class="recent-item">
                            <div class="recent-info">
                                <h4>
                                    <a href="topic_detail.php?slug=<?= urlencode($fav['slug']) ?>" class="text-purple-700 hover:underline">
                                        <?= htmlspecialchars($fav['title']) ?>
                                    </a>
                                </h4>
                                <p>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs"><?= htmlspecialchars($fav['language']) ?></span>
                                </p>
                            </div>
                            <div class="recent-date">
                                <?= date('M j, Y', strtotime($fav['created_at'])) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-600">You haven't added any favorites yet.</p>
            <?php endif; ?>
        </div>

        <!-- Account Quick Links -->
        <div class="dashboard-section">
            <h2><i class="fas fa-user-cog"></i> Account</h2>
            <div class="flex flex-wrap gap-4">
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-user"></i> Edit Profile
                </a>
                <a href="topics.php" class="btn btn-secondary">
                    <i class="fas fa-book"></i> Browse Tutorials
                </a>
                <a href="logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
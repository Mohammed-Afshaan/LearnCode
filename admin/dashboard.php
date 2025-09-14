<?php

/**
 * Admin Dashboard
 * Main admin overview page with statistics and quick actions
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

// Get dashboard statistics
$conn = getDBConnection();

// Get statistics
$stats = [];

// Total topics
$result = $conn->query("SELECT COUNT(*) as count FROM topics WHERE is_published = 1");
$stats['total_topics'] = $result ? $result->fetch_assoc()['count'] : 0;

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$stats['total_users'] = $result ? $result->fetch_assoc()['count'] : 0;

// Total views
$result = $conn->query("SELECT SUM(view_count) as total FROM topics");
$row = $result ? $result->fetch_assoc() : null;
$stats['total_views'] = $row && $row['total'] ? $row['total'] : 0;

// Topics by language
$result = $conn->query("SELECT language, COUNT(*) as count FROM topics WHERE is_published = 1 GROUP BY language ORDER BY count DESC LIMIT 5");
$stats['topics_by_language'] = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['topics_by_language'][] = $row;
    }
}

// Recent topics
$result = $conn->query("SELECT id, title, language, created_at FROM topics WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5");
$stats['recent_topics'] = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['recent_topics'][] = $row;
    }
}

// Recent users
$result = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$stats['recent_users'] = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['recent_users'][] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="../assets/css/globals.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .admin-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .stat-card.users {
            border-left-color: #f093fb;
        }

        .stat-card.views {
            border-left-color: #ffecd2;
        }

        .stat-card.topics {
            border-left-color: #a8edea;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .admin-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .admin-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .action-btn i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .recent-list {
            list-style: none;
            padding: 0;
        }

        .recent-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .recent-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .recent-date {
            color: #999;
            font-size: 0.85rem;
        }

        .language-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .language-tag {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .two-column {
                grid-template-columns: 1fr;
            }

            .admin-header h1 {
                font-size: 2rem;
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    
    <?php include '../includes/admin_header.php'; ?>

    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card topics">
                <div class="stat-number"><?php echo number_format($stats['total_topics']); ?></div>
                <div class="stat-label">Published Topics</div>
            </div>
            <div class="stat-card users">
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card views">
                <div class="stat-number"><?php echo number_format($stats['total_views']); ?></div>
                <div class="stat-label">Total Views</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="quick-actions">
                <a href="add_topic.php" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Add New Topic
                </a>
                <a href="add_user.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    Add New User
                </a>
                <a href="manage_users.php" class="action-btn">
                    <i class="fas fa-users-cog"></i>
                    Manage Users
                </a>
                <a href="../pages/topics.php" class="action-btn">
                    <i class="fas fa-eye"></i>
                    View Site
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="two-column">
            <div class="admin-section">
                <h2><i class="fas fa-clock"></i> Recent Topics</h2>
                <?php if (!empty($stats['recent_topics'])): ?>
                    <ul class="recent-list">
                        <?php foreach ($stats['recent_topics'] as $topic): ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <h4><?php echo htmlspecialchars($topic['title']); ?></h4>
                                    <p><strong><?php echo htmlspecialchars($topic['language']); ?></strong></p>
                                </div>
                                <div class="recent-date">
                                    <?php echo date('M j, Y', strtotime($topic['created_at'])); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 20px;">No topics found.</p>
                <?php endif; ?>
            </div>

            <div class="admin-section">
                <h2><i class="fas fa-user-friends"></i> Recent Users</h2>
                <?php if (!empty($stats['recent_users'])): ?>
                    <ul class="recent-list">
                        <?php foreach ($stats['recent_users'] as $user): ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="recent-date">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 20px;">No users found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Topics by Language -->
        <?php if (!empty($stats['topics_by_language'])): ?>
            <div class="admin-section">
                <h2><i class="fas fa-chart-pie"></i> Topics by Language</h2>
                <div class="language-stats">
                    <?php foreach ($stats['topics_by_language'] as $lang): ?>
                        <div class="language-tag">
                            <strong><?php echo htmlspecialchars($lang['language']); ?>:</strong>
                            <?php echo $lang['count']; ?> topics
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
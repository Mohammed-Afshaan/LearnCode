<?php
/**
 * Manage Users
 * Admin interface for managing user accounts
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

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token.');
    } else {
        $action = $_POST['bulk_action'];
        $selected_users = $_POST['selected_users'] ?? [];
        $count = 0;
        
        if (empty($selected_users)) {
            setFlashMessage('warning', 'No users selected.');
        } else {
            $conn = getDBConnection();
            
            foreach ($selected_users as $user_id) {
                $user_id = (int)$user_id;
                
                // Prevent admin from affecting their own account in bulk
                if ($user_id == $_SESSION['user_id']) {
                    continue;
                }
                
                switch ($action) {
                    case 'activate':
                        $query = "UPDATE users SET is_active = 1 WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        if ($stmt->execute()) $count++;
                        $stmt->close();
                        break;
                        
                    case 'deactivate':
                        $query = "UPDATE users SET is_active = 0 WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        if ($stmt->execute()) $count++;
                        $stmt->close();
                        break;
                        
                    case 'verify':
                        $query = "UPDATE users SET email_verified = 1 WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        if ($stmt->execute()) $count++;
                        $stmt->close();
                        break;
                        
                    case 'delete':
                        $query = "DELETE FROM users WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        if ($stmt->execute()) $count++;
                        $stmt->close();
                        break;
                }
            }
            
            $conn->close();
            
            if ($count > 0) {
                $action_text = [
                    'activate' => 'activated',
                    'deactivate' => 'deactivated', 
                    'verify' => 'verified',
                    'delete' => 'deleted'
                ][$action];
                setFlashMessage('success', "$count user(s) $action_text successfully.");
            } else {
                setFlashMessage('warning', 'No users were affected.');
            }
        }
    }
    
    redirect('manage_users.php');
}

// Get filters and pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$search = trim($_GET['search'] ?? '');
$filter_role = $_GET['role'] ?? '';
$filter_status = $_GET['status'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$sort_order = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

// Build query conditions
$where_conditions = ['1=1'];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = '(username LIKE ? OR email LIKE ? OR full_name LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if ($filter_role === 'admin') {
    $where_conditions[] = 'is_admin = 1';
} elseif ($filter_role === 'user') {
    $where_conditions[] = 'is_admin = 0';
}

if ($filter_status === 'active') {
    $where_conditions[] = 'is_active = 1';
} elseif ($filter_status === 'inactive') {
    $where_conditions[] = 'is_active = 0';
}

$where_clause = implode(' AND ', $where_conditions);

// Validate sort column
$allowed_sort = ['id', 'username', 'email', 'full_name', 'is_admin', 'is_active', 'created_at', 'last_login'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'created_at';
}

// Get total count
$conn = getDBConnection();
$count_query = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

// Calculate pagination
$total_pages = ceil($total_users / $per_page);
$offset = ($page - 1) * $per_page;

// Get users
$query = "SELECT id, username, email, full_name, is_admin, is_active, email_verified, 
                 last_login, created_at,
                 (SELECT COUNT(*) FROM topics WHERE author_id = users.id) as topic_count
          FROM users 
          WHERE $where_clause 
          ORDER BY $sort_by $sort_order 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin - <?php echo SITE_NAME; ?></title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .controls-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .bulk-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .users-table th a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .users-table th a:hover {
            color: #007bff;
        }
        
        .users-table tr:hover {
            background: #f8f9fa;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .user-details h4 {
            margin: 0;
            font-size: 1rem;
            color: #333;
        }
        
        .user-details p {
            margin: 2px 0 0 0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-admin {
            background: #28a745;
            color: white;
        }
        
        .badge-user {
            background: #6c757d;
            color: white;
        }
        
        .badge-active {
            background: #28a745;
            color: white;
        }
        
        .badge-inactive {
            background: #dc3545;
            color: white;
        }
        
        .badge-verified {
            background: #17a2b8;
            color: white;
        }
        
        .badge-unverified {
            background: #ffc107;
            color: #212529;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #007bff;
            color: #007bff;
        }
        
        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            padding: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            color: #007bff;
        }
        
        .pagination a:hover {
            background: #e9ecef;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .stats-bar {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: #d1edff;
            border-color: #007bff;
            color: #004085;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .checkbox-header,
        .checkbox-cell {
            width: 50px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .filters {
                grid-template-columns: 1fr;
            }
            
            .bulk-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .users-table {
                font-size: 0.85rem;
            }
            
            .users-table th,
            .users-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-users"></i> Manage Users</h1>
            </div>
            <div class="header-actions">
                <a href="add_user.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessages(); ?>
        
        <!-- Controls Section -->
        <div class="controls-section">
            <form method="GET" action="">
                <div class="filters">
                    <div class="filter-group">
                        <label for="search">Search Users</label>
                        <input type="text" id="search" name="search" placeholder="Username, email, or name..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $filter_role === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
            
            <!-- Stats -->
            <div class="stats-bar">
                <span><strong><?php echo number_format($total_users); ?></strong> total users</span>
                <span>Showing <?php echo number_format($offset + 1); ?>-<?php echo number_format(min($offset + $per_page, $total_users)); ?></span>
                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            </div>
            
            <!-- Bulk Actions -->
            <form method="POST" action="" id="bulkForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="bulk-actions">
                    <label>
                        <input type="checkbox" id="selectAll"> Select All
                    </label>
                    <select name="bulk_action" required>
                        <option value="">Choose Action...</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="verify">Mark as Verified</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn btn-warning" 
                            onclick="return confirm('Are you sure you want to perform this action on selected users?')">
                        <i class="fas fa-play"></i> Apply
                    </button>
                </div>
        </div>
        
        <!-- Users Table -->
        <div class="table-container">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No users found</h3>
                    <p>No users match your current filters. Try adjusting your search criteria.</p>
                    <a href="manage_users.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th class="checkbox-header">
                                    <input type="checkbox" id="selectAllHeader">
                                </th>
                                <th>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'username', 'order' => $sort_by === 'username' && $sort_order === 'asc' ? 'desc' : 'asc'])); ?>">
                                        User
                                        <?php if ($sort_by === 'username'): ?>
                                            <i class="fas fa-sort-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Topics</th>
                                <th>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => $sort_by === 'created_at' && $sort_order === 'asc' ? 'desc' : 'asc'])); ?>">
                                        Joined
                                        <?php if ($sort_by === 'created_at'): ?>
                                            <i class="fas fa-sort-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'last_login', 'order' => $sort_by === 'last_login' && $sort_order === 'asc' ? 'desc' : 'asc'])); ?>">
                                        Last Login
                                        <?php if ($sort_by === 'last_login'): ?>
                                            <i class="fas fa-sort-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="checkbox-cell">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <br>
                                        <span class="badge <?php echo $user['email_verified'] ? 'badge-verified' : 'badge-unverified'; ?>">
                                            <?php echo $user['email_verified'] ? 'Verified' : 'Unverified'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo number_format($user['topic_count']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <?php echo timeAgo($user['last_login']); ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Delete User"
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        </form>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">&laquo; First</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&lsaquo; Prev</a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &rsaquo;</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">Last &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Select all functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllMain = document.getElementById('selectAll');
            const selectAllHeader = document.getElementById('selectAllHeader');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            
            function updateSelectAll() {
                const checkedBoxes = document.querySelectorAll('.user-checkbox:checked').length;
                const totalBoxes = userCheckboxes.length;
                
                if (selectAllMain) {
                    selectAllMain.checked = checkedBoxes === totalBoxes;
                    selectAllMain.indeterminate = checkedBoxes > 0 && checkedBoxes < totalBoxes;
                }
                
                if (selectAllHeader) {
                    selectAllHeader.checked = checkedBoxes === totalBoxes;
                    selectAllHeader.indeterminate = checkedBoxes > 0 && checkedBoxes < totalBoxes;
                }
            }
            
            [selectAllMain, selectAllHeader].forEach(selectAll => {
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        userCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateSelectAll();
                    });
                }
            });
            
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAll);
            });
            
            // Initial state
            updateSelectAll();
            
            // Form submission validation
            document.getElementById('bulkForm').addEventListener('submit', function(e) {
                const selectedBoxes = document.querySelectorAll('.user-checkbox:checked');
                const action = this.querySelector('select[name="bulk_action"]').value;
                
                if (!action) {
                    e.preventDefault();
                    alert('Please select an action.');
                    return;
                }
                
                if (selectedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one user.');
                    return;
                }
                
                if (action === 'delete') {
                    if (!confirm(`Are you sure you want to delete ${selectedBoxes.length} user(s)? This action cannot be undone.`)) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>
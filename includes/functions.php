<?php
/**
 * W3Clone Helper Functions
 * Common functions used throughout the application
 */

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Database Functions
 */

/**
 * Execute a prepared statement safely
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types (e.g., 'ssi' for string, string, integer)
 * @param array $params Parameters to bind
 * @return mysqli_result|bool Query result
 */
function executeQuery($query, $types = '', $params = []) {
    // Use getDBConnection from config.php
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $conn->close();
        return false;
    }

    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }

    $result = $stmt->execute();

    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $queryResult = $stmt->get_result();
    $stmt->close();
    $conn->close();

    return $queryResult;
}

/**
 * Security Functions
 */

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 * @param string $password Plain text password
 * @param string $hash Stored hash
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Authentication Functions
 */

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return (function_exists('isLoggedIn') && isLoggedIn()) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user info
 * @return array|null User information array or null
 */
function getCurrentUser() {
    if (!(function_exists('isLoggedIn') && isLoggedIn())) {
        return null;
    }

    $query = "SELECT id, username, email, full_name, profile_image, is_admin, created_at FROM users WHERE id = ?";
    $result = executeQuery($query, 'i', [$_SESSION['user_id']]);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Login user
 * @param int $user_id User ID
 * @param array $user_data User data array
 */
function loginUser($user_id, $user_data) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['full_name'] = $user_data['full_name'];
    $_SESSION['is_admin'] = $user_data['is_admin'] ?? 0;
    $_SESSION['profile_image'] = $user_data['profile_image'] ?? null;
    $_SESSION['login_time'] = time();

    // Update last login time
    $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
    executeQuery($update_query, 'i', [$user_id]);
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
}

/**
 * URL and Redirect Functions
 */

/**
 * Get base URL for the application
 * @return string Base URL
 */
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);

    return $protocol . $host . ($path === '/' ? '' : $path) . '/';
}

/**
 * Get current page URL
 * @return string Current page URL
 */
function getCurrentURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Validation Functions
 */

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Result array with 'valid' bool and 'message' string
 */
function validatePassword($password) {
    $result = ['valid' => true, 'message' => ''];

    if (strlen($password) < 8) {
        $result['valid'] = false;
        $result['message'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'Password must contain at least one number';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'Password must contain at least one special character';
    }

    return $result;
}

/**
 * Utility Functions
 */

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Time ago function
 * @param string $date Date string
 * @return string Human readable time difference
 */
function timeAgo($date) {
    $time = time() - strtotime($date);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';

    return floor($time/31536000) . ' years ago';
}

/**
 * Generate excerpt from text
 * @param string $text Full text
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string Excerpt
 */
function excerpt($text, $length = 150, $suffix = '...') {
    $text = strip_tags($text);
    if (strlen($text) <= $length) {
        return $text;
    }

    $text = substr($text, 0, $length);
    $lastSpace = strrpos($text, ' ');
    if ($lastSpace !== false) {
        $text = substr($text, 0, $lastSpace);
    }

    return $text . $suffix;
}

/**
 * Slugify string for URLs
 * @param string $string String to slugify
 * @return string Slugified string
 */
function slugify($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

/**
 * File Upload Functions
 */

/**
 * Upload file safely
 * @param array $file $_FILES array element
 * @param string $upload_dir Upload directory
 * @param array $allowed_types Allowed file types
 * @param int $max_size Maximum file size in bytes
 * @return array Result array with 'success', 'filename', and 'message'
 */
function uploadFile($file, $upload_dir = 'uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    $result = ['success' => false, 'filename' => '', 'message' => ''];

    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload failed';
        return $result;
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $result['message'] = 'File size too large';
        return $result;
    }

    // Get file extension
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);

    // Check file type
    if (!in_array($extension, $allowed_types)) {
        $result['message'] = 'Invalid file type';
        return $result;
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $file_path = $upload_dir . $filename;

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['message'] = 'File uploaded successfully';
    } else {
        $result['message'] = 'Failed to save file';
    }

    return $result;
}

/**
 * Content Functions
 */

/**
 * Get topics with pagination
 * @param int $page Page number
 * @param int $per_page Items per page
 * @param array $filters Filter conditions
 * @return array Topics and pagination info
 */
function getTopics($page = 1, $per_page = 12, $filters = []) {
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_conditions = ['1=1'];
    $params = [];
    $types = '';

    if (!empty($filters['language'])) {
        $where_conditions[] = 'language = ?';
        $params[] = $filters['language'];
        $types .= 's';
    }

    if (!empty($filters['difficulty'])) {
        $where_conditions[] = 'difficulty = ?';
        $params[] = $filters['difficulty'];
        $types .= 's';
    }

    if (!empty($filters['search'])) {
        $where_conditions[] = '(title LIKE ? OR description LIKE ? OR content LIKE ?)';
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'sss';
    }

    if (!empty($filters['featured'])) {
        $where_conditions[] = 'is_featured = 1';
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM topics WHERE $where_clause";
    $count_result = executeQuery($count_query, $types, $params);
    $total = $count_result ? $count_result->fetch_assoc()['total'] : 0;

    // Get topics
    $query = "SELECT t.*, u.username as author_name 
              FROM topics t 
              LEFT JOIN users u ON t.author_id = u.id 
              WHERE $where_clause 
              ORDER BY t.created_at DESC 
              LIMIT ? OFFSET ?";

    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $result = executeQuery($query, $types, $params);
    $topics = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $topics[] = $row;
        }
    }

    return [
        'topics' => $topics,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'total_pages' => $per_page > 0 ? ceil($total / $per_page) : 1
        ]
    ];
}

/**
 * Error Handling
 */

/**
 * Log error message
 * @param string $message Error message
 * @param array $context Additional context
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";

    if (!empty($context)) {
        $log_message .= ' Context: ' . json_encode($context);
    }

    error_log($log_message);
}

/**
 * Flash Messages
 */

/**
 * Set a flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 * @return array Flash messages
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Display flash messages HTML
 * @return string HTML for flash messages
 */
function displayFlashMessages() {
    $messages = getFlashMessages();
    $html = '';

    foreach ($messages as $message) {
        $type_class = 'admin-alert-' . $message['type'];
        $icon = [
            'success' => 'fas fa-check-circle',
            'error' => 'fas fa-times-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle'
        ][$message['type']] ?? 'fas fa-info-circle';

        $html .= "<div class='admin-alert $type_class'>";
        $html .= "<i class='$icon'></i>";
        $html .= "<span>" . htmlspecialchars($message['message']) . "</span>";
        $html .= "</div>";
    }

    return $html;
}

/**
 * API Response Functions
 */

/**
 * Send JSON response
 * @param array $data Response data
 * @param int $status_code HTTP status code
 */
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Send success JSON response
 * @param mixed $data Response data
 * @param string $message Success message
 */
function jsonSuccess($data = null, $message = 'Success') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Send error JSON response
 * @param string $message Error message
 * @param int $code Error code
 * @param int $status_code HTTP status code
 */
function jsonError($message = 'Error occurred', $code = 1, $status_code = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'error_code' => $code
    ], $status_code);
}

/**
 * Configuration Functions
 */

/**
 * Get site setting
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getSiteSetting($key, $default = null) {
    static $settings_cache = [];

    if (!isset($settings_cache[$key])) {
        $query = "SELECT setting_value, setting_type FROM site_settings WHERE setting_key = ?";
        $result = executeQuery($query, 's', [$key]);

        if ($result && $result->num_rows > 0) {
            $setting = $result->fetch_assoc();
            $value = $setting['setting_value'];

            // Convert based on type
            switch ($setting['setting_type']) {
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'number':
                    $value = is_numeric($value) ? (float) $value : $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }

            $settings_cache[$key] = $value;
        } else {
            $settings_cache[$key] = $default;
        }
    }

    return $settings_cache[$key];
}

/**
 * Update site setting
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @param string $type Setting type
 * @return bool Success status
 */
function updateSiteSetting($key, $value, $type = 'string') {
    // Convert value based on type
    switch ($type) {
        case 'boolean':
            $value = $value ? '1' : '0';
            break;
        case 'json':
            $value = json_encode($value);
            break;
        default:
            $value = (string) $value;
    }

    $query = "INSERT INTO site_settings (setting_key, setting_value, setting_type) 
              VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              setting_value = VALUES(setting_value), 
              setting_type = VALUES(setting_type)";

    $result = executeQuery($query, 'sss', [$key, $value, $type]);
    return $result !== false;
}

/**
 * Statistics Functions
 */

/**
 * Get dashboard statistics
 * @return array Statistics data
 */
function getDashboardStats() {
    $stats = [];

    // Total topics
    $result = executeQuery("SELECT COUNT(*) as count FROM topics WHERE is_published = 1");
    $stats['total_topics'] = $result ? $result->fetch_assoc()['count'] : 0;

    // Total users
    $result = executeQuery("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['total_users'] = $result ? $result->fetch_assoc()['count'] : 0;

    // Total views
    $result = executeQuery("SELECT SUM(view_count) as total FROM topics");
    $stats['total_views'] = $result ? ($result->fetch_assoc()['total'] ?: 0) : 0;

    // Topics by language
    $result = executeQuery("SELECT language, COUNT(*) as count FROM topics WHERE is_published = 1 GROUP BY language ORDER BY count DESC");
    $stats['topics_by_language'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['topics_by_language'][] = $row;
        }
    }

    // Recent topics
    $result = executeQuery("SELECT id, title, language, created_at FROM topics WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5");
    $stats['recent_topics'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['recent_topics'][] = $row;
        }
    }

    return $stats;
}

/**
 * Get user progress statistics
 * @param int $user_id User ID
 * @return array User progress data
 */
function getUserProgress($user_id) {
    $progress = [];

    // Total completed topics
    $result = executeQuery("SELECT COUNT(*) as count FROM user_progress WHERE user_id = ? AND status = 'completed'", 'i', [$user_id]);
    $progress['completed'] = $result ? $result->fetch_assoc()['count'] : 0;

    // Total in progress
    $result = executeQuery("SELECT COUNT(*) as count FROM user_progress WHERE user_id = ? AND status = 'in_progress'", 'i', [$user_id]);
    $progress['in_progress'] = $result ? $result->fetch_assoc()['count'] : 0;

    // Total favorites
    $result = executeQuery("SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?", 'i', [$user_id]);
    $progress['favorites'] = $result ? $result->fetch_assoc()['count'] : 0;

    // Total time spent (in minutes)
    $result = executeQuery("SELECT SUM(time_spent) as total FROM user_progress WHERE user_id = ?", 'i', [$user_id]);
    $progress['time_spent'] = $result ? round(($result->fetch_assoc()['total'] ?: 0) / 60, 1) : 0;

    // Progress by language
    $result = executeQuery("
        SELECT t.language, 
               COUNT(*) as total, 
               SUM(CASE WHEN up.status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM user_progress up 
        JOIN topics t ON up.topic_id = t.id 
        WHERE up.user_id = ? 
        GROUP BY t.language
    ", 'i', [$user_id]);

    $progress['by_language'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['percentage'] = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1) : 0;
            $progress['by_language'][] = $row;
        }
    }

    return $progress;
}

/**
 * Content Management Functions
 */

/**
 * Create topic
 * @param array $data Topic data
 * @return int|bool Topic ID on success, false on failure
 */
function createTopic($data) {
    $required_fields = ['title', 'description', 'language', 'author_id'];

    // Validate required fields
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }

    // Generate slug
    $data['slug'] = slugify($data['title']);

    // Check if slug exists
    $count = 1;
    $original_slug = $data['slug'];
    while (true) {
        $result = executeQuery("SELECT id FROM topics WHERE slug = ?", 's', [$data['slug']]);
        if (!$result || $result->num_rows == 0) {
            break;
        }
        $data['slug'] = $original_slug . '-' . $count;
        $count++;
    }

    $query = "INSERT INTO topics (title, slug, description, content, code_snippet, language, 
              category_id, difficulty, tags, is_featured, is_published, author_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $data['title'],
        $data['slug'],
        $data['description'],
        $data['content'] ?? '',
        $data['code_snippet'] ?? '',
        $data['language'],
        $data['category_id'] ?? null,
        $data['difficulty'] ?? 'Beginner',
        $data['tags'] ?? '',
        $data['is_featured'] ?? 0,
        $data['is_published'] ?? 1,
        $data['author_id']
    ];

    $result = executeQuery($query, 'ssssssissisi', $params);

    if ($result) {
        $conn = getDBConnection();
        $topic_id = $conn->insert_id;
        $conn->close();
        return $topic_id;
    }

    return false;
}

/**
 * Update topic
 * @param int $topic_id Topic ID
 * @param array $data Updated data
 * @return bool Success status
 */
function updateTopic($topic_id, $data) {
    $allowed_fields = ['title', 'description', 'content', 'code_snippet', 'language', 
                       'category_id', 'difficulty', 'tags', 'is_featured', 'is_published'];

    $update_fields = [];
    $params = [];
    $types = '';

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $update_fields[] = "$field = ?";
            $params[] = $data[$field];
            $types .= is_int($data[$field]) ? 'i' : 's';
        }
    }

    if (empty($update_fields)) {
        return false;
    }

    // Update slug if title changed
    if (isset($data['title'])) {
        $new_slug = slugify($data['title']);

        // Check if slug exists (excluding current topic)
        $count = 1;
        $original_slug = $new_slug;
        while (true) {
            $result = executeQuery("SELECT id FROM topics WHERE slug = ? AND id != ?", 'si', [$new_slug, $topic_id]);
            if (!$result || $result->num_rows == 0) {
                break;
            }
            $new_slug = $original_slug . '-' . $count;
            $count++;
        }

        $update_fields[] = "slug = ?";
        $params[] = $new_slug;
        $types .= 's';
    }

    $params[] = $topic_id;
    $types .= 'i';

    $query = "UPDATE topics SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $result = executeQuery($query, $types, $params);

    return $result !== false;
}

/**
 * Delete topic
 * @param int $topic_id Topic ID
 * @return bool Success status
 */
function deleteTopic($topic_id) {
    $query = "DELETE FROM topics WHERE id = ?";
    $result = executeQuery($query, 'i', [$topic_id]);
    return $result !== false;
}

/**
 * Search Functions
 */

/**
 * Search topics
 * @param string $query Search query
 * @param int $limit Result limit
 * @return array Search results
 */
function searchTopics($query, $limit = 20) {
    $search_term = '%' . $query . '%';

    $sql = "SELECT id, title, description, language, slug, 
                   MATCH(title, description, content, tags) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM topics 
            WHERE is_published = 1 
            AND (title LIKE ? OR description LIKE ? OR content LIKE ? OR tags LIKE ? 
                 OR MATCH(title, description, content, tags) AGAINST(? IN NATURAL LANGUAGE MODE))
            ORDER BY relevance DESC, created_at DESC 
            LIMIT ?";

    $params = [$query, $search_term, $search_term, $search_term, $search_term, $query, $limit];
    $result = executeQuery($sql, 'ssssssi', $params);

    $results = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }

    return $results;
}

/**
 * Email Functions
 */

/**
 * Send email (basic implementation - replace with your preferred email service)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $headers Additional headers
 * @return bool Success status
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $default_headers = [
        'From: ' . getSiteSetting('admin_email', 'noreply@w3clone.com'),
        'Reply-To: ' . getSiteSetting('admin_email', 'noreply@w3clone.com'),
        'Content-Type: text/html; charset=UTF-8',
        'MIME-Version: 1.0'
    ];

    $all_headers = array_merge($default_headers, $headers);
    $header_string = implode("\r\n", $all_headers);

    return mail($to, $subject, $message, $header_string);
}

/**
 * Send welcome email to new user
 * @param string $email User email
 * @param string $username Username
 * @return bool Success status
 */
function sendWelcomeEmail($email, $username) {
    $subject = 'Welcome to ' . getSiteSetting('site_name', 'W3Clone');

    $message = "
    <html>
    <head>
        <title>Welcome to W3Clone</title>
    </head>
    <body>
        <h2>Welcome to W3Clone, {$username}!</h2>
        <p>Thank you for joining our learning community. You now have access to:</p>
        <ul>
            <li>100+ Programming Tutorials</li>
            <li>Interactive Code Examples</li>
            <li>Progress Tracking</li>
            <li>Community Support</li>
        </ul>
        <p><a href='" . getBaseURL() . "pages/topics.php'>Start Learning Now</a></p>
        <p>Happy coding!<br>The W3Clone Team</p>
    </body>
    </html>
    ";

    return sendEmail($email, $subject, $message);
}

/**
 * Cache Functions (Simple file-based caching)
 */

/**
 * Get cached data
 * @param string $key Cache key
 * @param int $ttl Time to live in seconds
 * @return mixed Cached data or false if expired/not found
 */
function getCache($key, $ttl = 3600) {
    $cache_dir = 'cache/';
    $cache_file = $cache_dir . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return false;
    }

    $cache_data = unserialize(file_get_contents($cache_file));

    if (time() - $cache_data['timestamp'] > $ttl) {
        unlink($cache_file);
        return false;
    }

    return $cache_data['data'];
}

/**
 * Set cache data
 * @param string $key Cache key
 * @param mixed $data Data to cache
 * @return bool Success status
 */
function setCache($key, $data) {
    $cache_dir = 'cache/';

    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . md5($key) . '.cache';
    $cache_data = [
        'timestamp' => time(),
        'data' => $data
    ];

    return file_put_contents($cache_file, serialize($cache_data)) !== false;
}

/**
 * Clear cache
 * @param string $key Specific cache key or null for all
 * @return bool Success status
 */
function clearCache($key = null) {
    $cache_dir = 'cache/';

    if (!is_dir($cache_dir)) {
        return true;
    }

    if ($key) {
        $cache_file = $cache_dir . md5($key) . '.cache';
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        return true;
    } else {
        // Clear all cache files
        $files = glob($cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}

/**
 * Rate Limiting Functions
 */

/**
 * Check rate limit
 * @param string $identifier Unique identifier (IP, user ID, etc.)
 * @param int $limit Maximum attempts
 * @param int $window Time window in seconds
 * @return bool True if within limit
 */
function checkRateLimit($identifier, $limit = 5, $window = 300) {
    $key = 'rate_limit_' . $identifier;
    $cache_data = getCache($key, $window);

    if ($cache_data === false) {
        setCache($key, 1);
        return true;
    }

    if ($cache_data >= $limit) {
        return false;
    }

    setCache($key, $cache_data + 1);
    return true;
}

/**
 * Debug Functions
 */

/**
 * Debug dump (only in development)
 * @param mixed $data Data to dump
 * @param bool $die Whether to stop execution
 */
function dd($data, $die = true) {
    if (defined('DEBUG') && DEBUG) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';

        if ($die) {
            die();
        }
    }
}

/**
 * Log debug information
 * @param mixed $data Data to log
 * @param string $label Optional label
 */
function debug_log($data, $label = '') {
    if (defined('DEBUG') && DEBUG) {
        $message = $label ? "[$label] " : '';
        $message .= is_string($data) ? $data : print_r($data, true);
        error_log($message);
    }
}

?>
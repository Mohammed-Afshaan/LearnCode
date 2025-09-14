-- W3Clone Database Schema
-- Create database first: CREATE DATABASE w3clone;
-- Then use: USE w3clone;

-- Drop tables if they exist (for fresh installation)
DROP TABLE IF EXISTS topic_views;
DROP TABLE IF EXISTS user_favorites;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS topics;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- ========================================
-- USERS TABLE
-- ========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- CATEGORIES TABLE
-- ========================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#6366f1',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active)
);

-- ========================================
-- TOPICS TABLE (Main content)
-- ========================================
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT,
    code_snippet LONGTEXT,
    language VARCHAR(50) NOT NULL,
    category_id INT DEFAULT NULL,
    difficulty ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    tags VARCHAR(500) DEFAULT NULL,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description VARCHAR(300) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    INDEX idx_language (language),
    INDEX idx_difficulty (difficulty),
    INDEX idx_slug (slug),
    INDEX idx_is_featured (is_featured),
    INDEX idx_is_published (is_published),
    INDEX idx_created_at (created_at),
    INDEX idx_view_count (view_count),
    INDEX idx_author_id (author_id),
    INDEX idx_category_id (category_id),
    
    FULLTEXT idx_search (title, description, content, tags)
);

-- ========================================
-- USER PROGRESS TABLE
-- ========================================
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress_percentage INT DEFAULT 0,
    time_spent INT DEFAULT 0, -- in seconds
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_topic (user_id, topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_topic_id (topic_id),
    INDEX idx_status (status),
    INDEX idx_last_accessed (last_accessed)
);

-- ========================================
-- USER FAVORITES TABLE
-- ========================================
CREATE TABLE user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_favorite (user_id, topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_topic_id (topic_id),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- COMMENTS TABLE
-- ========================================
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    comment TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    like_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    
    INDEX idx_topic_id (topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_approved (is_approved),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- TOPIC VIEWS TABLE (Analytics)
-- ========================================
CREATE TABLE topic_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_topic_id (topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_viewed_at (viewed_at)
);

-- ========================================
-- ADDITIONAL USEFUL TABLES
-- ========================================

-- Settings/Configuration table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

-- Contact form submissions
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    replied_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Newsletter subscribers
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    verification_token VARCHAR(255) DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_is_active (is_active),
    INDEX idx_subscribed_at (subscribed_at)
);

-- ========================================
-- CREATE TRIGGERS FOR AUTOMATIC UPDATES
-- ========================================

-- Update topic view count when a view is recorded
DELIMITER //
CREATE TRIGGER update_topic_view_count
    AFTER INSERT ON topic_views
    FOR EACH ROW
BEGIN
    UPDATE topics 
    SET view_count = (
        SELECT COUNT(*) FROM topic_views WHERE topic_id = NEW.topic_id
    ) 
    WHERE id = NEW.topic_id;
END//
DELIMITER ;

-- Update user last_login when they log in
DELIMITER //
CREATE TRIGGER update_user_last_login
    BEFORE UPDATE ON users
    FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login THEN
        SET NEW.updated_at = CURRENT_TIMESTAMP;
    END IF;
END//
DELIMITER ;
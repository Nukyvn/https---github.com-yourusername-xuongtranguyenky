-- Tạo database
CREATE DATABASE IF NOT EXISTS social_media_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE social_media_manager;

-- Bảng users - Quản lý người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng fanpages - Quản lý fanpage Facebook
CREATE TABLE fanpages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    page_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    followers_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_page (user_id, page_id)
);

-- Bảng zalo_accounts - Quản lý Zalo OA
CREATE TABLE zalo_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    oa_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    followers_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_oa (user_id, oa_id)
);

-- Bảng posts - Quản lý bài đăng
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    images JSON DEFAULT NULL,
    fanpage_id INT DEFAULT NULL,
    zalo_id INT DEFAULT NULL,
    platform ENUM('facebook', 'zalo', 'both') NOT NULL,
    status ENUM('draft', 'scheduled', 'published', 'failed') DEFAULT 'draft',
    scheduled_time TIMESTAMP NULL,
    published_time TIMESTAMP NULL,
    facebook_post_id VARCHAR(255) DEFAULT NULL,
    zalo_post_id VARCHAR(255) DEFAULT NULL,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fanpage_id) REFERENCES fanpages(id) ON DELETE SET NULL,
    FOREIGN KEY (zalo_id) REFERENCES zalo_accounts(id) ON DELETE SET NULL
);

-- Bảng post_analytics - Thống kê bài đăng
CREATE TABLE post_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    platform ENUM('facebook', 'zalo') NOT NULL,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    reach_count INT DEFAULT 0,
    impressions_count INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_platform_time (post_id, platform, recorded_at)
);

-- Bảng schedules - Lịch đăng bài
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    images JSON DEFAULT NULL,
    fanpage_ids JSON DEFAULT NULL,
    zalo_ids JSON DEFAULT NULL,
    schedule_type ENUM('once', 'daily', 'weekly', 'monthly') DEFAULT 'once',
    schedule_time TIME NOT NULL,
    schedule_days JSON DEFAULT NULL,
    schedule_date DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng notifications - Thông báo
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng api_logs - Log API calls
CREATE TABLE api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform ENUM('facebook', 'zalo') NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    method VARCHAR(10) NOT NULL,
    request_data JSON DEFAULT NULL,
    response_data JSON DEFAULT NULL,
    status_code INT NOT NULL,
    response_time INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng settings - Cài đặt hệ thống
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    setting_key VARCHAR(255) NOT NULL,
    setting_value TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_key)
);

-- Tạo indexes để tối ưu performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_fanpages_user_id ON fanpages(user_id);
CREATE INDEX idx_fanpages_status ON fanpages(status);
CREATE INDEX idx_zalo_accounts_user_id ON zalo_accounts(user_id);
CREATE INDEX idx_zalo_accounts_status ON zalo_accounts(status);
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_status ON posts(status);
CREATE INDEX idx_posts_scheduled_time ON posts(scheduled_time);
CREATE INDEX idx_posts_platform ON posts(platform);
CREATE INDEX idx_schedules_user_id ON schedules(user_id);
CREATE INDEX idx_schedules_next_run ON schedules(next_run);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);

-- Insert admin user mặc định
INSERT INTO users (name, email, password, role, status) VALUES 
('Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert settings mặc định
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Social Media Manager'),
('site_description', 'Quản lý fanpage và Zalo tập trung'),
('facebook_app_id', ''),
('facebook_app_secret', ''),
('zalo_app_id', ''),
('zalo_app_secret', ''),
('max_upload_size', '10485760'),
('allowed_image_types', 'jpg,jpeg,png,gif'),
('timezone', 'Asia/Ho_Chi_Minh'),
('date_format', 'd/m/Y'),
('time_format', 'H:i:s');
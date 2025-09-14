<?php
require_once 'config/database.php';

// Hàm đăng nhập
function login($email, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    
    return false;
}

// Hàm đăng ký
function register($name, $email, $password) {
    global $db;
    
    // Kiểm tra email đã tồn tại
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email đã tồn tại
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$name, $email, $hashed_password]);
}

// Lấy thông tin user theo ID
function getUserById($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Lấy danh sách fanpage của user
function getFanpagesByUserId($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM fanpages WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Lấy danh sách Zalo OA của user
function getZaloAccountsByUserId($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM zalo_accounts WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Thêm fanpage mới
function addFanpage($user_id, $page_id, $page_name, $access_token, $avatar) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO fanpages (user_id, page_id, name, access_token, avatar, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$user_id, $page_id, $page_name, $access_token, $avatar]);
}

// Thêm Zalo OA mới
function addZaloAccount($user_id, $oa_id, $oa_name, $access_token, $avatar) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO zalo_accounts (user_id, oa_id, name, access_token, avatar, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$user_id, $oa_id, $oa_name, $access_token, $avatar]);
}

// Lấy danh sách bài đăng
function getPostsByUserId($user_id, $limit = 10) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT p.*, f.name as fanpage_name, z.name as zalo_name 
        FROM posts p 
        LEFT JOIN fanpages f ON p.fanpage_id = f.id 
        LEFT JOIN zalo_accounts z ON p.zalo_id = z.id 
        WHERE p.user_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Tạo bài đăng mới
function createPost($user_id, $content, $fanpage_id = null, $zalo_id = null, $scheduled_time = null) {
    global $db;
    
    $stmt = $db->prepare("
        INSERT INTO posts (user_id, content, fanpage_id, zalo_id, scheduled_time, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$user_id, $content, $fanpage_id, $zalo_id, $scheduled_time]);
}

// Hàm gọi Facebook API
function callFacebookAPI($url, $access_token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '&access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    return false;
}

// Hàm gọi Zalo API
function callZaloAPI($url, $access_token, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    return false;
}

// Hàm upload ảnh
function uploadImage($file, $upload_dir = 'uploads/') {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return false;
}

// Hàm format thời gian
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'vừa xong';
    if ($time < 3600) return floor($time/60) . ' phút trước';
    if ($time < 86400) return floor($time/3600) . ' giờ trước';
    if ($time < 2592000) return floor($time/86400) . ' ngày trước';
    if ($time < 31536000) return floor($time/2592000) . ' tháng trước';
    
    return floor($time/31536000) . ' năm trước';
}

// Hàm escape HTML
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Hàm redirect
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Hàm kiểm tra đăng nhập
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Hàm logout
function logout() {
    session_destroy();
    redirect('login.php');
}
?>
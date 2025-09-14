<?php
$page_title = 'Cài đặt';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý cập nhật thông tin cá nhân
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng bởi tài khoản khác';
        } else {
            // Cập nhật thông tin cơ bản
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $user_id])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Cập nhật mật khẩu nếu có
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
                    } else {
                        // Kiểm tra mật khẩu hiện tại
                        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                        
                        if (password_verify($current_password, $user['password'])) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                            if ($stmt->execute([$hashed_password, $user_id])) {
                                $success = 'Cập nhật thông tin và mật khẩu thành công';
                            } else {
                                $error = 'Có lỗi xảy ra khi cập nhật mật khẩu';
                            }
                        } else {
                            $error = 'Mật khẩu hiện tại không đúng';
                        }
                    }
                } else {
                    $success = 'Cập nhật thông tin thành công';
                }
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật thông tin';
            }
        }
    }
}

// Lấy thông tin user hiện tại
$user = getUserById($user_id);

include 'templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fanpages.php">
                            <i class="fab fa-facebook"></i> Fanpage Facebook
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="zalo.php">
                            <i class="fas fa-comments"></i> Zalo OA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="posts.php">
                            <i class="fas fa-edit"></i> Quản lý bài đăng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php">
                            <i class="fas fa-clock"></i> Lên lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-bar"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cog"></i> Cài đặt
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Cài đặt</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo h($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo h($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Thông tin cá nhân -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo h($user['name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo h($user['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6 class="mb-3">Đổi mật khẩu (tùy chọn)</h6>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Cài đặt API -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Cài đặt API</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Cài đặt API được quản lý bởi admin hệ thống. 
                                Liên hệ admin để thay đổi các cài đặt này.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Facebook API</h6>
                                    <div class="mb-3">
                                        <label class="form-label">App ID</label>
                                        <input type="text" class="form-control" value="Chưa cấu hình" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">App Secret</label>
                                        <input type="password" class="form-control" value="••••••••••••••••" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Zalo API</h6>
                                    <div class="mb-3">
                                        <label class="form-label">App ID</label>
                                        <input type="text" class="form-control" value="Chưa cấu hình" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">App Secret</label>
                                        <input type="password" class="form-control" value="••••••••••••••••" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Thông tin tài khoản -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin tài khoản</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="https://via.placeholder.com/100x100?text=<?php echo urlencode(substr($user['name'], 0, 1)); ?>" 
                                     class="rounded-circle" width="100" height="100">
                                <h5 class="mt-2"><?php echo h($user['name']); ?></h5>
                                <p class="text-muted"><?php echo h($user['email']); ?></p>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Vai trò:</span>
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Trạng thái:</span>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ngày tạo:</span>
                                <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <span>Cập nhật cuối:</span>
                                <span><?php echo date('d/m/Y', strtotime($user['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Thống kê nhanh -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thống kê nhanh</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Lấy thống kê nhanh
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM fanpages WHERE user_id = ? AND status = 'active'");
                            $stmt->execute([$user_id]);
                            $fanpage_count = $stmt->fetch()['count'];
                            
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM zalo_accounts WHERE user_id = ? AND status = 'active'");
                            $stmt->execute([$user_id]);
                            $zalo_count = $stmt->fetch()['count'];
                            
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
                            $stmt->execute([$user_id]);
                            $post_count = $stmt->fetch()['count'];
                            ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Fanpage:</span>
                                <span class="fw-bold"><?php echo $fanpage_count; ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Zalo OA:</span>
                                <span class="fw-bold"><?php echo $zalo_count; ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Bài đăng:</span>
                                <span class="fw-bold"><?php echo $post_count; ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-grid">
                                <a href="analytics.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-chart-bar"></i> Xem thống kê chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Validate password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        this.setCustomValidity('');
    }
});

// Show password fields only when new password is entered
document.getElementById('new_password').addEventListener('input', function() {
    const currentPassword = document.getElementById('current_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (this.value.length > 0) {
        currentPassword.required = true;
        confirmPassword.required = true;
    } else {
        currentPassword.required = false;
        confirmPassword.required = false;
    }
});
</script>

<?php include 'templates/footer.php'; ?>
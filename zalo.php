<?php
$page_title = 'Quản lý Zalo OA';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý thêm Zalo OA mới
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add_zalo') {
    $oa_id = trim($_POST['oa_id'] ?? '');
    $access_token = trim($_POST['access_token'] ?? '');
    
    if (empty($oa_id) || empty($access_token)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Lấy thông tin Zalo OA từ API
        $url = "https://openapi.zalo.me/v2.0/oa/getoa";
        $data = [
            'oa_id' => $oa_id
        ];
        $response = callZaloAPI($url, $access_token, $data);
        
        if ($response && isset($response['data'])) {
            $oa_data = $response['data'];
            $avatar_url = $oa_data['avatar'] ?? '';
            
            if (addZaloAccount($user_id, $oa_id, $oa_data['name'], $access_token, $avatar_url)) {
                $success = 'Đã thêm Zalo OA thành công';
            } else {
                $error = 'Có lỗi xảy ra khi thêm Zalo OA';
            }
        } else {
            $error = 'OA ID hoặc Access Token không hợp lệ';
        }
    }
}

// Xử lý xóa Zalo OA
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'delete_zalo') {
    $zalo_id = $_POST['zalo_id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM zalo_accounts WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$zalo_id, $user_id])) {
        $success = 'Đã xóa Zalo OA thành công';
    } else {
        $error = 'Có lỗi xảy ra khi xóa Zalo OA';
    }
}

// Lấy danh sách Zalo OA
$zalo_accounts = getZaloAccountsByUserId($user_id);

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
                        <a class="nav-link active" href="zalo.php">
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
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Cài đặt
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quản lý Zalo OA</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addZaloModal">
                        <i class="fas fa-plus"></i> Thêm Zalo OA
                    </button>
                </div>
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

            <!-- Danh sách Zalo OA -->
            <div class="row">
                <?php if (empty($zalo_accounts)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có Zalo OA nào</h5>
                                <p class="text-muted">Hãy thêm Zalo OA đầu tiên để bắt đầu quản lý</p>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addZaloModal">
                                    <i class="fas fa-plus"></i> Thêm Zalo OA
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($zalo_accounts as $zalo): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo h($zalo['avatar']); ?>" 
                                             class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <h6 class="mb-0"><?php echo h($zalo['name']); ?></h6>
                                            <small class="text-muted">ID: <?php echo h($zalo['oa_id']); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="text-success fw-bold"><?php echo number_format($zalo['followers_count']); ?></div>
                                            <small class="text-muted">Followers</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-primary fw-bold"><?php echo ucfirst($zalo['status']); ?></div>
                                            <small class="text-muted">Trạng thái</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-info fw-bold">
                                                <?php echo $zalo['last_sync'] ? timeAgo($zalo['last_sync']) : 'Chưa sync'; ?>
                                            </div>
                                            <small class="text-muted">Lần cuối</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-success flex-fill" 
                                                onclick="syncZalo(<?php echo $zalo['id']; ?>)">
                                            <i class="fas fa-sync"></i> Sync
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteZalo(<?php echo $zalo['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Modal thêm Zalo OA -->
<div class="modal fade" id="addZaloModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Zalo OA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_zalo">
                    
                    <div class="mb-3">
                        <label for="oa_id" class="form-label">OA ID</label>
                        <input type="text" class="form-control" id="oa_id" name="oa_id" 
                               placeholder="Nhập OA ID của bạn" required>
                        <div class="form-text">
                            Tìm OA ID trong Zalo Admin Center
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="access_token" class="form-label">Access Token</label>
                        <input type="text" class="form-control" id="access_token" name="access_token" 
                               placeholder="Nhập Access Token của bạn" required>
                        <div class="form-text">
                            <a href="https://developers.zalo.me/" target="_blank">
                                Lấy Access Token tại đây
                            </a>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Hướng dẫn:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Truy cập <a href="https://developers.zalo.me/" target="_blank">Zalo Developers</a></li>
                            <li>Tạo ứng dụng mới hoặc chọn ứng dụng có sẵn</li>
                            <li>Lấy OA ID từ Zalo Admin Center</li>
                            <li>Lấy Access Token từ ứng dụng</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm Zalo OA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form xóa Zalo OA -->
<form id="deleteZaloForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_zalo">
    <input type="hidden" name="zalo_id" id="deleteZaloId">
</form>

<script>
function deleteZalo(zaloId) {
    if (confirm('Bạn có chắc chắn muốn xóa Zalo OA này?')) {
        document.getElementById('deleteZaloId').value = zaloId;
        document.getElementById('deleteZaloForm').submit();
    }
}

function syncZalo(zaloId) {
    // TODO: Implement sync functionality
    alert('Tính năng sync sẽ được triển khai trong phiên bản tiếp theo');
}
</script>

<?php include 'templates/footer.php'; ?>
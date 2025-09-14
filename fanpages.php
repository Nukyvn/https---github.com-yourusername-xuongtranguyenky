<?php
$page_title = 'Quản lý Fanpage';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý thêm fanpage mới
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add_fanpage') {
    $access_token = trim($_POST['access_token'] ?? '');
    
    if (empty($access_token)) {
        $error = 'Vui lòng nhập Access Token';
    } else {
        // Lấy thông tin fanpage từ Facebook API
        $url = "https://graph.facebook.com/me/accounts?access_token=" . $access_token;
        $response = callFacebookAPI($url, '');
        
        if ($response && isset($response['data'])) {
            $added_count = 0;
            foreach ($response['data'] as $page) {
                // Kiểm tra xem fanpage đã tồn tại chưa
                $stmt = $db->prepare("SELECT id FROM fanpages WHERE user_id = ? AND page_id = ?");
                $stmt->execute([$user_id, $page['id']]);
                
                if (!$stmt->fetch()) {
                    // Lấy avatar của fanpage
                    $avatar_url = "https://graph.facebook.com/{$page['id']}/picture?type=normal";
                    
                    if (addFanpage($user_id, $page['id'], $page['name'], $page['access_token'], $avatar_url)) {
                        $added_count++;
                    }
                }
            }
            
            if ($added_count > 0) {
                $success = "Đã thêm thành công {$added_count} fanpage";
            } else {
                $error = 'Không có fanpage mới nào được thêm';
            }
        } else {
            $error = 'Access Token không hợp lệ hoặc không có quyền truy cập fanpage';
        }
    }
}

// Xử lý xóa fanpage
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'delete_fanpage') {
    $fanpage_id = $_POST['fanpage_id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM fanpages WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$fanpage_id, $user_id])) {
        $success = 'Đã xóa fanpage thành công';
    } else {
        $error = 'Có lỗi xảy ra khi xóa fanpage';
    }
}

// Lấy danh sách fanpage
$fanpages = getFanpagesByUserId($user_id);

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
                        <a class="nav-link active" href="fanpages.php">
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
                <h1 class="h2">Quản lý Fanpage Facebook</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFanpageModal">
                        <i class="fas fa-plus"></i> Thêm Fanpage
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

            <!-- Danh sách fanpage -->
            <div class="row">
                <?php if (empty($fanpages)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fab fa-facebook fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có fanpage nào</h5>
                                <p class="text-muted">Hãy thêm fanpage đầu tiên để bắt đầu quản lý</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFanpageModal">
                                    <i class="fas fa-plus"></i> Thêm Fanpage
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($fanpages as $fanpage): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo h($fanpage['avatar']); ?>" 
                                             class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <h6 class="mb-0"><?php echo h($fanpage['name']); ?></h6>
                                            <small class="text-muted">ID: <?php echo h($fanpage['page_id']); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="text-primary fw-bold"><?php echo number_format($fanpage['followers_count']); ?></div>
                                            <small class="text-muted">Followers</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-success fw-bold"><?php echo ucfirst($fanpage['status']); ?></div>
                                            <small class="text-muted">Trạng thái</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-info fw-bold">
                                                <?php echo $fanpage['last_sync'] ? timeAgo($fanpage['last_sync']) : 'Chưa sync'; ?>
                                            </div>
                                            <small class="text-muted">Lần cuối</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary flex-fill" 
                                                onclick="syncFanpage(<?php echo $fanpage['id']; ?>)">
                                            <i class="fas fa-sync"></i> Sync
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteFanpage(<?php echo $fanpage['id']; ?>)">
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

<!-- Modal thêm fanpage -->
<div class="modal fade" id="addFanpageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Fanpage Facebook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_fanpage">
                    
                    <div class="mb-3">
                        <label for="access_token" class="form-label">Access Token</label>
                        <input type="text" class="form-control" id="access_token" name="access_token" 
                               placeholder="Nhập Access Token của bạn" required>
                        <div class="form-text">
                            <a href="https://developers.facebook.com/tools/explorer/" target="_blank">
                                Lấy Access Token tại đây
                            </a>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Hướng dẫn:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Truy cập <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Facebook Graph API Explorer</a></li>
                            <li>Chọn app của bạn và đăng nhập</li>
                            <li>Chọn quyền: <code>pages_manage_posts</code>, <code>pages_read_engagement</code></li>
                            <li>Generate Access Token và copy vào đây</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm Fanpage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form xóa fanpage -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_fanpage">
    <input type="hidden" name="fanpage_id" id="deleteFanpageId">
</form>

<script>
function deleteFanpage(fanpageId) {
    if (confirm('Bạn có chắc chắn muốn xóa fanpage này?')) {
        document.getElementById('deleteFanpageId').value = fanpageId;
        document.getElementById('deleteForm').submit();
    }
}

function syncFanpage(fanpageId) {
    // TODO: Implement sync functionality
    alert('Tính năng sync sẽ được triển khai trong phiên bản tiếp theo');
}
</script>

<?php include 'templates/footer.php'; ?>
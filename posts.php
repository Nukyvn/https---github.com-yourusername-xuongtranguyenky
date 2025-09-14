<?php
$page_title = 'Quản lý bài đăng';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý tạo bài đăng mới
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'create_post') {
    $content = trim($_POST['content'] ?? '');
    $platform = $_POST['platform'] ?? '';
    $fanpage_id = $_POST['fanpage_id'] ?? null;
    $zalo_id = $_POST['zalo_id'] ?? null;
    $scheduled_time = $_POST['scheduled_time'] ?? null;
    
    if (empty($content)) {
        $error = 'Vui lòng nhập nội dung bài đăng';
    } elseif ($platform === 'facebook' && empty($fanpage_id)) {
        $error = 'Vui lòng chọn fanpage để đăng';
    } elseif ($platform === 'zalo' && empty($zalo_id)) {
        $error = 'Vui lòng chọn Zalo OA để đăng';
    } elseif ($platform === 'both' && (empty($fanpage_id) || empty($zalo_id))) {
        $error = 'Vui lòng chọn cả fanpage và Zalo OA';
    } else {
        if (createPost($user_id, $content, $fanpage_id, $zalo_id, $scheduled_time)) {
            $success = 'Tạo bài đăng thành công';
        } else {
            $error = 'Có lỗi xảy ra khi tạo bài đăng';
        }
    }
}

// Lấy danh sách bài đăng
$posts = getPostsByUserId($user_id, 50);
$fanpages = getFanpagesByUserId($user_id);
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
                        <a class="nav-link" href="zalo.php">
                            <i class="fas fa-comments"></i> Zalo OA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="posts.php">
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
                <h1 class="h2">Quản lý bài đăng</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                        <i class="fas fa-plus"></i> Tạo bài đăng mới
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

            <!-- Danh sách bài đăng -->
            <div class="row">
                <?php if (empty($posts)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-edit fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có bài đăng nào</h5>
                                <p class="text-muted">Hãy tạo bài đăng đầu tiên để bắt đầu</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                    <i class="fas fa-plus"></i> Tạo bài đăng
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <?php if ($post['platform'] === 'facebook' || $post['platform'] === 'both'): ?>
                                                <i class="fab fa-facebook text-primary me-2"></i>
                                                <span class="text-muted"><?php echo h($post['fanpage_name']); ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if ($post['platform'] === 'zalo' || $post['platform'] === 'both'): ?>
                                                <?php if ($post['platform'] === 'both'): ?>
                                                    <span class="mx-2">+</span>
                                                <?php endif; ?>
                                                <i class="fas fa-comments text-success me-2"></i>
                                                <span class="text-muted"><?php echo h($post['zalo_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Chỉnh sửa</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-chart-bar"></i> Xem thống kê</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash"></i> Xóa</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <p class="mb-0"><?php echo nl2br(h($post['content'])); ?></p>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center text-muted">
                                            <small>
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo timeAgo($post['created_at']); ?>
                                                
                                                <?php if ($post['scheduled_time']): ?>
                                                    <span class="ms-3">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Lên lịch: <?php echo date('d/m/Y H:i', strtotime($post['scheduled_time'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-<?php 
                                                echo $post['status'] === 'published' ? 'success' : 
                                                    ($post['status'] === 'scheduled' ? 'warning' : 
                                                    ($post['status'] === 'failed' ? 'danger' : 'secondary')); 
                                            ?> me-2">
                                                <?php 
                                                echo $post['status'] === 'published' ? 'Đã đăng' : 
                                                    ($post['status'] === 'scheduled' ? 'Đã lên lịch' : 
                                                    ($post['status'] === 'failed' ? 'Lỗi' : 'Bản nháp')); 
                                                ?>
                                            </span>
                                            
                                            <div class="d-flex text-muted small">
                                                <span class="me-3"><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                                <span class="me-3"><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                                                <span><i class="fas fa-share"></i> <?php echo $post['shares_count']; ?></span>
                                            </div>
                                        </div>
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

<!-- Modal tạo bài đăng -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo bài đăng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_post">
                    
                    <div class="mb-3">
                        <label for="platform" class="form-label">Nền tảng</label>
                        <select class="form-select" id="platform" name="platform" required onchange="togglePlatformOptions()">
                            <option value="">Chọn nền tảng</option>
                            <option value="facebook">Facebook</option>
                            <option value="zalo">Zalo</option>
                            <option value="both">Cả hai</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="fanpageSelect" style="display: none;">
                        <label for="fanpage_id" class="form-label">Fanpage Facebook</label>
                        <select class="form-select" id="fanpage_id" name="fanpage_id">
                            <option value="">Chọn fanpage</option>
                            <?php foreach ($fanpages as $fanpage): ?>
                                <option value="<?php echo $fanpage['id']; ?>"><?php echo h($fanpage['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="zaloSelect" style="display: none;">
                        <label for="zalo_id" class="form-label">Zalo OA</label>
                        <select class="form-select" id="zalo_id" name="zalo_id">
                            <option value="">Chọn Zalo OA</option>
                            <?php foreach ($zalo_accounts as $zalo): ?>
                                <option value="<?php echo $zalo['id']; ?>"><?php echo h($zalo['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung</label>
                        <textarea class="form-control" id="content" name="content" rows="5" 
                                  placeholder="Nhập nội dung bài đăng..." required></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/2000 ký tự
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scheduled_time" class="form-label">Lên lịch đăng (tùy chọn)</label>
                        <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time">
                        <div class="form-text">Để trống để đăng ngay lập tức</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo bài đăng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePlatformOptions() {
    const platform = document.getElementById('platform').value;
    const fanpageSelect = document.getElementById('fanpageSelect');
    const zaloSelect = document.getElementById('zaloSelect');
    
    if (platform === 'facebook') {
        fanpageSelect.style.display = 'block';
        zaloSelect.style.display = 'none';
        document.getElementById('fanpage_id').required = true;
        document.getElementById('zalo_id').required = false;
    } else if (platform === 'zalo') {
        fanpageSelect.style.display = 'none';
        zaloSelect.style.display = 'block';
        document.getElementById('fanpage_id').required = false;
        document.getElementById('zalo_id').required = true;
    } else if (platform === 'both') {
        fanpageSelect.style.display = 'block';
        zaloSelect.style.display = 'block';
        document.getElementById('fanpage_id').required = true;
        document.getElementById('zalo_id').required = true;
    } else {
        fanpageSelect.style.display = 'none';
        zaloSelect.style.display = 'none';
        document.getElementById('fanpage_id').required = false;
        document.getElementById('zalo_id').required = false;
    }
}

// Đếm ký tự
document.getElementById('content').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('charCount').textContent = charCount;
    
    if (charCount > 2000) {
        document.getElementById('charCount').style.color = 'red';
    } else {
        document.getElementById('charCount').style.color = 'inherit';
    }
});

// Set min datetime to now
document.getElementById('scheduled_time').min = new Date().toISOString().slice(0, 16);
</script>

<?php include 'templates/footer.php'; ?>
<?php
$page_title = 'Lên lịch đăng bài';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý tạo lịch đăng bài
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'create_schedule') {
    $name = trim($_POST['name'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $schedule_type = $_POST['schedule_type'] ?? '';
    $schedule_time = $_POST['schedule_time'] ?? '';
    $schedule_days = $_POST['schedule_days'] ?? [];
    $schedule_date = $_POST['schedule_date'] ?? null;
    $fanpage_ids = $_POST['fanpage_ids'] ?? [];
    $zalo_ids = $_POST['zalo_ids'] ?? [];
    
    if (empty($name) || empty($content) || empty($schedule_type) || empty($schedule_time)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } elseif (empty($fanpage_ids) && empty($zalo_ids)) {
        $error = 'Vui lòng chọn ít nhất một fanpage hoặc Zalo OA';
    } else {
        // Tính toán next_run
        $next_run = null;
        if ($schedule_type === 'once') {
            $next_run = $schedule_date . ' ' . $schedule_time;
        } else {
            // Tính toán cho lịch lặp lại
            $next_run = calculateNextRun($schedule_type, $schedule_time, $schedule_days);
        }
        
        $stmt = $db->prepare("
            INSERT INTO schedules (user_id, name, content, fanpage_ids, zalo_ids, schedule_type, schedule_time, schedule_days, schedule_date, next_run) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $fanpage_ids_json = json_encode($fanpage_ids);
        $zalo_ids_json = json_encode($zalo_ids);
        $schedule_days_json = json_encode($schedule_days);
        
        if ($stmt->execute([$user_id, $name, $content, $fanpage_ids_json, $zalo_ids_json, $schedule_type, $schedule_time, $schedule_days_json, $schedule_date, $next_run])) {
            $success = 'Tạo lịch đăng bài thành công';
        } else {
            $error = 'Có lỗi xảy ra khi tạo lịch đăng bài';
        }
    }
}

// Lấy danh sách lịch đăng bài
$stmt = $db->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$schedules = $stmt->fetchAll();

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
                        <a class="nav-link" href="posts.php">
                            <i class="fas fa-edit"></i> Quản lý bài đăng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="schedule.php">
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
                <h1 class="h2">Lên lịch đăng bài</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                        <i class="fas fa-plus"></i> Tạo lịch mới
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

            <!-- Danh sách lịch đăng bài -->
            <div class="row">
                <?php if (empty($schedules)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có lịch đăng bài nào</h5>
                                <p class="text-muted">Hãy tạo lịch đăng bài đầu tiên để tự động hóa việc đăng bài</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                                    <i class="fas fa-plus"></i> Tạo lịch mới
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1"><?php echo h($schedule['name']); ?></h5>
                                            <p class="text-muted mb-0"><?php echo nl2br(h($schedule['content'])); ?></p>
                                        </div>
                                        
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Chỉnh sửa</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-play"></i> Chạy ngay</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash"></i> Xóa</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <small class="text-muted">Loại lịch</small>
                                            <div class="fw-bold">
                                                <?php 
                                                $types = [
                                                    'once' => 'Một lần',
                                                    'daily' => 'Hàng ngày',
                                                    'weekly' => 'Hàng tuần',
                                                    'monthly' => 'Hàng tháng'
                                                ];
                                                echo $types[$schedule['schedule_type']] ?? $schedule['schedule_type'];
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <small class="text-muted">Thời gian</small>
                                            <div class="fw-bold"><?php echo date('H:i', strtotime($schedule['schedule_time'])); ?></div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <small class="text-muted">Lần chạy tiếp theo</small>
                                            <div class="fw-bold">
                                                <?php echo $schedule['next_run'] ? date('d/m/Y H:i', strtotime($schedule['next_run'])) : 'Chưa xác định'; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <small class="text-muted">Trạng thái</small>
                                            <div>
                                                <span class="badge bg-<?php echo $schedule['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $schedule['is_active'] ? 'Hoạt động' : 'Tạm dừng'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center text-muted">
                                            <small>
                                                <i class="fas fa-clock me-1"></i>
                                                Tạo: <?php echo timeAgo($schedule['created_at']); ?>
                                                
                                                <?php if ($schedule['last_run']): ?>
                                                    <span class="ms-3">
                                                        <i class="fas fa-play me-1"></i>
                                                        Chạy cuối: <?php echo timeAgo($schedule['last_run']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex text-muted small">
                                            <?php 
                                            $fanpage_ids = json_decode($schedule['fanpage_ids'], true) ?: [];
                                            $zalo_ids = json_decode($schedule['zalo_ids'], true) ?: [];
                                            ?>
                                            <span class="me-3">
                                                <i class="fab fa-facebook"></i> <?php echo count($fanpage_ids); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-comments"></i> <?php echo count($zalo_ids); ?>
                                            </span>
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

<!-- Modal tạo lịch đăng bài -->
<div class="modal fade" id="createScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo lịch đăng bài</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_schedule">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên lịch</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Ví dụ: Đăng bài sáng hàng ngày" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung bài đăng</label>
                        <textarea class="form-control" id="content" name="content" rows="4" 
                                  placeholder="Nhập nội dung bài đăng..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule_type" class="form-label">Loại lịch</label>
                                <select class="form-select" id="schedule_type" name="schedule_type" required onchange="toggleScheduleOptions()">
                                    <option value="">Chọn loại lịch</option>
                                    <option value="once">Một lần</option>
                                    <option value="daily">Hàng ngày</option>
                                    <option value="weekly">Hàng tuần</option>
                                    <option value="monthly">Hàng tháng</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule_time" class="form-label">Thời gian</label>
                                <input type="time" class="form-control" id="schedule_time" name="schedule_time" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="scheduleDate" style="display: none;">
                        <label for="schedule_date" class="form-label">Ngày đăng</label>
                        <input type="date" class="form-control" id="schedule_date" name="schedule_date">
                    </div>
                    
                    <div class="mb-3" id="scheduleDays" style="display: none;">
                        <label class="form-label">Ngày trong tuần</label>
                        <div class="row">
                            <?php 
                            $days = [
                                'monday' => 'Thứ 2',
                                'tuesday' => 'Thứ 3', 
                                'wednesday' => 'Thứ 4',
                                'thursday' => 'Thứ 5',
                                'friday' => 'Thứ 6',
                                'saturday' => 'Thứ 7',
                                'sunday' => 'Chủ nhật'
                            ];
                            foreach ($days as $key => $day): ?>
                                <div class="col-md-3 col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="schedule_days[]" value="<?php echo $key; ?>" id="day_<?php echo $key; ?>">
                                        <label class="form-check-label" for="day_<?php echo $key; ?>">
                                            <?php echo $day; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fanpage Facebook</label>
                                <?php if (empty($fanpages)): ?>
                                    <p class="text-muted small">Chưa có fanpage nào. <a href="fanpages.php">Thêm fanpage</a></p>
                                <?php else: ?>
                                    <?php foreach ($fanpages as $fanpage): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="fanpage_ids[]" value="<?php echo $fanpage['id']; ?>" id="fanpage_<?php echo $fanpage['id']; ?>">
                                            <label class="form-check-label" for="fanpage_<?php echo $fanpage['id']; ?>">
                                                <?php echo h($fanpage['name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Zalo OA</label>
                                <?php if (empty($zalo_accounts)): ?>
                                    <p class="text-muted small">Chưa có Zalo OA nào. <a href="zalo.php">Thêm Zalo OA</a></p>
                                <?php else: ?>
                                    <?php foreach ($zalo_accounts as $zalo): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="zalo_ids[]" value="<?php echo $zalo['id']; ?>" id="zalo_<?php echo $zalo['id']; ?>">
                                            <label class="form-check-label" for="zalo_<?php echo $zalo['id']; ?>">
                                                <?php echo h($zalo['name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo lịch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleScheduleOptions() {
    const scheduleType = document.getElementById('schedule_type').value;
    const scheduleDate = document.getElementById('scheduleDate');
    const scheduleDays = document.getElementById('scheduleDays');
    
    if (scheduleType === 'once') {
        scheduleDate.style.display = 'block';
        scheduleDays.style.display = 'none';
        document.getElementById('schedule_date').required = true;
    } else if (scheduleType === 'weekly') {
        scheduleDate.style.display = 'none';
        scheduleDays.style.display = 'block';
        document.getElementById('schedule_date').required = false;
    } else {
        scheduleDate.style.display = 'none';
        scheduleDays.style.display = 'none';
        document.getElementById('schedule_date').required = false;
    }
}

// Set min date to today
document.getElementById('schedule_date').min = new Date().toISOString().split('T')[0];
</script>

<?php include 'templates/footer.php'; ?>
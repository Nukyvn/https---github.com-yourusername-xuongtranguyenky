<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Lấy danh sách fanpage và zalo của user
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
                        <a class="nav-link active" href="index.php">
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
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tạo bài đăng mới
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tổng Fanpage</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($fanpages); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fab fa-facebook fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Tổng Zalo OA</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($zalo_accounts); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Bài đăng hôm nay</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-edit fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Tổng lượt tương tác</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-heart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Bài đăng gần đây</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <p class="text-muted">Chưa có bài đăng nào</p>
                                <a href="posts.php" class="btn btn-primary">Tạo bài đăng đầu tiên</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Fanpage & Zalo</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6>Facebook Fanpages</h6>
                                <?php if (empty($fanpages)): ?>
                                    <p class="text-muted small">Chưa kết nối fanpage nào</p>
                                    <a href="fanpages.php" class="btn btn-sm btn-outline-primary">Kết nối fanpage</a>
                                <?php else: ?>
                                    <?php foreach ($fanpages as $page): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="<?php echo $page['avatar']; ?>" class="rounded-circle me-2" width="30" height="30">
                                            <span class="small"><?php echo htmlspecialchars($page['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h6>Zalo OA</h6>
                                <?php if (empty($zalo_accounts)): ?>
                                    <p class="text-muted small">Chưa kết nối Zalo OA nào</p>
                                    <a href="zalo.php" class="btn btn-sm btn-outline-success">Kết nối Zalo</a>
                                <?php else: ?>
                                    <?php foreach ($zalo_accounts as $zalo): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="<?php echo $zalo['avatar']; ?>" class="rounded-circle me-2" width="30" height="30">
                                            <span class="small"><?php echo htmlspecialchars($zalo['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
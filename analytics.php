<?php
$page_title = 'Thống kê';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Lấy thống kê tổng quan
$stats = [];

// Tổng số fanpage
$stmt = $db->prepare("SELECT COUNT(*) as count FROM fanpages WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$stats['total_fanpages'] = $stmt->fetch()['count'];

// Tổng số Zalo OA
$stmt = $db->prepare("SELECT COUNT(*) as count FROM zalo_accounts WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$stats['total_zalo'] = $stmt->fetch()['count'];

// Tổng số bài đăng
$stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats['total_posts'] = $stmt->fetch()['count'];

// Bài đăng hôm nay
$stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt->execute([$user_id]);
$stats['posts_today'] = $stmt->fetch()['count'];

// Tổng lượt tương tác
$stmt = $db->prepare("SELECT SUM(likes_count + comments_count + shares_count) as total FROM posts WHERE user_id = ? AND status = 'published'");
$stmt->execute([$user_id]);
$stats['total_engagement'] = $stmt->fetch()['total'] ?: 0;

// Lấy dữ liệu thống kê theo thời gian (30 ngày gần nhất)
$stmt = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as posts_count,
        SUM(likes_count + comments_count + shares_count) as engagement
    FROM posts 
    WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$user_id]);
$chart_data = $stmt->fetchAll();

// Lấy top fanpage theo engagement
$stmt = $db->prepare("
    SELECT 
        f.name,
        f.avatar,
        COUNT(p.id) as posts_count,
        SUM(p.likes_count + p.comments_count + p.shares_count) as total_engagement
    FROM fanpages f
    LEFT JOIN posts p ON f.id = p.fanpage_id AND p.status = 'published'
    WHERE f.user_id = ?
    GROUP BY f.id, f.name, f.avatar
    ORDER BY total_engagement DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$top_fanpages = $stmt->fetchAll();

// Lấy top Zalo OA theo engagement
$stmt = $db->prepare("
    SELECT 
        z.name,
        z.avatar,
        COUNT(p.id) as posts_count,
        SUM(p.likes_count + p.comments_count + p.shares_count) as total_engagement
    FROM zalo_accounts z
    LEFT JOIN posts p ON z.id = p.zalo_id AND p.status = 'published'
    WHERE z.user_id = ?
    GROUP BY z.id, z.name, z.avatar
    ORDER BY total_engagement DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$top_zalo = $stmt->fetchAll();

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
                        <a class="nav-link active" href="analytics.php">
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
                <h1 class="h2">Thống kê</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">7 ngày</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">30 ngày</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">90 ngày</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Xuất báo cáo
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_fanpages']; ?></div>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_zalo']; ?></div>
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
                                        Tổng bài đăng</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_posts']; ?></div>
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
                                        Tổng tương tác</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_engagement']); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-heart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Biểu đồ hoạt động 30 ngày</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="activityChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tóm tắt hôm nay</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h3 text-primary"><?php echo $stats['posts_today']; ?></div>
                                <p class="text-muted">Bài đăng hôm nay</p>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <span>Fanpage hoạt động:</span>
                                <span class="fw-bold"><?php echo $stats['total_fanpages']; ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <span>Zalo OA hoạt động:</span>
                                <span class="fw-bold"><?php echo $stats['total_zalo']; ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <span>Tổng tương tác:</span>
                                <span class="fw-bold"><?php echo number_format($stats['total_engagement']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Top Fanpage</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_fanpages)): ?>
                                <p class="text-muted text-center">Chưa có dữ liệu</p>
                            <?php else: ?>
                                <?php foreach ($top_fanpages as $index => $fanpage): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                        </div>
                                        <img src="<?php echo h($fanpage['avatar']); ?>" class="rounded-circle me-3" width="40" height="40">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo h($fanpage['name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo $fanpage['posts_count']; ?> bài đăng • 
                                                <?php echo number_format($fanpage['total_engagement']); ?> tương tác
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Top Zalo OA</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_zalo)): ?>
                                <p class="text-muted text-center">Chưa có dữ liệu</p>
                            <?php else: ?>
                                <?php foreach ($top_zalo as $index => $zalo): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <span class="badge bg-success"><?php echo $index + 1; ?></span>
                                        </div>
                                        <img src="<?php echo h($zalo['avatar']); ?>" class="rounded-circle me-3" width="40" height="40">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo h($zalo['name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo $zalo['posts_count']; ?> bài đăng • 
                                                <?php echo number_format($zalo['total_engagement']); ?> tương tác
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Chart data
const chartData = <?php echo json_encode($chart_data); ?>;

// Prepare data for Chart.js
const labels = chartData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
});

const postsData = chartData.map(item => item.posts_count);
const engagementData = chartData.map(item => item.engagement);

// Create activity chart
const ctx = document.getElementById('activityChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Số bài đăng',
            data: postsData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Tương tác',
            data: engagementData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});
</script>

<?php include 'templates/footer.php'; ?>
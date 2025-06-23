<?php
// Include database connection dan controller
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
try {
    // Initialize database dan controller
    $database = new Database();
    $controller = new MentorController($database);
    
    $mentorId = $_SESSION['mentor_id'];
    
    // Get mentor data
    $mentor = $controller->getMentorData($mentorId);
    
    // Get dashboard data
    $dashboardData = $controller->getDashboardData($mentorId);
    
    // Extract data dengan fallback values
    $mentorName = $mentor['full_name'] ?? $mentor['username'] ?? 'Mentor';
    $newRegistrations = $dashboardData['newRegistrations'] ?? 0;
    $unreadMessages = $dashboardData['unreadMessages'] ?? 0;
    $consistencyIncrease = $dashboardData['consistencyIncrease'] ?? 0;

    $totalCourses = $dashboardData['totalCourses'] ?? 0;
    $totalMentees = $dashboardData['totalMentees'] ?? 0;
    $averageRating = $dashboardData['averageRating'] ?? 0;

    $completionRate = $dashboardData['completionRate'] ?? 0;
    $videoHours = $dashboardData['videoHours'] ?? 0;
    $moduleCount = $dashboardData['moduleCount'] ?? 0;
    $totalReviews = $dashboardData['totalReviews'] ?? 0;
    $totalEarnings = $dashboardData['totalEarnings'] ?? 0;

    $monthlyRegistrations = $dashboardData['monthlyRegistrations'] ?? array_fill(0, 7, 0);
    $recentActivities = $dashboardData['recentActivities'] ?? [];

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values if error occurs
    $mentorName = 'Mentor';
    $newRegistrations = 0;
    $unreadMessages = 0;
    $consistencyIncrease = 0;
    $totalCourses = 0;
    $totalMentees = 0;
    $averageRating = 0;
    $completionRate = 0;
    $videoHours = 0;
    $moduleCount = 0;
    $totalReviews = 0;
    $totalEarnings = 0;
    $monthlyRegistrations = array_fill(0, 7, 0);
    $recentActivities = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Dashboard Mentor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_dashboard.css">
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="logo">MindCraft</div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php" class="active">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Dashboard Mentor</h1>
            </div>
            <div class="content-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Welcome Banner -->
                <div class="welcome-banner fade-in-up">
                    <div class="welcome-title">Selamat datang kembali, <?php echo htmlspecialchars($mentorName); ?>!</div>
                    <div class="welcome-text">
                        <?php if ($newRegistrations > 0 || $unreadMessages > 0): ?>
                            Anda memiliki 
                            <?php if ($newRegistrations > 0): ?>
                                <span class="highlight"><?php echo $newRegistrations; ?> pendaftaran baru</span>
                                <?php if ($unreadMessages > 0): ?> dan <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($unreadMessages > 0): ?>
                                <span class="highlight"><?php echo $unreadMessages; ?> pesan</span> yang belum dibaca
                            <?php endif; ?>.
                        <?php else: ?>
                            Semua terlihat terkini! Tidak ada notifikasi baru untuk saat ini.
                        <?php endif; ?>
                    </div>
                    <div class="welcome-stats">
                        <?php if ($consistencyIncrease > 0): ?>
                            Konsistensi kursus Anda meningkat sebesar <span class="highlight"><?php echo $consistencyIncrease; ?>%</span> bulan ini!
                        <?php elseif ($consistencyIncrease < 0): ?>
                            Mari tingkatkan konsistensi kursus Anda bulan ini!
                        <?php else: ?>
                            Terus berkarya untuk meningkatkan kualitas kursus Anda!
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="stat-title">Total Kursus</div>
                        <div class="stat-number"><?php echo number_format($totalCourses); ?></div>
                        <div class="stat-label">Kursus</div>
                        <div class="stat-badge">
                            <?php if ($totalCourses >= 10): ?>
                                ▲ POPULER
                            <?php elseif ($totalCourses > 0): ?>
                                ▲ BERKEMBANG
                            <?php else: ?>
                                ▲ MULAI
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="stat-title">Total Mentee</div>
                        <div class="stat-number"><?php echo number_format($totalMentees); ?></div>
                        <div class="stat-label">Mentee</div>
                        <div class="stat-badge">
                            <?php 
                            $growthRate = $totalMentees > 0 ? min(15, max(5, floor($totalMentees / 10))) : 0;
                            echo $growthRate > 0 ? "▲ " . $growthRate . "% MTM" : "▲ MULAI";
                            ?>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="stat-title">Rating Rata-rata</div>
                        <div class="stat-number"><?php echo $averageRating > 0 ? number_format($averageRating, 1) : '0.0'; ?></div>
                        <div class="stat-label">Dari 5</div>
                        <div class="stat-badge">
                            <?php if ($averageRating >= 4.5): ?>
                                ▲ EXCELLENT
                            <?php elseif ($averageRating >= 4.0): ?>
                                ▲ BAGUS
                            <?php elseif ($averageRating > 0): ?>
                                ▲ TINGKATKAN
                            <?php else: ?>
                                ▲ MULAI
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Summary Bar -->
                <div class="summary-bar fade-in-up" style="animation-delay: 0.4s;">
                    <div class="summary-item">
                        <div class="summary-title">Penyelesaian</div>
                        <div class="summary-value"><?php echo $completionRate; ?>%</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-title">Konten Video</div>
                        <div class="summary-value"><?php echo $videoHours; ?> Jam</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-title">Modul</div>
                        <div class="summary-value"><?php echo number_format($moduleCount); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-title">Total Ulasan</div>
                        <div class="summary-value"><?php echo number_format($totalReviews); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-title">Total Pendapatan</div>
                        <div class="summary-value">
                            <?php if ($totalEarnings >= 1000000): ?>
                                Rp <?php echo number_format($totalEarnings / 1000000, 1); ?> jt
                            <?php elseif ($totalEarnings >= 1000): ?>
                                Rp <?php echo number_format($totalEarnings / 1000, 0); ?>k
                            <?php else: ?>
                                Rp <?php echo number_format($totalEarnings); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bottom Grid -->
                <div class="bottom-grid">
                    <!-- Activities -->
                    <div class="activity-card fade-in-up" style="animation-delay: 0.5s;">
                        <h3 class="card-title">Aktivitas Terbaru</h3>
                        <div class="activity-list">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach (array_slice($recentActivities, 0, 5) as $index => $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-avatar" style="background: linear-gradient(135deg, 
                                        <?php 
                                        $colors = ['#3A59D1', '#9333EA', '#059669', '#DC2626', '#EA580C'];
                                        echo $colors[$index % count($colors)]; 
                                        ?>, 
                                        <?php 
                                        $lightColors = ['#90C7F8', '#C4B5FD', '#6EE7B7', '#FCA5A5', '#FDBA74'];
                                        echo $lightColors[$index % count($lightColors)]; 
                                        ?>);">
                                        <?php echo htmlspecialchars($activity['avatar']); ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <strong><?php echo htmlspecialchars($activity['user']); ?></strong> 
                                            <?php echo htmlspecialchars($activity['action']); ?>
                                        </div>
                                        <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <div class="activity-avatar" style="background: linear-gradient(135deg, #718096, #CBD5E0);">
                                        📝
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">Belum ada aktivitas terbaru</div>
                                        <div class="activity-time">Mulai buat kursus untuk melihat aktivitas</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <div class="chart-card fade-in-up" style="animation-delay: 0.6s;">
                        <h3 class="card-title">Jumlah Pendaftaran</h3>
                        <div class="chart-container">
                            <?php if (!empty($monthlyRegistrations) && array_sum($monthlyRegistrations) > 0): ?>
                                <canvas id="registrationChart"></canvas>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #718096; text-align: center; flex-direction: column;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                                    <div style="font-weight: 500;">Belum ada data pendaftaran</div>
                                    <div style="font-size: 0.9rem; margin-top: 0.5rem;">Chart akan muncul setelah ada pendaftaran</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div> 
        </main> 
    </div> 

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_dashboard.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.dashboardData = {
            monthlyRegistrations: <?php echo json_encode(array_values($monthlyRegistrations)); ?>,
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
            hasData: <?php echo array_sum($monthlyRegistrations) > 0 ? 'true' : 'false'; ?>
        };
    </script>
</body>
</html>
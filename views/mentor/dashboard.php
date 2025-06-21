<?php
// Lokasi: MindCraft-Project/views/mentor/dashboard.php

// Data mentor dan dashboard - gunakan data statis jika tidak ada dari controller
$mentorName = isset($mentor['username']) ? $mentor['username'] : 'Budi Mentor';

// Dashboard data dengan default values
$newRegistrations = isset($dashboardData['newRegistrations']) ? $dashboardData['newRegistrations'] : 3;
$unreadMessages = isset($dashboardData['unreadMessages']) ? $dashboardData['unreadMessages'] : 5;
$consistencyIncrease = isset($dashboardData['consistencyIncrease']) ? $dashboardData['consistencyIncrease'] : 12;

$totalCourses = isset($dashboardData['totalCourses']) ? $dashboardData['totalCourses'] : 12;
$totalMentees = isset($dashboardData['totalMentees']) ? $dashboardData['totalMentees'] : 96;
$averageRating = isset($dashboardData['averageRating']) ? $dashboardData['averageRating'] : 4.7;

$completionRate = isset($dashboardData['completionRate']) ? $dashboardData['completionRate'] : 78;
$videoHours = isset($dashboardData['videoHours']) ? $dashboardData['videoHours'] : 48;
$moduleCount = isset($dashboardData['moduleCount']) ? $dashboardData['moduleCount'] : 64;
$totalReviews = isset($dashboardData['totalReviews']) ? $dashboardData['totalReviews'] : 186;
$totalEarnings = isset($dashboardData['totalEarnings']) ? $dashboardData['totalEarnings'] : 12400000;

$monthlyRegistrations = isset($dashboardData['monthlyRegistrations']) ? $dashboardData['monthlyRegistrations'] : [10, 20, 25, 22, 28, 24, 30];

// Recent activities dengan data default
$recentActivities = isset($dashboardData['recentActivities']) ? $dashboardData['recentActivities'] : [
    [
        'user' => 'Budi S.',
        'action' => 'mendaftar kursus "Kerajian Anyaman untuk Pemula"',
        'time' => '2 jam yang lalu',
        'avatar' => 'B'
    ],
    [
        'user' => 'Siti A.',
        'action' => 'menyelesaikan modul "Pengenalan Anyaman"',
        'time' => '4 jam yang lalu',
        'avatar' => 'S'
    ],
    [
        'user' => 'Ahmad R.',
        'action' => 'memberikan ulasan untuk "Web Development"',
        'time' => '6 jam yang lalu',
        'avatar' => 'A'
    ],
    [
        'user' => 'Maya P.',
        'action' => 'mendaftar kursus "Digital Marketing"',
        'time' => '8 jam yang lalu',
        'avatar' => 'M'
    ],
    [
        'user' => 'Rizki P.',
        'action' => 'menyelesaikan kursus "Anyaman Lanjutan"',
        'time' => '1 hari yang lalu',
        'avatar' => 'R'
    ]
];
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
        <nav class="header-nav">
            <span>Notifikasi</span>
            <span>Pesan</span>
            <span>Profil</span>
        </nav>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php" class="active">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/courses.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/earnings.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/reviews.php">Ulasan & Feedback</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/settings.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Dashboard Mentor</h1>
            </div>
            <div class="content-body">
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
                            $growthRate = min(15, max(5, floor($totalMentees / 10)));
                            echo "▲ " . $growthRate . "% MTM";
                            ?>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="stat-title">Rating Rata-rata</div>
                        <div class="stat-number"><?php echo number_format($averageRating, 1); ?></div>
                        <div class="stat-label">Dari 5</div>
                        <div class="stat-badge">
                            <?php if ($averageRating >= 4.5): ?>
                                ▲ EXCELLENT
                            <?php elseif ($averageRating >= 4.0): ?>
                                ▲ BAGUS
                            <?php else: ?>
                                ▲ TINGKATKAN
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

                <!-- Quick Actions -->
                <div class="quick-actions fade-in-up" style="animation-delay: 0.7s;">
                    <h3 style="color: #2d3748; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Aksi Cepat</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2d3748; transition: all 0.2s ease;">
                            <span style="font-size: 1.5rem;">➕</span>
                            <div>
                                <div style="font-weight: 500;">Buat Kursus Baru</div>
                                <div style="font-size: 0.85rem; color: #718096;">Mulai berbagi pengetahuan</div>
                            </div>
                        </a>
                        
                        <a href="/MindCraft-Project/views/mentor/analitik.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2d3748; transition: all 0.2s ease;">
                            <span style="font-size: 1.5rem;">📈</span>
                            <div>
                                <div style="font-weight: 500;">Lihat Analitik</div>
                                <div style="font-size: 0.85rem; color: #718096;">Pantau performa kursus</div>
                            </div>
                        </a>
                        
                        <a href="/MindCraft-Project/views/mentor/earnings.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2d3748; transition: all 0.2s ease;">
                            <span style="font-size: 1.5rem;">💰</span>
                            <div>
                                <div style="font-weight: 500;">Cek Pendapatan</div>
                                <div style="font-size: 0.85rem; color: #718096;">Lihat earnings terbaru</div>
                            </div>
                        </a>
                        
                        <a href="/MindCraft-Project/views/mentor/reviews.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2d3748; transition: all 0.2s ease;">
                            <span style="font-size: 1.5rem;">⭐</span>
                            <div>
                                <div style="font-weight: 500;">Kelola Ulasan</div>
                                <div style="font-size: 0.85rem; color: #718096;">Respon feedback mentee</div>
                            </div>
                        </a>
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
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul']
        };
    </script>
</body>
</html>
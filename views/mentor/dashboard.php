<?php
require_once __DIR__ . '/../../templates/header_mentor.php';

// Data dinamis - dalam implementasi nyata, ini akan diambil dari database (ini masih dummy ya)
$mentorName = $mentor['username'] ?? 'Budi';
$newRegistrations = $dashboardData['newRegistrations'] ?? 3;
$unreadMessages = $dashboardData['unreadMessages'] ?? 5;
$consistencyIncrease = $dashboardData['consistencyIncrease'] ?? 12;

$totalCourses = $dashboardData['totalCourses'] ?? 12;
$totalMentees = $dashboardData['totalMentees'] ?? 96;
$averageRating = $dashboardData['averageRating'] ?? 4.7;

$completionRate = $dashboardData['completionRate'] ?? 78;
$videoHours = $dashboardData['videoHours'] ?? 48;
$moduleCount = $dashboardData['moduleCount'] ?? 64;
$totalReviews = $dashboardData['totalReviews'] ?? 186;
$totalEarnings = $dashboardData['totalEarnings'] ?? 12400000; // dalam rupiah

$monthlyRegistrations = $dashboardData['monthlyRegistrations'] ?? [10, 20, 25, 22, 28, 24, 30];
$recentActivities = $dashboardData['recentActivities'] ?? [
    [
        'user' => 'Budi S.',
        'action' => 'mendaftar kursus "Kerajian Anyaman untuk Pemula"',
        'time' => '2 jam yang lalu',
        'avatar' => 'B'
    ],
    [
        'user' => 'Budi S.',
        'action' => 'mendaftar kursus "Kerajian Anyaman untuk Pemula"',
        'time' => '2 jam yang lalu',
        'avatar' => 'B'
    ]
];
?>

<!-- Welcome Banner -->
<div class="welcome-banner fade-in-up">
    <div class="welcome-title">Selamat datang kembali, <?php echo htmlspecialchars($mentorName); ?>!</div>
    <div class="welcome-text">
        Anda memiliki <span class="highlight"><?php echo $newRegistrations; ?> pendaftaran baru</span> 
        dan <span class="highlight"><?php echo $unreadMessages; ?> pesan</span> yang belum dibaca.
    </div>
    <div class="welcome-stats">
        Konsistensi kursus Anda meningkat sebesar <span class="highlight"><?php echo $consistencyIncrease; ?>%</span> bulan ini!
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card fade-in-up" style="animation-delay: 0.1s;">
        <div class="stat-title">Total Kursus</div>
        <div class="stat-number"><?php echo $totalCourses; ?></div>
        <div class="stat-label">Kursus</div>
        <div class="stat-badge">▲ 2 BARU</div>
    </div>
    
    <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
        <div class="stat-title">Total Mentee</div>
        <div class="stat-number"><?php echo $totalMentees; ?></div>
        <div class="stat-label">Mentee</div>
        <div class="stat-badge">▲ 12% MTM</div>
    </div>
    
    <div class="stat-card fade-in-up" style="animation-delay: 0.3s;">
        <div class="stat-title">Rating Rata-rata</div>
        <div class="stat-number"><?php echo number_format($averageRating, 1); ?></div>
        <div class="stat-label">Dari 5</div>
        <div class="stat-badge">▲ 0.2 MTM</div>
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
        <div class="summary-value"><?php echo $moduleCount; ?></div>
    </div>
    <div class="summary-item">
        <div class="summary-title">Total Ulasan</div>
        <div class="summary-value"><?php echo $totalReviews; ?></div>
    </div>
    <div class="summary-item">
        <div class="summary-title">Total Pendapatan</div>
        <div class="summary-value">Rp <?php echo number_format($totalEarnings / 1000000, 1); ?> jt</div>
    </div>
</div>

<!-- Bottom Grid -->
<div class="bottom-grid">
    <!-- Activities -->
    <div class="activity-card fade-in-up" style="animation-delay: 0.5s;">
        <h3 class="card-title">Aktivitas Terbaru</h3>
        <div class="activity-list">
            <?php foreach ($recentActivities as $index => $activity): ?>
            <div class="activity-item">
                <div class="activity-avatar" style="background: linear-gradient(135deg, #<?php echo sprintf('%06X', mt_rand(0, 0xFFFFFF)); ?>, #<?php echo sprintf('%06X', mt_rand(0, 0xFFFFFF)); ?>);">
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
        </div>
    </div>
    
    <!-- Chart -->
    <div class="chart-card fade-in-up" style="animation-delay: 0.6s;">
        <h3 class="card-title">Jumlah Pendaftaran</h3>
        <div class="chart-container">
            <canvas id="registrationChart"></canvas>
        </div>
    </div>
</div>

            </div>
        </main>
    </div> 

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_dashboard.js"></script>
    <script>
        window.dashboardData = {
            monthlyRegistrations: <?php echo json_encode($monthlyRegistrations); ?>,
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul']
        };
    </script>
</body>
</html>
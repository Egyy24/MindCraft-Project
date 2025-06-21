<?php

// Data analytics dengan fallback ke static data
$totalRegistrations = 78;
$growthPercentage = 12;
$monthlyData = [15, 18, 25, 12, 16, 22, 28, 19, 24, 17, 20, 26];

// Sample courses
$courses = [
    ['id' => 1, 'title' => 'Kerajian Anyaman untuk Pemula'],
    ['id' => 2, 'title' => 'Pengenalan Web Development'],
    ['id' => 3, 'title' => 'Strategi Pemasaran Digital'],
    ['id' => 4, 'title' => 'UI/UX Design Fundamentals'],
    ['id' => 5, 'title' => 'Digital Photography Basics']
];

// Filter parameters
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : 'all';
$selectedPeriod = isset($_GET['period']) ? $_GET['period'] : '30';

// Adjust data based on filters
if ($selectedCourse !== 'all') {
    $totalRegistrations = (int)($totalRegistrations * 0.7);
    $growthPercentage = (int)($growthPercentage * 0.8);
}

if ($selectedPeriod === '90') {
    $totalRegistrations = (int)($totalRegistrations * 0.8);
} elseif ($selectedPeriod === '180') {
    $totalRegistrations = (int)($totalRegistrations * 1.3);
} elseif ($selectedPeriod === '365') {
    $totalRegistrations = (int)($totalRegistrations * 2.1);
}

// Period labels
$periodLabels = [
    '30' => '30 Hari',
    '90' => '90 Hari', 
    '180' => '6 Bulan',
    '365' => '1 Tahun'
];

$currentPeriodLabel = isset($periodLabels[$selectedPeriod]) ? $periodLabels[$selectedPeriod] : '30 Hari';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Analitik Performa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_analitik.css">
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
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/courses.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/earnings.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/reviews.php">Ulasan & Feedback</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php" class="active">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Analitik Performa</h1>
            </div>
            <div class="content-body">
                <!-- Analytics Controls -->
                <div class="analytics-controls">
                    <span class="control-label">Tampilkan analitik untuk:</span>
                    <div class="custom-select">
                        <select id="courseSelect" name="course">
                            <option value="all" <?php echo $selectedCourse === 'all' ? 'selected' : ''; ?>>Semua Kursus</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['id']); ?>" 
                                        <?php echo $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="custom-select">
                        <select id="periodSelect" name="period">
                            <option value="30" <?php echo $selectedPeriod === '30' ? 'selected' : ''; ?>>30 Hari</option>
                            <option value="90" <?php echo $selectedPeriod === '90' ? 'selected' : ''; ?>>90 Hari</option>
                            <option value="180" <?php echo $selectedPeriod === '180' ? 'selected' : ''; ?>>6 Bulan</option>
                            <option value="365" <?php echo $selectedPeriod === '365' ? 'selected' : ''; ?>>1 Tahun</option>
                        </select>
                    </div>
                </div>

                <!-- Analytics Cards Grid -->
                <div class="analytics-grid">
                    <div class="analytics-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="analytics-card-title">Total Pendaftaran</div>
                        <div class="analytics-number"><?php echo number_format($totalRegistrations); ?></div>
                        <div class="analytics-label">Pendaftar dalam <?php echo $currentPeriodLabel; ?></div>
                        <div class="analytics-trend">▲<?php echo $growthPercentage; ?>%</div>
                    </div>
                    
                    <div class="analytics-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="analytics-card-title">Tingkat Konversi</div>
                        <div class="analytics-number"><?php echo min(100, max(0, (int)($totalRegistrations * 0.15))); ?>%</div>
                        <div class="analytics-label">Dari pengunjung ke pendaftar</div>
                        <div class="analytics-trend">▲<?php echo min(15, max(2, floor($totalRegistrations * 0.1))); ?>%</div>
                    </div>
                    
                    <div class="analytics-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="analytics-card-title">Revenue</div>
                        <div class="analytics-number">
                            <?php 
                            $revenue = $totalRegistrations * 299000;
                            if ($revenue >= 1000000): ?>
                                <?php echo number_format($revenue / 1000000, 1); ?>M
                            <?php elseif ($revenue >= 1000): ?>
                                <?php echo number_format($revenue / 1000, 0); ?>K
                            <?php else: ?>
                                <?php echo number_format($revenue); ?>
                            <?php endif; ?>
                        </div>
                        <div class="analytics-label">Estimasi pendapatan</div>
                        <div class="analytics-trend">▲<?php echo min(20, max(5, $growthPercentage + 3)); ?>%</div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-section fade-in-up" style="animation-delay: 0.4s;">
                    <div class="chart-header">
                        <h2 class="chart-title">Tren Pendaftaran Bulanan</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Detail Link -->
                <a href="/MindCraft-Project/views/mentor/analitik-detail.php?course=<?php echo urlencode($selectedCourse); ?>&period=<?php echo urlencode($selectedPeriod); ?>" 
                   class="detail-link fade-in-up" style="animation-delay: 0.5s;">
                    <span class="detail-link-text">Lihat Analitik Detail Keterlibatan Mentee</span>
                    <span class="detail-link-arrow">→</span>
                </a>

            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_analitik.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.analyticsData = {
            monthlyData: <?php echo json_encode($monthlyData); ?>,
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            totalRegistrations: <?php echo (int)$totalRegistrations; ?>,
            growthPercentage: <?php echo (int)$growthPercentage; ?>
        };
    </script>
</body>
</html>
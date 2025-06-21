<?php
// Lokasi: MindCraft-Project/views/mentor/analitik-detail.php

// Static data untuk analytics detail
$totalMentees = 96;
$activeMentees = 78;
$completionRate = 67;
$avgTimeSpent = 45;

$courseEngagement = [
    ['course_name' => 'Kerajian Anyaman untuk Pemula', 'engagement' => 85, 'completion' => 72],
    ['course_name' => 'Pengenalan Web Development', 'engagement' => 78, 'completion' => 65],
    ['course_name' => 'Strategi Pemasaran Digital', 'engagement' => 92, 'completion' => 88]
];

$weeklyActivity = [12, 18, 15, 22, 25, 20, 19];

$menteeProgress = [
    ['name' => 'Budi Santoso', 'progress' => 85, 'lastActive' => '2 jam lalu', 'course' => 'Web Development'],
    ['name' => 'Siti Aminah', 'progress' => 92, 'lastActive' => '1 hari lalu', 'course' => 'Anyaman'],
    ['name' => 'Ahmad Rahman', 'progress' => 67, 'lastActive' => '3 hari lalu', 'course' => 'Digital Marketing'],
    ['name' => 'Maya Putri', 'progress' => 78, 'lastActive' => '5 jam lalu', 'course' => 'Web Development'],
    ['name' => 'Rizki Pratama', 'progress' => 95, 'lastActive' => '1 jam lalu', 'course' => 'Anyaman']
];

// Filter parameters
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : 'all';
$selectedPeriod = isset($_GET['period']) ? $_GET['period'] : '30';

// Courses list untuk dropdown
$courses = [
    ['id' => 1, 'title' => 'Kerajian Anyaman untuk Pemula'],
    ['id' => 2, 'title' => 'Pengenalan Web Development'], 
    ['id' => 3, 'title' => 'Strategi Pemasaran Digital'],
    ['id' => 4, 'title' => 'UI/UX Design Fundamentals'],
    ['id' => 5, 'title' => 'Digital Photography Basics']
];

// Helper function untuk status badge
function getProgressStatus($progress) {
    if ($progress >= 80) return ['Excellent', 'status-excellent'];
    if ($progress >= 60) return ['Good', 'status-good'];
    return ['Need Support', 'status-support'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Detail Keterlibatan Mentee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_analitik_detail.css">
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
                <li><a href="/MindCraft-Project/views/mentor/settings.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <div class="header-content">
                    <div class="breadcrumb">
                        <a href="/MindCraft-Project/views/mentor/analitik.php" class="breadcrumb-link">Analitik</a>
                        <span class="breadcrumb-separator">></span>
                        <span class="breadcrumb-current">Detail Keterlibatan Mentee</span>
                    </div>
                    <h1>Detail Keterlibatan Mentee</h1>
                </div>
            </div>
            
            <div class="content-body">
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <span class="control-label">Filter berdasarkan:</span>
                    <div class="custom-select">
                        <select id="courseSelect" name="course">
                            <option value="all" <?php echo $selectedCourse === 'all' ? 'selected' : ''; ?>>Semua Kursus</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="custom-select">
                        <select id="periodSelect" name="period">
                            <option value="7" <?php echo $selectedPeriod === '7' ? 'selected' : ''; ?>>7 Hari</option>
                            <option value="30" <?php echo $selectedPeriod === '30' ? 'selected' : ''; ?>>30 Hari</option>
                            <option value="90" <?php echo $selectedPeriod === '90' ? 'selected' : ''; ?>>90 Hari</option>
                        </select>
                    </div>
                </div>

                <!-- Overview Cards -->
                <div class="overview-grid">
                    <div class="overview-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="card-icon">👥</div>
                        <div class="card-content">
                            <div class="card-title">Total Mentee</div>
                            <div class="card-number"><?php echo number_format($totalMentees); ?></div>
                            <div class="card-subtitle">Terdaftar aktif</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="card-icon">⚡</div>
                        <div class="card-content">
                            <div class="card-title">Mentee Aktif</div>
                            <div class="card-number"><?php echo number_format($activeMentees); ?></div>
                            <div class="card-subtitle">7 hari terakhir</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="card-icon">📈</div>
                        <div class="card-content">
                            <div class="card-title">Tingkat Penyelesaian</div>
                            <div class="card-number"><?php echo $completionRate; ?>%</div>
                            <div class="card-subtitle">Rata-rata semua kursus</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.4s;">
                        <div class="card-icon">⏱️</div>
                        <div class="card-content">
                            <div class="card-title">Waktu Belajar</div>
                            <div class="card-number"><?php echo $avgTimeSpent; ?> min</div>
                            <div class="card-subtitle">Rata-rata per sesi</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <!-- Weekly Activity Chart -->
                    <div class="chart-card fade-in-up" style="animation-delay: 0.5s;">
                        <div class="chart-header">
                            <h3>Aktivitas Mingguan Mentee</h3>
                            <p>Jumlah mentee aktif per hari dalam 7 hari terakhir</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="weeklyActivityChart"></canvas>
                        </div>
                    </div>

                    <!-- Course Engagement Chart -->
                    <div class="chart-card fade-in-up" style="animation-delay: 0.6s;">
                        <div class="chart-header">
                            <h3>Keterlibatan per Kursus</h3>
                            <p>Tingkat engagement dan completion rate setiap kursus</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="courseEngagementChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Mentee Progress Table -->
                <div class="progress-section fade-in-up" style="animation-delay: 0.7s;">
                    <div class="section-header">
                        <h3>Progress Individual Mentee</h3>
                        <p>Daftar mentee dengan progress dan aktivitas terbaru</p>
                    </div>
                    
                    <div class="progress-table-container">
                        <table class="progress-table">
                            <thead>
                                <tr>
                                    <th>Nama Mentee</th>
                                    <th>Kursus</th>
                                    <th>Progress</th>
                                    <th>Terakhir Aktif</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menteeProgress as $mentee): ?>
                                <tr>
                                    <td>
                                        <div class="mentee-info">
                                            <div class="mentee-avatar"><?php echo strtoupper(substr($mentee['name'], 0, 1)); ?></div>
                                            <span class="mentee-name"><?php echo htmlspecialchars($mentee['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="course-badge"><?php echo htmlspecialchars($mentee['course']); ?></span>
                                    </td>
                                    <td>
                                        <div class="progress-cell">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $mentee['progress']; ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo $mentee['progress']; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="last-active"><?php echo htmlspecialchars($mentee['lastActive']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        list($statusText, $statusClass) = getProgressStatus($mentee['progress']);
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section fade-in-up" style="animation-delay: 0.8s;">
                    <button class="action-btn primary">
                        <span class="btn-icon">📧</span>
                        Kirim Motivasi ke Mentee
                    </button>
                    <button class="action-btn secondary">
                        <span class="btn-icon">📊</span>
                        Export Data Analytics
                    </button>
                    <button class="action-btn secondary">
                        <span class="btn-icon">⚙️</span>
                        Atur Notifikasi Progress
                    </button>
                </div>

            </div> 
        </main>
    </div> 

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_analitik_detail.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.detailData = {
            weeklyActivity: <?php echo json_encode($weeklyActivity); ?>,
            courseEngagement: <?php echo json_encode($courseEngagement); ?>,
            weekLabels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
        };
    </script>
</body>
</html>
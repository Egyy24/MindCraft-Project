<?php
$mentorId = $_SESSION['mentor_id'] ?? 1;

// Sample detailed analytics data
$detailData = [
    'totalMentees' => 96,
    'activeMentees' => 78,
    'completionRate' => 67,
    'avgTimeSpent' => 45, 
    'courseEngagement' => [
        ['course_name' => 'Kerajian Anyaman untuk Pemula', 'engagement' => 85, 'completion' => 72],
        ['course_name' => 'Pengenalan Web Development', 'engagement' => 78, 'completion' => 65],
        ['course_name' => 'Strategi Pemasaran Digital', 'engagement' => 92, 'completion' => 88]
    ],
    'weeklyActivity' => [12, 18, 15, 22, 25, 20, 19],
    'menteeProgress' => [
        ['name' => 'Budi Santoso', 'progress' => 85, 'lastActive' => '2 jam lalu', 'course' => 'Web Development'],
        ['name' => 'Siti Aminah', 'progress' => 92, 'lastActive' => '1 hari lalu', 'course' => 'Anyaman'],
        ['name' => 'Ahmad Rahman', 'progress' => 67, 'lastActive' => '3 hari lalu', 'course' => 'Digital Marketing'],
        ['name' => 'Maya Putri', 'progress' => 78, 'lastActive' => '5 jam lalu', 'course' => 'Web Development'],
        ['name' => 'Rizki Pratama', 'progress' => 95, 'lastActive' => '1 jam lalu', 'course' => 'Anyaman']
    ]
];

// Filter parameters
$selectedCourse = $_GET['course'] ?? 'all';
$selectedPeriod = $_GET['period'] ?? '30';
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
                <li><a href="/MindCraft-Project/views/mentor/create-course.php">Buat Kursus Baru</a></li>
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
                            <option value="all">Semua Kursus</option>
                            <option value="1">Kerajian Anyaman untuk Pemula</option>
                            <option value="2">Pengenalan Web Development</option>
                            <option value="3">Strategi Pemasaran Digital</option>
                        </select>
                    </div>
                    
                    <div class="custom-select">
                        <select id="periodSelect" name="period">
                            <option value="7">7 Hari</option>
                            <option value="30" selected>30 Hari</option>
                            <option value="90">90 Hari</option>
                        </select>
                    </div>
                </div>

                <!-- Overview Cards -->
                <div class="overview-grid">
                    <div class="overview-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="card-icon">👥</div>
                        <div class="card-content">
                            <div class="card-title">Total Mentee</div>
                            <div class="card-number"><?php echo $detailData['totalMentees']; ?></div>
                            <div class="card-subtitle">Terdaftar aktif</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="card-icon">⚡</div>
                        <div class="card-content">
                            <div class="card-title">Mentee Aktif</div>
                            <div class="card-number"><?php echo $detailData['activeMentees']; ?></div>
                            <div class="card-subtitle">7 hari terakhir</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="card-icon">📈</div>
                        <div class="card-content">
                            <div class="card-title">Tingkat Penyelesaian</div>
                            <div class="card-number"><?php echo $detailData['completionRate']; ?>%</div>
                            <div class="card-subtitle">Rata-rata semua kursus</div>
                        </div>
                    </div>
                    
                    <div class="overview-card fade-in-up" style="animation-delay: 0.4s;">
                        <div class="card-icon">⏱️</div>
                        <div class="card-content">
                            <div class="card-title">Waktu Belajar</div>
                            <div class="card-number"><?php echo $detailData['avgTimeSpent']; ?> min</div>
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
                                <?php foreach ($detailData['menteeProgress'] as $mentee): ?>
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
                                        $status = $mentee['progress'] >= 80 ? 'Excellent' : ($mentee['progress'] >= 60 ? 'Good' : 'Need Support');
                                        $statusClass = $mentee['progress'] >= 80 ? 'status-excellent' : ($mentee['progress'] >= 60 ? 'status-good' : 'status-support');
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
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
            weeklyActivity: <?php echo json_encode($detailData['weeklyActivity']); ?>,
            courseEngagement: <?php echo json_encode($detailData['courseEngagement']); ?>,
            weekLabels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
        };
    </script>
</body>
</html>
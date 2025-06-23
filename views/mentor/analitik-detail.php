<?php
// Include database connection dan controller
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

try {
    // Initialize database dan controller
    $database = new Database();
    $controller = new MentorController($database);
    
    $mentorId = $_SESSION['mentor_id'] ?? 1;
    
    // Filter parameters
    $selectedCourse = isset($_GET['course']) ? $_GET['course'] : 'all';
    $selectedPeriod = isset($_GET['period']) ? $_GET['period'] : '30';

    // Get real data dari database 
    $detailData = $controller->getAnalyticsDetailData($mentorId, $selectedCourse, $selectedPeriod);
    
    // Extract data untuk template
    $totalMentees = $detailData['totalMentees'] ?? 0;
    $activeMentees = $detailData['activeMentees'] ?? 0;
    $completionRate = $detailData['completionRate'] ?? 0;
    $avgTimeSpent = $detailData['avgTimeSpent'] ?? 0;
    $courseEngagement = $detailData['courseEngagement'] ?? [];
    $weeklyActivity = $detailData['weeklyActivity'] ?? array_fill(0, 7, 0);
    $menteeProgress = $detailData['menteeProgress'] ?? [];

    // Get courses dari database
    $courses = $database->fetchAll("
        SELECT id, title 
        FROM courses 
        WHERE mentor_id = ?
        ORDER BY title
    ", [$mentorId]);

} catch (Exception $e) {
    error_log("Analytics detail page error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data analitik detail.";
    
    // Set default empty values 
    $totalMentees = 0;
    $activeMentees = 0;
    $completionRate = 0;
    $avgTimeSpent = 0;
    $courseEngagement = [];
    $weeklyActivity = array_fill(0, 7, 0);
    $menteeProgress = [];
    $courses = [];
    $selectedCourse = 'all';
    $selectedPeriod = '30';
}

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
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_analitik-detail.css">
    
    <!-- Additional inline CSS to ensure sidebar appears -->
    <style>
        .sidebar {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .sidebar-menu {
            display: block !important;
        }
        
        .sidebar-menu li {
            display: block !important;
        }
        
        .sidebar-menu li a {
            display: block !important;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="logo">MindCraft</div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    </header>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php" class="active">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/logout.php">Logout</a></li>
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
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Controls -->
                <div class="filter-controls">
                    <span class="control-label">Filter berdasarkan:</span>
                    <div class="custom-select">
                        <select id="courseSelect" name="course">
                            <option value="all" <?php echo $selectedCourse === 'all' ? 'selected' : ''; ?>>Semua Kursus</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['id']); ?>" <?php echo $selectedCourse == $course['id'] ? 'selected' : ''; ?>>
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
                        <?php if (count($menteeProgress) > 0): ?>
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
                        <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 40px; color: #718096;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                            <h3 style="margin-bottom: 8px; color: #2D3748;">Belum Ada Data Mentee</h3>
                            <p>Data progress mentee akan muncul setelah ada siswa yang mendaftar kursus Anda.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section fade-in-up" style="animation-delay: 0.8s;">
                    <button class="action-btn primary">
                        <span class="btn-icon">📊</span>
                        Export Data Analytics
                    </button>
                </div>

            </div> 
        </main>
    </div> 

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_analitik-detail.js"></script>
    <script>
        // Pass real PHP data to JavaScript
        window.detailData = {
            weeklyActivity: <?php echo json_encode($weeklyActivity); ?>,
            courseEngagement: <?php echo json_encode($courseEngagement); ?>,
            weekLabels: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
        };

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const courseSelect = document.getElementById('courseSelect');
            const periodSelect = document.getElementById('periodSelect');

            function applyFilters() {
                const params = new URLSearchParams();
                if (courseSelect.value !== 'all') params.set('course', courseSelect.value);
                if (periodSelect.value !== '30') params.set('period', periodSelect.value);
                
                window.location.search = params.toString();
            }

            courseSelect.addEventListener('change', applyFilters);
            periodSelect.addEventListener('change', applyFilters);

            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');

            if (mobileMenuToggle && sidebar) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
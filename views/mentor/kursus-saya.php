<?php

// Simulasi session mentor
session_start();
if (!isset($_SESSION['mentor_id'])) {
    $_SESSION['mentor_id'] = 1;
}

$mentorId = $_SESSION['mentor_id'];
$mentorName = 'Budi Mentor';

// Sample courses data - dalam implementasi nyata akan diambil dari database
$courses = [
    [
        'id' => 1,
        'title' => 'Kursus Memasak : Sop Buntut (Oxtail Soup)',
        'category' => 'Kuliner',
        'status' => 'Published',
        'mentees' => 48,
        'modules' => 5,
        'rating' => 4.8,
        'earnings' => 1200000,
        'created_at' => '2024-10-15',
        'updated_at' => '2024-11-20',
        'description' => 'Belajar membuat sop buntut yang lezat dan bergizi dengan teknik tradisional.',
        'difficulty' => 'Pemula',
        'duration_hours' => 3,
        'price' => 299000
    ],
    [
        'id' => 2,
        'title' => 'Kursus Memasak : Rendang Padang Asli',
        'category' => 'Kuliner',
        'status' => 'Published',
        'mentees' => 62,
        'modules' => 7,
        'rating' => 4.9,
        'earnings' => 1850000,
        'created_at' => '2024-09-10',
        'updated_at' => '2024-11-18',
        'description' => 'Pelajari resep rendang Padang yang autentik dengan bumbu tradisional.',
        'difficulty' => 'Menengah',
        'duration_hours' => 5,
        'price' => 399000
    ],
    [
        'id' => 3,
        'title' => 'Dasar-dasar Fotografi Makanan',
        'category' => 'Fotografi',
        'status' => 'Draft',
        'mentees' => 0,
        'modules' => 4,
        'rating' => 0,
        'earnings' => 0,
        'created_at' => '2024-11-01',
        'updated_at' => '2024-11-15',
        'description' => 'Teknik fotografi makanan untuk media sosial dan komersial.',
        'difficulty' => 'Pemula',
        'duration_hours' => 4,
        'price' => 249000
    ],
    [
        'id' => 4,
        'title' => 'Bisnis Kuliner Online',
        'category' => 'Bisnis',
        'status' => 'Published',
        'mentees' => 35,
        'modules' => 8,
        'rating' => 4.7,
        'earnings' => 1050000,
        'created_at' => '2024-08-20',
        'updated_at' => '2024-11-10',
        'description' => 'Strategi membangun dan mengembangkan bisnis kuliner online.',
        'difficulty' => 'Menengah',
        'duration_hours' => 6,
        'price' => 499000
    ],
    [
        'id' => 5,
        'title' => 'Kerajinan Anyaman Bambu',
        'category' => 'Kerajinan',
        'status' => 'Published',
        'mentees' => 23,
        'modules' => 6,
        'rating' => 4.6,
        'earnings' => 690000,
        'created_at' => '2024-07-15',
        'updated_at' => '2024-10-30',
        'description' => 'Seni anyaman bambu tradisional untuk produk fungsional.',
        'difficulty' => 'Pemula',
        'duration_hours' => 4,
        'price' => 199000
    ]
];

// Get unique categories for filter
$categories = array_unique(array_column($courses, 'category'));
sort($categories);

// Filter parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date';

// Apply filters
$filteredCourses = $courses;

if (!empty($searchQuery)) {
    $filteredCourses = array_filter($filteredCourses, function($course) use ($searchQuery) {
        return stripos($course['title'], $searchQuery) !== false || 
               stripos($course['category'], $searchQuery) !== false ||
               stripos($course['description'], $searchQuery) !== false;
    });
}

if ($categoryFilter !== 'all') {
    $filteredCourses = array_filter($filteredCourses, function($course) use ($categoryFilter) {
        return $course['category'] === $categoryFilter;
    });
}

if ($statusFilter !== 'all') {
    $filteredCourses = array_filter($filteredCourses, function($course) use ($statusFilter) {
        return strtolower($course['status']) === strtolower($statusFilter);
    });
}

// Apply sorting
switch ($sortBy) {
    case 'title':
        usort($filteredCourses, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        break;
    case 'mentees':
        usort($filteredCourses, function($a, $b) {
            return $b['mentees'] - $a['mentees'];
        });
        break;
    case 'earnings':
        usort($filteredCourses, function($a, $b) {
            return $b['earnings'] - $a['earnings'];
        });
        break;
    case 'rating':
        usort($filteredCourses, function($a, $b) {
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        });
        break;
    case 'date':
    default:
        usort($filteredCourses, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        break;
}

// Calculate statistics
$totalCourses = count($courses);
$publishedCourses = count(array_filter($courses, function($c) { return $c['status'] === 'Published'; }));
$draftCourses = count(array_filter($courses, function($c) { return $c['status'] === 'Draft'; }));
$totalMentees = array_sum(array_column($courses, 'mentees'));
$totalEarnings = array_sum(array_column($courses, 'earnings'));

// Format currency helper
function formatCurrency($amount) {
    if ($amount >= 1000000) {
        return 'Rp ' . number_format($amount / 1000000, 1) . 'jt';
    } elseif ($amount >= 1000) {
        return 'Rp ' . number_format($amount / 1000, 0) . 'k';
    } else {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

// Get status badge class
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'published':
            return 'status-published';
        case 'draft':
            return 'status-draft';
        case 'archived':
            return 'status-archived';
        default:
            return 'status-draft';
    }
}

// Format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Kursus Saya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_kursus-saya.css">
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
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php" class="active">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/reviews.php">Ulasan & Feedback</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Kursus Saya</h1>
            </div>
            
            <div class="content-body">
                <!-- Statistics Overview -->
                <div class="stats-overview" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 32px;">
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 24px; font-weight: 700; color: var(--primary-blue);" class="stat-total-courses"><?php echo $totalCourses; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Kursus</div>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 24px; font-weight: 700; color: var(--success-green);" class="stat-published"><?php echo $publishedCourses; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Dipublikasi</div>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 24px; font-weight: 700; color: var(--warning-orange);" class="stat-draft"><?php echo $draftCourses; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Draft</div>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 24px; font-weight: 700; color: var(--text-dark);" class="stat-mentees"><?php echo $totalMentees; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Mentee</div>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 24px; font-weight: 700; color: var(--success-green);" class="stat-earnings"><?php echo formatCurrency($totalEarnings); ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Pendapatan</div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="search-box">
                        <input type="text" id="searchCourse" placeholder="Cari kursus..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    
                    <div class="filter-select">
                        <select id="categoryFilter" name="category">
                            <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" 
                                        <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-select">
                        <select id="statusFilter" name="status">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Dipublikasi</option>
                            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="archived" <?php echo $statusFilter === 'archived' ? 'selected' : ''; ?>>Diarsipkan</option>
                        </select>
                    </div>

                    <div class="filter-select">
                        <select id="sortBy" name="sort">
                            <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Judul A-Z</option>
                            <option value="mentees" <?php echo $sortBy === 'mentees' ? 'selected' : ''; ?>>Terbanyak Mentee</option>
                            <option value="earnings" <?php echo $sortBy === 'earnings' ? 'selected' : ''; ?>>Pendapatan Tertinggi</option>
                            <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                        </select>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <span style="font-size: 14px; color: var(--text-muted);">
                            Menampilkan <?php echo count($filteredCourses); ?> dari <?php echo $totalCourses; ?> kursus
                        </span>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button onclick="exportCourseData()" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                            📊 Export Data
                        </button>
                        <a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php" class="btn-create-course">
                            ➕ Buat Kursus Baru
                        </a>
                    </div>
                </div>

                <!-- Courses Grid -->
                <div class="courses-grid">
                    <?php if (empty($filteredCourses)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <h3>Tidak ada kursus ditemukan</h3>
                            <p>
                                <?php if (!empty($searchQuery) || $categoryFilter !== 'all' || $statusFilter !== 'all'): ?>
                                    Coba ubah filter pencarian atau buat kursus baru
                                <?php else: ?>
                                    Mulai berbagi pengetahuan Anda dengan membuat kursus pertama
                                <?php endif; ?>
                            </p>
                            <a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php" class="btn-create-course">
                                ➕ Buat Kursus Baru
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filteredCourses as $index => $course): ?>
                            <div class="course-card fade-in-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="course-header">
                                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <span class="course-status <?php echo getStatusClass($course['status']); ?>">
                                        <?php echo htmlspecialchars($course['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="course-stats">
                                    <div class="stat-item">
                                        <div class="stat-label">Mentee</div>
                                        <div class="stat-value"><?php echo number_format($course['mentees']); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Modul</div>
                                        <div class="stat-value"><?php echo $course['modules']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="course-metrics">
                                    <div class="metric-item">
                                        <span class="metric-label">Rating:</span>
                                        <span class="metric-value"><?php echo number_format($course['rating'], 1); ?>/5</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Pendapatan:</span>
                                        <span class="metric-value"><?php echo formatCurrency($course['earnings']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="course-chart">
                                    <div class="chart-placeholder">
                                        <div class="chart-bars">
                                            <?php
                                            // Generate random chart bars for visual appeal
                                            for ($i = 0; $i < 6; $i++) {
                                                $height = rand(20, 90);
                                                echo "<div class=\"chart-bar\" style=\"height: {$height}%;\"></div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="course-actions">
                                    <button class="btn btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                                        ✏️ Edit
                                    </button>
                                </div>

                                <!-- Additional course info for detailed view -->
                                <div class="course-details" style="display: none;">
                                    <div class="course-description">
                                        <?php echo htmlspecialchars($course['description']); ?>
                                    </div>
                                    <div class="course-meta">
                                        <div><strong>Kategori:</strong> <?php echo htmlspecialchars($course['category']); ?></div>
                                        <div><strong>Tingkat:</strong> <?php echo htmlspecialchars($course['difficulty']); ?></div>
                                        <div><strong>Durasi:</strong> <?php echo $course['duration_hours']; ?> jam</div>
                                        <div><strong>Harga:</strong> <?php echo formatCurrency($course['price']); ?></div>
                                        <div><strong>Dibuat:</strong> <?php echo formatDate($course['created_at']); ?></div>
                                        <div><strong>Diperbarui:</strong> <?php echo formatDate($course['updated_at']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <?php if (!empty($courses)): ?>
                <div class="quick-actions" style="margin-top: 32px; padding: 24px; background: white; border-radius: 12px; border: 1px solid var(--border-color);">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: var(--text-dark);">Aksi Cepat</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <button onclick="showCourseAnalytics()" class="btn btn-secondary" style="justify-content: flex-start;">
                            📈 Lihat Analitik Semua Kursus
                        </button>
                        <button onclick="bulkPublishDrafts()" class="btn btn-secondary" style="justify-content: flex-start;">
                            🚀 Publikasi Semua Draft
                        </button>
                        <button onclick="downloadCourseReport()" class="btn btn-secondary" style="justify-content: flex-start;">
                            📋 Download Laporan
                        </button>
                        <button onclick="manageCourseCategories()" class="btn btn-secondary" style="justify-content: flex-start;">
                            🏷️ Kelola Kategori
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Course Management Tips -->
                <div class="tips-section" style="margin-top: 32px; padding: 24px; background: linear-gradient(135deg, rgba(58, 89, 209, 0.05), rgba(144, 199, 248, 0.05)); border-radius: 12px; border: 1px solid rgba(58, 89, 209, 0.1);">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: var(--primary-blue);">💡 Tips Mengelola Kursus</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">📚 Update Konten Berkala</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Perbarui materi kursus secara rutin untuk menjaga relevansi dan kualitas pembelajaran.</p>
                        </div>
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">💬 Interaksi dengan Mentee</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Respond aktif terhadap pertanyaan dan feedback mentee untuk meningkatkan engagement.</p>
                        </div>
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">📊 Monitor Performa</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Pantau analytics kursus untuk memahami pola pembelajaran dan optimasi konten.</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_kursus-saya.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.coursesData = <?php echo json_encode(array_values($filteredCourses)); ?>;
        window.allCourses = <?php echo json_encode(array_values($courses)); ?>;
        window.coursesStats = {
            total: <?php echo $totalCourses; ?>,
            published: <?php echo $publishedCourses; ?>,
            draft: <?php echo $draftCourses; ?>,
            totalMentees: <?php echo $totalMentees; ?>,
            totalEarnings: <?php echo $totalEarnings; ?>
        };

        // Quick action functions
        function showCourseAnalytics() {
            showNotification('Memuat analitik semua kursus...', 'info');
            setTimeout(() => {
                window.location.href = '/MindCraft-Project/views/mentor/analitik.php';
            }, 1000);
        }

        function bulkPublishDrafts() {
            const draftCourses = window.allCourses.filter(c => c.status === 'Draft');
            if (draftCourses.length === 0) {
                showNotification('Tidak ada kursus draft yang perlu dipublikasi', 'info');
                return;
            }

            if (confirm(`Publikasi ${draftCourses.length} kursus draft?`)) {
                showNotification(`Mempublikasi ${draftCourses.length} kursus...`, 'info');
                setTimeout(() => {
                    showNotification('Semua kursus draft berhasil dipublikasi!', 'success');
                    location.reload();
                }, 2000);
            }
        }

        function downloadCourseReport() {
            showNotification('Menyiapkan laporan kursus...', 'info');
            setTimeout(() => {
                // Generate and download report
                const reportData = generateCourseReport();
                downloadCSV(reportData, 'laporan-kursus-' + new Date().toISOString().split('T')[0] + '.csv');
                showNotification('Laporan berhasil didownload!', 'success');
            }, 1500);
        }

        function generateCourseReport() {
            const headers = ['Judul', 'Kategori', 'Status', 'Mentee', 'Rating', 'Pendapatan', 'Tanggal Dibuat'];
            const rows = window.allCourses.map(course => [
                course.title,
                course.category,
                course.status,
                course.mentees,
                course.rating,
                course.earnings,
                course.created_at
            ]);
            
            return [headers, ...rows]
                .map(row => row.map(field => `"${field}"`).join(','))
                .join('\n');
        }

        function downloadCSV(content, filename) {
            const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function manageCourseCategories() {
            showNotification('Fitur kelola kategori akan segera tersedia!', 'info');
        }

        // Auto-refresh feature
        let autoRefreshEnabled = false;
        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            if (autoRefreshEnabled) {
                showNotification('Auto-refresh diaktifkan (setiap 30 detik)', 'info');
                setInterval(() => {
                    if (autoRefreshEnabled) {
                        // In real implementation, fetch updated data from server
                        console.log('Auto-refreshing course data...');
                    }
                }, 30000);
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add auto-refresh toggle to action bar if needed
            // toggleAutoRefresh can be called from UI elements
        });
    </script>
</body>
</html>
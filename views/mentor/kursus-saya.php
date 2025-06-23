<?php
// Include database connection dan controller
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
try {
    // Initialize database dan controller
    $database = new Database();
    $db = $database->connect();
    $controller = new MentorController($database);
    
    $mentorId = $_SESSION['mentor_id'];
    
    // Get courses list
    $courses = $controller->getCoursesList($mentorId);
    
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
                return ($b['mentees'] ?? 0) - ($a['mentees'] ?? 0);
            });
            break;
        case 'earnings':
            usort($filteredCourses, function($a, $b) {
                return ($b['earnings'] ?? 0) - ($a['earnings'] ?? 0);
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
    
} catch (Exception $e) {
    error_log("Courses page error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data kursus.";
    $courses = [];
    $filteredCourses = [];
    $categories = [];
    $totalCourses = 0;
    $publishedCourses = 0;
    $draftCourses = 0;
    $totalMentees = 0;
    $totalEarnings = 0;
}

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
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php" class="active">Kursus Saya</a></li>
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
                <h1>Kursus Saya</h1>
            </div>
            
            <div class="content-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

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
                            + Buat Kursus Baru
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
                                + Buat Kursus Baru
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
                                        <div class="stat-value"><?php echo number_format($course['mentees'] ?? 0); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Modul</div>
                                        <div class="stat-value"><?php echo $course['modules'] ?? 0; ?></div>
                                    </div>
                                </div>
                                
                                <div class="course-metrics">
                                    <div class="metric-item">
                                        <span class="metric-label">Rating:</span>
                                        <span class="metric-value"><?php echo ($course['rating'] ?? 0) > 0 ? number_format($course['rating'], 1) : '0.0'; ?>/5</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Pendapatan:</span>
                                        <span class="metric-value"><?php echo formatCurrency($course['earnings'] ?? 0); ?></span>
                                    </div>
                                </div>
                                
                                <div class="course-chart">
                                    <div class="chart-placeholder">
                                        <div class="chart-bars">
                                            <?php
                                            // Generate chart bars based on actual data
                                            $menteeCount = $course['mentees'] ?? 0;
                                            $maxBars = 6;
                                            for ($i = 0; $i < $maxBars; $i++) {
                                                $height = $menteeCount > 0 ? rand(20, min(90, $menteeCount * 5)) : rand(5, 20);
                                                echo "<div class=\"chart-bar\" style=\"height: {$height}%;\"></div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="course-actions">
                                    <button class="btn btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-view" onclick="viewCourse(<?php echo $course['id']; ?>)">
                                        👁️ Lihat
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
                        
                        <a href="/MindCraft-Project/views/mentor/pendapatan.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2d3748; transition: all 0.2s ease;">
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
                // Here you would make an AJAX call to update the courses
                setTimeout(() => {
                    showNotification('Semua kursus draft berhasil dipublikasi!', 'success');
                    location.reload();
                }, 2000);
            }
        }

        function downloadCourseReport() {
            showNotification('Menyiapkan laporan kursus...', 'info');
            setTimeout(() => {
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
                course.mentees || 0,
                course.rating || 0,
                course.earnings || 0,
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

        function editCourse(courseId) {
            window.location.href = `/MindCraft-Project/views/mentor/edit-course.php?id=${courseId}`;
        }

        function viewCourse(courseId) {
            window.location.href = `/MindCraft-Project/views/mentor/view-kursus.php?id=${courseId}`;
        }

        function exportCourseData() {
            downloadCourseReport();
        }

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchCourse');
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const sortBy = document.getElementById('sortBy');

            function applyFilters() {
                const params = new URLSearchParams();
                if (searchInput.value) params.set('search', searchInput.value);
                if (categoryFilter.value !== 'all') params.set('category', categoryFilter.value);
                if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
                if (sortBy.value !== 'date') params.set('sort', sortBy.value);
                
                window.location.search = params.toString();
            }

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });

            [categoryFilter, statusFilter, sortBy].forEach(element => {
                element.addEventListener('change', applyFilters);
            });
        });

        // Show notification function (implement this based on your notification system)
        function showNotification(message, type) {
            // This should be implemented based on your notification system
            console.log(`${type.toUpperCase()}: ${message}`);
            alert(message); // Simple fallback
        }
    </script>
</body>
</html>
<?php
// Include database connection dan controller
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';

// Start session
session_start();

// Check if user is logged in as mentor
if (!isset($_SESSION['mentor_id'])) {
    header('Location: /MindCraft-Project/views/login.php');
    exit();
}

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

try {
    // Initialize database dan controller
    $database = new Database();
    $db = $database->connect();
    $controller = new MentorController($database);
    
    $mentorId = $_SESSION['mentor_id'];
    $courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$courseId) {
        header('Location: /MindCraft-Project/views/mentor/kursus-saya.php');
        exit();
    }
    
    // Validate course ownership
    if (!$controller->validateCourseOwnership($courseId, $mentorId)) {
        header('Location: /MindCraft-Project/views/mentor/kursus-saya.php');
        exit();
    }
    
    // Get course data
    $course = getCourseData($courseId, $controller);
    $courseStats = getCourseStats($courseId, $controller);
    $students = getStudentsData($courseId, $controller);
    $reviews = getReviewsData($courseId, $controller);
    
} catch (Exception $e) {
    error_log("View course page error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data kursus.";
    $course = getDefaultCourseData($courseId);
    $courseStats = getDefaultCourseStats();
    $students = [];
    $reviews = getDefaultReviews();
}

/**
 * Get course data from database or return mock data
 */
function getCourseData($courseId, $controller) {
    try {
        // Try to get from database first
        if ($controller && method_exists($controller, 'getCourseById')) {
            $courseData = $controller->getCourseById($courseId);
            if ($courseData) {
                return $courseData;
            }
        }
        
        // Return mock data for demo
        return getDefaultCourseData($courseId);
        
    } catch (Exception $e) {
        error_log("Error getting course data: " . $e->getMessage());
        return getDefaultCourseData($courseId);
    }
}

/**
 * Get course statistics
 */
function getCourseStats($courseId, $controller) {
    try {
        if ($controller && method_exists($controller, 'getCourseStats')) {
            $stats = $controller->getCourseStats($courseId);
            if ($stats) {
                return $stats;
            }
        }
        
        return getDefaultCourseStats();
        
    } catch (Exception $e) {
        error_log("Error getting course stats: " . $e->getMessage());
        return getDefaultCourseStats();
    }
}

/**
 * Get students data
 */
function getStudentsData($courseId, $controller) {
    try {
        if ($controller && method_exists($controller, 'getCourseStudents')) {
            $students = $controller->getCourseStudents($courseId);
            if ($students) {
                return $students;
            }
        }
        
        return getDefaultStudents();
        
    } catch (Exception $e) {
        error_log("Error getting students data: " . $e->getMessage());
        return getDefaultStudents();
    }
}

/**
 * Get reviews data
 */
function getReviewsData($courseId, $controller) {
    try {
        if ($controller && method_exists($controller, 'getCourseReviews')) {
            $reviews = $controller->getCourseReviews($courseId);
            if ($reviews) {
                return $reviews;
            }
        }
        
        return getDefaultReviews();
        
    } catch (Exception $e) {
        error_log("Error getting reviews data: " . $e->getMessage());
        return getDefaultReviews();
    }
}

/**
 * Get default course data for demo
 */
function getDefaultCourseData($courseId) {
    return [
        'id' => $courseId,
        'title' => 'Kerajinan Anyaman untuk Pemula',
        'category' => 'Kerajinan & Seni',
        'difficulty' => 'Pemula',
        'description' => 'Pelajari seni anyaman tradisional Indonesia dari dasar hingga mahir. Kursus ini akan mengajarkan berbagai teknik anyaman menggunakan bahan alami seperti pandan, bambu, dan rotan. Cocok untuk pemula yang ingin belajar kerajinan tangan yang memiliki nilai ekonomi tinggi.',
        'price' => 299000,
        'duration_hours' => 12,
        'status' => 'Published',
        'cover_image' => '/MindCraft-Project/assets/images/courses/anyaman-cover.jpg',
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-06-20 15:45:00',
        'what_you_learn' => 'Memahami sejarah dan filosofi seni anyaman Indonesia|Menguasai teknik dasar anyaman dengan berbagai pola|Mampu membuat produk anyaman sederhana seperti tas dan tempat pensil|Memahami cara merawat dan mengawetkan hasil anyaman|Mengetahui peluang bisnis dari kerajinan anyaman',
        'requirements' => 'Tidak ada persyaratan khusus. Cocok untuk pemula yang ingin belajar kerajinan tangan.',
        'target_audience' => 'Pemula yang tertarik dengan kerajinan tradisional, ibu rumah tangga, dan siapa saja yang ingin mengembangkan keterampilan baru.',
        'tags' => 'kerajinan,anyaman,tradisional,handmade,indonesia,bambu,rotan'
    ];
}

/**
 * Get default course statistics
 */
function getDefaultCourseStats() {
    return [
        'total_students' => 125,
        'active_students' => 89,
        'completion_rate' => 72,
        'average_rating' => 4.8,
        'total_reviews' => 34,
        'total_earnings' => 2500000,
        'total_modules' => 8,
        'total_lessons' => 24
    ];
}

/**
 * Get default students data
 */
function getDefaultStudents() {
    return [
        [
            'id' => 1,
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@email.com',
            'enrolled_at' => '2024-06-01 10:00:00',
            'last_activity' => '2024-06-23 14:30:00',
            'progress' => 85
        ],
        [
            'id' => 2,
            'name' => 'Ahmad Rizki',
            'email' => 'ahmad@email.com',
            'enrolled_at' => '2024-06-03 15:20:00',
            'last_activity' => '2024-06-22 09:15:00',
            'progress' => 92
        ],
        [
            'id' => 3,
            'name' => 'Maya Putri',
            'email' => 'maya@email.com',
            'enrolled_at' => '2024-06-05 08:45:00',
            'last_activity' => '2024-06-23 11:20:00',
            'progress' => 67
        ],
        [
            'id' => 4,
            'name' => 'Budi Santoso',
            'email' => 'budi@email.com',
            'enrolled_at' => '2024-06-07 16:30:00',
            'last_activity' => '2024-06-21 13:45:00',
            'progress' => 45
        ],
        [
            'id' => 5,
            'name' => 'Dewi Lestari',
            'email' => 'dewi@email.com',
            'enrolled_at' => '2024-06-10 12:15:00',
            'last_activity' => '2024-06-23 16:10:00',
            'progress' => 78
        ]
    ];
}

/**
 * Get default reviews data
 */
function getDefaultReviews() {
    return [
        'summary' => [
            'average_rating' => 4.8,
            'total_reviews' => 34,
            'rating_breakdown' => [
                5 => 24,
                4 => 7,
                3 => 2,
                2 => 1,
                1 => 0
            ]
        ],
        'reviews' => [
            [
                'id' => 1,
                'student_name' => 'Siti Nurhaliza',
                'rating' => 5,
                'review_text' => 'Kursus yang sangat bagus! Penjelasan mentor sangat detail dan mudah dipahami. Sekarang saya sudah bisa membuat tas anyaman sendiri. Terima kasih!',
                'created_at' => '2024-06-20 14:30:00',
                'mentor_reply' => null
            ],
            [
                'id' => 2,
                'student_name' => 'Ahmad Rizki',
                'rating' => 5,
                'review_text' => 'Materi lengkap dan praktis. Video tutorialnya jelas dan bisa diulang-ulang. Recommended banget untuk yang mau belajar kerajinan anyaman!',
                'created_at' => '2024-06-18 09:15:00',
                'mentor_reply' => 'Terima kasih untuk reviewnya, Ahmad! Senang mendengar Anda puas dengan kursusnya.'
            ],
            [
                'id' => 3,
                'student_name' => 'Maya Putri',
                'rating' => 4,
                'review_text' => 'Kursus bagus tapi mungkin bisa ditambahkan lebih banyak variasi pola anyaman. Overall tetap recommend!',
                'created_at' => '2024-06-15 16:45:00',
                'mentor_reply' => null
            ],
            [
                'id' => 4,
                'student_name' => 'Dewi Lestari',
                'rating' => 5,
                'review_text' => 'Sangat puas dengan kursus ini! Mentor responsif dan materinya aplikatif. Sekarang saya sudah mulai jualan hasil anyaman saya.',
                'created_at' => '2024-06-12 11:20:00',
                'mentor_reply' => 'Wah, selamat Dewi! Senang sekali mendengar Anda sudah bisa berbisnis dari keterampilan yang dipelajari.'
            ]
        ]
    ];
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    if ($amount == 0) return 'Gratis';
    
    if ($amount >= 1000000) {
        return 'Rp ' . number_format($amount / 1000000, 1) . 'jt';
    } elseif ($amount >= 1000) {
        return 'Rp ' . number_format($amount / 1000, 0) . 'k';
    } else {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Get learning objectives array
 */
function getLearningObjectives($course) {
    if (empty($course['what_you_learn'])) {
        return [];
    }
    
    return explode('|', $course['what_you_learn']);
}

/**
 * Get course tags array
 */
function getCourseTags($course) {
    if (empty($course['tags'])) {
        return [];
    }
    
    return explode(',', $course['tags']);
}

/**
 * Get status class
 */
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

/**
 * Generate stars rating
 */
function generateStars($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    return str_repeat('★', $fullStars) . 
           ($hasHalfStar ? '☆' : '') . 
           str_repeat('☆', $emptyStars);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - <?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_view-kursus.css">
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
            <div class="content-header fade-in-up">
                <div class="header-info">
                    <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                    <span class="course-status <?php echo getStatusClass($course['status']); ?>">
                        <span><?php echo $course['status'] === 'Published' ? '🟢' : ($course['status'] === 'Draft' ? '🟡' : '🔴'); ?></span>
                        <?php echo htmlspecialchars($course['status']); ?>
                    </span>
                </div>
                <div class="header-actions">
                    <a href="/MindCraft-Project/views/mentor/kursus-saya.php" class="btn-back">
                        ← Kembali
                    </a>
                    <button class="btn btn-secondary" onclick="viewCoursePublic()">
                        👁️ Lihat Public
                    </button>
                    <button class="btn btn-primary" onclick="editCourse()">
                        ✏️ Edit Kursus
                    </button>
                    <button class="btn btn-success" onclick="toggleCourseStatus()">
                        <?php echo $course['status'] === 'Published' ? '📝 Jadikan Draft' : '🚀 Publikasi'; ?>
                    </button>
                </div>
            </div>

            <!-- Course Overview -->
            <div class="course-overview fade-in-up" style="animation-delay: 0.1s;">
                <div class="course-main">
                    <!-- Course Cover -->
                    <div class="course-cover">
                        <?php if (!empty($course['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars($course['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="color: var(--text-muted); text-align: center;">
                                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🖼️</div>
                                <div>Belum ada gambar sampul</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Course Info -->
                    <div class="course-info">
                        <h2 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h2>
                        
                        <div class="course-meta">
                            <div class="meta-item">
                                <span>📚</span>
                                <span><?php echo htmlspecialchars($course['category']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span>📊</span>
                                <span><?php echo htmlspecialchars($course['difficulty']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span>💰</span>
                                <span><?php echo formatCurrency($course['price']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span>🕒</span>
                                <span><?php echo $course['duration_hours']; ?> jam</span>
                            </div>
                            <div class="meta-item">
                                <span>📅</span>
                                <span>Dibuat <?php echo formatDate($course['created_at']); ?></span>
                            </div>
                        </div>

                        <div class="course-description">
                            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                        </div>

                        <!-- Learning Objectives -->
                        <div class="course-objectives">
                            <h3 class="objectives-title">Yang akan dipelajari:</h3>
                            <ul class="objectives-list">
                                <?php 
                                $objectives = getLearningObjectives($course);
                                if (empty($objectives)): 
                                ?>
                                    <li>Belum ada tujuan pembelajaran yang ditambahkan</li>
                                <?php else: ?>
                                    <?php foreach ($objectives as $objective): ?>
                                        <li><?php echo htmlspecialchars($objective); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Course Tags -->
                        <div class="course-tags">
                            <?php 
                            $tags = getCourseTags($course);
                            if (empty($tags)): 
                            ?>
                                <span class="tag">Belum ada tag</span>
                            <?php else: ?>
                                <?php foreach ($tags as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Course Statistics Sidebar -->
                <div class="course-stats">
                    <h3 class="stats-title">Statistik Kursus</h3>
                    
                    <div class="stat-item">
                        <div class="stat-label">Total Siswa</div>
                        <div class="stat-value" data-stat="total-students"><?php echo number_format($courseStats['total_students']); ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Siswa Aktif</div>
                        <div class="stat-value success" data-stat="active-students"><?php echo number_format($courseStats['active_students']); ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Tingkat Selesai</div>
                        <div class="stat-value" data-stat="completion-rate"><?php echo $courseStats['completion_rate']; ?>%</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Rating Rata-rata</div>
                        <div class="stat-value success" data-stat="average-rating"><?php echo $courseStats['average_rating']; ?>/5</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Total Ulasan</div>
                        <div class="stat-value" data-stat="total-reviews"><?php echo number_format($courseStats['total_reviews']); ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-value success" data-stat="total-earnings"><?php echo formatCurrency($courseStats['total_earnings']); ?></div>
                    </div>

                    <!-- Performance Chart -->
                    <div class="performance-chart">
                        <h4 class="chart-title">Performa 7 Hari Terakhir</h4>
                        <div class="chart-container">
                            <div class="chart-bars">
                                <!-- Chart bars akan di-generate oleh JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Tabs -->
            <div class="content-tabs fade-in-up" style="animation-delay: 0.2s;">
                <div class="tabs-header">
                    <button class="tab-button active" data-tab="overview">📋 Overview</button>
                    <button class="tab-button" data-tab="students">👥 Siswa (<?php echo count($students); ?>)</button>
                    <button class="tab-button" data-tab="reviews">⭐ Ulasan (<?php echo $reviews['summary']['total_reviews']; ?>)</button>
                    <button class="tab-button" data-tab="analytics">📈 Analitik</button>
                    <button class="tab-button" data-tab="settings">⚙️ Pengaturan</button>
                </div>

                <!-- Overview Tab -->
                <div class="tab-content active" id="overviewTab">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Aksi Cepat</h3>
                            <div style="display: grid; gap: 1rem;">
                                <button class="btn btn-primary" onclick="editCourse()" style="justify-content: flex-start;">
                                    ✏️ Edit Kursus
                                </button>
                                <button class="btn btn-secondary" onclick="sendMessage()" style="justify-content: flex-start;">
                                    💬 Kirim Pesan ke Siswa
                                </button>
                                <button class="btn btn-secondary" onclick="exportCourseData()" style="justify-content: flex-start;">
                                    📊 Export Data
                                </button>
                                <button class="btn btn-secondary" onclick="duplicateCourse()" style="justify-content: flex-start;">
                                    📋 Duplikasi Kursus
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Informasi Kursus</h3>
                            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                                <div style="margin-bottom: 1rem;">
                                    <strong>Status:</strong> 
                                    <span class="<?php echo getStatusClass($course['status']); ?>" style="margin-left: 0.5rem; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem;">
                                        <?php echo $course['status']; ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 1rem;">
                                    <strong>Dibuat:</strong> <?php echo formatDate($course['created_at']); ?>
                                </div>
                                <div style="margin-bottom: 1rem;">
                                    <strong>Terakhir diupdate:</strong> <?php echo formatDate($course['updated_at']); ?>
                                </div>
                                <div style="margin-bottom: 1rem;">
                                    <strong>Total Modul:</strong> <?php echo $courseStats['total_modules'] ?? 0; ?>
                                </div>
                                <div>
                                    <strong>Total Pelajaran:</strong> <?php echo $courseStats['total_lessons'] ?? 0; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="danger-zone">
                        <h4 class="danger-title">⚠️ Zona Berbahaya</h4>
                        <p style="margin-bottom: 1rem; color: var(--text-muted);">
                            Tindakan berikut tidak dapat dibatalkan. Pastikan Anda yakin sebelum melanjutkan.
                        </p>
                        <div style="display: flex; gap: 1rem;">
                            <button class="btn btn-danger" onclick="deleteCourse()">
                                🗑️ Hapus Kursus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Students Tab -->
                <div class="tab-content" id="studentsTab">
                    <div class="students-header">
                        <h3>Daftar Siswa</h3>
                        <div class="search-students">
                            <input type="text" class="search-input" placeholder="Cari siswa...">
                            <button class="btn btn-secondary" onclick="exportStudentsList()">
                                📊 Export
                            </button>
                            <button class="btn btn-primary" onclick="sendMessage()">
                                💬 Kirim Pesan
                            </button>
                        </div>
                    </div>

                    <div class="students-list">
                        <?php if (empty($students)): ?>
                            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                                <h3>Belum ada siswa terdaftar</h3>
                                <p>Promosikan kursus Anda untuk menarik lebih banyak siswa</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <div class="student-item" data-student-id="<?php echo $student['id']; ?>">
                                    <div class="student-avatar">
                                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                    </div>
                                    <div class="student-info">
                                        <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                                        <div class="student-meta">
                                            Bergabung <?php echo formatDate($student['enrolled_at']); ?> • 
                                            Terakhir aktif <?php echo formatDate($student['last_activity']); ?>
                                        </div>
                                    </div>
                                    <div class="student-progress">
                                        <div class="progress-percentage"><?php echo $student['progress']; ?>%</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $student['progress']; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div class="tab-content" id="reviewsTab">
                    <div class="reviews-header">
                        <h3>Ulasan & Rating</h3>
                        <div class="reviews-summary">
                            <div class="rating-overview">
                                <div class="avg-rating"><?php echo number_format($reviews['summary']['average_rating'], 1); ?></div>
                                <div class="rating-stars"><?php echo generateStars($reviews['summary']['average_rating']); ?></div>
                                <div class="total-reviews"><?php echo $reviews['summary']['total_reviews']; ?> ulasan</div>
                            </div>
                            
                            <div class="rating-breakdown">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <?php 
                                    $count = $reviews['summary']['rating_breakdown'][$i] ?? 0;
                                    $percentage = $reviews['summary']['total_reviews'] > 0 ? ($count / $reviews['summary']['total_reviews'] * 100) : 0;
                                    ?>
                                    <div class="rating-row">
                                        <div class="rating-label"><?php echo $i; ?></div>
                                        <div class="rating-bar">
                                            <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <div class="rating-count"><?php echo $count; ?></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="reviews-list">
                        <?php if (empty($reviews['reviews'])): ?>
                            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                                <h3>Belum ada ulasan</h3>
                                <p>Ulasan akan muncul setelah siswa menyelesaikan kursus</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews['reviews'] as $review): ?>
                                <div class="review-item" data-review-id="<?php echo $review['id']; ?>">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <?php echo strtoupper(substr($review['student_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="reviewer-name"><?php echo htmlspecialchars($review['student_name']); ?></div>
                                                <div class="review-rating"><?php echo generateStars($review['rating']); ?></div>
                                            </div>
                                        </div>
                                        <div class="review-date"><?php echo formatDate($review['created_at']); ?></div>
                                    </div>
                                    <div class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></div>
                                    
                                    <?php if ($review['mentor_reply']): ?>
                                        <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--border-radius); border-left: 4px solid var(--primary-blue);">
                                            <div style="font-weight: 600; color: var(--primary-blue); margin-bottom: 0.5rem;">Balasan Mentor:</div>
                                            <div><?php echo nl2br(htmlspecialchars($review['mentor_reply'])); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="review-actions">
                                        <button class="btn btn-secondary btn-sm" onclick="replyToReview(<?php echo $review['id']; ?>)">
                                            💬 Balas
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="reportReview(<?php echo $review['id']; ?>)">
                                            🚩 Laporkan
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div class="tab-content" id="analyticsTab">
                    <h3 style="margin-bottom: 2rem;">Analitik Kursus</h3>
                    
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h4 class="analytics-title">Tren Pendaftaran</h4>
                            <div class="analytics-chart">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📈</div>
                                    <div>Chart Pendaftaran</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h4 class="analytics-title">Tingkat Penyelesaian</h4>
                            <div class="analytics-chart">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                                    <div>Chart Penyelesaian</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h4 class="analytics-title">Engagement Siswa</h4>
                            <div class="analytics-chart">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                                    <div>Chart Engagement</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <h4 class="analytics-title">Pendapatan</h4>
                            <div class="analytics-chart">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
                                    <div>Chart Pendapatan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-content" id="settingsTab">
                    <h3 style="margin-bottom: 2rem;">Pengaturan Kursus</h3>
                    
                    <form class="settings-form" id="settingsForm">
                        <div class="settings-section">
                            <h4 class="settings-title">Visibilitas Kursus</h4>
                            <div class="form-group">
                                <label class="form-label">Status Publikasi</label>
                                <select class="form-control" name="status">
                                    <option value="Published" <?php echo $course['status'] === 'Published' ? 'selected' : ''; ?>>Dipublikasi</option>
                                    <option value="Draft" <?php echo $course['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="Archived" <?php echo $course['status'] === 'Archived' ? 'selected' : ''; ?>>Diarsipkan</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h4 class="settings-title">Notifikasi</h4>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="emailNotifications" name="email_notifications" checked>
                                    <label for="emailNotifications">Kirim notifikasi email untuk pendaftaran baru</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="reviewNotifications" name="review_notifications" checked>
                                    <label for="reviewNotifications">Kirim notifikasi untuk ulasan baru</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="completionNotifications" name="completion_notifications" checked>
                                    <label for="completionNotifications">Kirim notifikasi untuk penyelesaian kursus</label>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h4 class="settings-title">Pengaturan Siswa</h4>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="allowReviews" name="allow_reviews" checked>
                                    <label for="allowReviews">Izinkan siswa memberikan ulasan</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="allowDiscussion" name="allow_discussion" checked>
                                    <label for="allowDiscussion">Izinkan diskusi antar siswa</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="autoCertificate" name="auto_certificate">
                                    <label for="autoCertificate">Buat sertifikat otomatis setelah selesai</label>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                            <button type="button" class="btn btn-secondary">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="saveSettings()">
                                💾 Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_view-kursus.js"></script>
    <script>
        // Pass course data to JavaScript
        window.courseData = <?php echo json_encode($course); ?>;
        window.courseStats = <?php echo json_encode($courseStats); ?>;
        window.studentsData = <?php echo json_encode($students); ?>;
        window.reviewsData = <?php echo json_encode($reviews); ?>;
    </script>
</body>
</html>
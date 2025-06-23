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
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'save';
        
        $courseData = [
            'title' => trim($_POST['courseTitle'] ?? ''),
            'category' => $_POST['courseCategory'] ?? '',
            'difficulty' => $_POST['courseDifficulty'] ?? 'Pemula',
            'description' => trim($_POST['courseDescription'] ?? ''),
            'price' => (float)($_POST['coursePrice'] ?? 0),
            'is_premium' => isset($_POST['isPremium']) ? 1 : 0,
            'allow_reviews' => isset($_POST['allowReviews']) ? 1 : 0,
            'send_notifications' => isset($_POST['sendNotifications']) ? 1 : 0,
            'auto_certificate' => isset($_POST['autoCertificate']) ? 1 : 0,
            'requirements' => trim($_POST['courseRequirements'] ?? ''),
            'target_audience' => trim($_POST['targetAudience'] ?? ''),
            'status' => $action === 'publish' ? 'Published' : 'Draft'
        ];
        
        // Handle file upload
        if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleFileUpload($_FILES['coverImage'], $courseId);
            if ($uploadResult['success']) {
                $courseData['cover_image'] = $uploadResult['path'];
            }
        }
        
        // Handle learning objectives
        if (isset($_POST['learning_objectives'])) {
            $objectives = json_decode($_POST['learning_objectives'], true);
            if (is_array($objectives)) {
                $courseData['what_you_learn'] = implode('|', $objectives);
            }
        }
        
        // Update course
        $result = $controller->updateCourse($courseId, $mentorId, $courseData);
        
        if ($result) {
            if ($action === 'publish') {
                $_SESSION['success_message'] = 'Kursus berhasil dipublikasi!';
                header('Location: /MindCraft-Project/views/mentor/kursus-saya.php');
                exit();
            } else {
                $success_message = 'Perubahan berhasil disimpan!';
            }
        } else {
            $error_message = 'Gagal menyimpan perubahan. Silakan coba lagi.';
        }
    }
    
    // Get course data
    $course = getCourseData($courseId, $controller);
    
    // Get course categories
    $categories = $controller->getCourseCategories();
    
} catch (Exception $e) {
    error_log("Edit course page error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data kursus.";
    $course = getDefaultCourseData($courseId);
    $categories = getDefaultCategories();
}

/**
 * Get course data from database or return mock data
 */
function getCourseData($courseId, $controller) {
    try {
        // Try to get from database first
        if ($controller && method_exists($controller, 'getCourseById')) {
            $course = $controller->getCourseById($courseId);
            if ($course) {
                return $course;
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
 * Get default course data for demo
 */
function getDefaultCourseData($courseId) {
    return [
        'id' => $courseId,
        'title' => 'Kerajinan Anyaman untuk Pemula',
        'category' => 'Kerajinan',
        'difficulty' => 'Pemula',
        'description' => 'Pelajari seni anyaman tradisional Indonesia dari dasar hingga mahir. Kursus ini akan mengajarkan berbagai teknik anyaman menggunakan bahan alami seperti pandan, bambu, dan rotan.',
        'price' => 299000,
        'is_premium' => 0,
        'allow_reviews' => 1,
        'send_notifications' => 1,
        'auto_certificate' => 0,
        'requirements' => 'Tidak ada persyaratan khusus. Cocok untuk pemula yang ingin belajar kerajinan tangan.',
        'target_audience' => 'Pemula yang tertarik dengan kerajinan tradisional, ibu rumah tangga, dan siapa saja yang ingin mengembangkan keterampilan baru.',
        'what_you_learn' => 'Memahami sejarah dan filosofi seni anyaman Indonesia|Menguasai teknik dasar anyaman dengan berbagai pola|Mampu membuat produk anyaman sederhana seperti tas dan tempat pensil|Memahami cara merawat dan mengawetkan hasil anyaman',
        'cover_image' => '/MindCraft-Project/assets/images/courses/anyaman-cover.jpg',
        'status' => 'Draft',
        'created_at' => '2024-01-15 10:30:00',
        'updated_at' => '2024-06-20 15:45:00'
    ];
}

/**
 * Get default categories
 */
function getDefaultCategories() {
    return [
        ['id' => 1, 'name' => 'Pendidikan', 'slug' => 'pendidikan'],
        ['id' => 2, 'name' => 'UI/UX Design', 'slug' => 'ui-ux'],
        ['id' => 3, 'name' => 'Programming', 'slug' => 'programming'],
        ['id' => 4, 'name' => 'Bisnis & Marketing', 'slug' => 'bisnis'],
        ['id' => 5, 'name' => 'Kerajinan & Seni', 'slug' => 'kerajinan'],
        ['id' => 6, 'name' => 'Kesehatan & Kebugaran', 'slug' => 'kesehatan'],
        ['id' => 7, 'name' => 'Musik & Audio', 'slug' => 'musik'],
        ['id' => 8, 'name' => 'Fotografi & Video', 'slug' => 'fotografi'],
        ['id' => 9, 'name' => 'Bahasa', 'slug' => 'bahasa'],
        ['id' => 10, 'name' => 'Hobi & Lifestyle', 'slug' => 'hobi']
    ];
}

/**
 * Handle file upload
 */
function handleFileUpload($file, $courseId) {
    try {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Format file tidak didukung'];
        }
        
        // Validate file size (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar'];
        }
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../../uploads/courses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'course_' . $courseId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $relativePath = '/MindCraft-Project/uploads/courses/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'path' => $relativePath];
        } else {
            return ['success' => false, 'message' => 'Gagal mengupload file'];
        }
        
    } catch (Exception $e) {
        error_log("File upload error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mengupload'];
    }
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    if ($amount >= 1000000) {
        return 'Rp ' . number_format($amount / 1000000, 1) . 'jt';
    } elseif ($amount >= 1000) {
        return 'Rp ' . number_format($amount / 1000, 0) . 'k';
    } else {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Edit Kursus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_edit-course.css">
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
                <h1>Edit Kursus</h1>
                <div class="header-actions">
                    <a href="/MindCraft-Project/views/mentor/kursus-saya.php" class="btn-back">
                        ← Kembali ke Kursus Saya
                    </a>
                </div>
            </div>

            <!-- Auto Save Indicator -->
            <div class="auto-save-indicator" id="autoSaveIndicator">
                Tersimpan otomatis
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="success-message fade-in-up">
                    <span>✅</span> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message fade-in-up">
                    <span>❌</span> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <div class="edit-form-container fade-in-up">
                <div class="form-header">
                    <h2>Edit Kursus: <?php echo htmlspecialchars($course['title']); ?></h2>
                    <p>Perbarui informasi kursus Anda dan tingkatkan kualitas pembelajaran</p>
                </div>

                <div class="form-body">
                    <form id="editCourseForm" method="POST" enctype="multipart/form-data">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">Informasi Dasar</h3>
                            
                            <div class="form-group">
                                <label for="courseTitle" class="form-label required">Judul Kursus</label>
                                <input type="text" id="courseTitle" name="courseTitle" class="form-control" 
                                       value="<?php echo htmlspecialchars($course['title']); ?>" 
                                       placeholder="Contoh: Belajar Web Development dari Dasar" 
                                       required maxlength="100" data-name="Judul kursus">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="courseCategory" class="form-label required">Kategori</label>
                                    <select id="courseCategory" name="courseCategory" class="form-control" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                                    <?php echo $course['category'] === $category['name'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="courseDifficulty" class="form-label required">Tingkat Kesulitan</label>
                                    <select id="courseDifficulty" name="courseDifficulty" class="form-control" required>
                                        <option value="Pemula" <?php echo $course['difficulty'] === 'Pemula' ? 'selected' : ''; ?>>Pemula</option>
                                        <option value="Menengah" <?php echo $course['difficulty'] === 'Menengah' ? 'selected' : ''; ?>>Menengah</option>
                                        <option value="Mahir" <?php echo $course['difficulty'] === 'Mahir' ? 'selected' : ''; ?>>Mahir</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="courseDescription" class="form-label required">Deskripsi Kursus</label>
                                <textarea id="courseDescription" name="courseDescription" class="form-control" 
                                          placeholder="Jelaskan tentang kursus Anda, apa yang akan dipelajari siswa, dan mengapa mereka harus memilih kursus ini..." 
                                          required minlength="20" maxlength="2000" rows="6"><?php echo htmlspecialchars($course['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="coursePrice" class="form-label">Harga Kursus (Rp)</label>
                                <input type="number" id="coursePrice" name="coursePrice" class="form-control" 
                                       value="<?php echo $course['price']; ?>" 
                                       placeholder="0" min="0" max="10000000" step="1000">
                                <small style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                                    Kosongkan atau isi 0 untuk kursus gratis
                                </small>
                            </div>
                        </div>

                        <!-- Cover Image Section -->
                        <div class="form-section">
                            <h3 class="section-title">Gambar Sampul</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Upload Gambar Sampul</label>
                                <div class="file-upload-area">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">Klik untuk upload atau drag & drop</div>
                                    <div class="upload-hint">JPG, PNG, GIF, WebP - Maksimal 5MB</div>
                                    <input type="file" id="coverImage" name="coverImage" class="file-input" 
                                           accept="image/jpeg,image/png,image/gif,image/webp">
                                </div>
                                
                                <?php if (!empty($course['cover_image'])): ?>
                                    <div class="current-image">
                                        <img src="<?php echo htmlspecialchars($course['cover_image']); ?>" alt="Course cover">
                                        <div class="image-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="changeImage()">
                                                Ganti Gambar
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Learning Objectives Section -->
                        <div class="form-section">
                            <h3 class="section-title">Tujuan Pembelajaran</h3>
                            
                            <div class="form-group">
                                <label class="form-label required">Apa yang akan dipelajari siswa?</label>
                                <div class="objectives-list">
                                    <?php 
                                    $objectives = getLearningObjectives($course);
                                    if (empty($objectives)): 
                                    ?>
                                        <div class="objective-item">
                                            <span style="color: var(--primary-blue); font-weight: bold;">•</span>
                                            <input type="text" class="objective-input" placeholder="Contoh: Mampu membuat aplikasi web sederhana" maxlength="200">
                                            <button type="button" class="btn-remove-objective" onclick="removeObjective(this)">×</button>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($objectives as $objective): ?>
                                            <div class="objective-item">
                                                <span style="color: var(--primary-blue); font-weight: bold;">•</span>
                                                <input type="text" class="objective-input" value="<?php echo htmlspecialchars($objective); ?>" maxlength="200">
                                                <button type="button" class="btn-remove-objective" onclick="removeObjective(this)">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn-add-objective">
                                    + Tambah Tujuan Pembelajaran
                                </button>
                            </div>
                        </div>

                        <!-- Course Details Section -->
                        <div class="form-section">
                            <h3 class="section-title">Detail Kursus</h3>
                            
                            <div class="form-group">
                                <label for="courseRequirements" class="form-label">Persyaratan</label>
                                <textarea id="courseRequirements" name="courseRequirements" class="form-control" 
                                          placeholder="Contoh: Tidak ada persyaratan khusus, cocok untuk pemula..." 
                                          maxlength="1000" rows="4"><?php echo htmlspecialchars($course['requirements']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="targetAudience" class="form-label">Target Audience</label>
                                <textarea id="targetAudience" name="targetAudience" class="form-control" 
                                          placeholder="Contoh: Pemula yang ingin belajar programming, mahasiswa IT, profesional yang ingin upgrade skill..." 
                                          maxlength="1000" rows="4"><?php echo htmlspecialchars($course['target_audience']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tags</label>
                                <div class="tags-container">
                                    <input type="text" class="tag-input" placeholder="Ketik tag dan tekan Enter...">
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                                    Tambahkan tag yang relevan untuk membantu siswa menemukan kursus Anda (maksimal 10 tag)
                                </small>
                            </div>
                        </div>

                        <!-- Settings Section -->
                        <div class="form-section">
                            <h3 class="section-title">Pengaturan Kursus</h3>
                            
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="isPremium" name="isPremium" 
                                           <?php echo $course['is_premium'] ? 'checked' : ''; ?>>
                                    <label for="isPremium">Kursus Premium</label>
                                </div>
                                
                                <div class="checkbox-item">
                                    <input type="checkbox" id="allowReviews" name="allowReviews" 
                                           <?php echo $course['allow_reviews'] ? 'checked' : ''; ?>>
                                    <label for="allowReviews">Izinkan Review & Rating</label>
                                </div>
                                
                                <div class="checkbox-item">
                                    <input type="checkbox" id="sendNotifications" name="sendNotifications" 
                                           <?php echo $course['send_notifications'] ? 'checked' : ''; ?>>
                                    <label for="sendNotifications">Kirim Notifikasi ke Siswa</label>
                                </div>
                                
                                <div class="checkbox-item">
                                    <input type="checkbox" id="autoCertificate" name="autoCertificate" 
                                           <?php echo $course['auto_certificate'] ? 'checked' : ''; ?>>
                                    <label for="autoCertificate">Sertifikat Otomatis</label>
                                </div>
                            </div>
                        </div>

                        <!-- Course Preview -->
                        <div class="course-preview">
                            <div class="preview-header">
                                Preview Kursus
                            </div>
                            <div class="preview-content">
                                <div class="preview-image">
                                    <?php if (!empty($course['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($course['cover_image']); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">
                                    <?php else: ?>
                                        Belum ada gambar
                                    <?php endif; ?>
                                </div>
                                <div class="preview-details">
                                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <div class="preview-meta">
                                        <span>📚 <?php echo htmlspecialchars($course['category']); ?></span>
                                        <span>📊 <?php echo htmlspecialchars($course['difficulty']); ?></span>
                                        <span>💰 <?php echo formatCurrency($course['price']); ?></span>
                                    </div>
                                    <div class="preview-description">
                                        <?php echo htmlspecialchars(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="goBackToCourses()">
                                Batal
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="previewCourse()">
                                👁️ Preview
                            </button>
                            <button type="submit" name="action" value="save" class="btn btn-primary" data-action="save">
                                💾 Simpan Perubahan
                            </button>
                            <button type="submit" name="action" value="publish" class="btn btn-success" data-action="publish">
                                🚀 Simpan & Publikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_edit-course.js"></script>
    <script>
        // Initialize with existing course data
        window.courseData = <?php echo json_encode($course); ?>;
        window.courseCategories = <?php echo json_encode($categories); ?>;
        
        // Populate existing tags if any
        document.addEventListener('DOMContentLoaded', function() {
            // Add some demo tags for existing course
            const demoTags = ['kerajinan', 'anyaman', 'tradisional', 'handmade', 'indonesia'];
            demoTags.forEach(tag => {
                addTag(tag);
            });
            
            // Setup objective input change listeners
            document.querySelectorAll('.objective-input').forEach(input => {
                input.addEventListener('input', () => {
                    unsavedChanges = true;
                });
            });
        });
    </script>
</body>
</html>
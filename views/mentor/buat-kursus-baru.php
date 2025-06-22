<?php
// views/mentor/buat-kursus-baru.php

// Include database connection
require_once __DIR__ . '/../../config/Database.php';

// Session handling
session_start();
if (!isset($_SESSION['mentor_id'])) {
    header('Location: /MindCraft-Project/views/auth/login.php');
    exit();
}

$mentorId = $_SESSION['mentor_id'];

try {
    // Initialize database
    $database = new Database();
    $db = $database->connect();
    
    // Get categories from database
    $stmt = $db->prepare("SELECT name, slug FROM course_categories WHERE is_active = 1 ORDER BY sort_order, name");
    $stmt->execute();
    $categoriesFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categories = [];
    foreach ($categoriesFromDb as $cat) {
        $categories[$cat['slug']] = $cat['name'];
    }
    
    // If no categories in database, use default ones
    if (empty($categories)) {
        $categories = [
            'pendidikan' => 'Pendidikan & Akademik',
            'ui-ux' => 'UI/UX Design', 
            'programming' => 'Programming & Development',
            'bisnis' => 'Bisnis & Marketing',
            'kerajinan' => 'Kerajinan & Seni',
            'kesehatan' => 'Kesehatan & Kebugaran',
            'musik' => 'Musik & Audio',
            'fotografi' => 'Fotografi & Video',
            'bahasa' => 'Bahasa Asing',
            'hobi' => 'Hobi & Lifestyle'
        ];
    }
    
} catch (Exception $e) {
    error_log("Database error in buat-kursus-baru.php: " . $e->getMessage());
    // Use default categories if database fails
    $categories = [
        'pendidikan' => 'Pendidikan & Akademik',
        'ui-ux' => 'UI/UX Design', 
        'programming' => 'Programming & Development',
        'bisnis' => 'Bisnis & Marketing',
        'kerajinan' => 'Kerajinan & Seni',
        'kesehatan' => 'Kesehatan & Kebugaran',
        'musik' => 'Musik & Audio',
        'fotografi' => 'Fotografi & Video',
        'bahasa' => 'Bahasa Asing',
        'hobi' => 'Hobi & Lifestyle'
    ];
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'draft';
    
    try {
        // Validasi basic
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? '';
        $difficulty = $_POST['difficulty'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $price = str_replace(['.', ',', 'Rp', ' '], '', $_POST['price'] ?? '0');
        $freemium = isset($_POST['freemium']);
        
        $errors = [];
        
        // Validasi required fields
        if (empty($title)) {
            $errors[] = 'Judul kursus wajib diisi';
        } elseif (strlen($title) < 5) {
            $errors[] = 'Judul kursus minimal 5 karakter';
        }
        
        if (empty($category)) {
            $errors[] = 'Kategori kursus wajib dipilih';
        }
        
        if (empty($difficulty)) {
            $errors[] = 'Tingkat kesulitan wajib dipilih';
        }
        
        if (empty($description)) {
            $errors[] = 'Deskripsi kursus wajib diisi';
        } elseif (strlen($description) < 20) {
            $errors[] = 'Deskripsi kursus minimal 20 karakter';
        }
        
        if (!$freemium && (empty($price) || $price <= 0)) {
            $errors[] = 'Harga kursus wajib diisi untuk kursus berbayar';
        }
        
        // Handle file upload
        $coverImage = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/course-covers/';
            
            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['cover_image']['name']);
            $fileName = uniqid() . '_' . time() . '.' . $fileInfo['extension'];
            $uploadPath = $uploadDir . $fileName;
            
            // Validate file type and size
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array(strtolower($fileInfo['extension']), $allowedTypes)) {
                $errors[] = 'Format file cover tidak didukung';
            } elseif ($_FILES['cover_image']['size'] > $maxSize) {
                $errors[] = 'Ukuran file cover terlalu besar (maksimal 5MB)';
            } elseif (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                $coverImage = '/MindCraft-Project/uploads/course-covers/' . $fileName;
            } else {
                $errors[] = 'Gagal mengunggah file cover';
            }
        }
        
        if (empty($errors)) {
            // Save to database
            if ($db) {
                $stmt = $db->prepare("
                    INSERT INTO courses (
                        mentor_id, title, slug, category, difficulty, description, 
                        cover_image, price, is_premium, status, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $slug = generateSlug($title);
                $status = $action === 'publish' ? 'Published' : 'Draft';
                $isPremium = $freemium ? 1 : 0;
                $finalPrice = $freemium ? 0 : (int)$price;
                
                $result = $stmt->execute([
                    $mentorId, $title, $slug, $category, $difficulty, $description,
                    $coverImage, $finalPrice, $isPremium, $status
                ]);
                
                if ($result) {
                    $courseId = $db->lastInsertId();
                    
                    if ($action === 'publish') {
                        $successMessage = 'Kursus berhasil dipublikasikan! 🎉';
                        // Redirect to course management
                        header('Location: /MindCraft-Project/views/mentor/kursus-saya.php?success=' . urlencode($successMessage));
                        exit();
                    } elseif ($action === 'preview') {
                        $successMessage = 'Pratinjau kursus akan dibuka...';
                        // Redirect to preview
                        header('Location: /MindCraft-Project/views/mentor/preview-kursus.php?id=' . $courseId);
                        exit();
                    } else {
                        $successMessage = 'Draft kursus berhasil disimpan! 💾';
                        // Clear form data after successful save
                        $_POST = [];
                    }
                } else {
                    $errors[] = 'Gagal menyimpan kursus ke database';
                }
            } else {
                $errors[] = 'Koneksi database tidak tersedia';
            }
        }
        
        if (!empty($errors)) {
            $errorMessage = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        error_log("Error creating course: " . $e->getMessage());
        $errorMessage = 'Terjadi kesalahan saat menyimpan kursus. Silakan coba lagi.';
    }
}

// Helper function untuk generate slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    return trim($text, '-');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Buat Kursus Baru</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_buat-kursus-baru.css">
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
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php" class="active">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/reviews.php">Ulasan & Feedback</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Buat Kursus Baru</h1>
            </div>
            
            <div class="content-body">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success" style="background: #e6ffed; border: 1px solid #2B992B; color: #2B992B; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <div class="form-container fade-in-up">
                    <form id="createCourseForm" method="POST" enctype="multipart/form-data">
                        <!-- Judul Kursus -->
                        <div class="form-group">
                            <label for="title">Judul Kursus</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                placeholder="Masukkan judul kursus"
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                maxlength="100"
                                required
                            >
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <div class="custom-select">
                                <select id="category" name="category" required>
                                    <option value="">Pilih kategori</option>
                                    <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" 
                                                <?php echo (($_POST['category'] ?? '') === $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Tingkat Kesulitan -->
                        <div class="form-group">
                            <label>Tingkat Kesulitan</label>
                            <div class="difficulty-options">
                                <div class="difficulty-option">
                                    <input type="radio" id="pemula" name="difficulty" value="Pemula" 
                                           <?php echo (($_POST['difficulty'] ?? '') === 'Pemula') ? 'checked' : ''; ?>>
                                    <label for="pemula">Pemula</label>
                                </div>
                                <div class="difficulty-option">
                                    <input type="radio" id="menengah" name="difficulty" value="Menengah"
                                           <?php echo (($_POST['difficulty'] ?? '') === 'Menengah') ? 'checked' : ''; ?>>
                                    <label for="menengah">Menengah</label>
                                </div>
                                <div class="difficulty-option">
                                    <input type="radio" id="mahir" name="difficulty" value="Mahir"
                                           <?php echo (($_POST['difficulty'] ?? '') === 'Mahir') ? 'checked' : ''; ?>>
                                    <label for="mahir">Mahir</label>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi Khusus -->
                        <div class="form-group">
                            <label for="description">Deskripsi Khusus</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                placeholder="Masukkan deskripsi khusus"
                                maxlength="1000"
                                required
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Foto/Gambar Cover -->
                        <div class="form-group">
                            <label>Foto/Gambar Cover</label>
                            <div class="file-upload">
                                <input type="file" id="coverImage" name="cover_image" accept="image/*">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Klik untuk pilih gambar atau drag & drop</div>
                                <div class="upload-hint">Format: JPG, PNG, GIF, WebP (Max: 5MB)</div>
                            </div>
                            <div class="file-preview" style="display: none;">
                                <div class="file-icon">🖼️</div>
                                <div class="file-info">
                                    <div class="file-name">filename.jpg</div>
                                    <div class="file-size">2.5 MB</div>
                                </div>
                                <button type="button" class="file-remove">✕</button>
                            </div>
                        </div>

                        <!-- Harga Kursus -->
                        <div class="form-group">
                            <label for="price">Harga Kursus</label>
                            <div class="price-input">
                                <input 
                                    type="text" 
                                    id="price" 
                                    name="price" 
                                    placeholder="Masukkan harga kursus"
                                    value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                                >
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="freemium" name="freemium" value="1"
                                       <?php echo isset($_POST['freemium']) ? 'checked' : ''; ?>>
                                <label for="freemium">Aktifkan model Freemium (beberapa konten gratis)</label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-actions">
                            <button type="submit" name="action" value="draft" class="btn btn-secondary" data-action="draft">
                                💾 Simpan Draft
                            </button>
                            <button type="submit" name="action" value="preview" class="btn btn-outline" data-action="preview">
                                👁️ Pratinjau
                            </button>
                            <button type="submit" name="action" value="publish" class="btn btn-primary" data-action="publish">
                                🚀 Publikasikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_buat-kursus-baru.js"></script>
    <script>
        // Pass PHP data to JavaScript if needed
        window.courseData = {
            categories: <?php echo json_encode($categories); ?>,
            mentorId: <?php echo $mentorId; ?>,
            maxFileSize: 5242880, // 5MB in bytes
            allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        };

        // Show any server messages
        <?php if ($successMessage): ?>
            setTimeout(() => {
                showNotification('<?php echo addslashes($successMessage); ?>', 'success');
            }, 500);
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            setTimeout(() => {
                showNotification('Terdapat kesalahan dalam form', 'error');
            }, 500);
        <?php endif; ?>

        // File upload handling
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('coverImage');
            const fileUpload = document.querySelector('.file-upload');
            const filePreview = document.querySelector('.file-preview');
            const fileName = document.querySelector('.file-name');
            const fileSize = document.querySelector('.file-size');
            const removeBtn = document.querySelector('.file-remove');

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    if (!window.courseData.allowedTypes.includes(file.type)) {
                        showNotification('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.', 'error');
                        fileInput.value = '';
                        return;
                    }
                    
                    // Validate file size
                    if (file.size > window.courseData.maxFileSize) {
                        showNotification('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
                        fileInput.value = '';
                        return;
                    }
                    
                    // Show preview
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileUpload.style.display = 'none';
                    filePreview.style.display = 'flex';
                }
            });

            removeBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileUpload.style.display = 'flex';
                filePreview.style.display = 'none';
            });

            // Price formatting
            const priceInput = document.getElementById('price');
            priceInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value) {
                    value = parseInt(value).toLocaleString('id-ID');
                }
                e.target.value = value;
            });

            // Freemium checkbox handling
            const freemiumCheckbox = document.getElementById('freemium');
            freemiumCheckbox.addEventListener('change', function() {
                priceInput.disabled = this.checked;
                if (this.checked) {
                    priceInput.value = '0';
                }
            });
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showNotification(message, type) {
            // Simple notification implementation
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 16px;
                border-radius: 8px;
                z-index: 1000;
                max-width: 300px;
                ${type === 'success' ? 'background: #e6ffed; border: 1px solid #2B992B; color: #2B992B;' : 'background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E;'}
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>
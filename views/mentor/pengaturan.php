<?php

session_start();
if (!isset($_SESSION['mentor_id'])) {
    $_SESSION['mentor_id'] = 1;
}

$mentorId = $_SESSION['mentor_id'];

// Sample mentor data 
$mentorData = [
    'id' => $mentorId,
    'username' => 'Budi Santoso',
    'email' => 'budi.santoso@gmail.com',
    'full_name' => 'Budi Santoso',
    'bio' => 'Saya adalah praktisi kuliner tradisional dengan pengalaman lebih dari 15 tahun. Spesialisasi saya adalah masakan Padang dan Jawa.',
    'profile_picture' => null,
    'phone' => '+62812345678',
    'website' => 'https://budikusiner.com',
    'linkedin' => 'https://linkedin.com/in/budisantoso',
    'instagram' => '@budikusiner',
    'youtube' => 'Budi Kuliner Channel',
    'specialization' => 'Kuliner Tradisional Indonesia',
    'experience_years' => 15,
    'education' => 'Diploma Tata Boga, Institut Seni Kuliner Jakarta',
    'certifications' => 'Sertifikat Halal MUI, Food Safety Certificate',
    'hourly_rate' => 150000.00,
    'teaching_language' => 'Bahasa Indonesia',
    'timezone' => 'Asia/Jakarta',
    'availability_status' => 'Available'
];

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabType = $_POST['tab_type'] ?? 'profil';
    
    try {
        switch ($tabType) {
            case 'profil':
                $result = handleProfileUpdate($_POST, $_FILES);
                break;
            case 'keamanan':
                $result = handleSecurityUpdate($_POST);
                break;
            case 'notifikasi':
                $result = handleNotificationUpdate($_POST);
                break;
            case 'pembayaran':
                $result = handlePaymentUpdate($_POST);
                break;
            default:
                throw new Exception('Tab tidak valid');
        }
        
        if ($result['success']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['message'];
        }
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Handle Profile Update
function handleProfileUpdate($postData, $files) {
    global $mentorData;
    
    $errors = [];
    
    // Validate required fields
    $fullName = trim($postData['full_name'] ?? '');
    $email = trim($postData['email'] ?? '');
    $bio = trim($postData['bio'] ?? '');
    
    if (empty($fullName)) {
        $errors[] = 'Nama lengkap wajib diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (empty($bio)) {
        $errors[] = 'Bio mentor wajib diisi';
    } elseif (strlen($bio) < 20) {
        $errors[] = 'Bio mentor minimal 20 karakter';
    }
    
    // Handle profile picture upload
    $profilePicture = $mentorData['profile_picture'];
    if (isset($files['profile_picture']) && $files['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleProfilePictureUpload($files['profile_picture']);
        if ($uploadResult['success']) {
            $profilePicture = $uploadResult['path'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode('<br>', $errors)
        ];
    }
    
    // Update mentor data (dalam implementasi nyata, simpan ke database)
    $mentorData['full_name'] = $fullName;
    $mentorData['email'] = $email;
    $mentorData['bio'] = $bio;
    $mentorData['phone'] = trim($postData['phone'] ?? '');
    $mentorData['website'] = trim($postData['website'] ?? '');
    $mentorData['linkedin'] = trim($postData['linkedin'] ?? '');
    $mentorData['instagram'] = trim($postData['instagram'] ?? '');
    $mentorData['youtube'] = trim($postData['youtube'] ?? '');
    $mentorData['specialization'] = trim($postData['specialization'] ?? '');
    $mentorData['experience_years'] = (int)($postData['experience_years'] ?? 0);
    $mentorData['education'] = trim($postData['education'] ?? '');
    $mentorData['certifications'] = trim($postData['certifications'] ?? '');
    $mentorData['profile_picture'] = $profilePicture;
    
    return [
        'success' => true,
        'message' => 'Profil berhasil diperbarui! 👤'
    ];
}

// Handle Security Update
function handleSecurityUpdate($postData) {
    $errors = [];
    
    $currentPassword = $postData['current_password'] ?? '';
    $newPassword = $postData['new_password'] ?? '';
    $confirmPassword = $postData['confirm_password'] ?? '';
    
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Password lama wajib diisi';
        }
        
        if (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        // Dalam implementasi nyata, verifikasi password lama dari database
        if ($currentPassword !== 'password123') { // Simulasi
            $errors[] = 'Password lama tidak benar';
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode('<br>', $errors)
        ];
    }
    
    // Update password (dalam implementasi nyata, hash dan simpan ke database)
    if (!empty($newPassword)) {
        // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        // UPDATE users SET password = $hashedPassword WHERE id = $mentorId
    }
    
    return [
        'success' => true,
        'message' => 'Pengaturan keamanan berhasil diperbarui! 🔐'
    ];
}

// Handle Notification Update
function handleNotificationUpdate($postData) {
    // Update notification preferences
    $notifications = [
        'email_notifications' => isset($postData['email_notifications']),
        'push_notifications' => isset($postData['push_notifications']),
        'course_notifications' => isset($postData['course_notifications']),
        'review_notifications' => isset($postData['review_notifications']),
        'payment_notifications' => isset($postData['payment_notifications']),
        'marketing_emails' => isset($postData['marketing_emails'])
    ];
    
    // Dalam implementasi nyata, simpan ke database
    // UPDATE mentor_settings SET ... WHERE mentor_id = $mentorId
    
    return [
        'success' => true,
        'message' => 'Pengaturan notifikasi berhasil diperbarui! 🔔'
    ];
}

// Handle Payment Update
function handlePaymentUpdate($postData) {
    $paymentMethod = $postData['payment_method'] ?? '';
    $bankName = trim($postData['bank_name'] ?? '');
    $accountNumber = trim($postData['account_number'] ?? '');
    $accountName = trim($postData['account_name'] ?? '');
    
    $errors = [];
    
    if (empty($paymentMethod)) {
        $errors[] = 'Metode pembayaran wajib dipilih';
    }
    
    if ($paymentMethod === 'bank_transfer') {
        if (empty($bankName)) {
            $errors[] = 'Nama bank wajib diisi';
        }
        if (empty($accountNumber)) {
            $errors[] = 'Nomor rekening wajib diisi';
        }
        if (empty($accountName)) {
            $errors[] = 'Nama pemilik rekening wajib diisi';
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode('<br>', $errors)
        ];
    }
    
    // Update payment method (dalam implementasi nyata, simpan ke database)
    // UPDATE mentor_settings SET payment_method = ?, bank_name = ?, ... WHERE mentor_id = ?
    
    return [
        'success' => true,
        'message' => 'Metode pembayaran berhasil diperbarui! 💳'
    ];
}

// Handle Profile Picture Upload
function handleProfilePictureUpload($file) {
    $uploadDir = '../../uploads/profile-pictures/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Format file tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.'
        ];
    }
    
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'
        ];
    }
    
    // Generate unique filename
    $fileInfo = pathinfo($file['name']);
    $fileName = uniqid() . '_' . time() . '.' . $fileInfo['extension'];
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'success' => true,
            'path' => '/MindCraft-Project/uploads/profile-pictures/' . $fileName
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal mengunggah file'
        ];
    }
}

// Get current tab from URL hash or default to 'profil'
$currentTab = 'profil';
if (isset($_GET['tab']) && in_array($_GET['tab'], ['profil', 'keamanan', 'notifikasi', 'pembayaran'])) {
    $currentTab = $_GET['tab'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Pengaturan Akun</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_pengaturan.css">
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
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php" class="active">Pengaturan</a></li>
                <li><a href="/MindCraft-Project/views/mentorlogout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Pengaturan Akun</h1>
            </div>
            
            <div class="content-body">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success" style="background: #e6ffed; border: 1px solid #2B992B; color: #2B992B; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <div class="settings-container fade-in-up">
                    <!-- Tab Navigation -->
                    <div class="tab-navigation">
                        <button class="tab-button <?php echo $currentTab === 'profil' ? 'active' : ''; ?>" data-tab="profil">
                            Profil
                        </button>
                        <button class="tab-button <?php echo $currentTab === 'keamanan' ? 'active' : ''; ?>" data-tab="keamanan">
                            Keamanan
                        </button>
                        <button class="tab-button <?php echo $currentTab === 'notifikasi' ? 'active' : ''; ?>" data-tab="notifikasi">
                            Notifikasi
                        </button>
                        <button class="tab-button <?php echo $currentTab === 'pembayaran' ? 'active' : ''; ?>" data-tab="pembayaran">
                            Pembayaran
                        </button>
                    </div>

                    <!-- Profil Tab -->
                    <div id="profil" class="tab-content <?php echo $currentTab === 'profil' ? 'active' : ''; ?>">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="tab_type" value="profil">
                            
                            <div class="profile-section">
                                <h3 class="section-title">Informasi Profil</h3>
                                
                                <!-- Profile Photo -->
                                <div class="profile-photo-section">
                                    <div class="photo-preview">
                                        <?php if ($mentorData['profile_picture']): ?>
                                            <img src="<?php echo htmlspecialchars($mentorData['profile_picture']); ?>" alt="Profile Photo">
                                        <?php else: ?>
                                            <div class="photo-placeholder">👤</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="photo-actions">
                                        <input type="file" id="profilePhotoInput" name="profile_picture" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-primary" id="changePhotoBtn">
                                            Ubah Foto
                                        </button>
                                        <button type="button" class="btn btn-danger" id="deletePhotoBtn" 
                                                style="<?php echo !$mentorData['profile_picture'] ? 'display: none;' : ''; ?>">
                                            Hapus Foto
                                        </button>
                                    </div>
                                </div>

                                <!-- Basic Information -->
                                <div class="form-group">
                                    <label for="full_name">Nama Lengkap</label>
                                    <input type="text" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($mentorData['full_name']); ?>" 
                                           placeholder="Masukkan nama lengkap" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($mentorData['email']); ?>" 
                                           placeholder="contoh@email.com" required>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio Mentor</label>
                                    <textarea id="bio" name="bio" placeholder="Ceritakan tentang diri Anda sebagai mentor..." required><?php echo htmlspecialchars($mentorData['bio']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Nomor Telepon</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($mentorData['phone']); ?>" 
                                           placeholder="+62812345678">
                                </div>

                                <div class="form-group">
                                    <label for="specialization">Spesialisasi</label>
                                    <input type="text" id="specialization" name="specialization" 
                                           value="<?php echo htmlspecialchars($mentorData['specialization']); ?>" 
                                           placeholder="Bidang keahlian utama Anda">
                                </div>

                                <div class="form-group">
                                    <label for="experience_years">Pengalaman (Tahun)</label>
                                    <input type="number" id="experience_years" name="experience_years" 
                                           value="<?php echo $mentorData['experience_years']; ?>" 
                                           placeholder="0" min="0" max="50">
                                </div>

                                <div class="form-group">
                                    <label for="education">Pendidikan</label>
                                    <textarea id="education" name="education" placeholder="Riwayat pendidikan formal..."><?php echo htmlspecialchars($mentorData['education']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="certifications">Sertifikasi</label>
                                    <textarea id="certifications" name="certifications" placeholder="Sertifikat atau lisensi yang dimiliki..."><?php echo htmlspecialchars($mentorData['certifications']); ?></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Keamanan Tab -->
                    <div id="keamanan" class="tab-content <?php echo $currentTab === 'keamanan' ? 'active' : ''; ?>">
                        <form method="POST">
                            <input type="hidden" name="tab_type" value="keamanan">
                            
                            <div class="profile-section">
                                <h3 class="section-title">Pengaturan Keamanan</h3>
                                
                                <!-- Change Password -->
                                <div class="security-group">
                                    <h4>Ubah Password</h4>
                                    <p>Pastikan password Anda kuat dan unik untuk melindungi akun.</p>
                                    
                                    <div class="form-group">
                                        <label for="current_password">Password Lama</label>
                                        <input type="password" id="current_password" name="current_password" 
                                               placeholder="Masukkan password lama">
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">Password Baru</label>
                                        <input type="password" id="new_password" name="new_password" 
                                               placeholder="Masukkan password baru">
                                        <div class="password-strength" style="font-size: 12px; margin-top: 4px;"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Konfirmasi Password Baru</label>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru">
                                    </div>

                                    <button type="button" class="btn btn-primary" id="changePasswordBtn">
                                        Ubah Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Notifikasi Tab -->
                    <div id="notifikasi" class="tab-content <?php echo $currentTab === 'notifikasi' ? 'active' : ''; ?>">
                        <form method="POST">
                            <input type="hidden" name="tab_type" value="notifikasi">
                            
                            <div class="profile-section">
                                <h3 class="section-title">Pengaturan Notifikasi</h3>
                                
                                <div class="notification-group">
                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>Notifikasi Email</h4>
                                            <p>Terima notifikasi penting melalui email</p>
                                        </div>
                                        <input type="checkbox" name="email_notifications" value="1" checked style="display: none;">
                                        <div class="toggle-switch active"></div>
                                    </div>

                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>Aktivitas Kursus</h4>
                                            <p>Pemberitahuan saat ada pendaftaran atau progress mentee</p>
                                        </div>
                                        <input type="checkbox" name="course_notifications" value="1" checked style="display: none;">
                                        <div class="toggle-switch active"></div>
                                    </div>

                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>Ulasan & Feedback</h4>
                                            <p>Notifikasi saat mendapat ulasan baru dari mentee</p>
                                        </div>
                                        <input type="checkbox" name="review_notifications" value="1" checked style="display: none;">
                                        <div class="toggle-switch active"></div>
                                    </div>

                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>Pembayaran</h4>
                                            <p>Pemberitahuan terkait pendapatan dan pembayaran</p>
                                        </div>
                                        <input type="checkbox" name="payment_notifications" value="1" checked style="display: none;">
                                        <div class="toggle-switch active"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Pembayaran Tab -->
                    <div id="pembayaran" class="tab-content <?php echo $currentTab === 'pembayaran' ? 'active' : ''; ?>">
                        <form method="POST">
                            <input type="hidden" name="tab_type" value="pembayaran">
                            
                            <div class="profile-section">
                                <h3 class="section-title">Metode Pembayaran</h3>
                                
                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="payment_method" value="bank_transfer" checked>
                                        Transfer Bank
                                    </label>
                                    <div class="payment-details">
                                        Terima pembayaran melalui transfer bank lokal
                                    </div>
                                </div>

                                <div class="payment-method">
                                    <label>
                                        <input type="radio" name="payment_method" value="e_wallet">
                                        E-Wallet
                                    </label>
                                    <div class="payment-details">
                                        GoPay, OVO, DANA, dan e-wallet lainnya
                                    </div>
                                </div>

                                <!-- Bank Details -->
                                <div style="margin-top: 24px;">
                                    <h4 style="margin-bottom: 16px; font-size: 16px; font-weight: 600;">Detail Rekening Bank</h4>
                                    
                                    <div class="form-group">
                                        <label for="bank_name">Nama Bank</label>
                                        <input type="text" id="bank_name" name="bank_name" 
                                               placeholder="Contoh: Bank Central Asia (BCA)" 
                                               value="Bank Central Asia (BCA)">
                                    </div>

                                    <div class="form-group">
                                        <label for="account_number">Nomor Rekening</label>
                                        <input type="text" id="account_number" name="account_number" 
                                               placeholder="1234567890" 
                                               value="1234567890">
                                    </div>

                                    <div class="form-group">
                                        <label for="account_name">Nama Pemilik Rekening</label>
                                        <input type="text" id="account_name" name="account_name" 
                                               placeholder="Sesuai dengan nama di buku tabungan" 
                                               value="<?php echo htmlspecialchars($mentorData['full_name']); ?>">
                                    </div>

                                    <button type="button" class="btn btn-primary" id="updatePaymentBtn">
                                        Perbarui Metode Pembayaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Save Button -->
                    <div class="save-button-container">
                        <button type="button" class="btn-save">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_pengaturan.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.mentorData = <?php echo json_encode($mentorData); ?>;
        window.currentTab = '<?php echo $currentTab; ?>';

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
    </script>
</body>
</html>
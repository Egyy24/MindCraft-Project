<?php
session_start();

// // Check if user is logged in
// if (!isset($_SESSION['mentor_id'])) {
//     header('Location: /MindCraft-Project/views/auth/login.php');
//     exit();
// }

// Get user info for display
$mentorName = isset($_SESSION['mentor_name']) ? $_SESSION['mentor_name'] : 'Mentor';
$mentorEmail = isset($_SESSION['mentor_email']) ? $_SESSION['mentor_email'] : '';

// Handle logout confirmation
if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'yes') {
    // Clear all session data
    session_unset();
    session_destroy();
    
    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Redirect to login page with logout message
    header('Location: /MindCraft-Project/views/auth/login.php?logout=success');
    exit();
}

// Handle cancel logout (both POST and GET)
if (isset($_POST['cancel_logout']) || isset($_GET['cancel'])) {
    header('Location: /MindCraft-Project/views/mentor/dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Logout</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_logout.css">
</head>
<body>
    <!-- Background Elements -->
    <div class="background-elements">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Main Container -->
    <div class="logout-container">
        <!-- Header -->
        <div class="logout-header">
            <div class="logo">MindCraft</div>
            <div class="header-subtitle">Platform Pembelajaran Online</div>
        </div>

        <!-- Logout Card -->
        <div class="logout-card">

            <!-- User Info -->
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($mentorName, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h2 class="user-name"><?php echo htmlspecialchars($mentorName); ?></h2>
                    <p class="user-email"><?php echo htmlspecialchars($mentorEmail); ?></p>
                    <span class="user-role">Mentor</span>
                </div>
            </div>

            <!-- Logout Message -->
            <div class="logout-message">
                <h3>Keluar dari Akun Anda?</h3>
                <p>Anda akan keluar dari dashboard mentor MindCraft. Pastikan semua pekerjaan sudah tersimpan sebelum keluar.</p>
            </div>

            <!-- Session Info -->
            <div class="session-info">
                <div class="session-item">
                    <span class="session-label">Sesi login:</span>
                    <span class="session-value"><?php echo date('d M Y, H:i'); ?></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="logout-actions">
                <form method="POST" id="logoutForm">
                    <button type="button" class="btn btn-secondary" onclick="cancelLogout()" id="cancelBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Batal
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmLogout()" id="logoutBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Ya, Logout
                    </button>
                    <input type="hidden" name="confirm_logout" id="confirmLogoutInput" value="">
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <p class="quick-actions-title">Atau kembali ke:</p>
                <div class="quick-links">
                    <a href="/MindCraft-Project/views/mentor/dashboard.php" class="quick-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="/MindCraft-Project/views/mentor/kursus-saya.php" class="quick-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kursus Saya
                    </a>
                    <a href="/MindCraft-Project/views/mentor/pendapatan.php" class="quick-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 1V23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Pendapatan
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="logout-footer">
            <p>&copy; 2024 MindCraft. Semua hak dilindungi.</p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Sedang logout...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_logout.js"></script>
</body>
</html>
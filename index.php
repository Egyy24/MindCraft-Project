<?php
// Lokasi: MindCraft-Project/views/index.php

// Error reporting untuk development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Load dependencies
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controller/MentorController.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Untuk demo, set mentor_id jika belum ada
if (!isset($_SESSION['mentor_id'])) {
    $_SESSION['mentor_id'] = 1;
}

// Validate dan seed database jika diperlukan
if ($db) {
    if (!$database->validateDatabaseStructure()) {
        error_log("Database structure validation failed - using static data");
    } else {
        $database->seedInitialData();
    }
}

// Router setup
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/MindCraft-Project/views/';

// Clean the path
$path = str_replace($base_path, '', $request_uri);
$path = strtok($path, '?'); // Remove query parameters
$path = trim($path, '/'); // Remove leading/trailing slashes

// Debug info (dapat dihapus di production)
error_log("Request URI: " . $request_uri);
error_log("Cleaned Path: " . $path);

try {
    $controller = new MentorController($db);
    
    // Route handling
    switch ($path) {
        case '':
        case 'index.php':
            // Redirect ke dashboard mentor sebagai default
            header('Location: /MindCraft-Project/views/mentor/dashboard');
            exit();
            break;
            
        case 'mentor/dashboard':
        case 'mentor/dashboard.php':
            $controller->dashboard();
            break;
            
        case 'mentor/analitik':
        case 'mentor/analitik.php':
            $controller->analytics();
            break;
            
        case 'mentor/analitik-detail':
        case 'mentor/analitik-detail.php':
            $controller->analyticsDetail();
            break;
            
        // API endpoints untuk AJAX calls
        case 'api/mentor/dashboard':
            $controller->getDashboardDataJson();
            break;
            
        case 'api/mentor/analytics':
            $controller->getAnalyticsDataJson();
            break;
            
        // Halaman yang belum diimplementasi - tampilkan coming soon
        case 'mentor/courses':
        case 'mentor/courses.php':
            showComingSoon('Kursus Saya', 'Kelola dan pantau semua kursus yang Anda buat');
            break;
            
        case 'mentor/buat-kursus-baru':
        case 'mentor/buat-kursus-baru.php':
            showComingSoon('Buat Kursus Baru', 'Buat kursus baru untuk berbagi pengetahuan Anda');
            break;
            
        case 'mentor/earnings':
        case 'mentor/earnings.php':
            showComingSoon('Pendapatan', 'Lihat ringkasan pendapatan dan riwayat pembayaran');
            break;
            
        case 'mentor/reviews':
        case 'mentor/reviews.php':
            showComingSoon('Ulasan & Feedback', 'Kelola ulasan dan feedback dari mentee');
            break;
            
        case 'mentor/settings':
        case 'mentor/settings.php':
            showComingSoon('Pengaturan', 'Atur profil dan preferensi akun Anda');
            break;
            
        // Health check endpoint
        case 'health':
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ok',
                'database' => $database->getConnectionStatus(),
                'time' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ]);
            break;
            
        default:
            show404($path);
            break;
    }
    
} catch (Exception $e) {
    // Error handling
    error_log("MindCraft Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Tampilkan error page yang user-friendly
    showErrorPage($e->getMessage(), $path);
}

/**
 * Tampilkan halaman coming soon
 */
function showComingSoon($title, $description) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MindCraft - <?php echo htmlspecialchars($title); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 500px;
                margin: 2rem;
            }
            .icon {
                font-size: 4rem;
                margin-bottom: 1rem;
            }
            h1 {
                color: #3A59D1;
                margin: 0 0 1rem 0;
                font-size: 2rem;
                font-weight: 600;
            }
            p {
                color: #718096;
                margin: 0 0 2rem 0;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #3A59D1;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .btn:hover {
                background: #3305BC;
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">🚧</div>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($description); ?></p>
            <p>Halaman ini sedang dalam pengembangan dan akan segera hadir!</p>
            <a href="/MindCraft-Project/views/mentor/dashboard" class="btn">← Kembali ke Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Tampilkan halaman 404
 */
function show404($path) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MindCraft - Halaman Tidak Ditemukan</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 500px;
                margin: 2rem;
            }
            .error-code {
                font-size: 6rem;
                font-weight: 700;
                color: #3A59D1;
                margin: 0;
                line-height: 1;
            }
            h1 {
                color: #2d3748;
                margin: 1rem 0;
                font-size: 1.5rem;
                font-weight: 600;
            }
            p {
                color: #718096;
                margin: 0 0 2rem 0;
                line-height: 1.6;
            }
            .path {
                background: #f7fafc;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                font-family: monospace;
                color: #4a5568;
                margin: 1rem 0;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #3A59D1;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                margin: 0 0.5rem;
            }
            .btn:hover {
                background: #3305BC;
                transform: translateY(-2px);
            }
            .btn.secondary {
                background: #e2e8f0;
                color: #4a5568;
            }
            .btn.secondary:hover {
                background: #cbd5e0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-code">404</div>
            <h1>Halaman Tidak Ditemukan</h1>
            <p>Maaf, halaman yang Anda cari tidak dapat ditemukan.</p>
            <?php if ($path): ?>
            <div class="path"><?php echo htmlspecialchars($path); ?></div>
            <?php endif; ?>
            <div style="margin-top: 2rem;">
                <a href="/MindCraft-Project/views/mentor/dashboard" class="btn">🏠 Dashboard</a>
                <a href="javascript:history.back()" class="btn secondary">← Kembali</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Tampilkan halaman error
 */
function showErrorPage($message, $path) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MindCraft - Terjadi Kesalahan</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 600px;
                margin: 2rem;
            }
            .icon {
                font-size: 4rem;
                margin-bottom: 1rem;
            }
            h1 {
                color: #e53e3e;
                margin: 0 0 1rem 0;
                font-size: 1.8rem;
                font-weight: 600;
            }
            p {
                color: #718096;
                margin: 0 0 1rem 0;
                line-height: 1.6;
            }
            .error-details {
                background: #fed7d7;
                border: 1px solid #feb2b2;
                color: #c53030;
                padding: 1rem;
                border-radius: 8px;
                margin: 1rem 0;
                font-size: 0.9rem;
                text-align: left;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #3A59D1;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                margin: 0 0.5rem;
            }
            .btn:hover {
                background: #3305BC;
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">⚠️</div>
            <h1>Terjadi Kesalahan Sistem</h1>
            <p>Maaf, terjadi kesalahan pada sistem. Tim kami sedang bekerja untuk memperbaikinya.</p>
            <p>Sistem akan menggunakan data statis sebagai fallback untuk memastikan fungsionalitas tetap berjalan.</p>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
            <div class="error-details">
                <strong>Debug Info:</strong><br>
                Error: <?php echo htmlspecialchars($message); ?><br>
                Path: <?php echo htmlspecialchars($path); ?><br>
                Time: <?php echo date('Y-m-d H:i:s'); ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 2rem;">
                <a href="/MindCraft-Project/views/mentor/dashboard" class="btn">🏠 Coba Dashboard</a>
                <a href="javascript:location.reload()" class="btn">🔄 Refresh</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
<?php
// Lokasi: MindCraft-Project/index.php

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Load dependencies 
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/ContentModel.php';
require_once __DIR__ . '/../controller/MentorController.php';

// Inisialisasi db connection
$database = new Database();
$db = $database->connect();

// Untuk demo, set mentor_id jika belum ada
if (!isset($_SESSION['mentor_id'])) {
    $_SESSION['mentor_id'] = 1; // Default mentor ID untuk demo
}

// Router
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/MindCraft-Project/views/mentor'; 

// Hapus base path dan query string dari URI
$path = str_replace($base_path, '', $request_uri);
$path = strtok($path, '?');

// Debug: tampilkan path yang diakses
// echo "Accessing path: " . $path . "<br>";

try {
    $controller = new MentorController($db);
    
    // Mengatur route
    switch ($path) {
        case '/':
            echo "Welcome to MindCraft! Please navigate to /mentor/dashboard or other paths.";
            break;
            
        case '/mentor/dashboard.php':
            $controller->dashboard();
            break;

        case '/mentor/analitik.php':
            $controller->analytics();
            break;

        case '/mentor/analitik.php':
            $controller->getAnalyticsDataJson();
            break;
            
        case '/mentor/courses':
            // TODO: Implement courses view
            echo "<h1>Halaman Kursus Saya</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
            
        case '/mentor/create-course':
            // TODO: Implement create course view
            echo "<h1>Halaman Buat Kursus Baru</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
            
        case '/mentor/earnings':
            // TODO: Implement earnings view
            echo "<h1>Halaman Pendapatan</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
            
        case '/mentor/reviews':
            // TODO: Implement reviews view
            echo "<h1>Halaman Ulasan & Feedback</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
            
        case '/mentor/settings':
            // TODO: Implement settings view
            echo "<h1>Halaman Pengaturan</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
            
        case '/mentor/analytics-detail':
            // TODO: Implement analytics detail view
            echo "<h1>Halaman Detail Analitik Keterlibatan Mentee</h1><p>Coming Soon</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/analitik.php'>← Kembali ke Analytics</a></p>";
            break;
            
        default:
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
            echo "<p>Path tidak ditemukan: " . htmlspecialchars($path) . "</p>";
            echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>← Kembali ke Dashboard</a></p>";
            break;
    }
    
} catch (Exception $e) {
    // Error handling
    error_log("MindCraft Error: " . $e->getMessage());
    
    // Tampilkan halaman error atau dashboard dengan data default
    if (strpos($path, '/mentor/') === 0) {
        echo "<h1>Terjadi kesalahan</h1>";
        echo "<p>Maaf, terjadi kesalahan pada sistem. Silakan coba lagi nanti.</p>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='/MindCraft-Project/views/mentor/dashboard.php'>Kembali ke Dashboard</a></p>";
    } else {
        http_response_code(500);
        echo "Internal Server Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
<?php

// untuk login mentor
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ini dummy data
$current_mentor_id = 1; 
$current_mentor_name = "Tom Holland"; 

// Inisialisasi koneksi database 
if (!isset($pdo)) {
    require_once 'db_connection.php';
}

$mentor_data = [];
try {
    $stmt = $pdo->prepare("SELECT nama FROM mentors WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $mentor_data = $stmt->fetch();
    if ($mentor_data) {
        $current_mentor_name = $mentor_data['nama'];
    }
} catch (PDOException $e) {
    // (belum diisi dulu) untuk handle error, log it, atau display a friendly message 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'MindCraft'; ?></title>
    <link rel="stylesheet" href="../mentor/assets/css/mentor.css">
    </head>
<body>
    <div class="navbar">
        <div class="logo">MindCraft</div>
        <div class="nav-links">
            <a href="#">Notifikasi</a>
            <a href="#">Pesan</a>
            <a href="profile.php">Profil</a>
        </div>
    </div>

    <div class="container">
        <?php require_once 'sidebar.php'; ?>
        <div class="main-content"></div>
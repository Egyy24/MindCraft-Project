<?php
session_start(); 

// Cek apakah mentor sudah login
if (!isset($_SESSION['mentor_id'])) {
    // Jika belum login, arahkan ke halaman login utama 
    header('Location: ../login.php'); // masih perlu diganti dengan login page pathnya
    exit;
} else {
    // Jika sudah login, arahkan ke dashboard mentor
    header('Location: dashboard.php');
    exit;
}
?>
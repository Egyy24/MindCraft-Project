<?php
$host = "localhost";     // Nama host, biasanya localhost
$user = "root";          // Username MySQL default XAMPP biasanya 'root'
$password = "";          // Kosongkan jika Anda tidak mengatur password
$database = "mindcraft"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

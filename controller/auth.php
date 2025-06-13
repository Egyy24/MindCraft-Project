<?php
session_start();
include('../model/db.php');

// === LOGIN ===
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect sesuai role
        if ($user['role'] === 'mentor') {
            header("Location: ../dashboard/mentor.php");
        } elseif ($user['role'] === 'mentee') {
            header("Location: ../dashboard/mentee.php");
        } else {
            header("Location: ../dashboard/admin.php");
        }
        exit;
    } else {
        echo "<script>alert('Username atau password salah'); window.location.href='../login.php';</script>";
    }
}

// === REGISTER ===
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Cek apakah username sudah ada
    $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        echo "<script>alert('Username sudah terdaftar!'); window.location.href='../register.php';</script>";
    } else {
        $insert = $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
        if ($insert) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location.href='../login.php';</script>";
        } else {
            echo "<script>alert('Pendaftaran gagal.'); window.location.href='../register.php';</script>";
        }
    }
}
?>

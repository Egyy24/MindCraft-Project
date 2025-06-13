<?php
include 'koneksi.php';

require 'config.php';
session_start();


// Query untuk mendapatkan statistik dari database
$queryUsers = "SELECT COUNT(*) as total FROM users";
$resultUsers = mysqli_query($conn, $queryUsers);
$totalUsers = mysqli_fetch_assoc($resultUsers)['total'];

$queryMentors = "SELECT COUNT(*) as total FROM users WHERE user_type = 'Mentor'";
$resultMentors = mysqli_query($conn, $queryMentors);
$totalMentors = mysqli_fetch_assoc($resultMentors)['total'];

$queryMentees = "SELECT COUNT(*) as total FROM users WHERE user_type = 'Mentee'";
$resultMentees = mysqli_query($conn, $queryMentees);
$totalMentees = mysqli_fetch_assoc($resultMentees)['total'];

$queryModules = "SELECT COUNT(*) as total FROM content";
$resultModules = mysqli_query($conn, $queryModules);
$totalModules = mysqli_fetch_assoc($resultModules)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Platform Pengembangan Keterampilan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --utama: #4361ee;
            --sekunder: #a29bfe;
            --gelap: #2d3436;
            --terang: #f5f6fa;
            --sukses: #00b894;
            --peringatan: #fdcb6e;
            --bahaya: #d63031;
            --putih: #ffffff;
            --mentor: #0984e3;
            --mentee: #00b894;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--terang);
            color: var(--gelap);
            line-height: 1.6;
        }

        .kontainer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Animasi Keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from { 
                opacity: 0;
                transform: translateX(-50px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from { 
                opacity: 0;
                transform: translateX(50px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @keyframes animasiModalMasuk {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Gaya Header */
        header {
            background-color: var(--putih);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            animation: fadeIn 0.5s ease-out;
        }

        .navigasi {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo-kontainer {
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .logo-kontainer:hover {
            transform: scale(1.05);
        }

        .logo {
            font-size: 25px;
            font-weight: 700;
            color: var(--utama);
        }

        .gambar-logo {
            height: 45px;
            width: auto;
        }

        .tautan-navigasi {
            display: flex;
            gap: 30px;
        }

        .tautan-navigasi a {
            text-decoration: none;
            color: var(--gelap);
            font-weight: 500;
            transition: color 0.3s;
        }

        .tautan-navigasi a:hover {
            color: var(--utama);
        }

        .aksi-navigasi {
            display: flex;
            gap: 15px;
        }

        .tombol {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: none;
            transform: translateY(0);
        }

        .tombol:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .tombol:active {
            transform: translateY(1px);
        }

        .tombol-outline {
            background: transparent;
            border: 2px solid var(--utama);
            color: var(--utama);
        }

        .tombol-outline:hover {
            background: var(--utama);
            color: var(--putih);
        }

        .tombol-utama {
            background: var(--utama);
            color: var(--putih);
        }

        .tombol-utama:hover {
            background: #5649c5;
        }

        .tombol-mentor {
            background: var(--mentor);
            color: var(--putih);
        }

        .tombol-mentor:hover {
            background: #0767b3;
        }

        .tombol-mentee {
            background: var(--mentee);
            color: var(--putih);
        }

        .tombol-mentee:hover {
            background: #009d7a;
        }

        /* Gaya Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .konten-modal {
            background-color: var(--putih);
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            animation: animasiModalMasuk 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .tombol-tutup {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #636e72;
            transition: color 0.3s;
        }

        .tombol-tutup:hover {
            color: var(--gelap);
        }

        .judul-modal {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--utama);
            text-align: center;
        }

        .kelompok-form {
            margin-bottom: 20px;
        }

        .kelompok-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gelap);
        }

        .kontrol-form {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .kontrol-form:focus {
            outline: none;
            border-color: var(--utama);
        }

        .footer-form {
            margin-top: 30px;
            text-align: center;
        }

        .footer-form p {
            margin-top: 15px;
            color: #636e72;
        }

        .footer-form a {
            color: var(--utama);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-form a:hover {
            text-decoration: underline;
        }

        .pilihan-peran {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .opsi-peran {
            text-align: center;
            cursor: pointer;
            padding: 15px;
            border-radius: 8px;
            transition: all 0.3s;
            border: 2px solid transparent;
            width: 45%;
        }

        .opsi-peran:hover {
            transform: translateY(-3px);
        }

        .opsi-peran.aktif {
            border-color: var(--utama);
            background-color: rgba(108, 92, 231, 0.1);
        }

        .ikon-peran {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .opsi-peran.mentor .ikon-peran {
            color: var(--mentor);
        }

        .opsi-peran.mentee .ikon-peran {
            color: var(--mentee);
        }

        .nama-peran {
            font-weight: 600;
        }

        /* Gaya Dashboard */
        .dashboard {
            display: none;
            padding-top: 100px;
            animation: fadeIn 0.8s ease-out;
        }

        .header-dashboard {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .pesan-selamat-datang h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .pesan-selamat-datang p {
            color: #636e72;
        }

        .avatar-pengguna {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--utama);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .avatar-pengguna:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .kartu-statistik {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .kartu-stat {
            background: var(--putih);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .kartu-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .kartu-stat h3 {
            font-size: 14px;
            color: #636e72;
            margin-bottom: 10px;
        }

        .kartu-stat .nilai {
            font-size: 28px;
            font-weight: 700;
            color: var(--gelap);
            transition: all 0.5s ease;
        }

        .kartu-stat:hover .nilai {
            transform: scale(1.1);
        }

        .kartu-stat.pendapatan .nilai {
            color: var(--sukses);
        }

        .kartu-stat.mentor .nilai {
            color: var(--mentor);
        }

        .kartu-stat.mentee .nilai {
            color: var(--mentee);
        }

        /* Bagian Hero */
        .hero {
            padding: 150px 0 80px;
            background: linear-gradient(135deg, var(--utama), var(--sekunder));
            color: var(--putih);
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 800;
            animation: slideUp 0.8s ease-out 0.3s both;
        }

        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 40px;
            animation: slideUp 0.8s ease-out 0.5s both;
        }

        .tombol-hero {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            animation: slideUp 0.8s ease-out 0.7s both;
        }

        /* Bagian Fitur */
        .bagian {
            padding: 80px 0;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .bagian.scrolled {
            opacity: 1;
            transform: translateY(0);
        }

        .judul-bagian {
            text-align: center;
            margin-bottom: 60px;
        }

        .judul-bagian h2 {
            font-size: 36px;
            color: var(--gelap);
            margin-bottom: 15px;
            animation: slideUp 0.8s ease-out;
        }

        .judul-bagian p {
            color: #636e72;
            max-width: 700px;
            margin: 0 auto;
        }

        .fitur {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .kartu-fitur {
            background: var(--putih);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: perspective(1000px) rotateX(0) rotateY(0) scale(1);
        }

        .kartu-fitur:hover {
            transform: perspective(1000px) rotateX(2deg) rotateY(2deg) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .kartu-fitur:nth-child(1) {
            animation: slideInLeft 0.8s ease-out;
        }

        .kartu-fitur:nth-child(2) {
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .kartu-fitur:nth-child(3) {
            animation: slideInRight 0.8s ease-out;
        }

        .ikon-fitur {
            font-size: 40px;
            color: var(--utama);
            margin-bottom: 20px;
        }

        .kartu-fitur h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        /* Bagian Statistik */
        .statistik {
            background: var(--putih);
            padding: 60px 0;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .statistik.scrolled {
            opacity: 1;
            transform: translateY(0);
        }

        .kontainer-statistik {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .item-statistik {
            transition: all 0.3s ease;
        }

        .item-statistik:hover h3 {
            color: var(--sekunder);
        }

        .item-statistik h3 {
            font-size: 48px;
            color: var(--utama);
            margin-bottom: 10px;
            transition: all 0.5s ease;
        }

        .item-statistik p {
            color: #636e72;
            font-weight: 500;
        }

        .item-statistik:nth-child(1) {
            animation: slideInLeft 0.8s ease-out;
        }

        .item-statistik:nth-child(2) {
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .item-statistik:nth-child(3) {
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .item-statistik:nth-child(4) {
            animation: slideInRight 0.8s ease-out;
        }

        /* Bagian CTA */
        .cta {
            background: linear-gradient(135deg, var(--utama), var(--sekunder));
            color: var(--putih);
            padding: 100px 0;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .cta.scrolled {
            opacity: 1;
            transform: translateY(0);
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 20px;
            animation: slideUp 0.8s ease-out;
        }

        .cta p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 40px;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        /* Desain Responsif */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .tautan-navigasi {
                display: none;
            }
            
            .konten-modal {
                padding: 30px 20px;
                margin: 0 15px;
            }

            .pilihan-peran {
                flex-direction: column;
                align-items: center;
            }

            .opsi-peran {
                width: 80%;
            }

            .header-dashboard {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .kartu-fitur:nth-child(1),
            .kartu-fitur:nth-child(2),
            .kartu-fitur:nth-child(3) {
                animation: slideUp 0.8s ease-out;
            }

            .item-statistik:nth-child(1),
            .item-statistik:nth-child(2),
            .item-statistik:nth-child(3),
            .item-statistik:nth-child(4) {
                animation: slideUp 0.8s ease-out;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="kontainer">
            <nav class="navigasi">
                <div class="logo-kontainer">
                    <img src="mc2.png" alt="MindCraft" class="gambar-logo">
                    <div class="logo">MindCraft</div>
                </div>
                <div class="aksi-navigasi" id="tombolAuth">
                    <button class="tombol tombol-outline" id="tombolMasuk">Masuk</button>
                    <button class="tombol tombol-utama" id="tombolDaftar">Daftar</button>
                </div>
                <div class="menu-pengguna" id="menuPengguna" style="display: none;">
                    <div class="avatar-pengguna" id="avatarPengguna">P</div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main id="kontenUtama">
        <!-- Hero Section -->
        <section class="hero">
            <div class="kontainer">
                <h1>Ngembangin skill? Gas di sini!</h1>
                <p>Platform Cerdas Berbasis Web untuk Akselerasi Keterampilan Digital dan Ekosistem Kreatif.</p>
                <div class="tombol-hero">
                    <button class="tombol tombol-utama" id="heroDaftar">Yuk Mulai</button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bagian">
            <div class="kontainer">
                <div class="judul-bagian">
                    <h2>Kenapa Memilih MindCraft</h2>
                    <p>Kami menyediakan platform cerdas dan terintegrasi untuk mempercepat pengembangan keterampilan digital dan mendukung pertumbuhan ekosistem kreatif Anda secara berkelanjutan.</p>
                </div>
                <div class="fitur">
                    <div class="kartu-fitur">
                        <div class="ikon-fitur">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Jaringan Mentorship</h3>
                        <p>Terhubung langsung dengan mentor berpengalaman melalui sistem pencocokan otomatis yang disesuaikan dengan kebutuhan pengembangan Anda.</p>
                    </div>
                    <div class="kartu-fitur">
                        <div class="ikon-fitur">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Kurikulum Modular</h3>
                        <p>Pelajari keterampilan digital secara fleksibel melalui modul pembelajaran yang dapat disesuaikan dengan tujuan dan ritme belajar Anda.</p>
                    </div>
                    <div class="kartu-fitur">
                        <div class="ikon-fitur">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3>Penilaian Keterampilan</h3>
                        <p>Uji kemampuan Anda secara objektif melalui tes dan proyek akhir yang dirancang untuk mencerminkan keterampilan dunia nyata.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistik">
            <div class="kontainer">
                <div class="kontainer-statistik">
                    <div class="item-statistik">
                        <h3 data-target="<?php echo $totalUsers; ?>">0</h3>
                        <p>Pengguna Terdaftar di Platform MindCraft</p>
                    </div>
                    <div class="item-statistik">
                        <h3 data-target="<?php echo $totalMentors; ?>">0</h3>
                        <p>Mentor Aktif dari Berbagai Bidang Industri Kreatif dan Digital</p>
                    </div>
                    <div class="item-statistik">
                    <h3 data-target="<?php echo $totalMentees; ?>">0</h3>
                        <p>Mentee yang Sedang Mengembangkan Keahlian di Industri Kreatif dan Digital</p>
                    </div>
                    <div class="item-statistik">
                    <h3 data-target="<?php echo $totalModules; ?>">0</h3>
                        <p>Topik-topik pembelajaran dalam setiap kategori/materi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="kontainer">
                <h2>Siap tingkatkan skill dan jadi bagian dari ekosistem kreatif?</h2>
                <p>Bergabunglah dengan ribuan pengguna lainnya di MindCraft dan mulai perjalananmu membangun keterampilan masa depan!</p>
            </div>
        </section>
    </main>

    <!-- Dashboard (Hidden by default) -->
    <div class="dashboard kontainer" id="dashboard">
        <div class="header-dashboard">
            <div class="pesan-selamat-datang">
                <h1 id="selamatDatangPengguna">Selamat datang kembali, Pengguna!</h1>
                <p id="peranPengguna">Anda masuk sebagai Mentee</p>
            </div>
            <div class="avatar-pengguna" id="avatarDashboard">P</div>
        </div>

        <div class="kartu-statistik">
            <div class="kartu-stat pendapatan">
                <h3>Total Pendapatan</h3>
                <div class="nilai">Rp15.000.000</div>
            </div>
            <div class="kartu-stat" id="kartuStatPeran">
                <h3>Mentor Aktif</h3>
                <div class="nilai">24</div>
            </div>
            <div class="kartu-stat">
                <h3>Transaksi</h3>
                <div class="nilai">180</div>
            </div>
            <div class="kartu-stat">
                <h3>Pertumbuhan</h3>
                <div class="nilai">24%</div>
            </div>
        </div>

        <div class="judul-bagian">
            <h2>Dashboard Anda</h2>
            <p>Kelajari keuangan dan koneksi Anda di satu tempat</p>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal" id="modalMasuk">
        <div class="konten-modal">
            <span class="tombol-tutup" id="tutupMasuk">&times;</span>
            <h2 class="judul-modal">Masuk ke Akun Anda</h2>
            
            <div class="pilihan-peran">
                <div class="opsi-peran mentee aktif" data-peran="mentee">
                    <div class="ikon-peran"><i class="fas fa-user-graduate"></i></div>
                    <div class="nama-peran">Mentee</div>
                </div>
                <div class="opsi-peran mentor" data-peran="mentor">
                    <div class="ikon-peran"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="nama-peran">Mentor</div>
                </div>
            </div>
            
            <form id="formMasuk">
                <div class="kelompok-form">
                    <label for="emailMasuk">Alamat Email</label>
                    <input type="email" id="emailMasuk" class="kontrol-form" placeholder="Masukkan email Anda" required>
                </div>
                <div class="kelompok-form">
                    <label for="sandiMasuk">Kata Sandi</label>
                    <input type="password" id="sandiMasuk" class="kontrol-form" placeholder="Masukkan kata sandi Anda" required>
                </div>
                <div class="kelompok-form">
                    <button type="submit" class="tombol tombol-utama tombol-block" id="submitMasuk">Masuk</button>
                </div>
                <div class="footer-form">
                    <p>Belum punya akun? <a href="#" id="bukaDaftar">Daftar</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Registration Modal -->
    <div class="modal" id="modalDaftar">
        <div class="konten-modal">
            <span class="tombol-tutup" id="tutupDaftar">&times;</span>
            <h2 class="judul-modal">Buat Akun Anda</h2>
            
            <div class="pilihan-peran">
                <div class="opsi-peran mentee aktif" data-peran="mentee">
                    <div class="ikon-peran"><i class="fas fa-user-graduate"></i></div>
                    <div class="nama-peran">Mentee</div>
                </div>
                <div class="opsi-peran mentor" data-peran="mentor">
                    <div class="ikon-peran"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="nama-peran">Mentor</div>
                </div>
            </div>
            
            <form id="formDaftar">
                <div class="kelompok-form">
                    <label for="namaDaftar">Nama Lengkap</label>
                    <input type="text" id="namaDaftar" class="kontrol-form" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="kelompok-form">
                    <label for="emailDaftar">Alamat Email</label>
                    <input type="email" id="emailDaftar" class="kontrol-form" placeholder="Masukkan email Anda" required>
                </div>
                <div class="kelompok-form">
                    <label for="sandiDaftar">Kata Sandi</label>
                    <input type="password" id="sandiDaftar" class="kontrol-form" placeholder="Buat kata sandi" required>
                </div>
                <div class="kelompok-form">
                    <label for="konfirmasiSandi">Konfirmasi Kata Sandi</label>
                    <input type="password" id="konfirmasiSandi" class="kontrol-form" placeholder="Konfirmasi kata sandi Anda" required>
                </div>
                <div class="kelompok-form">
                    <button type="submit" class="tombol tombol-utama tombol-block" id="submitDaftar">Buat Akun</button>
                </div>
                <div class="footer-form">
                    <p>Sudah punya akun? <a href="#" id="bukaMasuk">Masuk</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const modalMasuk = document.getElementById('modalMasuk');
        const modalDaftar = document.getElementById('modalDaftar');
        const tombolMasuk = document.getElementById('tombolMasuk');
        const tombolDaftar = document.getElementById('tombolDaftar');
        const heroDaftar = document.getElementById('heroDaftar');
        const tutupMasuk = document.getElementById('tutupMasuk');
        const tutupDaftar = document.getElementById('tutupDaftar');
        const bukaDaftar = document.getElementById('bukaDaftar');
        const bukaMasuk = document.getElementById('bukaMasuk');
        const formMasuk = document.getElementById('formMasuk');
        const formDaftar = document.getElementById('formDaftar');
        const kontenUtama = document.getElementById('kontenUtama');
        const dashboard = document.getElementById('dashboard');
        const tombolAuth = document.getElementById('tombolAuth');
        const menuPengguna = document.getElementById('menuPengguna');
        const avatarPengguna = document.getElementById('avatarPengguna');
        const avatarDashboard = document.getElementById('avatarDashboard');
        const selamatDatangPengguna = document.getElementById('selamatDatangPengguna');
        const peranPengguna = document.getElementById('peranPengguna');
        const kartuStatPeran = document.getElementById('kartuStatPeran');
        
        // Role selection
        const opsiPeran = document.querySelectorAll('.opsi-peran');
        let peranTerpilih = 'mentee';
        
        // User data
        let penggunaSaatIni = null;
        
        // Modal functions
        function openModal(modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Event listeners for modals
        tombolMasuk.addEventListener('click', () => openModal(modalMasuk));
        tombolDaftar.addEventListener('click', () => openModal(modalDaftar));
        heroDaftar.addEventListener('click', () => openModal(modalDaftar));
        
        tutupMasuk.addEventListener('click', () => closeModal(modalMasuk));
        tutupDaftar.addEventListener('click', () => closeModal(modalDaftar));
        
        // Switch between login and register modals
        bukaDaftar.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(modalMasuk);
            openModal(modalDaftar);
        });

        bukaMasuk.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(modalDaftar);
            openModal(modalMasuk);
        });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === modalMasuk) closeModal(modalMasuk);
        if (e.target === modalDaftar) closeModal(modalDaftar);
    });
    
    // Role selection function
    opsiPeran.forEach(opsi => {
        opsi.addEventListener('click', () => {
            opsiPeran.forEach(opt => opt.classList.remove('aktif'));
            opsi.classList.add('aktif');
            peranTerpilih = opsi.dataset.peran;
        });
    });
    
    // Login form handler
    formMasuk.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('emailMasuk').value;
        const sandi = document.getElementById('sandiMasuk').value;
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: sandi,
                    user_type: peranTerpilih === 'mentor' ? 'Mentor' : 'Mentee'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                penggunaSaatIni = {
                    id: data.user.id,
                    nama: data.user.username,
                    email: data.user.email,
                    peran: data.user.user_type.toLowerCase(),
                    inisial: data.user.username.charAt(0).toUpperCase()
                };
                
                perbaruiUISetelahMasuk();
                closeModal(modalMasuk);
                alert(`Berhasil masuk sebagai ${data.user.user_type}!`);
            } else {
                alert(`Gagal masuk: ${data.message}`);
            }
        } catch (error) {
            console.error('Error during login:', error);
            alert('Terjadi kesalahan saat login');
        }
    });
    
    // Registration form handler
    formDaftar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nama = document.getElementById('namaDaftar').value;
        const email = document.getElementById('emailDaftar').value;
        const sandi = document.getElementById('sandiDaftar').value;
        const konfirmasiSandi = document.getElementById('konfirmasiSandi').value;
        
        if (sandi !== konfirmasiSandi) {
            alert('Kata sandi tidak cocok!');
            return;
        }
        
        try {
            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: nama,
                    email: email,
                    password: sandi,
                    confirm_password: konfirmasiSandi,
                    user_type: peranTerpilih === 'mentor' ? 'Mentor' : 'Mentee',
                    gender: 'Laki-laki'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Auto login after registration
                const loginResponse = await fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: sandi,
                        user_type: peranTerpilih === 'mentor' ? 'Mentor' : 'Mentee'
                    })
                });
                
                const loginData = await loginResponse.json();
                
                if (loginData.success) {
                    penggunaSaatIni = {
                        id: loginData.user.id,
                        nama: loginData.user.username,
                        email: loginData.user.email,
                        peran: loginData.user.user_type.toLowerCase(),
                        inisial: loginData.user.username.charAt(0).toUpperCase()
                    };
                    
                    perbaruiUISetelahMasuk();
                    closeModal(modalDaftar);
                    alert(`Berhasil mendaftar sebagai ${loginData.user.user_type}! Anda sekarang masuk.`);
                } else {
                    alert('Registrasi berhasil tetapi gagal login otomatis. Silakan login manual.');
                }
            } else {
                alert(`Gagal mendaftar: ${data.message}`);
            }
        } catch (error) {
            console.error('Error during registration:', error);
            alert('Terjadi kesalahan saat registrasi');
        }
    });
    
    // Update UI after login/registration
    function perbaruiUISetelahMasuk() {
        tombolAuth.style.display = 'none';
        menuPengguna.style.display = 'block';
        avatarPengguna.textContent = penggunaSaatIni.inisial;
        
        kontenUtama.style.display = 'none';
        dashboard.style.display = 'block';
        
        selamatDatangPengguna.textContent = `Selamat datang kembali, ${penggunaSaatIni.nama}!`;
        peranPengguna.textContent = `Anda masuk sebagai ${penggunaSaatIni.peran === 'mentor' ? 'Mentor' : 'Mentee'}`;
        avatarDashboard.textContent = penggunaSaatIni.inisial;
        
        if (penggunaSaatIni.peran === 'mentor') {
            kartuStatPeran.querySelector('h3').textContent = 'Mentee Aktif';
            kartuStatPeran.classList.add('mentor');
            kartuStatPeran.classList.remove('mentee');
        } else {
            kartuStatPeran.querySelector('h3').textContent = 'Mentor Aktif';
            kartuStatPeran.classList.add('mentee');
            kartuStatPeran.classList.remove('mentor');
        }
    }
    
    // Check session on page load
    async function checkSession() {
        try {
            const response = await fetch('check_session.php');
            const data = await response.json();
            
            if (data.logged_in) {
                penggunaSaatIni = {
                    id: data.user.id,
                    nama: data.user.username,
                    email: data.user.email,
                    peran: data.user.user_type.toLowerCase(),
                    inisial: data.user.username.charAt(0).toUpperCase()
                };
                perbaruiUISetelahMasuk();
            } else {
                tombolAuth.style.display = 'flex';
                menuPengguna.style.display = 'none';
                kontenUtama.style.display = 'block';
                dashboard.style.display = 'none';
            }
        } catch (error) {
            console.error('Error checking session:', error);
        }
    }
    // Logout function
    async function logout() {
        try {
            const response = await fetch('logout.php');
            const data = await response.json();
            
            if (data.success) {
                penggunaSaatIni = null;
                tombolAuth.style.display = 'flex';
                menuPengguna.style.display = 'none';
                kontenUtama.style.display = 'block';
                dashboard.style.display = 'none';
                
                formMasuk.reset();
                formDaftar.reset();
                
                opsiPeran.forEach(opt => opt.classList.remove('aktif'));
                document.querySelector('.opsi-peran.mentee').classList.add('aktif');
                peranTerpilih = 'mentee';
            } else {
                alert('Gagal logout');
            }
        } catch (error) {
            console.error('Error during logout:', error);
        }
    }
    
    // Logout when clicking avatar
    avatarPengguna.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin logout?')) {
            logout();
        }
    });
    
    // Counter animation for statistics
    function animateCounters() {
        const counters = document.querySelectorAll('.item-statistik h3');
        const speed = 200;
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / speed;
            
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(animateCounters, 1);
            } else {
                counter.innerText = target;
            }
        });
    }
    
    // Scroll animation handler
    const scrollElements = document.querySelectorAll('.bagian, .statistik, .cta');
    
    function elementInView(el, dividend = 1) {
        const elementTop = el.getBoundingClientRect().top;
        return (
            elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend
        );
    }
    
    function displayScrollElement(element) {
        element.classList.add('scrolled');
        
        // Animate counters when statistics section comes into view
        if (element.classList.contains('statistik')) {
            animateCounters();
        }
    }
    
    function handleScrollAnimation() {
        scrollElements.forEach((el) => {
            if (elementInView(el, 1.25)) {
                displayScrollElement(el);
            }
        });
    }
    
    // Initialize
    window.addEventListener('load', () => {
        handleScrollAnimation();
        checkSession();
    });
    
    window.addEventListener('scroll', () => {
        handleScrollAnimation();
    });
});
</script>
</body>
</html>
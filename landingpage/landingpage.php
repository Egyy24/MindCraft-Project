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
    <link rel="stylesheet" href="../assets/css/landingpage.css">
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

    <script src="../assets/js/landingpage.js"></script>
</body>
</html>
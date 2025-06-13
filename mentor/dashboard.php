<?php
$page_title = "Dashboard Mentor - MindCraft";
$current_page = "dashboard"; 

require_once 'includes/db_connection.php'; 
require_once 'includes/header.php'; 

// ambil data untuk dashboard
$total_kursus = 0;
$total_mentee = 0;
$rating_rata_rata = 0;
$total_ulasan = 0;
$total_pendapatan_jt = 0; 

try {
    // Total Kursus
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM courses WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $total_kursus = $stmt->fetchColumn();

    // Total Mentee 
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT mentee_id) AS total FROM reviews WHERE mentor_id = ?"); 
    $stmt->execute([$current_mentor_id]);
    $total_mentee = $stmt->fetchColumn();

    // Rating Rata-rata 
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $avg_rating_result = $stmt->fetch();
    $rating_rata_rata = round($avg_rating_result['avg_rating'] ?? 0, 1);

    // Total Ulasan
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reviews WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $total_ulasan = $stmt->fetchColumn();

    // Total Pendapatan
    $stmt = $pdo->prepare("SELECT SUM(jumlah) AS total FROM mentor_earnings WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $total_pendapatan_result = $stmt->fetch();
    $total_pendapatan = $total_pendapatan_result['total'] ?? 0;
    $total_pendapatan_jt = number_format($total_pendapatan / 1000000, 1, ',', '.'); // Format ke "juta"

} catch (PDOException $e) {
    // Error handling
    echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Error: " . $e->getMessage() . "</div>";
    $total_kursus = $total_mentee = $rating_rata_rata = $total_ulasan = $total_pendapatan_jt = "N/A";
}

// Data dummy untuk recent activity dan chart 
$aktivitas_terbaru = [
    ['Budi S.', 'Kerajinan An...', '2 jam yang lalu'],
    ['Siti K.', 'Kursus Memasak...', '5 jam yang lalu'],
    ['Andi P.', 'Fotografi Digital...', '1 hari yang lalu'],
];

// Data dummy untuk jumlah pendaftaran (untuk chart)
$pendaftaran_data = [10, 15, 20, 12, 25, 18, 30, 22, 16, 28, 35, 20]; // Contoh data untuk 12 bulan
?>

            <h1>Dashboard Mentor</h1>

            <div class="alert-info">
                Selamat datang kembali, **<?php echo htmlspecialchars($current_mentor_name); ?>** !<br>
                Anda memiliki **3 pendaftaran baru** dan **5 pesan** yang belum dibaca.<br>
                Konsistensi kursus Anda meningkat sebesar **12%** bulan ini!
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <h2>Total Kursus</h2>
                    <p class="stat-number"><?php echo $total_kursus; ?> <span class="trend up">▲ 2 BARU</span></p>
                </div>
                <div class="stat-card">
                    <h2>Total Mentee</h2>
                    <p class="stat-number"><?php echo $total_mentee; ?> <span class="trend up">▲ 12% MTM</span></p>
                </div>
                <div class="stat-card">
                    <h2>Rating Rata-rata</h2>
                    <p class="stat-number"><?php echo $rating_rata_rata; ?> <span class="trend up">▲ 0.2 MTM</span></p>
                </div>
            </div>

            <div class="dashboard-summary-cards">
                <div class="summary-card">
                    <h3>Penyelesaian</h3>
                    <p>78%</p> </div>
                <div class="summary-card">
                    <h3>Konten Video</h3>
                    <p>48 Jam</p> </div>
                <div class="summary-card">
                    <h3>Modul</h3>
                    <p>64</p> </div>
                <div class="summary-card">
                    <h3>Total Ulasan</h3>
                    <p><?php echo $total_ulasan; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Pendapatan</h3>
                    <p>Rp <?php echo $total_pendapatan_jt; ?> jt</p>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Aktivitas Terbaru</h2>
                <ul class="activity-list">
                    <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                        <li><?php echo htmlspecialchars($aktivitas[0]); ?> mendaftar kursus "<?php echo htmlspecialchars($aktivitas[1]); ?>" <?php echo htmlspecialchars($aktivitas[2]); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="dashboard-section">
                <h2>Jumlah Pendaftaran</h2>
                <div class="chart-placeholder">
                    <canvas id="pendaftaranChart" width="400" height="150"></canvas>
                </div>
            </div>
<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Render Chart Pendaftaran
    const pendaftaranCtx = document.getElementById('pendaftaranChart');
    if (pendaftaranCtx) {
        new Chart(pendaftaranCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Pendaftaran',
                    data: <?php echo json_encode($pendaftaran_data); ?>,
                    backgroundColor: 'rgba(92, 107, 192, 0.7)',
                    borderColor: 'rgba(92, 107, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>
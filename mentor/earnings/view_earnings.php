<?php
$page_title = "Pendapatan - MindCraft";
$current_page = "earnings";

require_once '../includes/db_connection.php'; 
require_once '../includes/header.php'; 

// ambil data pendapatan dari db
$total_pendapatan = 0;
$total_penjualan_kursus = 0;
$rata_rata_per_kursus = 0;
$prev_month_earnings = 0;
$prev_month_sales = 0;
$prev_month_avg_per_course = 0;

$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59'); 

$prev_month_start = date('Y-m-01 00:00:00', strtotime('-1 month'));
$prev_month_end = date('Y-m-t 23:59:59', strtotime('-1 month'));

try {
    // Data bulan ini
    $stmt_current = $pdo->prepare("
        SELECT
            SUM(me.jumlah) AS total_earnings,
            COUNT(DISTINCT me.course_id) AS total_courses_sold -- Ini bisa tidak akurat jika 1 course terjual berkali-kali. Lebih baik hitung dari tabel order.
        FROM mentor_earnings me
        WHERE me.mentor_id = ? AND me.tanggal BETWEEN ? AND ?
    ");
    $stmt_current->execute([$current_mentor_id, $current_month_start, $current_month_end]);
    $current_data = $stmt_current->fetch();

    $total_pendapatan = $current_data['total_earnings'] ?? 0;
    $total_penjualan_kursus = $current_data['total_courses_sold'] ?? 0; // masih mau disesuaikan untuk menghitung penjualan, bukan kursus unik

    if ($total_penjualan_kursus > 0) {
        $rata_rata_per_kursus = $total_pendapatan / $total_penjualan_kursus;
    }

    // Data untuk bulan lalu
    $stmt_prev = $pdo->prepare("
        SELECT
            SUM(me.jumlah) AS total_earnings,
            COUNT(DISTINCT me.course_id) AS total_courses_sold
        FROM mentor_earnings me
        WHERE me.mentor_id = ? AND me.tanggal BETWEEN ? AND ?
    ");
    $stmt_prev->execute([$current_mentor_id, $prev_month_start, $prev_month_end]);
    $prev_data = $stmt_prev->fetch();

    $prev_month_earnings = $prev_data['total_earnings'] ?? 0;
    $prev_month_sales = $prev_data['total_courses_sold'] ?? 0;
    if ($prev_month_sales > 0) {
        $prev_month_avg_per_course = $prev_month_earnings / $prev_month_sales;
    }

} catch (PDOException $e) {
    echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Error loading earnings data: " . $e->getMessage() . "</div>";
    $total_pendapatan = $total_penjualan_kursus = $rata_rata_per_kursus = $prev_month_earnings = $prev_month_sales = $prev_month_avg_per_course = 0;
}

// Hitung persentase perubahan
$pendapatan_trend_percent = ($prev_month_earnings > 0) ? (($total_pendapatan - $prev_month_earnings) / $prev_month_earnings) * 100 : ($total_pendapatan > 0 ? 100 : 0);
$sales_trend_percent = ($prev_month_sales > 0) ? (($total_penjualan_kursus - $prev_month_sales) / $prev_month_sales) * 100 : ($total_penjualan_kursus > 0 ? 100 : 0);
$avg_per_course_trend_percent = ($prev_month_avg_per_course > 0) ? (($rata_rata_per_kursus - $prev_month_avg_per_course) / $prev_month_avg_per_course) * 100 : ($rata_rata_per_kursus > 0 ? 100 : 0);

$pendapatan_trend_class = $pendapatan_trend_percent >= 0 ? 'up' : 'down';
$sales_trend_class = $sales_trend_percent >= 0 ? 'up' : 'down';
$avg_per_course_trend_class = $avg_per_course_trend_percent >= 0 ? 'up' : 'down';

// Data dummy tren pendapatan bulanan untuk 12 bulan terakhir
$earnings_trend_data = [];
$earnings_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m-01', strtotime("-$i months"));
    $month_name = date('M Y', strtotime($month));
    $month_end = date('Y-m-t 23:59:59', strtotime($month));

    $stmt = $pdo->prepare("
        SELECT SUM(jumlah) AS monthly_earnings
        FROM mentor_earnings
        WHERE mentor_id = ? AND tanggal BETWEEN ? AND ?
    ");
    $stmt->execute([$current_mentor_id, $month, $month_end]);
    $monthly_earnings = $stmt->fetchColumn() ?? 0;

    $earnings_trend_data[] = $monthly_earnings;
    $earnings_labels[] = $month_name;
}

?>

            <h1>Pendapatan</h1>

            <div class="filter-bar-earnings">
                <span>Tampilkan analitik untuk:</span>
                <select id="course_filter_earnings">
                    <option value="">Semua Kursus</option>
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT course_id, judul FROM courses WHERE mentor_id = ? ORDER BY judul");
                        $stmt->execute([$current_mentor_id]);
                        while ($row = $stmt->fetch()) {
                            echo "<option value='" . htmlspecialchars($row['course_id']) . "'>" . htmlspecialchars($row['judul']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        // Handle error (belum ada)
                    }
                    ?>
                </select>
                <select id="time_filter_earnings">
                    <option value="30">30 Hari</option>
                    <option value="90">90 Hari</option>
                    <option value="365">1 Tahun</option>
                    <option value="all">Semua Waktu</option>
                </select>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <h2>TOTAL PENDAPATAN</h2>
                    <p class="stat-number-large">Rp. <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></p>
                    <p class="stat-info"><span class="trend <?php echo $pendapatan_trend_class; ?>">
                        <?php echo ($pendapatan_trend_percent >= 0 ? '▲' : '▼') . ' ' . abs(round($pendapatan_trend_percent, 1)); ?>%
                    </span> dari bulan lalu</p>
                    <p class="stat-sub-info">Bulan lalu: Rp. <?php echo number_format($prev_month_earnings, 0, ',', '.'); ?></p>
                </div>
                <div class="stat-card">
                    <h2>PENJUALAN KURSUS</h2>
                    <p class="stat-number-large"><?php echo $total_penjualan_kursus; ?> Kursus</p>
                    <p class="stat-info"><span class="trend <?php echo $sales_trend_class; ?>">
                        <?php echo ($sales_trend_percent >= 0 ? '▲' : '▼') . ' ' . abs(round($sales_trend_percent, 1)); ?>%
                    </span> dari bulan lalu</p>
                    <p class="stat-sub-info">Bulan lalu: <?php echo $prev_month_sales; ?></p>
                </div>
                <div class="stat-card">
                    <h2>RATA-RATA PER KURSUS</h2>
                    <p class="stat-number-large">Rp. <?php echo number_format($rata_rata_per_kursus, 0, ',', '.'); ?></p>
                    <p class="stat-info"><span class="trend <?php echo $avg_per_course_trend_class; ?>">
                        <?php echo ($avg_per_course_trend_percent >= 0 ? '▲' : '▼') . ' ' . abs(round($avg_per_course_trend_percent, 1)); ?>%
                    </span> dari bulan lalu</p>
                    <p class="stat-sub-info">Bulan lalu: Rp. <?php echo number_format($prev_month_avg_per_course, 0, ',', '.'); ?></p>
                </div>
            </div>

            <div class="dashboard-section chart-section">
                <h2>Tren Pendapatan Bulanan</h2>
                <div class="chart-placeholder">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>

            <div class="link-details">
                <a href="#">Lihat Analitik DetailPendapatan Anda <span class="arrow">→</span></a>
            </div>
<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Render Chart Pendapatan
    const earningsCtx = document.getElementById('earningsChart');
    if (earningsCtx) {
        new Chart(earningsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($earnings_labels); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?php echo json_encode($earnings_trend_data); ?>,
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
    const courseFilterEarnings = document.getElementById('course_filter_earnings');
    const timeFilterEarnings = document.getElementById('time_filter_earnings');

    function applyEarningsFilters() {
        const selectedCourse = courseFilterEarnings.value;
        const selectedTime = timeFilterEarnings.value;
        console.log(`Filtering earnings by: Course="${selectedCourse}", Time="${selectedTime}"`);
    }

    courseFilterEarnings.addEventListener('change', applyEarningsFilters);
    timeFilterEarnings.addEventListener('change', applyEarningsFilters);
});
</script>
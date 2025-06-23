<?php
session_start();

// // Check if user is logged in and is a mentor
// if (!isset($_SESSION['mentor_id']) || $_SESSION['user_type'] !== 'Mentor') {
//     header('Location: /MindCraft-Project/views/auth/login.php');
//     exit();
// }

// Include database connection dan controller
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

try {
    // Initialize database dan controller
    $database = new Database();
    $db = $database->connect();
    $controller = new MentorController($database);
    
    $mentorId = $_SESSION['mentor_id'];
    
    // Get earnings data
    $earningsData = $controller->getEarningsData($mentorId);
    
    // Ensure transactions is always an array
    if (!isset($earningsData['transactions']) || !is_array($earningsData['transactions'])) {
        $earningsData['transactions'] = [];
    }
    
    // Get filter parameters
    $courseFilter = isset($_GET['course']) ? $_GET['course'] : 'all';
    $periodFilter = isset($_GET['period']) ? $_GET['period'] : '30';
    
    // Apply filters to transactions
    $filteredTransactions = applyEarningsFilters($earningsData['transactions'], $courseFilter, $periodFilter);
    
    // Calculate summary statistics
    $summaryStats = calculateEarningsSummary($filteredTransactions, $earningsData);
    
    // Get courses list for filter dropdown
    $courses = getCoursesForFilter($db, $mentorId);
    
} catch (Exception $e) {
    error_log("Pendapatan page error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data pendapatan.";
    $earningsData = [
        'total_earnings' => 0,
        'total_sales' => 0,
        'avg_per_sale' => 0,
        'available_balance' => 0,
        'transactions' => [],
        'monthly_earnings' => array_fill(0, 12, 0)
    ];
    $filteredTransactions = [];
    $summaryStats = [
        'total_earnings' => 0,
        'total_sales' => 0,
        'avg_per_sale' => 0,
        'available_balance' => 0,
        'trend_percentage' => 0
    ];
    $courses = [];
}

/**
 * Apply Filters to Earnings Data
 */
function applyEarningsFilters($transactions, $courseFilter, $periodFilter) {
    // Ensure transactions is an array
    if (!is_array($transactions)) {
        return [];
    }
    
    $filtered = $transactions;
    
    // Filter by course
    if ($courseFilter !== 'all') {
        $filtered = array_filter($filtered, function($transaction) use ($courseFilter) {
            return isset($transaction['course_id']) && $transaction['course_id'] == $courseFilter;
        });
    }
    
    // Filter by period
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$periodFilter} days"));
    $filtered = array_filter($filtered, function($transaction) use ($cutoffDate) {
        return isset($transaction['created_at']) && $transaction['created_at'] >= $cutoffDate;
    });
    
    return array_values($filtered);
}

/**
 * Calculate Summary Statistics
 */
function calculateEarningsSummary($filteredTransactions, $allData) {
    // Ensure filteredTransactions is an array
    if (!is_array($filteredTransactions)) {
        $filteredTransactions = [];
    }
    
    $completedTransactions = array_filter($filteredTransactions, function($t) {
        return isset($t['status']) && isset($t['transaction_type']) && 
               $t['status'] === 'completed' && $t['transaction_type'] === 'course_sale';
    });
    
    $totalEarnings = 0;
    foreach ($completedTransactions as $transaction) {
        if (isset($transaction['net_amount'])) {
            $totalEarnings += $transaction['net_amount'];
        }
    }
    
    $totalSales = count($completedTransactions);
    $avgPerSale = $totalSales > 0 ? $totalEarnings / $totalSales : 0;
    
    // Calculate trend (comparison with previous period)
    $currentMonth = date('n');
    $previousMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;
    $currentMonthEarnings = isset($allData['monthly_earnings'][$currentMonth - 1]) ? $allData['monthly_earnings'][$currentMonth - 1] : 0;
    $previousMonthEarnings = isset($allData['monthly_earnings'][$previousMonth - 1]) ? $allData['monthly_earnings'][$previousMonth - 1] : 0;
    
    $trend = 0;
    if ($previousMonthEarnings > 0) {
        $trend = (($currentMonthEarnings - $previousMonthEarnings) / $previousMonthEarnings) * 100;
    }
    
    return [
        'total_earnings' => $totalEarnings ?: (isset($allData['total_earnings']) ? $allData['total_earnings'] : 0),
        'total_sales' => $totalSales ?: (isset($allData['total_sales']) ? $allData['total_sales'] : 0),
        'avg_per_sale' => $avgPerSale ?: (isset($allData['avg_per_sale']) ? $allData['avg_per_sale'] : 0),
        'available_balance' => isset($allData['available_balance']) ? $allData['available_balance'] : 0,
        'trend_percentage' => round($trend, 1)
    ];
}

/**
 * Get Courses for Filter Dropdown
 */
function getCoursesForFilter($db, $mentorId) {
    try {
        if ($db) {
            $stmt = $db->prepare("SELECT id, title FROM courses WHERE mentor_id = ? ORDER BY title");
            $stmt->execute([$mentorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
        
    } catch (Exception $e) {
        error_log("Error getting courses: " . $e->getMessage());
        return [];
    }
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
    if (!is_numeric($amount)) {
        $amount = 0;
    }
    
    if ($amount >= 1000000) {
        return 'Rp ' . number_format($amount / 1000000, 1) . ' jt';
    } elseif ($amount >= 1000) {
        return 'Rp ' . number_format($amount / 1000, 0) . 'k';
    } else {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

/**
 * Format Date
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Get Transaction Type Label
 */
function getTransactionTypeLabel($type) {
    $labels = [
        'course_sale' => 'Penjualan Kursus',
        'tip' => 'Tip dari Mentee',
        'bonus' => 'Bonus Platform',
        'refund' => 'Refund',
        'withdrawal' => 'Penarikan Dana'
    ];
    
    return isset($labels[$type]) ? $labels[$type] : 'Transaksi Lain';
}

/**
 * Get Status Badge Class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'completed' => 'status-completed',
        'pending' => 'status-pending',
        'cancelled' => 'status-cancelled'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'status-pending';
}

/**
 * Get Payout Badge Class
 */
function getPayoutBadgeClass($status) {
    $classes = [
        'paid' => 'payout-paid',
        'pending' => 'payout-pending',
        'hold' => 'payout-hold'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'payout-pending';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Pendapatan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_pendapatan.css">
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="logo">MindCraft</div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php" class="active">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/logout.php" class="logout-link">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Pendapatan</h1>
            </div>
            
            <div class="content-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error" style="background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Controls -->
                <div class="filter-controls">
                    <span class="control-label">Tampilkan analitik untuk:</span>
                    <div class="custom-select">
                        <select id="courseSelect" name="course">
                            <option value="all" <?php echo $courseFilter === 'all' ? 'selected' : ''; ?>>Semua Kursus</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['id']); ?>" 
                                        <?php echo $courseFilter == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="custom-select">
                        <select id="periodSelect" name="period">
                            <option value="7" <?php echo $periodFilter === '7' ? 'selected' : ''; ?>>7 Hari</option>
                            <option value="30" <?php echo $periodFilter === '30' ? 'selected' : ''; ?>>30 Hari</option>
                            <option value="90" <?php echo $periodFilter === '90' ? 'selected' : ''; ?>>90 Hari</option>
                            <option value="365" <?php echo $periodFilter === '365' ? 'selected' : ''; ?>>1 Tahun</option>
                        </select>
                    </div>
                </div>

                <!-- Earnings Summary Cards -->
                <div class="earnings-summary">
                    <div class="summary-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="summary-title">Total Pendapatan</div>
                        <div class="summary-amount"><?php echo formatCurrency($summaryStats['total_earnings']); ?></div>
                        <div class="summary-trend">
                            <?php if ($summaryStats['trend_percentage'] >= 0): ?>
                                ▲ <?php echo abs($summaryStats['trend_percentage']); ?>% dari bulan lalu
                            <?php else: ?>
                                ▼ <?php echo abs($summaryStats['trend_percentage']); ?>% dari bulan lalu
                            <?php endif; ?>
                        </div>
                        <div class="summary-subtitle">
                            Bulan lalu: <?php echo formatCurrency(isset($earningsData['monthly_earnings'][date('n') - 2]) ? $earningsData['monthly_earnings'][date('n') - 2] : 0); ?>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="summary-title">Penjualan Kursus</div>
                        <div class="summary-amount"><?php echo number_format($summaryStats['total_sales']); ?> Kursus</div>
                        <div class="summary-trend">
                            <?php 
                            $salesGrowth = $summaryStats['trend_percentage'] > 0 ? '▲ ' . abs($summaryStats['trend_percentage']) . '%' : '▼ ' . abs($summaryStats['trend_percentage']) . '%';
                            echo $salesGrowth . ' dari bulan lalu';
                            ?>
                        </div>
                        <div class="summary-subtitle">
                            Bulan lalu: <?php echo number_format(max(0, $summaryStats['total_sales'] - round($summaryStats['total_sales'] * 0.12))); ?>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="summary-title">Rata-rata per Kursus</div>
                        <div class="summary-amount"><?php echo formatCurrency($summaryStats['avg_per_sale']); ?></div>
                        <div class="summary-trend">
                            <?php 
                            $avgGrowth = $summaryStats['trend_percentage'] > 0 ? '▲ ' . abs($summaryStats['trend_percentage']) . '%' : '▼ ' . abs($summaryStats['trend_percentage']) . '%';
                            echo $avgGrowth . ' dari bulan lalu';
                            ?>
                        </div>
                        <div class="summary-subtitle">
                            Bulan lalu: <?php echo formatCurrency($summaryStats['avg_per_sale'] * 0.88); ?>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-section fade-in-up" style="animation-delay: 0.4s;">
                    <div class="chart-header">
                        <h2 class="chart-title">Tren Pendapatan Bulanan</h2>
                    </div>
                    <div class="chart-container">
                        <?php if (isset($earningsData['monthly_earnings']) && array_sum($earningsData['monthly_earnings']) > 0): ?>
                            <canvas id="earningsChart"></canvas>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 300px; color: #718096; text-align: center; flex-direction: column;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                                <div style="font-weight: 500;">Belum ada data pendapatan</div>
                                <div style="font-size: 0.9rem; margin-top: 0.5rem;">Chart akan muncul setelah ada transaksi</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detail Link -->
                <a href="/MindCraft-Project/views/mentor/pendapatan-detail.php?course=<?php echo urlencode($courseFilter); ?>&period=<?php echo urlencode($periodFilter); ?>" 
                   class="detail-link fade-in-up" style="animation-delay: 0.5s;">
                    <span class="detail-link-text">Lihat Analitik Detail Pendapatan Anda</span>
                    <span class="detail-link-arrow">→</span>
                </a>

                <!-- Withdrawal Section -->
                <div class="withdrawal-section fade-in-up" style="animation-delay: 0.6s;">
                    <div class="section-header">
                        <h3>Penarikan Dana</h3>
                        <p>Kelola saldo dan penarikan pendapatan Anda</p>
                    </div>
                    
                    <div class="withdrawal-info">
                        <div class="withdrawal-balance"><?php echo formatCurrency($summaryStats['available_balance']); ?></div>
                        <div class="withdrawal-note">
                            Saldo tersedia untuk ditarik. Minimum penarikan Rp 100.000. 
                            Dana akan masuk ke rekening dalam 1-2 hari kerja.
                        </div>
                    </div>
                    
                    <div class="withdrawal-actions">
                        <button id="withdrawBtn" class="btn btn-primary" 
                                <?php echo $summaryStats['available_balance'] < 100000 ? 'disabled' : ''; ?>>
                            Tarik Dana
                        </button>
                        <a href="/MindCraft-Project/views/mentor/riwayat-penarikan.php" class="btn btn-secondary">
                            Riwayat Penarikan
                        </a>
                        <button onclick="exportEarningsData()" class="btn btn-secondary">
                            Export Data
                        </button>
                    </div>
                </div>

                <!-- Earnings Table -->
                <div class="earnings-table-section fade-in-up" style="animation-delay: 0.7s;">
                    <div class="section-header">
                        <h3>Riwayat Transaksi</h3>
                        <p>Detail transaksi dan pendapatan terbaru</p>
                    </div>
                    
                    <div class="earnings-table-container">
                        <table class="earnings-table">
                            <thead>
                                <tr>
                                    <th data-sort="type">Jenis Transaksi</th>
                                    <th data-sort="amount">Jumlah</th>
                                    <th>Status</th>
                                    <th>Status Payout</th>
                                    <th data-sort="date">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filteredTransactions)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #718096;">
                                            <div style="font-size: 48px; margin-bottom: 16px;">💰</div>
                                            <div style="font-weight: 500; margin-bottom: 8px;">Belum ada transaksi</div>
                                            <div style="font-size: 13px;">Transaksi akan muncul setelah ada penjualan kursus</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($filteredTransactions, 0, 20) as $transaction): ?>
                                    <tr>
                                        <td>
                                            <div class="transaction-info">
                                                <div class="transaction-type">
                                                    <?php echo getTransactionTypeLabel(isset($transaction['transaction_type']) ? $transaction['transaction_type'] : ''); ?>
                                                </div>
                                                <?php if (!empty($transaction['course_title'])): ?>
                                                    <div class="transaction-course">
                                                        <?php echo htmlspecialchars($transaction['course_title']); ?>
                                                        <?php if (!empty($transaction['student_name'])): ?>
                                                            - <?php echo htmlspecialchars($transaction['student_name']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="amount-cell">
                                                <div class="amount-gross">
                                                    <?php if (isset($transaction['transaction_type']) && $transaction['transaction_type'] === 'withdrawal'): ?>
                                                        -<?php echo formatCurrency(abs(isset($transaction['net_amount']) ? $transaction['net_amount'] : 0)); ?>
                                                    <?php else: ?>
                                                        <?php echo formatCurrency(isset($transaction['amount']) ? $transaction['amount'] : 0); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (isset($transaction['transaction_type']) && $transaction['transaction_type'] === 'course_sale'): ?>
                                                    <div class="amount-net">Bersih: <?php echo formatCurrency(isset($transaction['net_amount']) ? $transaction['net_amount'] : 0); ?></div>
                                                    <?php if (isset($transaction['platform_fee']) && $transaction['platform_fee'] > 0): ?>
                                                        <div class="amount-fee">Fee platform: <?php echo formatCurrency($transaction['platform_fee']); ?></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusBadgeClass(isset($transaction['status']) ? $transaction['status'] : 'pending'); ?>">
                                                <?php echo ucfirst(isset($transaction['status']) ? $transaction['status'] : 'pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="payout-badge <?php echo getPayoutBadgeClass(isset($transaction['payout_status']) ? $transaction['payout_status'] : 'pending'); ?>">
                                                <?php echo ucfirst(isset($transaction['payout_status']) ? $transaction['payout_status'] : 'pending'); ?>
                                            </span>
                                        </td>
                                        <td class="date-cell">
                                            <?php echo formatDate(isset($transaction['created_at']) ? $transaction['created_at'] : ''); ?>
                                            <?php if (!empty($transaction['payout_date'])): ?>
                                                <br><small style="color: #2B992B;">Dibayar: <?php echo formatDate($transaction['payout_date']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($filteredTransactions) > 20): ?>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="/MindCraft-Project/views/mentor/pendapatan-detail.php?course=<?php echo urlencode($courseFilter); ?>&period=<?php echo urlencode($periodFilter); ?>" 
                               class="btn btn-secondary">
                                Lihat Semua Transaksi (<?php echo count($filteredTransactions); ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Earnings Tips -->
                <div class="tips-section" style="margin-top: 32px; padding: 24px; background: linear-gradient(135deg, rgba(58, 89, 209, 0.05), rgba(144, 199, 248, 0.05)); border-radius: 12px; border: 1px solid rgba(58, 89, 209, 0.1);">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: var(--primary-blue);">💡 Tips Meningkatkan Pendapatan</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">🎯 Optimasi Harga</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Sesuaikan harga kursus dengan nilai yang diberikan dan kompetitor di pasar.</p>
                        </div>
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">📢 Marketing Aktif</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Promosikan kursus melalui media sosial dan jaringan profesional Anda.</p>
                        </div>
                        <div style="padding: 16px; background: white; border-radius: 8px; border: 1px solid var(--border-color);">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark);">⭐ Kualitas Tinggi</h4>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">Pertahankan kualitas konten dan layanan untuk mendapat ulasan positif.</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Withdrawal Modal -->
    <div id="withdrawalModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tarik Dana</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="withdrawal-form">
                    <div class="form-group">
                        <label>Jumlah Penarikan</label>
                        <input type="text" id="withdrawAmount" placeholder="Masukkan jumlah" class="form-input">
                        <div class="form-hint">Minimum: Rp 100.000 | Maksimum: <?php echo formatCurrency($summaryStats['available_balance']); ?></div>
                    </div>
                    <div class="form-group">
                        <label>Metode Penarikan</label>
                        <select id="withdrawMethod" class="form-select">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="gopay">GoPay</option>
                            <option value="ovo">OVO</option>
                            <option value="dana">DANA</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close">Batal</button>
                <button class="btn btn-primary" onclick="processWithdrawal()">Proses Penarikan</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_pendapatan.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.earningsData = {
            monthlyEarnings: <?php echo json_encode(isset($earningsData['monthly_earnings']) ? $earningsData['monthly_earnings'] : array_fill(0, 12, 0)); ?>,
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            totalEarnings: <?php echo $summaryStats['total_earnings']; ?>,
            totalSales: <?php echo $summaryStats['total_sales']; ?>,
            avgPerSale: <?php echo $summaryStats['avg_per_sale']; ?>,
            availableBalance: <?php echo $summaryStats['available_balance']; ?>,
            hasData: <?php echo (isset($earningsData['monthly_earnings']) && array_sum($earningsData['monthly_earnings']) > 0) ? 'true' : 'false'; ?>
        };

        window.earningsTransactions = <?php echo json_encode($filteredTransactions); ?>;
        window.currentFilters = {
            course: '<?php echo $courseFilter; ?>',
            period: '<?php echo $periodFilter; ?>'
        };

        // Withdrawal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const withdrawBtn = document.getElementById('withdrawBtn');
            const modal = document.getElementById('withdrawalModal');
            const closeButtons = document.querySelectorAll('.modal-close');
            
            withdrawBtn.addEventListener('click', function() {
                if (window.earningsData.availableBalance >= 100000) {
                    modal.style.display = 'flex';
                } else {
                    showNotification('Saldo minimum untuk penarikan adalah Rp 100.000', 'warning');
                }
            });
            
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            });
            
            // Filter functionality
            const courseSelect = document.getElementById('courseSelect');
            const periodSelect = document.getElementById('periodSelect');
            
            function applyFilters() {
                const params = new URLSearchParams();
                if (courseSelect.value !== 'all') params.set('course', courseSelect.value);
                if (periodSelect.value !== '30') params.set('period', periodSelect.value);
                
                window.location.search = params.toString();
            }
            
            courseSelect.addEventListener('change', applyFilters);
            periodSelect.addEventListener('change', applyFilters);
        });

        function processWithdrawal() {
            const amount = document.getElementById('withdrawAmount').value;
            const method = document.getElementById('withdrawMethod').value;
            
            if (!amount || parseFloat(amount.replace(/[^\d]/g, '')) < 100000) {
                showNotification('Jumlah penarikan minimum Rp 100.000', 'error');
                return;
            }
            
            // Simulate processing
            showNotification('Memproses penarikan dana...', 'info');
            setTimeout(() => {
                document.getElementById('withdrawalModal').style.display = 'none';
                showNotification('Penarikan dana berhasil diproses! Dana akan masuk dalam 1-2 hari kerja.', 'success');
            }, 2000);
        }

        function exportEarningsData() {
            showNotification('Menyiapkan data pendapatan...', 'info');
            setTimeout(() => {
                const reportData = generateEarningsReport();
                downloadCSV(reportData, 'pendapatan-' + new Date().toISOString().split('T')[0] + '.csv');
                showNotification('Data pendapatan berhasil diexport!', 'success');
            }, 1500);
        }

        function generateEarningsReport() {
            const headers = ['Tanggal', 'Jenis Transaksi', 'Kursus', 'Jumlah Kotor', 'Fee Platform', 'Jumlah Bersih', 'Status'];
            const rows = window.earningsTransactions.map(transaction => [
                transaction.created_at || '',
                transaction.transaction_type || '',
                transaction.course_title || '',
                transaction.amount || 0,
                transaction.platform_fee || 0,
                transaction.net_amount || 0,
                transaction.status || ''
            ]);
            
            return [headers, ...rows]
                .map(row => row.map(field => `"${field}"`).join(','))
                .join('\n');
        }

        function downloadCSV(content, filename) {
            const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 16px;
                border-radius: 8px;
                z-index: 1000;
                max-width: 300px;
                ${type === 'success' ? 'background: #e6ffed; border: 1px solid #2B992B; color: #2B992B;' : 
                  type === 'error' ? 'background: #fed7d7; border: 1px solid #E53E3E; color: #E53E3E;' :
                  type === 'warning' ? 'background: #fef3cd; border: 1px solid #F59E0B; color: #F59E0B;' :
                  'background: #e6f3ff; border: 1px solid #3B82F6; color: #3B82F6;'}
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>
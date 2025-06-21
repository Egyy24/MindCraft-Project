<?php
// Lokasi: MindCraft-Project/views/mentor/pendapatan.php

// Simulasi session mentor
session_start();
if (!isset($_SESSION['mentor_id'])) {
    $_SESSION['mentor_id'] = 1;
}

$mentorId = $_SESSION['mentor_id'];

// Include database connection
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controller/MentorController.php';

// Initialize database dan controller
$database = new Database();
$db = $database->connect();
$controller = new MentorController($database);

// Get earnings data dari database atau fallback ke static
$earningsData = getEarningsData($db, $mentorId);

// Filter parameters
$courseFilter = isset($_GET['course']) ? $_GET['course'] : 'all';
$periodFilter = isset($_GET['period']) ? $_GET['period'] : '30';

// Apply filters to data
$filteredEarnings = applyEarningsFilters($earningsData['transactions'], $courseFilter, $periodFilter);

// Calculate summary statistics
$summaryStats = calculateEarningsSummary($filteredEarnings, $earningsData);

// Get courses list for filter dropdown
$courses = getCoursesForFilter($db, $mentorId);

/**
 * Get Earnings Data from Database or Static Fallback
 */
function getEarningsData($db, $mentorId) {
    try {
        if ($db) {
            return getDatabaseEarningsData($db, $mentorId);
        }
        
        return getStaticEarningsData();
        
    } catch (Exception $e) {
        error_log("Error getting earnings data: " . $e->getMessage());
        return getStaticEarningsData();
    }
}

/**
 * Get Earnings Data from Database
 */
function getDatabaseEarningsData($db, $mentorId) {
    try {
        // Get total earnings
        $stmt = $db->prepare("
            SELECT 
                SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total_earnings,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_sales,
                AVG(CASE WHEN status = 'completed' THEN net_amount ELSE NULL END) as avg_per_sale,
                SUM(CASE WHEN status = 'completed' AND payout_status = 'pending' THEN net_amount ELSE 0 END) as available_balance
            FROM earnings 
            WHERE mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get detailed transactions
        $stmt = $db->prepare("
            SELECT 
                e.*,
                c.title as course_title,
                u.username as student_name
            FROM earnings e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.student_id = u.id
            WHERE e.mentor_id = ?
            ORDER BY e.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$mentorId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get monthly earnings for chart
        $stmt = $db->prepare("
            SELECT 
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                SUM(net_amount) as total_amount
            FROM earnings
            WHERE mentor_id = ? 
            AND status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year, month
        ");
        $stmt->execute([$mentorId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format monthly data untuk chart
        $monthlyEarnings = array_fill(0, 12, 0);
        foreach ($monthlyData as $month) {
            $index = $month['month'] - 1;
            $monthlyEarnings[$index] = (float)$month['total_amount'];
        }

        return [
            'total_earnings' => (float)($summary['total_earnings'] ?? 0),
            'total_sales' => (int)($summary['total_sales'] ?? 0),
            'avg_per_sale' => (float)($summary['avg_per_sale'] ?? 0),
            'available_balance' => (float)($summary['available_balance'] ?? 0),
            'transactions' => $transactions,
            'monthly_earnings' => $monthlyEarnings
        ];

    } catch (Exception $e) {
        error_log("Database earnings error: " . $e->getMessage());
        return getStaticEarningsData();
    }
}

/**
 * Static Earnings Data (Fallback)
 */
function getStaticEarningsData() {
    return [
        'total_earnings' => 12450000,
        'total_sales' => 186,
        'avg_per_sale' => 66789,
        'available_balance' => 2850000,
        'transactions' => [
            [
                'id' => 1,
                'transaction_type' => 'course_sale',
                'amount' => 299000,
                'commission_rate' => 70.00,
                'platform_fee' => 89700,
                'net_amount' => 209300,
                'status' => 'completed',
                'payout_status' => 'paid',
                'course_title' => 'Kursus Memasak: Sop Buntut',
                'student_name' => 'Budi Santoso',
                'created_at' => '2024-12-20 10:30:00',
                'payout_date' => '2024-12-22 09:00:00'
            ],
            [
                'id' => 2,
                'transaction_type' => 'course_sale',
                'amount' => 399000,
                'commission_rate' => 70.00,
                'platform_fee' => 119700,
                'net_amount' => 279300,
                'status' => 'completed',
                'payout_status' => 'pending',
                'course_title' => 'Rendang Padang Asli',
                'student_name' => 'Siti Aminah',
                'created_at' => '2024-12-19 14:15:00',
                'payout_date' => null
            ],
            [
                'id' => 3,
                'transaction_type' => 'course_sale',
                'amount' => 249000,
                'commission_rate' => 70.00,
                'platform_fee' => 74700,
                'net_amount' => 174300,
                'status' => 'completed',
                'payout_status' => 'pending',
                'course_title' => 'Fotografi Makanan',
                'student_name' => 'Ahmad Rahman',
                'created_at' => '2024-12-18 16:45:00',
                'payout_date' => null
            ],
            [
                'id' => 4,
                'transaction_type' => 'course_sale',
                'amount' => 499000,
                'commission_rate' => 70.00,
                'platform_fee' => 149700,
                'net_amount' => 349300,
                'status' => 'completed',
                'payout_status' => 'paid',
                'course_title' => 'Bisnis Kuliner Online',
                'student_name' => 'Maya Putri',
                'created_at' => '2024-12-17 11:20:00',
                'payout_date' => '2024-12-19 10:30:00'
            ],
            [
                'id' => 5,
                'transaction_type' => 'withdrawal',
                'amount' => 1500000,
                'commission_rate' => 0,
                'platform_fee' => 0,
                'net_amount' => -1500000,
                'status' => 'completed',
                'payout_status' => 'paid',
                'course_title' => null,
                'student_name' => null,
                'created_at' => '2024-12-15 09:00:00',
                'payout_date' => '2024-12-16 14:30:00'
            ]
        ],
        'monthly_earnings' => [850000, 920000, 1150000, 780000, 1020000, 1350000, 1680000, 1240000, 1430000, 1150000, 1580000, 1720000]
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
        
        return [
            ['id' => 1, 'title' => 'Kursus Memasak: Sop Buntut'],
            ['id' => 2, 'title' => 'Rendang Padang Asli'],
            ['id' => 3, 'title' => 'Fotografi Makanan'],
            ['id' => 4, 'title' => 'Bisnis Kuliner Online'],
            ['id' => 5, 'title' => 'Kerajinan Anyaman Bambu']
        ];
        
    } catch (Exception $e) {
        error_log("Error getting courses: " . $e->getMessage());
        return [];
    }
}

/**
 * Apply Filters to Earnings Data
 */
function applyEarningsFilters($transactions, $courseFilter, $periodFilter) {
    $filtered = $transactions;
    
    // Filter by course
    if ($courseFilter !== 'all') {
        $filtered = array_filter($filtered, function($transaction) use ($courseFilter) {
            return $transaction['course_id'] == $courseFilter;
        });
    }
    
    // Filter by period
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$periodFilter} days"));
    $filtered = array_filter($filtered, function($transaction) use ($cutoffDate) {
        return $transaction['created_at'] >= $cutoffDate;
    });
    
    return array_values($filtered);
}

/**
 * Calculate Summary Statistics
 */
function calculateEarningsSummary($filteredTransactions, $allData) {
    $completedTransactions = array_filter($filteredTransactions, function($t) {
        return $t['status'] === 'completed' && $t['transaction_type'] === 'course_sale';
    });
    
    $totalEarnings = array_sum(array_column($completedTransactions, 'net_amount'));
    $totalSales = count($completedTransactions);
    $avgPerSale = $totalSales > 0 ? $totalEarnings / $totalSales : 0;
    
    // Calculate trend (comparison with previous period)
    $currentMonth = date('n');
    $previousMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;
    $currentMonthEarnings = $allData['monthly_earnings'][$currentMonth - 1] ?? 0;
    $previousMonthEarnings = $allData['monthly_earnings'][$previousMonth - 1] ?? 0;
    
    $trend = 0;
    if ($previousMonthEarnings > 0) {
        $trend = (($currentMonthEarnings - $previousMonthEarnings) / $previousMonthEarnings) * 100;
    }
    
    return [
        'total_earnings' => $totalEarnings ?: $allData['total_earnings'],
        'total_sales' => $totalSales ?: $allData['total_sales'],
        'avg_per_sale' => $avgPerSale ?: $allData['avg_per_sale'],
        'available_balance' => $allData['available_balance'],
        'trend_percentage' => round($trend, 1)
    ];
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
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
    
    return $labels[$type] ?? 'Transaksi Lain';
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
    
    return $classes[$status] ?? 'status-pending';
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
    
    return $classes[$status] ?? 'payout-pending';
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
        <nav class="header-nav">
            <span>Notifikasi</span>
            <span>Pesan</span>
            <span>Profil</span>
        </nav>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/MindCraft-Project/views/mentor/dashboard.php">Dashboard</a></li>
                <li><a href="/MindCraft-Project/views/mentor/kursus-saya.php">Kursus Saya</a></li>
                <li><a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php">Buat Kursus Baru</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pendapatan.php" class="active">Pendapatan</a></li>
                <li><a href="/MindCraft-Project/views/mentor/reviews.php">Ulasan & Feedback</a></li>
                <li><a href="/MindCraft-Project/views/mentor/analitik.php">Analitik</a></li>
                <li><a href="/MindCraft-Project/views/mentor/pengaturan.php">Pengaturan</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Pendapatan</h1>
            </div>
            
            <div class="content-body">
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
                        <div class="summary-subtitle">Bulan lalu: <?php echo formatCurrency($earningsData['monthly_earnings'][date('n') - 2] ?? 0); ?></div>
                    </div>
                    
                    <div class="summary-card fade-in-up" style="animation-delay: 0.2s;">
                        <div class="summary-title">Penjualan Kursus</div>
                        <div class="summary-amount"><?php echo number_format($summaryStats['total_sales']); ?> Kursus</div>
                        <div class="summary-trend">▲ 12% dari bulan lalu</div>
                        <div class="summary-subtitle">Bulan lalu: <?php echo number_format(max(0, $summaryStats['total_sales'] - round($summaryStats['total_sales'] * 0.12))); ?></div>
                    </div>
                    
                    <div class="summary-card fade-in-up" style="animation-delay: 0.3s;">
                        <div class="summary-title">Rata-rata per Kursus</div>
                        <div class="summary-amount"><?php echo formatCurrency($summaryStats['avg_per_sale']); ?></div>
                        <div class="summary-trend">▲ 12% dari bulan lalu</div>
                        <div class="summary-subtitle">Bulan lalu: <?php echo formatCurrency($summaryStats['avg_per_sale'] * 0.88); ?></div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-section fade-in-up" style="animation-delay: 0.4s;">
                    <div class="chart-header">
                        <h2 class="chart-title">Tren Pendapatan Bulanan</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="earningsChart"></canvas>
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
                            💳 Tarik Dana
                        </button>
                        <button id="withdrawalHistoryBtn" class="btn btn-secondary">
                            📋 Riwayat Penarikan
                        </button>
                        <button onclick="exportEarningsData()" class="btn btn-secondary">
                            📊 Export Data
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
                                    <?php foreach ($filteredTransactions as $transaction): ?>
                                    <tr>
                                        <td>
                                            <div class="transaction-info">
                                                <div class="transaction-type">
                                                    <?php echo getTransactionTypeLabel($transaction['transaction_type']); ?>
                                                </div>
                                                <?php if ($transaction['course_title']): ?>
                                                    <div class="transaction-course">
                                                        <?php echo htmlspecialchars($transaction['course_title']); ?>
                                                        <?php if ($transaction['student_name']): ?>
                                                            - <?php echo htmlspecialchars($transaction['student_name']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="amount-cell">
                                                <div class="amount-gross">
                                                    <?php if ($transaction['transaction_type'] === 'withdrawal'): ?>
                                                        -<?php echo formatCurrency(abs($transaction['net_amount'])); ?>
                                                    <?php else: ?>
                                                        <?php echo formatCurrency($transaction['amount']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($transaction['transaction_type'] === 'course_sale'): ?>
                                                    <div class="amount-net">Bersih: <?php echo formatCurrency($transaction['net_amount']); ?></div>
                                                    <div class="amount-fee">Fee platform: <?php echo formatCurrency($transaction['platform_fee']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusBadgeClass($transaction['status']); ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="payout-badge <?php echo getPayoutBadgeClass($transaction['payout_status']); ?>">
                                                <?php echo ucfirst($transaction['payout_status']); ?>
                                            </span>
                                        </td>
                                        <td class="date-cell">
                                            <?php echo formatDate($transaction['created_at']); ?>
                                            <?php if ($transaction['payout_date']): ?>
                                                <br><small style="color: #2B992B;">Dibayar: <?php echo formatDate($transaction['payout_date']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_pendapatan.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.earningsData = {
            monthlyEarnings: <?php echo json_encode($earningsData['monthly_earnings']); ?>,
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            totalEarnings: <?php echo $summaryStats['total_earnings']; ?>,
            totalSales: <?php echo $summaryStats['total_sales']; ?>,
            avgPerSale: <?php echo $summaryStats['avg_per_sale']; ?>,
            availableBalance: <?php echo $summaryStats['available_balance']; ?>
        };

        window.earningsTransactions = <?php echo json_encode($filteredTransactions); ?>;
        window.currentFilters = {
            course: '<?php echo $courseFilter; ?>',
            period: '<?php echo $periodFilter; ?>'
        };
    </script>
</body>
</html>
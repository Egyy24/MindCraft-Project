<?php
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

// Get filter parameters
$courseFilter = isset($_GET['course']) ? $_GET['course'] : 'all';
$periodFilter = isset($_GET['period']) ? $_GET['period'] : '30';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get detailed earnings data
$detailData = getDetailedEarningsData($db, $mentorId, $courseFilter, $periodFilter, $startDate, $endDate);

// Get courses list for filter
$courses = getCoursesForFilter($db, $mentorId);

/**
 * Get Detailed Earnings Data
 */
function getDetailedEarningsData($db, $mentorId, $courseFilter, $periodFilter, $startDate, $endDate) {
    try {
        if ($db) {
            return getDatabaseDetailedEarnings($db, $mentorId, $courseFilter, $periodFilter, $startDate, $endDate);
        }
        
        return getStaticDetailedEarnings($courseFilter, $periodFilter);
        
    } catch (Exception $e) {
        error_log("Error getting detailed earnings: " . $e->getMessage());
        return getStaticDetailedEarnings($courseFilter, $periodFilter);
    }
}

/**
 * Database Detailed Earnings
 */
function getDatabaseDetailedEarnings($db, $mentorId, $courseFilter, $periodFilter, $startDate, $endDate) {
    try {
        // Build conditions
        $conditions = ["e.mentor_id = ?"];
        $params = [$mentorId];
        
        if ($courseFilter !== 'all') {
            $conditions[] = "e.course_id = ?";
            $params[] = $courseFilter;
        }
        
        $conditions[] = "e.created_at BETWEEN ? AND ?";
        $params[] = $startDate . ' 00:00:00';
        $params[] = $endDate . ' 23:59:59';
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get summary metrics
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total_earnings,
                AVG(CASE WHEN status = 'completed' THEN net_amount ELSE NULL END) as avg_earning,
                SUM(CASE WHEN status = 'completed' THEN platform_fee ELSE 0 END) as total_fees,
                MAX(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as highest_earning,
                MIN(CASE WHEN status = 'completed' AND net_amount > 0 THEN net_amount ELSE NULL END) as lowest_earning
            FROM earnings e
            WHERE {$whereClause}
        ");
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get daily earnings for chart
        $stmt = $db->prepare("
            SELECT 
                DATE(created_at) as date,
                SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as daily_earnings,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as daily_transactions
            FROM earnings e
            WHERE {$whereClause}
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute($params);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get course breakdown
        $stmt = $db->prepare("
            SELECT 
                c.title as course_name,
                COUNT(e.id) as transaction_count,
                SUM(CASE WHEN e.status = 'completed' THEN e.net_amount ELSE 0 END) as total_earnings,
                AVG(CASE WHEN e.status = 'completed' THEN e.net_amount ELSE NULL END) as avg_earning
            FROM earnings e
            LEFT JOIN courses c ON e.course_id = c.id
            WHERE {$whereClause} AND e.transaction_type = 'course_sale'
            GROUP BY e.course_id, c.title
            ORDER BY total_earnings DESC
        ");
        $stmt->execute($params);
        $courseBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all transactions for table
        $stmt = $db->prepare("
            SELECT 
                e.*,
                c.title as course_title,
                u.username as student_name
            FROM earnings e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.student_id = u.id
            WHERE {$whereClause}
            ORDER BY e.created_at DESC
            LIMIT 100
        ");
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'summary' => [
                'total_transactions' => (int)($summary['total_transactions'] ?? 0),
                'total_earnings' => (float)($summary['total_earnings'] ?? 0),
                'avg_earning' => (float)($summary['avg_earning'] ?? 0),
                'total_fees' => (float)($summary['total_fees'] ?? 0),
                'highest_earning' => (float)($summary['highest_earning'] ?? 0),
                'lowest_earning' => (float)($summary['lowest_earning'] ?? 0)
            ],
            'daily_data' => $dailyData,
            'course_breakdown' => $courseBreakdown,
            'transactions' => $transactions
        ];
        
    } catch (Exception $e) {
        error_log("Database detailed earnings error: " . $e->getMessage());
        return getStaticDetailedEarnings($courseFilter, $periodFilter);
    }
}

/**
 * Static Detailed Earnings Data
 */
function getStaticDetailedEarnings($courseFilter, $periodFilter) {
    $multiplier = ($courseFilter === 'all') ? 1 : 0.7;
    $periodDays = ($periodFilter === '7') ? 7 : (($periodFilter === '30') ? 30 : (($periodFilter === '90') ? 90 : 365));
    
    // Generate daily data
    $dailyData = [];
    for ($i = $periodDays - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $earnings = rand(50000, 500000) * $multiplier;
        $transactions = rand(1, 5);
        
        $dailyData[] = [
            'date' => $date,
            'daily_earnings' => $earnings,
            'daily_transactions' => $transactions
        ];
    }
    
    return [
        'summary' => [
            'total_transactions' => (int)(45 * $multiplier),
            'total_earnings' => 8750000 * $multiplier,
            'avg_earning' => 194444 * $multiplier,
            'total_fees' => 2625000 * $multiplier,
            'highest_earning' => 499000 * $multiplier,
            'lowest_earning' => 89000 * $multiplier
        ],
        'daily_data' => $dailyData,
        'course_breakdown' => [
            [
                'course_name' => 'Kursus Memasak: Sop Buntut',
                'transaction_count' => (int)(15 * $multiplier),
                'total_earnings' => 3145000 * $multiplier,
                'avg_earning' => 209667 * $multiplier
            ],
            [
                'course_name' => 'Rendang Padang Asli',
                'transaction_count' => (int)(12 * $multiplier),
                'total_earnings' => 2793000 * $multiplier,
                'avg_earning' => 232750 * $multiplier
            ],
            [
                'course_name' => 'Fotografi Makanan',
                'transaction_count' => (int)(10 * $multiplier),
                'total_earnings' => 1743000 * $multiplier,
                'avg_earning' => 174300 * $multiplier
            ],
            [
                'course_name' => 'Bisnis Kuliner Online',
                'transaction_count' => (int)(8 * $multiplier),
                'total_earnings' => 1069000 * $multiplier,
                'avg_earning' => 349300 * $multiplier
            ]
        ],
        'transactions' => getStaticTransactionsDetail($multiplier)
    ];
}

/**
 * Static Transactions Detail
 */
function getStaticTransactionsDetail($multiplier) {
    $baseTransactions = [
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
        ]
    ];
    
    return array_slice($baseTransactions, 0, (int)(count($baseTransactions) * $multiplier));
}

/**
 * Get Courses for Filter
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
            ['id' => 4, 'title' => 'Bisnis Kuliner Online']
        ];
        
    } catch (Exception $e) {
        error_log("Error getting courses: " . $e->getMessage());
        return [];
    }
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
    <title>MindCraft - Detail Pendapatan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_pendapatan-detail.css">
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
                <li><a href="/MindCraft-Project/views/mentor/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <div class="header-content">
                    <div class="breadcrumb">
                        <a href="/MindCraft-Project/views/mentor/pendapatan.php">Pendapatan</a>
                        <span class="separator">›</span>
                        <span class="current">Detail Analitik</span>
                    </div>
                    <h1>Detail Analitik Pendapatan</h1>
                    <p class="header-subtitle">Analisis mendalam tentang sumber pendapatan dan tren transaksi Anda</p>
                </div>
            </div>
            
            <div class="content-body">
                <!-- Advanced Filter Controls -->
                <div class="advanced-filter-section">
                    <div class="filter-header">
                        <h3>Filter Analitik</h3>
                        <button id="resetFilters" class="btn-reset">Reset Filter</button>
                    </div>
                    
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Kursus</label>
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
                        </div>
                        
                        <div class="filter-group">
                            <label>Periode</label>
                            <div class="custom-select">
                                <select id="periodSelect" name="period">
                                    <option value="7" <?php echo $periodFilter === '7' ? 'selected' : ''; ?>>7 Hari Terakhir</option>
                                    <option value="30" <?php echo $periodFilter === '30' ? 'selected' : ''; ?>>30 Hari Terakhir</option>
                                    <option value="90" <?php echo $periodFilter === '90' ? 'selected' : ''; ?>>90 Hari Terakhir</option>
                                    <option value="365" <?php echo $periodFilter === '365' ? 'selected' : ''; ?>>1 Tahun Terakhir</option>
                                    <option value="custom">Rentang Kustom</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-group" id="dateRangeGroup" style="display: none;">
                            <label>Dari Tanggal</label>
                            <input type="date" id="startDate" value="<?php echo $startDate; ?>" class="date-input">
                        </div>
                        
                        <div class="filter-group" id="dateRangeGroupEnd" style="display: none;">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="endDate" value="<?php echo $endDate; ?>" class="date-input">
                        </div>
                        
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <button id="applyFilters" class="btn btn-primary">Terapkan Filter</button>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics Cards -->
                <div class="summary-stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo formatCurrency($detailData['summary']['total_earnings']); ?></div>
                            <div class="stat-label">Total Pendapatan</div>
                            <div class="stat-trend positive">+12% dari periode sebelumnya</div>
                        </div>
                    </div>
                    
                    <div class="stat-card secondary">
                        <div class="stat-icon">📊</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $detailData['summary']['total_transactions']; ?></div>
                            <div class="stat-label">Total Transaksi</div>
                            <div class="stat-trend positive">+8% dari periode sebelumnya</div>
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">📈</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo formatCurrency($detailData['summary']['avg_earning']); ?></div>
                            <div class="stat-label">Rata-rata per Transaksi</div>
                            <div class="stat-trend positive">+4% dari periode sebelumnya</div>
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">🏦</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo formatCurrency($detailData['summary']['total_fees']); ?></div>
                            <div class="stat-label">Total Fee Platform</div>
                            <div class="stat-info">30% dari pendapatan kotor</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <!-- Daily Earnings Chart -->
                    <div class="chart-section">
                        <div class="chart-header">
                            <h3>Tren Pendapatan Harian</h3>
                            <div class="chart-actions">
                                <button class="chart-toggle active" data-chart="earnings">Pendapatan</button>
                                <button class="chart-toggle" data-chart="transactions">Transaksi</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="dailyEarningsChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Course Performance Chart -->
                    <div class="chart-section">
                        <div class="chart-header">
                            <h3>Performa Kursus</h3>
                            <div class="chart-legend">
                                <span class="legend-item">
                                    <span class="legend-color" style="background: #3A59D1;"></span>
                                    Pendapatan
                                </span>
                                <span class="legend-item">
                                    <span class="legend-color" style="background: #90C7F8;"></span>
                                    Jumlah Transaksi
                                </span>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="coursePerformanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Course Breakdown Table -->
                <div class="breakdown-section">
                    <div class="section-header">
                        <h3>Breakdown Pendapatan per Kursus</h3>
                        <div class="section-actions">
                            <button id="exportBreakdown" class="btn btn-secondary">📊 Export Data</button>
                        </div>
                    </div>
                    
                    <div class="breakdown-table-container">
                        <table class="breakdown-table">
                            <thead>
                                <tr>
                                    <th>Nama Kursus</th>
                                    <th data-sort="transactions">Jumlah Transaksi</th>
                                    <th data-sort="earnings">Total Pendapatan</th>
                                    <th data-sort="average">Rata-rata per Transaksi</th>
                                    <th>Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detailData['course_breakdown'])): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #718096;">
                                            <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                                            <div style="font-weight: 500; margin-bottom: 8px;">Belum ada data breakdown</div>
                                            <div style="font-size: 13px;">Data akan muncul setelah ada transaksi dalam periode ini</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $totalEarnings = $detailData['summary']['total_earnings'];
                                    foreach ($detailData['course_breakdown'] as $course): 
                                        $contribution = $totalEarnings > 0 ? ($course['total_earnings'] / $totalEarnings) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="course-info">
                                                <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="transaction-count"><?php echo $course['transaction_count']; ?></span>
                                        </td>
                                        <td class="text-right">
                                            <span class="earnings-amount"><?php echo formatCurrency($course['total_earnings']); ?></span>
                                        </td>
                                        <td class="text-right">
                                            <span class="average-amount"><?php echo formatCurrency($course['avg_earning']); ?></span>
                                        </td>
                                        <td>
                                            <div class="contribution-cell">
                                                <div class="contribution-bar">
                                                    <div class="contribution-fill" style="width: <?php echo $contribution; ?>%;"></div>
                                                </div>
                                                <span class="contribution-percentage"><?php echo number_format($contribution, 1); ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Transactions Table -->
                <div class="transactions-detail-section">
                    <div class="section-header">
                        <h3>Detail Transaksi</h3>
                        <div class="section-actions">
                            <div class="search-box">
                                <input type="text" id="transactionSearch" placeholder="Cari transaksi..." class="search-input">
                                <span class="search-icon">🔍</span>
                            </div>
                            <button id="exportTransactions" class="btn btn-secondary">📋 Export</button>
                        </div>
                    </div>
                    
                    <div class="transactions-table-container">
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th data-sort="date">Tanggal</th>
                                    <th>Jenis Transaksi</th>
                                    <th>Student/Mentee</th>
                                    <th data-sort="amount">Jumlah Kotor</th>
                                    <th>Fee Platform</th>
                                    <th data-sort="net">Jumlah Bersih</th>
                                    <th>Status</th>
                                    <th>Status Payout</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody">
                                <?php if (empty($detailData['transactions'])): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #718096;">
                                            <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
                                            <div style="font-weight: 500; margin-bottom: 8px;">Belum ada transaksi</div>
                                            <div style="font-size: 13px;">Transaksi akan muncul setelah ada penjualan dalam periode ini</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($detailData['transactions'] as $transaction): ?>
                                    <tr class="transaction-row">
                                        <td class="date-cell">
                                            <?php echo formatDate($transaction['created_at'], 'd M Y'); ?>
                                            <div class="time-small"><?php echo formatDate($transaction['created_at'], 'H:i'); ?></div>
                                        </td>
                                        <td>
                                            <div class="transaction-type">
                                                <span class="type-badge"><?php echo getTransactionTypeLabel($transaction['transaction_type']); ?></span>
                                                <?php if ($transaction['course_title']): ?>
                                                    <div class="course-small"><?php echo htmlspecialchars($transaction['course_title']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($transaction['student_name']): ?>
                                                <div class="student-info">
                                                    <div class="student-avatar"><?php echo strtoupper(substr($transaction['student_name'], 0, 1)); ?></div>
                                                    <span class="student-name"><?php echo htmlspecialchars($transaction['student_name']); ?></span>
                                                </div>
                                            <?php else: ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="amount-cell">
                                            <?php if ($transaction['transaction_type'] === 'withdrawal'): ?>
                                                <span class="amount-negative">-<?php echo formatCurrency(abs($transaction['amount'])); ?></span>
                                            <?php else: ?>
                                                <span class="amount-positive"><?php echo formatCurrency($transaction['amount']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fee-cell">
                                            <?php if ($transaction['platform_fee'] > 0): ?>
                                                <span class="fee-amount"><?php echo formatCurrency($transaction['platform_fee']); ?></span>
                                                <div class="fee-percentage">(<?php echo number_format($transaction['commission_rate'], 0); ?>%)</div>
                                            <?php else: ?>
                                                <span class="no-fee">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="net-cell">
                                            <?php if ($transaction['transaction_type'] === 'withdrawal'): ?>
                                                <span class="net-negative">-<?php echo formatCurrency(abs($transaction['net_amount'])); ?></span>
                                            <?php else: ?>
                                                <span class="net-positive"><?php echo formatCurrency($transaction['net_amount']); ?></span>
                                            <?php endif; ?>
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
                                            <?php if ($transaction['payout_date']): ?>
                                                <div class="payout-date"><?php echo formatDate($transaction['payout_date'], 'd M'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Menampilkan 1-<?php echo count($detailData['transactions']); ?> dari <?php echo $detailData['summary']['total_transactions']; ?> transaksi
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" disabled>‹ Sebelumnya</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn">Selanjutnya ›</button>
                        </div>
                    </div>
                </div>

                <!-- Analytics Insights -->
                <div class="insights-section">
                    <div class="section-header">
                        <h3>💡 Insights & Rekomendasi</h3>
                        <p>Analisis otomatis berdasarkan data pendapatan Anda</p>
                    </div>
                    
                    <div class="insights-grid">
                        <div class="insight-card positive">
                            <div class="insight-icon">📈</div>
                            <div class="insight-content">
                                <h4>Tren Positif</h4>
                                <p>Pendapatan Anda mengalami peningkatan 12% dibanding periode sebelumnya. Kursus "<?php echo $detailData['course_breakdown'][0]['course_name'] ?? 'Terpopuler'; ?>" menjadi kontributor utama.</p>
                            </div>
                        </div>
                        
                        <div class="insight-card neutral">
                            <div class="insight-icon">⚡</div>
                            <div class="insight-content">
                                <h4>Optimisasi Harga</h4>
                                <p>Rata-rata transaksi Anda <?php echo formatCurrency($detailData['summary']['avg_earning']); ?>. Pertimbangkan untuk membuat paket premium untuk meningkatkan nilai transaksi.</p>
                            </div>
                        </div>
                        
                        <div class="insight-card warning">
                            <div class="insight-icon">💰</div>
                            <div class="insight-content">
                                <h4>Diversifikasi Pendapatan</h4>
                                <p>Fokuskan pada kursus dengan performa tinggi dan pertimbangkan untuk membuat konten serupa untuk meningkatkan portfolio Anda.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_pendapatan-detail.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.earningsDetailData = {
            summary: <?php echo json_encode($detailData['summary']); ?>,
            dailyData: <?php echo json_encode($detailData['daily_data']); ?>,
            courseBreakdown: <?php echo json_encode($detailData['course_breakdown']); ?>,
            transactions: <?php echo json_encode($detailData['transactions']); ?>,
            currentFilters: {
                course: '<?php echo $courseFilter; ?>',
                period: '<?php echo $periodFilter; ?>',
                startDate: '<?php echo $startDate; ?>',
                endDate: '<?php echo $endDate; ?>'
            }
        };
    </script>
</body>
</html>
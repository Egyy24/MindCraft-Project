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
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$periodFilter = isset($_GET['period']) ? $_GET['period'] : '30';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get withdrawal history data
$withdrawalData = getWithdrawalHistoryData($db, $mentorId, $statusFilter, $periodFilter, $startDate, $endDate);

// Get available balance
$availableBalance = getAvailableBalance($db, $mentorId);

/**
 * Get Withdrawal History Data
 */
function getWithdrawalHistoryData($db, $mentorId, $statusFilter, $periodFilter, $startDate, $endDate) {
    try {
        if ($db) {
            return getDatabaseWithdrawalHistory($db, $mentorId, $statusFilter, $periodFilter, $startDate, $endDate);
        }
        
        return getStaticWithdrawalHistory($statusFilter, $periodFilter);
        
    } catch (Exception $e) {
        error_log("Error getting withdrawal history: " . $e->getMessage());
        return getStaticWithdrawalHistory($statusFilter, $periodFilter);
    }
}

/**
 * Database Withdrawal History
 */
function getDatabaseWithdrawalHistory($db, $mentorId, $statusFilter, $periodFilter, $startDate, $endDate) {
    try {
        // Build conditions
        $conditions = ["e.mentor_id = ?", "e.transaction_type = 'withdrawal'"];
        $params = [$mentorId];
        
        if ($statusFilter !== 'all') {
            $conditions[] = "e.payout_status = ?";
            $params[] = $statusFilter;
        }
        
        $conditions[] = "e.created_at BETWEEN ? AND ?";
        $params[] = $startDate . ' 00:00:00';
        $params[] = $endDate . ' 23:59:59';
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get summary statistics
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_withdrawals,
                SUM(ABS(net_amount)) as total_amount,
                AVG(ABS(net_amount)) as avg_amount,
                COUNT(CASE WHEN payout_status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN payout_status = 'paid' THEN 1 END) as completed_count,
                COUNT(CASE WHEN payout_status = 'hold' THEN 1 END) as failed_count
            FROM earnings e
            WHERE {$whereClause}
        ");
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get monthly withdrawal data for chart
        $stmt = $db->prepare("
            SELECT 
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                COUNT(*) as withdrawal_count,
                SUM(ABS(net_amount)) as total_amount
            FROM earnings e
            WHERE e.mentor_id = ? 
            AND e.transaction_type = 'withdrawal'
            AND e.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year, month
        ");
        $stmt->execute([$mentorId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get detailed withdrawal records
        $stmt = $db->prepare("
            SELECT 
                e.*,
                CASE 
                    WHEN e.withdrawal_method = 'bank_transfer' THEN 'Transfer Bank'
                    WHEN e.withdrawal_method = 'gopay' THEN 'GoPay'
                    WHEN e.withdrawal_method = 'ovo' THEN 'OVO'
                    WHEN e.withdrawal_method = 'dana' THEN 'DANA'
                    ELSE 'Transfer Bank'
                END as method_name
            FROM earnings e
            WHERE {$whereClause}
            ORDER BY e.created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format monthly data untuk chart
        $monthlyWithdrawals = array_fill(0, 12, 0);
        foreach ($monthlyData as $month) {
            $index = $month['month'] - 1;
            $monthlyWithdrawals[$index] = (float)$month['total_amount'];
        }
        
        return [
            'summary' => [
                'total_withdrawals' => (int)($summary['total_withdrawals'] ?? 0),
                'total_amount' => (float)($summary['total_amount'] ?? 0),
                'avg_amount' => (float)($summary['avg_amount'] ?? 0),
                'pending_count' => (int)($summary['pending_count'] ?? 0),
                'completed_count' => (int)($summary['completed_count'] ?? 0),
                'failed_count' => (int)($summary['failed_count'] ?? 0)
            ],
            'monthly_data' => $monthlyWithdrawals,
            'withdrawals' => $withdrawals
        ];
        
    } catch (Exception $e) {
        error_log("Database withdrawal history error: " . $e->getMessage());
        return getStaticWithdrawalHistory($statusFilter, $periodFilter);
    }
}

/**
 * Static Withdrawal History Data
 */
function getStaticWithdrawalHistory($statusFilter, $periodFilter) {
    $multiplier = ($statusFilter === 'all') ? 1 : 0.6;
    $periodDays = ($periodFilter === '7') ? 7 : (($periodFilter === '30') ? 30 : (($periodFilter === '90') ? 90 : 365));
    
    $baseWithdrawals = [
        [
            'id' => 1,
            'transaction_type' => 'withdrawal',
            'amount' => 1500000,
            'net_amount' => -1500000,
            'status' => 'completed',
            'payout_status' => 'paid',
            'withdrawal_method' => 'bank_transfer',
            'method_name' => 'Transfer Bank',
            'withdrawal_account' => 'BCA - 1234567890',
            'description' => 'Penarikan dana bulanan',
            'reference_id' => 'WD-2024-001',
            'created_at' => '2024-12-15 09:00:00',
            'payout_date' => '2024-12-16 14:30:00'
        ],
        [
            'id' => 2,
            'transaction_type' => 'withdrawal',
            'amount' => 850000,
            'net_amount' => -850000,
            'status' => 'pending',
            'payout_status' => 'pending',
            'withdrawal_method' => 'bank_transfer',
            'method_name' => 'Transfer Bank',
            'withdrawal_account' => 'BCA - 1234567890',
            'description' => 'Penarikan dana mingguan',
            'reference_id' => 'WD-2024-002',
            'created_at' => '2024-12-20 10:15:00',
            'payout_date' => null
        ],
        [
            'id' => 3,
            'transaction_type' => 'withdrawal',
            'amount' => 2000000,
            'net_amount' => -2000000,
            'status' => 'completed',
            'payout_status' => 'paid',
            'withdrawal_method' => 'gopay',
            'method_name' => 'GoPay',
            'withdrawal_account' => '0812-3456-7890',
            'description' => 'Penarikan dana express',
            'reference_id' => 'WD-2024-003',
            'created_at' => '2024-12-18 16:45:00',
            'payout_date' => '2024-12-18 17:30:00'
        ],
        [
            'id' => 4,
            'transaction_type' => 'withdrawal',
            'amount' => 500000,
            'net_amount' => -500000,
            'status' => 'cancelled',
            'payout_status' => 'hold',
            'withdrawal_method' => 'bank_transfer',
            'method_name' => 'Transfer Bank',
            'withdrawal_account' => 'BCA - 1234567890',
            'description' => 'Penarikan dibatalkan - verifikasi gagal',
            'reference_id' => 'WD-2024-004',
            'created_at' => '2024-12-17 11:20:00',
            'payout_date' => null
        ],
        [
            'id' => 5,
            'transaction_type' => 'withdrawal',
            'amount' => 1200000,
            'net_amount' => -1200000,
            'status' => 'completed',
            'payout_status' => 'paid',
            'withdrawal_method' => 'dana',
            'method_name' => 'DANA',
            'withdrawal_account' => '0856-7890-1234',
            'description' => 'Penarikan dana harian',
            'reference_id' => 'WD-2024-005',
            'created_at' => '2024-12-14 08:30:00',
            'payout_date' => '2024-12-14 09:15:00'
        ]
    ];
    
    $filteredWithdrawals = array_slice($baseWithdrawals, 0, (int)(count($baseWithdrawals) * $multiplier));
    
    return [
        'summary' => [
            'total_withdrawals' => count($filteredWithdrawals),
            'total_amount' => array_sum(array_column($filteredWithdrawals, 'amount')) * $multiplier,
            'avg_amount' => count($filteredWithdrawals) > 0 ? array_sum(array_column($filteredWithdrawals, 'amount')) / count($filteredWithdrawals) : 0,
            'pending_count' => count(array_filter($filteredWithdrawals, function($w) { return $w['payout_status'] === 'pending'; })),
            'completed_count' => count(array_filter($filteredWithdrawals, function($w) { return $w['payout_status'] === 'paid'; })),
            'failed_count' => count(array_filter($filteredWithdrawals, function($w) { return $w['payout_status'] === 'hold'; }))
        ],
        'monthly_data' => [450000, 680000, 1200000, 850000, 1350000, 950000, 1680000, 1240000, 1430000, 1150000, 1580000, 1720000],
        'withdrawals' => $filteredWithdrawals
    ];
}

/**
 * Get Available Balance
 */
function getAvailableBalance($db, $mentorId) {
    try {
        if ($db) {
            $stmt = $db->prepare("
                SELECT SUM(CASE WHEN status = 'completed' AND payout_status = 'pending' THEN net_amount ELSE 0 END) as available_balance
                FROM earnings 
                WHERE mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['available_balance'] ?? 2850000);
        }
        
        return 2850000; // Default static balance
        
    } catch (Exception $e) {
        error_log("Error getting available balance: " . $e->getMessage());
        return 2850000;
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

/**
 * Get Method Icon
 */
function getMethodIcon($method) {
    $icons = [
        'bank_transfer' => '🏦',
        'gopay' => '💚',
        'ovo' => '💜',
        'dana' => '💙',
        'shopeepay' => '🧡'
    ];
    
    return $icons[$method] ?? '🏦';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Riwayat Penarikan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_riwayat-penarikan.css">
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
                        <span class="current">Riwayat Penarikan</span>
                    </div>
                    <div class="header-main">
                        <div class="header-info">
                            <h1>Riwayat Penarikan Dana</h1>
                            <p class="header-subtitle">Kelola dan pantau semua aktivitas penarikan dana Anda</p>
                        </div>
                        <div class="header-actions">
                            <button id="newWithdrawalBtn" class="btn btn-primary">
                                + Tarik Dana Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <!-- Quick Stats Cards -->
                <div class="quick-stats-grid">
                    <div class="stat-card total">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo formatCurrency($withdrawalData['summary']['total_amount']); ?></div>
                            <div class="stat-label">Total Penarikan</div>
                            <div class="stat-meta"><?php echo $withdrawalData['summary']['total_withdrawals']; ?> transaksi</div>
                        </div>
                    </div>
                    
                    <div class="stat-card pending">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $withdrawalData['summary']['pending_count']; ?></div>
                            <div class="stat-label">Menunggu Proses</div>
                            <div class="stat-meta">1-2 hari kerja</div>
                        </div>
                    </div>
                    
                    <div class="stat-card completed">
                        <div class="stat-icon">✅</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $withdrawalData['summary']['completed_count']; ?></div>
                            <div class="stat-label">Berhasil</div>
                            <div class="stat-meta">Sudah diterima</div>
                        </div>
                    </div>
                    
                    <div class="stat-card available">
                        <div class="stat-icon">🏦</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo formatCurrency($availableBalance); ?></div>
                            <div class="stat-label">Saldo Tersedia</div>
                            <div class="stat-meta">Siap ditarik</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-header">
                        <h3>Filter Riwayat</h3>
                        <button id="resetFilters" class="btn-reset">Reset</button>
                    </div>
                    
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Status</label>
                            <div class="custom-select">
                                <select id="statusSelect" name="status">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Menunggu Proses</option>
                                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Berhasil</option>
                                    <option value="hold" <?php echo $statusFilter === 'hold' ? 'selected' : ''; ?>>Ditahan/Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label>Periode</label>
                            <div class="custom-select">
                                <select id="periodSelect" name="period">
                                    <option value="7" <?php echo $periodFilter === '7' ? 'selected' : ''; ?>>7 Hari</option>
                                    <option value="30" <?php echo $periodFilter === '30' ? 'selected' : ''; ?>>30 Hari</option>
                                    <option value="90" <?php echo $periodFilter === '90' ? 'selected' : ''; ?>>90 Hari</option>
                                    <option value="365" <?php echo $periodFilter === '365' ? 'selected' : ''; ?>>1 Tahun</option>
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
                            <button id="applyFilters" class="btn btn-primary">Terapkan</button>
                        </div>
                    </div>
                </div>

                <!-- Monthly Chart Section -->
                <div class="chart-section">
                    <div class="chart-header">
                        <h3>Tren Penarikan Bulanan</h3>
                        <div class="chart-info">
                            <span class="chart-metric">Rata-rata: <?php echo formatCurrency($withdrawalData['summary']['avg_amount']); ?></span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="withdrawalChart"></canvas>
                    </div>
                </div>

                <!-- Withdrawal Methods Overview -->
                <div class="methods-overview">
                    <div class="section-header">
                        <h3>Metode Penarikan Favorit</h3>
                        <p>Pilih metode yang paling nyaman untuk Anda</p>
                    </div>
                    
                    <div class="methods-grid">
                        <div class="method-card active">
                            <div class="method-icon">🏦</div>
                            <div class="method-info">
                                <h4>Transfer Bank</h4>
                                <p>1-2 hari kerja • Gratis</p>
                                <div class="method-usage">Paling sering digunakan</div>
                            </div>
                        </div>
                        
                        <div class="method-card">
                            <div class="method-icon">💚</div>
                            <div class="method-info">
                                <h4>GoPay</h4>
                                <p>Instan • Gratis</p>
                                <div class="method-usage">Tercepat</div>
                            </div>
                        </div>
                        
                        <div class="method-card">
                            <div class="method-icon">💙</div>
                            <div class="method-info">
                                <h4>DANA</h4>
                                <p>Instan • Gratis</p>
                                <div class="method-usage">Populer</div>
                            </div>
                        </div>
                        
                        <div class="method-card">
                            <div class="method-icon">💜</div>
                            <div class="method-info">
                                <h4>OVO</h4>
                                <p>Instan • Gratis</p>
                                <div class="method-usage">Mudah</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal History Table -->
                <div class="history-section">
                    <div class="section-header">
                        <h3>Riwayat Transaksi Penarikan</h3>
                        <div class="section-actions">
                            <div class="search-box">
                                <input type="text" id="historySearch" placeholder="Cari transaksi..." class="search-input">
                                <span class="search-icon">🔍</span>
                            </div>
                            <button id="exportHistory" class="btn btn-secondary">📊 Export</button>
                        </div>
                    </div>
                    
                    <div class="history-table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th data-sort="date">Tanggal</th>
                                    <th>Metode</th>
                                    <th>Akun Tujuan</th>
                                    <th data-sort="amount">Jumlah</th>
                                    <th>Status</th>
                                    <th>Referensi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <?php if (empty($withdrawalData['withdrawals'])): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px; color: #718096;">
                                            <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
                                            <div style="font-weight: 500; margin-bottom: 8px;">Belum ada riwayat penarikan</div>
                                            <div style="font-size: 13px;">Riwayat akan muncul setelah Anda melakukan penarikan pertama</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($withdrawalData['withdrawals'] as $withdrawal): ?>
                                    <tr class="withdrawal-row">
                                        <td class="date-cell">
                                            <div class="date-main"><?php echo formatDate($withdrawal['created_at'], 'd M Y'); ?></div>
                                            <div class="time-small"><?php echo formatDate($withdrawal['created_at'], 'H:i'); ?></div>
                                        </td>
                                        <td>
                                            <div class="method-info">
                                                <span class="method-icon-small"><?php echo getMethodIcon($withdrawal['withdrawal_method']); ?></span>
                                                <span class="method-name"><?php echo htmlspecialchars($withdrawal['method_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="account-info">
                                                <div class="account-number"><?php echo htmlspecialchars($withdrawal['withdrawal_account']); ?></div>
                                            </div>
                                        </td>
                                        <td class="amount-cell">
                                            <span class="amount-value"><?php echo formatCurrency(abs($withdrawal['net_amount'])); ?></span>
                                        </td>
                                        <td>
                                            <div class="status-group">
                                                <span class="status-badge <?php echo getStatusBadgeClass($withdrawal['status']); ?>">
                                                    <?php echo ucfirst($withdrawal['status']); ?>
                                                </span>
                                                <span class="payout-badge <?php echo getPayoutBadgeClass($withdrawal['payout_status']); ?>">
                                                    <?php echo ucfirst($withdrawal['payout_status']); ?>
                                                </span>
                                            </div>
                                            <?php if ($withdrawal['payout_date']): ?>
                                                <div class="completion-date">Selesai: <?php echo formatDate($withdrawal['payout_date'], 'd M'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="reference-info">
                                                <code class="reference-id"><?php echo htmlspecialchars($withdrawal['reference_id']); ?></code>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-detail" data-id="<?php echo $withdrawal['id']; ?>" title="Lihat Detail">👁️</button>
                                                <?php if ($withdrawal['payout_status'] === 'pending'): ?>
                                                    <button class="btn-action btn-cancel" data-id="<?php echo $withdrawal['id']; ?>" title="Batalkan">❌</button>
                                                <?php endif; ?>
                                                <button class="btn-action btn-receipt" data-id="<?php echo $withdrawal['id']; ?>" title="Unduh Bukti">📄</button>
                                            </div>
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
                            Menampilkan 1-<?php echo count($withdrawalData['withdrawals']); ?> dari <?php echo $withdrawalData['summary']['total_withdrawals']; ?> transaksi
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
            </div>
        </main>
    </div>

    <!-- Withdrawal Detail Modal -->
    <div id="withdrawalModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Penarikan</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MindCraft-Project/assets/js/mentor_riwayat-penarikan.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.withdrawalHistoryData = {
            summary: <?php echo json_encode($withdrawalData['summary']); ?>,
            monthlyData: <?php echo json_encode($withdrawalData['monthly_data']); ?>,
            withdrawals: <?php echo json_encode($withdrawalData['withdrawals']); ?>,
            availableBalance: <?php echo $availableBalance; ?>,
            currentFilters: {
                status: '<?php echo $statusFilter; ?>',
                period: '<?php echo $periodFilter; ?>',
                startDate: '<?php echo $startDate; ?>',
                endDate: '<?php echo $endDate; ?>'
            }
        };
    </script>
</body>
</html>
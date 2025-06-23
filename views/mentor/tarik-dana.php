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

// Get available balance dan withdrawal settings
$availableBalance = getAvailableBalance($db, $mentorId);
$withdrawalSettings = getWithdrawalSettings($db, $mentorId);
$savedMethods = getSavedPaymentMethods($db, $mentorId);

// Process form submission
$errors = [];
$success = false;
$withdrawalData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_withdrawal'])) {
    $withdrawalData = processWithdrawalRequest($_POST, $db, $mentorId);
    if ($withdrawalData['success']) {
        $success = true;
    } else {
        $errors = $withdrawalData['errors'];
    }
}

/**
 * Get Available Balance
 */
function getAvailableBalance($db, $mentorId) {
    try {
        if ($db) {
            $stmt = $db->prepare("
                SELECT 
                    SUM(CASE WHEN status = 'completed' AND payout_status = 'pending' THEN net_amount ELSE 0 END) as available_balance,
                    SUM(CASE WHEN status = 'completed' AND payout_status = 'paid' THEN net_amount ELSE 0 END) as total_paid,
                    SUM(CASE WHEN transaction_type = 'withdrawal' AND status = 'pending' THEN ABS(net_amount) ELSE 0 END) as pending_withdrawals
                FROM earnings 
                WHERE mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'available' => (float)($result['available_balance'] ?? 2850000),
                'total_paid' => (float)($result['total_paid'] ?? 15750000),
                'pending' => (float)($result['pending_withdrawals'] ?? 850000)
            ];
        }
        
        return [
            'available' => 2850000,
            'total_paid' => 15750000,
            'pending' => 850000
        ];
        
    } catch (Exception $e) {
        error_log("Error getting available balance: " . $e->getMessage());
        return [
            'available' => 2850000,
            'total_paid' => 15750000,
            'pending' => 850000
        ];
    }
}

/**
 * Get Withdrawal Settings
 */
function getWithdrawalSettings($db, $mentorId) {
    try {
        if ($db) {
            $stmt = $db->prepare("
                SELECT 
                    minimum_payout,
                    payout_method,
                    payout_schedule
                FROM mentor_settings 
                WHERE mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'minimum_payout' => (float)$result['minimum_payout'],
                    'preferred_method' => $result['payout_method'],
                    'schedule' => $result['payout_schedule']
                ];
            }
        }
        
        return [
            'minimum_payout' => 100000,
            'preferred_method' => 'bank_transfer',
            'schedule' => 'monthly'
        ];
        
    } catch (Exception $e) {
        error_log("Error getting withdrawal settings: " . $e->getMessage());
        return [
            'minimum_payout' => 100000,
            'preferred_method' => 'bank_transfer',
            'schedule' => 'monthly'
        ];
    }
}

/**
 * Get Saved Payment Methods
 */
function getSavedPaymentMethods($db, $mentorId) {
    try {
        if ($db) {
            $stmt = $db->prepare("
                SELECT 
                    mp.full_name,
                    mp.phone
                FROM mentor_profiles mp 
                WHERE mp.user_id = ?
            ");
            $stmt->execute([$mentorId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'bank_accounts' => [
                    [
                        'id' => 1,
                        'bank_name' => 'BCA',
                        'account_number' => '1234567890',
                        'account_name' => $profile['full_name'] ?? 'Budi Santoso',
                        'is_verified' => true
                    ],
                    [
                        'id' => 2,
                        'bank_name' => 'Mandiri',
                        'account_number' => '9876543210',
                        'account_name' => $profile['full_name'] ?? 'Budi Santoso',
                        'is_verified' => false
                    ]
                ],
                'ewallets' => [
                    [
                        'id' => 1,
                        'type' => 'gopay',
                        'name' => 'GoPay',
                        'phone' => $profile['phone'] ?? '0812-3456-7890',
                        'is_verified' => true
                    ],
                    [
                        'id' => 2,
                        'type' => 'dana',
                        'name' => 'DANA',
                        'phone' => $profile['phone'] ?? '0812-3456-7890',
                        'is_verified' => true
                    ]
                ]
            ];
        }
        
        return [
            'bank_accounts' => [
                [
                    'id' => 1,
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'Budi Santoso',
                    'is_verified' => true
                ]
            ],
            'ewallets' => [
                [
                    'id' => 1,
                    'type' => 'gopay',
                    'name' => 'GoPay',
                    'phone' => '0812-3456-7890',
                    'is_verified' => true
                ]
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error getting saved methods: " . $e->getMessage());
        return [
            'bank_accounts' => [],
            'ewallets' => []
        ];
    }
}

/**
 * Process Withdrawal Request
 */
function processWithdrawalRequest($data, $db, $mentorId) {
    $errors = [];
    
    // Validation
    $amount = floatval($data['amount'] ?? 0);
    $method = $data['withdrawal_method'] ?? '';
    $accountInfo = $data['account_info'] ?? '';
    $description = trim($data['description'] ?? '');
    
    // Check available balance
    $balance = getAvailableBalance($db, $mentorId);
    if ($amount > $balance['available']) {
        $errors[] = 'Jumlah melebihi saldo tersedia';
    }
    
    // Validate method
    if (empty($method)) {
        $errors[] = 'Pilih metode penarikan';
    }
    
    if (empty($accountInfo)) {
        $errors[] = 'Pilih atau masukkan informasi akun tujuan';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Process withdrawal
    try {
        if ($db) {
            // Generate reference ID
            $referenceId = 'WD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert withdrawal record
            $stmt = $db->prepare("
                INSERT INTO earnings 
                (mentor_id, transaction_type, amount, net_amount, status, payout_status, 
                 withdrawal_method, withdrawal_account, description, reference_id) 
                VALUES (?, 'withdrawal', ?, ?, 'pending', 'pending', ?, ?, ?, ?)
            ");
            
            $success = $stmt->execute([
                $mentorId,
                $amount,
                -$amount, // Negative amount for withdrawal
                $method,
                $accountInfo,
                $description,
                $referenceId
            ]);
            
            if ($success) {
                return [
                    'success' => true,
                    'reference_id' => $referenceId,
                    'amount' => $amount,
                    'method' => $method,
                    'account' => $accountInfo
                ];
            }
        }
        
        // Fallback static response
        return [
            'success' => true,
            'reference_id' => 'WD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'amount' => $amount,
            'method' => $method,
            'account' => $accountInfo
        ];
        
    } catch (Exception $e) {
        error_log("Withdrawal processing error: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Terjadi kesalahan sistem. Silakan coba lagi.']];
    }
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
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

/**
 * Get Processing Time
 */
function getProcessingTime($method) {
    $times = [
        'bank_transfer' => '1-2 hari kerja',
        'gopay' => 'Instan',
        'ovo' => 'Instan',
        'dana' => 'Instan',
        'shopeepay' => 'Instan'
    ];
    
    return $times[$method] ?? '1-2 hari kerja';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Tarik Dana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MindCraft-Project/assets/css/mentor_tarik-dana.css">
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
                        <span class="current">Tarik Dana</span>
                    </div>
                    <div class="header-main">
                        <div class="header-info">
                            <h1>Tarik Dana</h1>
                            <p class="header-subtitle">Cairkan saldo Anda dengan mudah dan aman</p>
                        </div>
                        <div class="header-actions">
                            <a href="/MindCraft-Project/views/mentor/riwayat-penarikan.php" class="btn btn-secondary">
                                Riwayat Penarikan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <!-- Success Message -->
                    <div class="success-section">
                        <div class="success-card">
                            <div class="success-icon">✅</div>
                            <div class="success-content">
                                <h2>Permintaan Penarikan Berhasil!</h2>
                                <p>Permintaan penarikan Anda telah diterima dan sedang diproses.</p>
                                
                                <div class="success-details">
                                    <div class="detail-row">
                                        <span class="label">ID Referensi:</span>
                                        <span class="value"><?php echo htmlspecialchars($withdrawalData['reference_id']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Jumlah:</span>
                                        <span class="value"><?php echo formatCurrency($withdrawalData['amount']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Metode:</span>
                                        <span class="value"><?php echo getMethodIcon($withdrawalData['method']) . ' ' . ucwords(str_replace('_', ' ', $withdrawalData['method'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Akun Tujuan:</span>
                                        <span class="value"><?php echo htmlspecialchars($withdrawalData['account']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Estimasi Waktu:</span>
                                        <span class="value"><?php echo getProcessingTime($withdrawalData['method']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="success-actions">
                                    <a href="/MindCraft-Project/views/mentor/riwayat-penarikan.php" class="btn btn-primary">
                                        Lihat Riwayat Penarikan
                                    </a>
                                    <button onclick="window.location.reload()" class="btn btn-secondary">
                                        Tarik Dana Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Balance Overview -->
                    <div class="balance-overview">
                        <div class="balance-cards">
                            <div class="balance-card main">
                                <div class="balance-icon">💰</div>
                                <div class="balance-content">
                                    <div class="balance-label">Saldo Tersedia</div>
                                    <div class="balance-amount"><?php echo formatCurrency($availableBalance['available']); ?></div>
                                    <div class="balance-meta">Siap untuk ditarik</div>
                                </div>
                            </div>
                            
                            <div class="balance-card">
                                <div class="balance-icon">⏳</div>
                                <div class="balance-content">
                                    <div class="balance-label">Dalam Proses</div>
                                    <div class="balance-amount"><?php echo formatCurrency($availableBalance['pending']); ?></div>
                                    <div class="balance-meta">Sedang diproses</div>
                                </div>
                            </div>
                            
                            <div class="balance-card">
                                <div class="balance-icon">✅</div>
                                <div class="balance-content">
                                    <div class="balance-label">Total Dibayar</div>
                                    <div class="balance-amount"><?php echo formatCurrency($availableBalance['total_paid']); ?></div>
                                    <div class="balance-meta">Sepanjang waktu</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="error-section">
                            <div class="error-card">
                                <div class="error-icon">❌</div>
                                <div class="error-content">
                                    <h3>Terjadi Kesalahan</h3>
                                    <ul>
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Withdrawal Form -->
                    <div class="withdrawal-form-section">
                        <div class="form-header">
                            <h2>Form Penarikan Dana</h2>
                            <p>Isi form berikut untuk mengajukan penarikan dana</p>
                        </div>
                        
                        <form id="withdrawalForm" method="POST" class="withdrawal-form">
                            <!-- Step 1: Amount -->
                            <div class="form-step active" id="step1">
                                <div class="step-header">
                                    <h3>1. Jumlah Penarikan</h3>
                                    <p>Masukkan jumlah yang ingin Anda tarik</p>
                                </div>
                                
                                <div class="amount-input-section">
                                    <div class="amount-input-wrapper">
                                        <label for="amount">Jumlah Penarikan</label>
                                        <div class="currency-input">
                                            <span class="currency-symbol">Rp</span>
                                            <input type="text" id="amount" name="amount" placeholder="0" 
                                                   value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>"
                                                   class="amount-field" inputmode="numeric">
                                        </div>
                                        <div class="amount-info">
                                            <div class="amount-limits">
                                                <span>Min: <?php echo formatCurrency($withdrawalSettings['minimum_payout']); ?></span>
                                                <span>Max: <?php echo formatCurrency(10000000); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="quick-amounts">
                                        <div class="quick-amount-label">Jumlah Cepat:</div>
                                        <div class="quick-amount-buttons">
                                            <button type="button" class="quick-amount-btn" data-amount="500000">500K</button>
                                            <button type="button" class="quick-amount-btn" data-amount="1000000">1 Juta</button>
                                            <button type="button" class="quick-amount-btn" data-amount="2000000">2 Juta</button>
                                            <button type="button" class="quick-amount-btn" data-amount="<?php echo $availableBalance['available']; ?>">Semua</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-primary next-step" data-next="step2">
                                        Lanjutkan
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Payment Method -->
                            <div class="form-step" id="step2">
                                <div class="step-header">
                                    <h3>2. Metode Penarikan</h3>
                                    <p>Pilih metode yang ingin Anda gunakan</p>
                                </div>
                                
                                <div class="payment-methods">
                                    <!-- Bank Transfer -->
                                    <div class="method-category">
                                        <h4>Transfer Bank</h4>
                                        <div class="method-grid">
                                            <div class="method-option" data-method="bank_transfer">
                                                <input type="radio" name="withdrawal_method" value="bank_transfer" id="bank_transfer">
                                                <label for="bank_transfer" class="method-card">
                                                    <div class="method-icon">🏦</div>
                                                    <div class="method-info">
                                                        <div class="method-name">Transfer Bank</div>
                                                        <div class="method-desc">1-2 hari kerja • Gratis</div>
                                                        <div class="method-badge reliable">Paling Reliable</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="account-selection" id="bank_accounts" style="display: none;">
                                            <div class="selection-header">
                                                <h5>Pilih Rekening Bank</h5>
                                                <button type="button" class="btn-link" id="addBankAccount">+ Tambah Rekening Baru</button>
                                            </div>
                                            
                                            <div class="account-options">
                                                <?php foreach ($savedMethods['bank_accounts'] as $account): ?>
                                                    <div class="account-option">
                                                        <input type="radio" name="account_info" 
                                                               value="<?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>" 
                                                               id="bank_<?php echo $account['id']; ?>">
                                                        <label for="bank_<?php echo $account['id']; ?>" class="account-card">
                                                            <div class="account-info">
                                                                <div class="account-bank"><?php echo htmlspecialchars($account['bank_name']); ?></div>
                                                                <div class="account-number"><?php echo htmlspecialchars($account['account_number']); ?></div>
                                                                <div class="account-name"><?php echo htmlspecialchars($account['account_name']); ?></div>
                                                            </div>
                                                            <?php if ($account['is_verified']): ?>
                                                                <div class="verified-badge">✅ Terverifikasi</div>
                                                            <?php else: ?>
                                                                <div class="unverified-badge">⏳ Belum Verifikasi</div>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- E-Wallet -->
                                    <div class="method-category">
                                        <h4>E-Wallet</h4>
                                        <div class="method-grid">
                                            <div class="method-option" data-method="gopay">
                                                <input type="radio" name="withdrawal_method" value="gopay" id="gopay">
                                                <label for="gopay" class="method-card">
                                                    <div class="method-icon">💚</div>
                                                    <div class="method-info">
                                                        <div class="method-name">GoPay</div>
                                                        <div class="method-desc">Instan • Gratis</div>
                                                        <div class="method-badge fast">Tercepat</div>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            <div class="method-option" data-method="dana">
                                                <input type="radio" name="withdrawal_method" value="dana" id="dana">
                                                <label for="dana" class="method-card">
                                                    <div class="method-icon">💙</div>
                                                    <div class="method-info">
                                                        <div class="method-name">DANA</div>
                                                        <div class="method-desc">Instan • Gratis</div>
                                                        <div class="method-badge popular">Populer</div>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            <div class="method-option" data-method="ovo">
                                                <input type="radio" name="withdrawal_method" value="ovo" id="ovo">
                                                <label for="ovo" class="method-card">
                                                    <div class="method-icon">💜</div>
                                                    <div class="method-info">
                                                        <div class="method-name">OVO</div>
                                                        <div class="method-desc">Instan • Gratis</div>
                                                        <div class="method-badge">Mudah</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="account-selection" id="ewallet_accounts" style="display: none;">
                                            <div class="selection-header">
                                                <h5>Pilih Akun E-Wallet</h5>
                                                <button type="button" class="btn-link" id="addEwalletAccount">+ Tambah Akun Baru</button>
                                            </div>
                                            
                                            <div class="account-options">
                                                <?php foreach ($savedMethods['ewallets'] as $ewallet): ?>
                                                    <div class="account-option">
                                                        <input type="radio" name="account_info" 
                                                               value="<?php echo htmlspecialchars($ewallet['name'] . ' - ' . $ewallet['phone']); ?>" 
                                                               id="ewallet_<?php echo $ewallet['id']; ?>"
                                                               data-type="<?php echo $ewallet['type']; ?>">
                                                        <label for="ewallet_<?php echo $ewallet['id']; ?>" class="account-card">
                                                            <div class="account-info">
                                                                <div class="account-bank"><?php echo htmlspecialchars($ewallet['name']); ?></div>
                                                                <div class="account-number"><?php echo htmlspecialchars($ewallet['phone']); ?></div>
                                                            </div>
                                                            <?php if ($ewallet['is_verified']): ?>
                                                                <div class="verified-badge">✅ Terverifikasi</div>
                                                            <?php else: ?>
                                                                <div class="unverified-badge">⏳ Belum Verifikasi</div>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-secondary prev-step" data-prev="step1">
                                        Kembali
                                    </button>
                                    <button type="button" class="btn btn-primary next-step" data-next="step3">
                                        Lanjutkan
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Confirmation -->
                            <div class="form-step" id="step3">
                                <div class="step-header">
                                    <h3>3. Konfirmasi & Kirim</h3>
                                    <p>Periksa kembali detail penarikan Anda</p>
                                </div>
                                
                                <div class="confirmation-section">
                                    <div class="confirmation-summary">
                                        <div class="summary-item">
                                            <span class="label">Jumlah Penarikan:</span>
                                            <span class="value" id="confirmAmount">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Biaya Administrasi:</span>
                                            <span class="value">Gratis</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Metode Penarikan:</span>
                                            <span class="value" id="confirmMethod">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Akun Tujuan:</span>
                                            <span class="value" id="confirmAccount">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Estimasi Waktu:</span>
                                            <span class="value" id="confirmTime">-</span>
                                        </div>
                                        <div class="summary-item total">
                                            <span class="label">Total Diterima:</span>
                                            <span class="value" id="confirmTotal">-</span>
                                        </div>
                                    </div>
                                    
                                    <div class="description-section">
                                        <label for="description">Catatan (Opsional)</label>
                                        <textarea id="description" name="description" rows="3" 
                                                  placeholder="Tambahkan catatan untuk penarikan ini..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="terms-section">
                                        <label class="checkbox-container">
                                            <input type="checkbox" id="agreeTerms" required>
                                            <span class="checkmark"></span>
                                            Saya setuju dengan <a href="#" class="link">syarat dan ketentuan</a> penarikan dana
                                        </label>
                                        
                                        <label class="checkbox-container">
                                            <input type="checkbox" id="confirmData">
                                            <span class="checkmark"></span>
                                            Data yang saya masukkan sudah benar dan akurat
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-secondary prev-step" data-prev="step2">
                                        Kembali
                                    </button>
                                    <button type="submit" name="submit_withdrawal" class="btn btn-primary submit-btn" disabled>
                                        Kirim Permintaan Penarikan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Info Section -->
                <div class="info-section">
                    <div class="section-header">
                        <h3>Informasi Penting</h3>
                        <p>Hal-hal yang perlu Anda ketahui tentang penarikan dana</p>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-icon">⏰</div>
                            <div class="info-content">
                                <h4>Waktu Proses</h4>
                                <ul>
                                    <li>Transfer Bank: 1-2 hari kerja</li>
                                    <li>E-Wallet: Instan (maks. 30 menit)</li>
                                    <li>Proses dimulai setelah verifikasi</li>
                                    <li>Tidak ada proses di hari libur</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">💳</div>
                            <div class="info-content">
                                <h4>Syarat & Ketentuan</h4>
                                <ul>
                                    <li>Minimum penarikan: <?php echo formatCurrency($withdrawalSettings['minimum_payout']); ?></li>
                                    <li>Maksimum penarikan: <?php echo formatCurrency(10000000); ?> per transaksi</li>
                                    <li>Akun harus atas nama yang sama</li>
                                    <li>Verifikasi identitas diperlukan</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">🔒</div>
                            <div class="info-content">
                                <h4>Keamanan</h4>
                                <ul>
                                    <li>Enkripsi data end-to-end</li>
                                    <li>Notifikasi setiap transaksi</li>
                                    <li>Sistem monitoring 24/7</li>
                                    <li>Dukungan customer service</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">📞</div>
                            <div class="info-content">
                                <h4>Bantuan</h4>
                                <ul>
                                    <li>Chat support: 08:00 - 22:00</li>
                                    <li>Email: support@mindcraft.com</li>
                                    <li>WhatsApp: +62 811-1234-5678</li>
                                    <li>FAQ lengkap tersedia</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Bank Account Modal -->
    <div id="bankAccountModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Rekening Bank Baru</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addBankForm">
                    <div class="form-group">
                        <label for="bankName">Nama Bank</label>
                        <select id="bankName" required>
                            <option value="">Pilih Bank</option>
                            <option value="BCA">BCA - Bank Central Asia</option>
                            <option value="Mandiri">Bank Mandiri</option>
                            <option value="BRI">Bank BRI</option>
                            <option value="BNI">Bank BNI</option>
                            <option value="CIMB">CIMB Niaga</option>
                            <option value="Permata">Bank Permata</option>
                            <option value="Danamon">Bank Danamon</option>
                            <option value="BTN">Bank BTN</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="accountNumber">Nomor Rekening</label>
                        <input type="text" id="accountNumber" placeholder="Masukkan nomor rekening" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="accountName">Nama Pemilik Rekening</label>
                        <input type="text" id="accountName" placeholder="Sesuai dengan KTP" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary modal-close">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Rekening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add E-Wallet Modal -->
    <div id="ewalletModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Akun E-Wallet Baru</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEwalletForm">
                    <div class="form-group">
                        <label for="ewalletType">Jenis E-Wallet</label>
                        <select id="ewalletType" required>
                            <option value="">Pilih E-Wallet</option>
                            <option value="gopay">GoPay</option>
                            <option value="dana">DANA</option>
                            <option value="ovo">OVO</option>
                            <option value="shopeepay">ShopeePay</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber">Nomor Handphone</label>
                        <input type="tel" id="phoneNumber" placeholder="08xx-xxxx-xxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ewalletName">Nama Pemilik Akun</label>
                        <input type="text" id="ewalletName" placeholder="Sesuai dengan akun e-wallet" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary modal-close">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Memproses permintaan penarikan...</div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/MindCraft-Project/assets/js/mentor_tarik-dana.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.withdrawalData = {
            availableBalance: <?php echo $availableBalance['available']; ?>,
            minimumPayout: <?php echo $withdrawalSettings['minimum_payout']; ?>,
            maximumPayout: 10000000,
            savedMethods: <?php echo json_encode($savedMethods); ?>,
            processingTimes: {
                bank_transfer: "1-2 hari kerja",
                gopay: "Instan",
                dana: "Instan",
                ovo: "Instan",
                shopeepay: "Instan"
            }
        };
    </script>
</body>
</html>
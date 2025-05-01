<!-- <?php
session_start();

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$initial = strtoupper(substr($username, 0, 1));
?> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCraft - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>MindCraft</h1>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#" class="active" onclick="showDashboard()"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" onclick="showUsers()"><i class="fas fa-users"></i> Data User</a></li>
                <li><a href="#" onclick="showContent()"><i class="fas fa-book"></i> Data Konten</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div id="dashboard-content">
                <div class="header">
                    <h2>Dashboard</h2>
                    <div class="user-info">
                        <div class="user-avatar"><?= $initial ?></div>
                        <span><?= htmlspecialchars($username) ?></span>
                        <span id="last-updated" class="last-updated"></span>
                    </div>
                </div>

                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="card-header">
                            <div class="icon users">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Total User</h3>
                        </div>
                        <div class="value" id="total-users"><span class="loading"></span></div>
                    </div>

                    <div class="summary-card">
                        <div class="card-header">
                            <div class="icon mentee">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3>Total Mentee</h3>
                        </div>
                        <div class="value" id="total-mentees"><span class="loading"></span></div>
                    </div>

                    <div class="summary-card">
                        <div class="card-header">
                            <div class="icon mentor">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3>Total Mentor</h3>
                        </div>
                        <div class="value" id="total-mentors"><span class="loading"></span></div>
                    </div>

                    <div class="summary-card">
                        <div class="card-header">
                            <div class="icon content">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3>Total Konten</h3>
                        </div>
                        <div class="value" id="total-contents"><span class="loading"></span></div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <h2><i class="fas fa-chart-pie"></i> Distribusi User</h2>
                        <div class="chart-container">
                            <canvas id="userDistributionChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h2><i class="fas fa-chart-line"></i> Pertumbuhan User</h2>
                        <div class="chart-container">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h2><i class="fas fa-chart-pie"></i> Status Konten</h2>
                        <div class="chart-container">
                            <canvas id="contentStatusChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h2><i class="fas fa-chart-bar"></i> Kategori Konten</h2>
                        <div class="chart-container">
                            <canvas id="contentCategoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="activity-card">
                    <h2><i class="fas fa-clock"></i> Aktivitas Terbaru</h2>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Belum ada aktivitas terbaru</p>
                    </div>
                </div>
            </div>

            <div id="users-content" style="display: none;">
                <div class="header">
                    <h2>Data User</h2>
                    <div class="user-info">
                        <div class="user-avatar"><?= $initial ?></div>
                        <span><?= htmlspecialchars($username) ?></span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Daftar User</h3>
                        <button class="btn btn-primary" onclick="openUserModal()">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="users-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                        <th>Tipe User</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="content-content" style="display: none;">
                <div class="header">
                    <h2>Data Konten</h2>
                    <div class="user-info">
                        <div class="user-avatar"><?= $initial ?></div>
                        <span><?= htmlspecialchars($username) ?></span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Daftar Konten</h3>
                        <button class="btn btn-primary" onclick="openContentModal()">
                            <i class="fas fa-plus"></i> Tambah Konten
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="content-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Thumbnail</th>
                                        <th>Judul Kursus</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="user-modal-title">Tambah User</h3>
                <span class="close" onclick="closeModal('user-modal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" id="user-id">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Konfirmasi Password</label>
                        <input type="password" id="confirm-password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="user-type">Tipe User</label>
                        <select id="user-type" class="form-control" required>
                            <option value="">Pilih Tipe User</option>
                            <option value="Mentee">Mentee</option>
                            <option value="Mentor">Mentor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender">Jenis Kelamin</label>
                        <select id="gender" class="form-control" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" onclick="closeModal('user-modal')">Batal</button>
                <button class="btn btn-primary" onclick="saveUser()">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Konten -->
    <div id="content-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="content-modal-title">Tambah Konten</h3>
                <span class="close" onclick="closeModal('content-modal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="content-form">
                    <input type="hidden" id="content-id">
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail URL</label>
                        <input type="text" id="thumbnail" class="form-control" required>
                        <small class="form-text text-muted">Contoh: https://contoh.com/image.jpg</small>
                    </div>
                    <div class="form-group">
                        <label for="title">Judul Kursus</label>
                        <input type="text" id="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="UI/UX">UI/UX</option>
                            <option value="Programming">Programming</option>
                            <option value="Bisnis">Bisnis</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" class="form-control" required>
                            <option value="">Pilih Status</option>
                            <option value="Published">Published</option>
                            <option value="Draft">Draft</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" onclick="closeModal('content-modal')">Batal</button>
                <button class="btn btn-primary" onclick="saveContent()">Simpan</button>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>
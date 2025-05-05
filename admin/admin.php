<!DOCTYPE html>
<html lang="id">
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
                <div class="logo-container">
                    <h1>MindCraft</h1>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#" class="active" onclick="showDashboard()"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" onclick="showUsers()"><i class="fas fa-users"></i> Data User</a></li>
                <li><a href="#" onclick="showContent()"><i class="fas fa-book"></i> Data Konten</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <!-- Dashboard Content -->
            <div id="dashboard-content">
                <div class="header">
                    <h2>Dashboard</h2>
                    <div class="user-info">
                        <div class="user-avatar" title="Admin">A</div>
                        <div class="user-details">
                            <span class="username">Admin</span>
                            <span class="last-updated" id="last-updated"></span>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total User</h3>
                            <div class="value" id="total-users"><span class="loading"></span></div>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-icon mentee">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Mentee</h3>
                            <div class="value" id="total-mentees"><span class="loading"></span></div>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-icon mentor">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Mentor</h3>
                            <div class="value" id="total-mentors"><span class="loading"></span></div>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="card-icon content">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Konten</h3>
                            <div class="value" id="total-contents"><span class="loading"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie" style="color: blue;"></i> Distribusi User</h3>
                            <div class="chart-actions">
                                <button class="btn-chart-action" onclick="loadDashboardData()"></button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="userDistributionChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line" style="color: blue;"></i> Pertumbuhan User</h3>
                            <div class="chart-actions"></div>
                        </div>
                        <div class="chart-container">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                        <h3><i class="fas fa-chart-pie" style="color: blue;"></i> Status Konten</h3>
                            <div class="chart-actions">
                                <button class="btn-chart-action" onclick="loadDashboardData()"></button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="contentStatusChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-bar"style="color: blue;"></i> Kategori Konten</h3>
                            <div class="chart-actions">
                                <button class="btn-chart-action" onclick="loadDashboardData()"></button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="contentCategoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="activity-card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock" style="color: blue;"></i> Aktivitas Terbaru</h3>
                        <button class="btn-refresh"></button>
                    </div>
                    <div class="activity-list">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada aktivitas terbaru</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Content -->
            <div id="users-content" style="display: none;">
                <div class="header">
                    <h2>Data User</h2>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openUserModal()">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="users-table" class="data-table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Tipe User</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Daftar</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Content -->
            <div id="content-content" style="display: none;">
                <div class="header">
                    <h2></i>Data Konten</h2>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openContentModal()">
                            <i class="fas fa-plus"></i> Tambah Konten
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="content-table" class="data-table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Thumbnail</th>
                                        <th>Judul Kursus</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div id="user-modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="user-modal-title">Tambah User</h4>
                    <button type="button" class="close" onclick="closeModal('user-modal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="user-form">
                        <input type="hidden" id="user-id">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" required>
                            <small class="form-text text-muted">Minimal 3 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div id="password-fields">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password">
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm-password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user-type">Tipe User</label>
                            <select class="form-control" id="user-type" required>
                                <option value="">Pilih Tipe User</option>
                                <option value="Mentee">Mentee</option>
                                <option value="Mentor">Mentor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gender">Jenis Kelamin</label>
                            <select class="form-control" id="gender" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('user-modal')">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Modal -->
    <div id="content-modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="content-modal-title">Tambah Konten</h4>
                    <button type="button" class="close" onclick="closeModal('content-modal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="content-form">
                        <input type="hidden" id="content-id">
                        <div class="form-group">
                            <label for="thumbnail">Thumbnail URL</label>
                            <input type="text" class="form-control" id="thumbnail" required>
                            <small class="form-text text-muted">Contoh: https://example.com/image.jpg</small>
                        </div>
                        <div class="form-group">
                            <label for="title">Judul Kursus</label>
                            <input type="text" class="form-control" id="title" required>
                            <small class="form-text text-muted">Minimal 5 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select class="form-control" id="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Pendidikan">Pendidikan</option>
                                <option value="UI/UX">UI/UX</option>
                                <option value="Programming">Programming</option>
                                <option value="Bisnis">Bisnis</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Published">Published</option>
                                <option value="Draft">Draft</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('content-modal')">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveContent()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="spinner"></div>
    </div>

    <script src="admin.js"></script>
</body>
</html>
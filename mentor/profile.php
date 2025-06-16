<?php
$page_title = "Profil Mentor - MindCraft";
$current_page = "profile"; 

if (!isset($pdo)) {
    require_once 'includes/db_connection.php';
}
require_once 'includes/header.php';

$current_mentor_id = $_SESSION['mentor_id'] ?? 1;

$mentor_info = [];
try {
    $stmt = $pdo->prepare("SELECT nama, email, bio, foto_profil FROM mentors WHERE mentor_id = ?");
    $stmt->execute([$current_mentor_id]);
    $mentor_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mentor_info) {
        echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Data mentor tidak ditemukan.</div>";
        $mentor_info = ['nama' => '', 'email' => '', 'bio' => '', 'foto_profil' => '']; 
    }

} catch (PDOException $e) {
    echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Error loading mentor data: " . $e->getMessage() . "</div>";
    $mentor_info = ['nama' => '', 'email' => '', 'bio' => '', 'foto_profil' => ''];
}

// Logika untuk Update Profil (pas form disubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars($_POST['nama_lengkap'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $bio = htmlspecialchars($_POST['bio_mentor'] ?? '');

    // Handle foto profil 
    $foto_profil_path = $mentor_info['foto_profil'];
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['foto_profil']['name']);
        $uploadDir = '../assets/images/profile_photos/'; // ini belum ada + harus make sure dia writable
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $foto_profil_path = 'assets/images/profile_photos/' . $fileName; 
            // Hapus foto lama jika ada
            if ($mentor_info['foto_profil'] && file_exists('../' . $mentor_info['foto_profil'])) {
                unlink('../' . $mentor_info['foto_profil']);
            }
        } else {
            echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Gagal mengunggah foto profil.</div>";
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE mentors SET nama = ?, email = ?, bio = ?, foto_profil = ? WHERE mentor_id = ?");
        $stmt->execute([$nama, $email, $bio, $foto_profil_path, $current_mentor_id]);
        echo "<div class='alert-info' style='background-color:#e0f7fa; border-color:#b2ebf2;'>Profil berhasil diperbarui!</div>";
        // Refresh data setelah update
        $stmt = $pdo->prepare("SELECT nama, email, bio, foto_profil FROM mentors WHERE mentor_id = ?");
        $stmt->execute([$current_mentor_id]);
        $mentor_info = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Gagal memperbarui profil: " . $e->getMessage() . "</div>";
    }
}
?>

            <h1>Profil Mentor</h1>

            <form class="settings-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($mentor_info['nama']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($mentor_info['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="bio_mentor">Bio Mentor</label>
                    <textarea id="bio_mentor" name="bio_mentor" placeholder="Ceritakan tentang diri Anda dan keahlian Anda..."><?php echo htmlspecialchars($mentor_info['bio']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Profil</label>
                    <div class="profile-photo-area">
                        <?php
                        $display_photo_path = !empty($mentor_info['foto_profil']) ? '../' . $mentor_info['foto_profil'] : 'https://via.placeholder.com/100x100?text=Foto';
                        ?>
                        <img src="<?php echo htmlspecialchars($display_photo_path); ?>" alt="Profile Photo" class="profile-photo">
                        <div class="photo-actions">
                            <input type="file" id="foto_profil" name="foto_profil" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('foto_profil').click();">Ubah Foto</button>
                            <button type="button" class="btn btn-danger">Hapus Foto</button>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

<?php require_once 'includes/footer.php'; ?>
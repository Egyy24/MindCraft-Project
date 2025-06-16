<?php
$page_title = "Kursus Saya - MindCraft";
$current_page = "my_courses";

require_once '../includes/db_connection.php'; 
require_once '../includes/header.php'; 

// ambil data kursus from db
$courses = [];
try {
    $stmt = $pdo->prepare("
        SELECT
            c.course_id,
            c.judul,
            c.tingkat_kesulitan,
            c.harga,
            COUNT(DISTINCT r.mentee_id) AS total_mentee,
            COUNT(DISTINCT s.session_id) AS total_modules, -- Asumsi setiap sesi adalah modul
            AVG(r.rating) AS avg_rating,
            SUM(me.jumlah) AS total_earnings_course,
            c.status -- Asumsi ada kolom status di tabel courses (misal: 'Dipublikasikan', 'Draft')
        FROM courses c
        LEFT JOIN reviews r ON c.course_id = r.course_id
        LEFT JOIN mentoring_sessions s ON c.course_id = s.course_id
        LEFT JOIN mentor_earnings me ON c.course_id = me.course_id
        WHERE c.mentor_id = ?
        GROUP BY c.course_id, c.judul, c.tingkat_kesulitan, c.harga, c.status
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$current_mentor_id]);
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert-info' style='background-color:#ffe0e0; border-color:#ffb0b0;'>Error loading courses: " . $e->getMessage() . "</div>";
}

?>

            <h1>Kursus Saya</h1>

            <div class="filter-bar">
                <input type="text" id="search_course" placeholder="Cari kursus...">
                <select id="category_filter">
                    <option value="">Kategori</option>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT category_id, category_name FROM course_categories ORDER BY category_name");
                        while ($row = $stmt->fetch()) {
                            echo "<option value='" . htmlspecialchars($row['category_id']) . "'>" . htmlspecialchars($row['category_name']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        // Handle error (belum ada)
                    }
                    ?>
                </select>
                <select id="status_filter">
                    <option value="">Status</option>
                    <option value="Dipublikasikan">Dipublikasikan</option>
                    <option value="Draft">Draft</option>
                </select>
            </div>

            <div class="course-list">
                <?php if (empty($courses)): ?>
                    <p>Anda belum memiliki kursus. Yuk, buat kursus baru!</p>
                <?php else: ?>
                    <?php foreach ($courses as $course):
                        $status_class = ($course['status'] == 'Dipublikasikan') ? 'published' : 'draft';
                        $rating_display = ($course['avg_rating'] !== null) ? number_format($course['avg_rating'], 1) . '/5' : 'Belum ada rating';
                        $earnings_display = ($course['total_earnings_course'] !== null) ? 'Rp. ' . number_format($course['total_earnings_course'] / 1000000, 1, ',', '.') . ' jt' : 'Rp. 0 jt';
                    ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h2><?php echo htmlspecialchars($course['judul']); ?></h2>
                                <span class="status <?php echo $status_class; ?>"><?php echo htmlspecialchars($course['status']); ?></span>
                            </div>
                            <div class="course-details">
                                <div class="detail-box">
                                    <span>Mentee: <?php echo $course['total_mentee'] ?? 0; ?></span>
                                </div>
                                <div class="detail-box">
                                    <span>Modul: <?php echo $course['total_modules'] ?? 0; ?></span>
                                </div>
                                <div class="detail-box">
                                    <span>Rating: <?php echo $rating_display; ?></span>
                                </div>
                                <div class="detail-box">
                                    <span>Pendapatan: <?php echo $earnings_display; ?></span>
                                </div>
                                <div class="chart-placeholder small-chart">
                                    <img src="https://via.placeholder.com/100x50?text=Mini+Chart" alt="Mini Chart" style="width: 100%; height: auto;">
                                </div>
                            </div>
                            <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-edit">Edit</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
<?php require_once '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_course');
    const categoryFilter = document.getElementById('category_filter');
    const statusFilter = document.getElementById('status_filter');

    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedStatus = statusFilter.value;

        console.log(`Filtering by: Search="${searchText}", Category="${selectedCategory}", Status="${selectedStatus}"`);
        document.querySelectorAll('.course-card').forEach(card => {
            const courseTitle = card.querySelector('h2').textContent.toLowerCase();
            const courseStatus = card.querySelector('.status').textContent; 

            const matchesSearch = courseTitle.includes(searchText);
            const matchesCategory = true; 
            const matchesStatus = selectedStatus === '' || courseStatus === selectedStatus;

            if (matchesSearch && matchesCategory && matchesStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});
</script>
<?php

$current_page = $current_page ?? ''; 
?>
<div class="sidebar">
    <a href="../dashboard.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
    <a href="../courses/view_courses.php" class="<?php echo ($current_page == 'my_courses') ? 'active' : ''; ?>">Kursus Saya</a>
    <a href="../courses/create_course.php" class="<?php echo ($current_page == 'create_course') ? 'active' : ''; ?>">Buat Kursus Baru</a>
    <a href="../earnings/view_earnings.php" class="<?php echo ($current_page == 'earnings') ? 'active' : ''; ?>">Pendapatan</a>
    <a href="../reviews/view_reviews.php" class="<?php echo ($current_page == 'reviews') ? 'active' : ''; ?>">Ulasan & Feedback</a>
    <a href="../analytics/view_analytics.php" class="<?php echo ($current_page == 'analytics') ? 'active' : ''; ?>">Analitik</a>
    <a href="../settings.php" class="<?php echo ($current_page == 'settings') ? 'active' : ''; ?>">Pengaturan</a>
</div>
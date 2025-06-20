<?php

class MentorController {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Dashboard Page
     */
    public function dashboard() {
        // Ambil data mentor dari session
        session_start();
        $mentorId = $_SESSION['mentor_id'] ?? 1;
        
        // Ambil data mentor
        $mentor = $this->getMentorData($mentorId);
        
        // Ambil data dashboard
        $dashboardData = $this->getDashboardData($mentorId);
        
        // Load view dengan data
        require_once __DIR__ . '/../views/mentor/dashboard.php';
    }
    
    /**
     * Analytics Page
     */
    public function analytics() {
        // Ambil data mentor dari session
        session_start();
        $mentorId = $_SESSION['mentor_id'] ?? 1;
        
        // Ambil data mentor
        $mentor = $this->getMentorData($mentorId);
        
        // Ambil parameter filter
        $courseFilter = $_GET['course'] ?? 'all';
        $periodFilter = $_GET['period'] ?? '30';
        
        // Ambil data analytics
        $dashboardData = $this->getAnalyticsData($mentorId, $courseFilter, $periodFilter);
        
        // Load view analytics
        require_once __DIR__ . '/../views/mentor/analitik.php';
    }
    
    /**
     * Get Mentor Data
     */
    private function getMentorData($mentorId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mentors WHERE id = ?");
            $stmt->execute([$mentorId]);
            $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $mentor ?: ['username' => 'Budi', 'id' => $mentorId];
        } catch (Exception $e) {
            return ['username' => 'Budi', 'id' => $mentorId];
        }
    }
    
    /**
     * Get Dashboard Data
     */
    private function getDashboardData($mentorId) {
        $data = [];
        
        try {
            // Total Kursus
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM courses WHERE mentor_id = ?");
            $stmt->execute([$mentorId]);
            $data['totalCourses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 12;
            
            // Total Mentee/Siswa
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT student_id) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['totalMentees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 96;
            
            // Rating rata-rata
            $stmt = $this->db->prepare("
                SELECT AVG(rating) as avg_rating 
                FROM reviews r 
                JOIN courses c ON r.course_id = c.id 
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['averageRating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'] ?? 4.7;
            
            // Pendaftaran baru (7 hari terakhir)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as new_registrations 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                AND e.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmt->execute([$mentorId]);
            $data['newRegistrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_registrations'] ?? 3;
            
            // Pesan belum dibaca
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as unread_messages 
                FROM messages 
                WHERE recipient_id = ? 
                AND is_read = 0
            ");
            $stmt->execute([$mentorId]);
            $data['unreadMessages'] = $stmt->fetch(PDO::FETCH_ASSOC)['unread_messages'] ?? 5;
            
            // Tingkat penyelesaian
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(CASE WHEN progress = 100 THEN 1 END) * 100.0 / COUNT(*) as completion_rate
                FROM course_progress cp
                JOIN courses c ON cp.course_id = c.id
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['completionRate'] = round($stmt->fetch(PDO::FETCH_ASSOC)['completion_rate'] ?? 78);
            
            // Total jam video
            $stmt = $this->db->prepare("
                SELECT SUM(duration_minutes) / 60 as total_hours
                FROM course_videos cv
                JOIN courses c ON cv.course_id = c.id
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['videoHours'] = round($stmt->fetch(PDO::FETCH_ASSOC)['total_hours'] ?? 48);
            
            // Total modul
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_modules
                FROM course_modules cm
                JOIN courses c ON cm.course_id = c.id
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['moduleCount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_modules'] ?? 64;
            
            // Total ulasan
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_reviews
                FROM reviews r
                JOIN courses c ON r.course_id = c.id
                WHERE c.mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['totalReviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_reviews'] ?? 186;
            
            // Total pendapatan
            $stmt = $this->db->prepare("
                SELECT SUM(amount) as total_earnings
                FROM earnings
                WHERE mentor_id = ?
            ");
            $stmt->execute([$mentorId]);
            $data['totalEarnings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_earnings'] ?? 12400000;
            
            // Data pendaftaran bulanan (6 bulan terakhir)
            $stmt = $this->db->prepare("
                SELECT 
                    MONTH(e.created_at) as month,
                    COUNT(*) as registrations
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
                AND e.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY MONTH(e.created_at)
                ORDER BY MONTH(e.created_at)
            ");
            $stmt->execute([$mentorId]);
            $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data untuk chart
            $data['monthlyRegistrations'] = [10, 20, 25, 22, 28, 24, 30]; // Default
            if (!empty($monthlyData)) {
                $data['monthlyRegistrations'] = array_column($monthlyData, 'registrations');
            }
            
            // Aktivitas terbaru
            $stmt = $this->db->prepare("
                SELECT 
                    u.name as user_name,
                    c.title as course_title,
                    e.created_at,
                    'enrollment' as activity_type
                FROM enrollments e
                JOIN users u ON e.student_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
                ORDER BY e.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$mentorId]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format aktivitas
            $data['recentActivities'] = [];
            foreach ($activities as $activity) {
                $timeAgo = $this->timeAgo($activity['created_at']);
                $data['recentActivities'][] = [
                    'user' => $activity['user_name'],
                    'action' => 'mendaftar kursus "' . $activity['course_title'] . '"',
                    'time' => $timeAgo,
                    'avatar' => strtoupper(substr($activity['user_name'], 0, 1))
                ];
            }
            
            if (empty($data['recentActivities'])) {
                $data['recentActivities'] = [
                    [
                        'user' => 'Budi S.',
                        'action' => 'mendaftar kursus "Kerajian Anyaman untuk Pemula"',
                        'time' => '2 jam yang lalu',
                        'avatar' => 'B'
                    ],
                    [
                        'user' => 'Siti A.',
                        'action' => 'menyelesaikan modul "Pengenalan Anyaman"',
                        'time' => '4 jam yang lalu',
                        'avatar' => 'S'
                    ]
                ];
            }
            
            // Hitung peningkatan konsistensi 
            $data['consistencyIncrease'] = 12;
            
        } catch (Exception $e) {
            $data = $this->getDefaultDashboardData();
        }
        
        return $data;
    }
    
    /**
     * Get Analytics Data with Filters
     */
    private function getAnalyticsData($mentorId, $courseFilter = 'all', $periodFilter = '30') {
        $data = [];
        
        try {
            // Base date condition berdasarkan period filter
            $dateCondition = $this->getDateCondition($periodFilter);
            
            // Course condition
            $courseCondition = '';
            $params = [$mentorId];
            
            if ($courseFilter !== 'all') {
                $courseCondition = ' AND c.id = ?';
                $params[] = $courseFilter;
            }
            
            // Total registrations dengan filter
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                {$courseCondition}
                {$dateCondition}
            ");
            $stmt->execute($params);
            $data['totalRegistrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 78;
            
            // Hitung growth percentage (bandingkan dengan periode sebelumnya)
            $previousPeriodCondition = $this->getPreviousDateCondition($periodFilter);
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                {$courseCondition}
                {$previousPeriodCondition}
            ");
            $stmt->execute($params);
            $previousTotal = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 1;
            
            // Calculate growth percentage
            $currentTotal = $data['totalRegistrations'];
            $data['growthPercentage'] = $previousTotal > 0 ? 
                round((($currentTotal - $previousTotal) / $previousTotal) * 100) : 12;
            
            // Pastikan growth percentage tidak negatif untuk demo
            if ($data['growthPercentage'] <= 0) {
                $data['growthPercentage'] = 12;
            }
            
            // Data bulanan untuk chart
            $stmt = $this->db->prepare("
                SELECT 
                    MONTH(e.created_at) as month,
                    COUNT(*) as registrations
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
                {$courseCondition}
                AND e.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY MONTH(e.created_at)
                ORDER BY MONTH(e.created_at)
            ");
            $stmt->execute($params);
            $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data untuk chart (12 bulan)
            $data['monthlyTrend'] = array_fill(0, 12, 0);
            foreach ($monthlyData as $month) {
                $data['monthlyTrend'][$month['month'] - 1] = (int)$month['registrations'];
            }
            
            // Jika tidak ada data, gunakan data contoh
            if (array_sum($data['monthlyTrend']) === 0) {
                $data['monthlyTrend'] = [15, 18, 25, 12, 16, 22, 28, 19, 24, 17, 20, 26];
            }
            
            // Daftar kursus mentor
            $stmt = $this->db->prepare("
                SELECT id, title 
                FROM courses 
                WHERE mentor_id = ?
                ORDER BY title
            ");
            $stmt->execute([$mentorId]);
            $data['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Jika tidak ada kursus, gunakan data contoh
            if (empty($data['courses'])) {
                $data['courses'] = [
                    ['id' => 1, 'title' => 'Kerajian Anyaman untuk Pemula'],
                    ['id' => 2, 'title' => 'Pengenalan Web Development'],
                    ['id' => 3, 'title' => 'Strategi Pemasaran Digital']
                ];
            }
            
        } catch (Exception $e) {
            // Jika ada error database, gunakan data default
            error_log("Analytics Error: " . $e->getMessage());
            $data = $this->getDefaultAnalyticsData();
        }
        
        return $data;
    }
    
    /**
     * Get Date Condition for SQL Queries
     */
    private function getDateCondition($period) {
        switch ($period) {
            case '30':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            case '90':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
            case '180':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)';
            case '365':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)';
            default:
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }
    }
    
    /**
     * Get Previous Period Date Condition for Growth Calculation
     */
    private function getPreviousDateCondition($period) {
        switch ($period) {
            case '30':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
            case '90':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)';
            case '180':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 360 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 180 DAY)';
            case '365':
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 730 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)';
            default:
                return ' AND e.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }
    }
    
    /**
     * Default Dashboard Data for Fallback
     */
    private function getDefaultDashboardData() {
        return [
            'totalCourses' => 12,
            'totalMentees' => 96,
            'averageRating' => 4.7,
            'newRegistrations' => 3,
            'unreadMessages' => 5,
            'consistencyIncrease' => 12,
            'completionRate' => 78,
            'videoHours' => 48,
            'moduleCount' => 64,
            'totalReviews' => 186,
            'totalEarnings' => 12400000,
            'monthlyRegistrations' => [10, 20, 25, 22, 28, 24, 30],
            'recentActivities' => [
                [
                    'user' => 'Budi S.',
                    'action' => 'mendaftar kursus "Kerajian Anyaman untuk Pemula"',
                    'time' => '2 jam yang lalu',
                    'avatar' => 'B'
                ],
                [
                    'user' => 'Siti A.',
                    'action' => 'menyelesaikan modul "Pengenalan Anyaman"',
                    'time' => '4 jam yang lalu',
                    'avatar' => 'S'
                ]
            ]
        ];
    }
    
    /**
     * Default Analytics Data for Fallback
     */
    private function getDefaultAnalyticsData() {
        return [
            'totalRegistrations' => 78,
            'growthPercentage' => 12,
            'monthlyTrend' => [15, 18, 25, 12, 16, 22, 28, 19, 24, 17, 20, 26],
            'courses' => [
                ['id' => 1, 'title' => 'Kerajian Anyaman untuk Pemula'],
                ['id' => 2, 'title' => 'Pengenalan Web Development'],
                ['id' => 3, 'title' => 'Strategi Pemasaran Digital']
            ]
        ];
    }
    
    /**
     * Time Ago Helper Function
     */
    private function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'baru saja';
        if ($time < 3600) return floor($time/60) . ' menit yang lalu';
        if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
        if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
        if ($time < 31536000) return floor($time/2592000) . ' bulan yang lalu';
        
        return floor($time/31536000) . ' tahun yang lalu';
    }
    
    /**
     * API endpoint untuk update data dashboard real-time
     */
    public function getDashboardDataJson() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        try {
            $mentorId = $_SESSION['mentor_id'] ?? 1;
            $data = $this->getDashboardData($mentorId);
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * API endpoint untuk update data analytics via AJAX
     */
    public function getAnalyticsDataJson() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        try {
            $mentorId = $_SESSION['mentor_id'] ?? 1;
            $courseFilter = $_POST['course'] ?? $_GET['course'] ?? 'all';
            $periodFilter = $_POST['period'] ?? $_GET['period'] ?? '30';
            
            $data = $this->getAnalyticsData($mentorId, $courseFilter, $periodFilter);
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch analytics data',
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>
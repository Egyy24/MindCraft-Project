<?php

class MentorController {
    private $db;
    private $database;
    
    public function __construct($database) {
        $this->database = $database;
        // Get the actual PDO connection from your Database class
        $this->db = $database->connect();
    }
    
    /**
     * Dashboard Page
     */
    public function dashboard() {
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
     * Analytics Detail Page
     */
    public function analyticsDetail() {
        session_start();
        $mentorId = $_SESSION['mentor_id'] ?? 1;
        
        // Ambil data mentor
        $mentor = $this->getMentorData($mentorId);
        
        // Ambil parameter filter
        $courseFilter = $_GET['course'] ?? 'all';
        $periodFilter = $_GET['period'] ?? '30';
        
        // Ambil data analytics detail
        $dashboardData = $this->getAnalyticsDetailData($mentorId, $courseFilter, $periodFilter);
        
        // Load view analytics detail
        require_once __DIR__ . '/../views/mentor/analitik-detail.php';
    }
    
    /**
     * Get Mentor Data
     */
    public function getMentorData($mentorId) {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }

            $mentor = $this->database->fetchOne("
                SELECT u.*, mp.* 
                FROM users u 
                LEFT JOIN mentor_profiles mp ON u.id = mp.user_id 
                WHERE u.id = ? AND u.user_type = 'Mentor'
            ", [$mentorId]);
            
            if ($mentor) {
                return $mentor;
            }
            
            throw new Exception("Mentor not found");
            
        } catch (Exception $e) {
            error_log("Error getting mentor data: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Dashboard Data
     */
    public function getDashboardData($mentorId) {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }
            
            $data = [];
            
            // Total Kursus
            $result = $this->database->fetchOne("SELECT COUNT(*) as total FROM courses WHERE mentor_id = ?", [$mentorId]);
            $data['totalCourses'] = (int)($result['total'] ?? 0);
            
            // Total Mentee/Siswa
            $result = $this->database->fetchOne("
                SELECT COUNT(DISTINCT student_id) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $data['totalMentees'] = (int)($result['total'] ?? 0);
            
            // Rating rata-rata
            $result = $this->database->fetchOne("
                SELECT AVG(rating) as avg_rating 
                FROM reviews r 
                JOIN courses c ON r.course_id = c.id 
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $data['averageRating'] = $result['avg_rating'] ? round((float)$result['avg_rating'], 1) : 0;
            
            // Pendaftaran baru (7 hari terakhir)
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as new_registrations 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                AND e.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ", [$mentorId]);
            $data['newRegistrations'] = (int)($result['new_registrations'] ?? 0);
            
            // Pesan belum dibaca
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as unread_messages 
                FROM messages 
                WHERE recipient_id = ? AND is_read = 0
            ", [$mentorId]);
            $data['unreadMessages'] = (int)($result['unread_messages'] ?? 0);
            
            // Tingkat penyelesaian
            $result = $this->database->fetchOne("
                SELECT 
                    COUNT(CASE WHEN e.status = 'completed' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0) as completion_rate
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $data['completionRate'] = $result['completion_rate'] ? round((float)$result['completion_rate']) : 0;
            
            // Total pendapatan
            $result = $this->database->fetchOne("
                SELECT SUM(net_amount) as total_earnings
                FROM earnings
                WHERE mentor_id = ? AND status = 'completed'
            ", [$mentorId]);
            $data['totalEarnings'] = $result['total_earnings'] ? (float)$result['total_earnings'] : 0;
            
            // Video hours dan module count
            $result = $this->database->fetchOne("
                SELECT 
                    SUM(c.duration_hours) as video_hours,
                    SUM(c.total_lessons) as module_count
                FROM courses c
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $data['videoHours'] = (int)($result['video_hours'] ?? 0);
            $data['moduleCount'] = (int)($result['module_count'] ?? 0);
            
            // Total reviews
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as total_reviews
                FROM reviews r
                JOIN courses c ON r.course_id = c.id
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $data['totalReviews'] = (int)($result['total_reviews'] ?? 0);
            
            // Consistency increase (growth percentage last month vs previous)
            $result = $this->database->fetchOne("
                SELECT 
                    COUNT(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as current_month,
                    COUNT(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND e.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as previous_month
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
            ", [$mentorId]);
            $currentMonth = (int)($result['current_month'] ?? 0);
            $previousMonth = (int)($result['previous_month'] ?? 0);
            $data['consistencyIncrease'] = $previousMonth > 0 ? round((($currentMonth - $previousMonth) / $previousMonth) * 100) : 0;
            
            // Data pendaftaran bulanan (7 bulan terakhir)
            $monthlyData = $this->database->fetchAll("
                SELECT 
                    MONTH(e.created_at) as month,
                    COUNT(*) as registrations
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
                AND e.created_at >= DATE_SUB(NOW(), INTERVAL 7 MONTH)
                GROUP BY MONTH(e.created_at)
                ORDER BY e.created_at
            ", [$mentorId]);
            
            // Format data untuk chart (7 bulan)
            $data['monthlyRegistrations'] = array_fill(0, 7, 0);
            foreach ($monthlyData as $month) {
                $monthIndex = ((int)$month['month'] - 1) % 7;
                $data['monthlyRegistrations'][$monthIndex] = (int)$month['registrations'];
            }
            
            // Aktivitas terbaru
            $data['recentActivities'] = $this->getRecentActivities($mentorId);
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Database dashboard error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Recent Activities dari Database
     */
    public function getRecentActivities($mentorId) {
        try {
            if (!$this->database->isConnected()) {
                return [];
            }
            
            $activities = $this->database->fetchAll("
                (SELECT 
                    u.username as user_name,
                    c.title as course_title,
                    e.created_at,
                    'enrollment' as activity_type
                FROM enrollments e
                JOIN users u ON e.student_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ?
                ORDER BY e.created_at DESC
                LIMIT 3)
                UNION ALL
                (SELECT 
                    u.username as user_name,
                    c.title as course_title,
                    r.created_at,
                    'review' as activity_type
                FROM reviews r
                JOIN users u ON r.student_id = u.id
                JOIN courses c ON r.course_id = c.id
                WHERE c.mentor_id = ?
                ORDER BY r.created_at DESC
                LIMIT 2)
                ORDER BY created_at DESC
                LIMIT 5
            ", [$mentorId, $mentorId]);
            
            $formattedActivities = [];
            foreach ($activities as $activity) {
                $timeAgo = $this->timeAgo($activity['created_at']);
                $action = $activity['activity_type'] === 'enrollment' 
                    ? 'mendaftar kursus "' . $activity['course_title'] . '"'
                    : 'memberikan ulasan untuk "' . $activity['course_title'] . '"';
                    
                $formattedActivities[] = [
                    'user' => $activity['user_name'],
                    'action' => $action,
                    'time' => $timeAgo,
                    'avatar' => strtoupper(substr($activity['user_name'], 0, 1))
                ];
            }
            
            return $formattedActivities;
            
        } catch (Exception $e) {
            error_log("Error getting activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Analytics Data
     */
    public function getAnalyticsData($mentorId, $courseFilter = 'all', $periodFilter = '30') {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }
            
            $data = [];
            
            // Base date condition
            $dateCondition = $this->getDateCondition($periodFilter);
            
            // Course condition
            $courseCondition = '';
            $params = [$mentorId];
            
            if ($courseFilter !== 'all') {
                $courseCondition = ' AND c.id = ?';
                $params[] = $courseFilter;
            }
            
            // Total registrations dalam periode
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                {$courseCondition}
                {$dateCondition}
            ", $params);
            $data['totalRegistrations'] = (int)($result['total'] ?? 0);
            
            // Growth percentage (comparison with previous period)
            $prevParams = $params;
            $prevDateCondition = $this->getDateCondition($periodFilter, true); // Previous period
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as total 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE c.mentor_id = ? 
                {$courseCondition}
                {$prevDateCondition}
            ", $prevParams);
            $previousTotal = (int)($result['total'] ?? 0);
            
            $data['growthPercentage'] = $previousTotal > 0 
                ? round((($data['totalRegistrations'] - $previousTotal) / $previousTotal) * 100) 
                : 0;
            
            // Monthly trend data (last 12 months)
            $monthlyData = $this->database->fetchAll("
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
            ", $params);
            
            // Format monthly data
            $data['monthlyTrend'] = array_fill(0, 12, 0);
            foreach ($monthlyData as $month) {
                $data['monthlyTrend'][$month['month'] - 1] = (int)$month['registrations'];
            }
            
            // Daftar kursus
            $data['courses'] = $this->database->fetchAll("
                SELECT id, title 
                FROM courses 
                WHERE mentor_id = ?
                ORDER BY title
            ", [$mentorId]);
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Database analytics error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Analytics Detail Data - FIXED VERSION
     */
    public function getAnalyticsDetailData($mentorId, $courseFilter = 'all', $periodFilter = '30') {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }
            
            $data = [];
            
            // Course condition
            $courseCondition = '';
            $params = [$mentorId];
            
            if ($courseFilter !== 'all') {
                $courseCondition = ' AND c.id = ?';
                $params[] = $courseFilter;
            }
            
            // Total mentees
            $result = $this->database->fetchOne("
                SELECT COUNT(DISTINCT e.student_id) as total_mentees
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
            ", $params);
            $data['totalMentees'] = (int)($result['total_mentees'] ?? 0);
            
            // Active mentees (last 7 days)
            $result = $this->database->fetchOne("
                SELECT COUNT(DISTINCT e.student_id) as active_mentees
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
                AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ", $params);
            $data['activeMentees'] = (int)($result['active_mentees'] ?? 0);
            
            // Completion rate
            $result = $this->database->fetchOne("
                SELECT 
                    COUNT(CASE WHEN e.status = 'completed' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0) as completion_rate
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
            ", $params);
            $data['completionRate'] = $result['completion_rate'] ? round((float)$result['completion_rate']) : 0;
            
            // Average time spent - calculated from progress data
            $result = $this->database->fetchOne("
                SELECT AVG(cp.watch_time) as avg_time
                FROM course_progress cp
                JOIN course_lessons cl ON cp.lesson_id = cl.id
                JOIN course_modules cm ON cl.module_id = cm.id
                JOIN courses c ON cm.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
                AND cp.watch_time > 0
            ", $params);
            $data['avgTimeSpent'] = $result['avg_time'] ? round((float)$result['avg_time'] / 60) : 0; // Convert seconds to minutes
            
            // Course engagement data
            $courseEngagement = $this->database->fetchAll("
                SELECT 
                    c.title as course_name,
                    COUNT(e.id) as enrollment_count,
                    AVG(e.progress_percentage) as avg_progress,
                    COUNT(CASE WHEN e.status = 'completed' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0) as completion_rate
                FROM courses c
                LEFT JOIN enrollments e ON c.id = e.course_id
                WHERE c.mentor_id = ? {$courseCondition}
                GROUP BY c.id, c.title
                ORDER BY enrollment_count DESC
            ", $params);
            
            $data['courseEngagement'] = [];
            foreach ($courseEngagement as $course) {
                $data['courseEngagement'][] = [
                    'course_name' => $course['course_name'],
                    'engagement' => round((float)($course['avg_progress'] ?? 0)),
                    'completion' => round((float)($course['completion_rate'] ?? 0))
                ];
            }
            
            // Weekly activity (last 7 days)
            $weeklyData = $this->database->fetchAll("
                SELECT 
                    DAYOFWEEK(e.last_accessed) - 1 as day_index,
                    COUNT(DISTINCT e.student_id) as active_users
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
                AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND e.last_accessed IS NOT NULL
                GROUP BY DAYOFWEEK(e.last_accessed)
                ORDER BY day_index
            ", $params);
            
            $data['weeklyActivity'] = array_fill(0, 7, 0);
            foreach ($weeklyData as $day) {
                $dayIndex = (int)$day['day_index'];
                if ($dayIndex >= 0 && $dayIndex < 7) {
                    $data['weeklyActivity'][$dayIndex] = (int)$day['active_users'];
                }
            }
            
            // Mentee progress
            $menteeProgress = $this->database->fetchAll("
                SELECT 
                    u.username as name,
                    e.progress_percentage as progress,
                    e.last_accessed,
                    c.title as course
                FROM enrollments e
                JOIN users u ON e.student_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE c.mentor_id = ? {$courseCondition}
                ORDER BY e.last_accessed DESC
                LIMIT 10
            ", $params);
            
            $data['menteeProgress'] = [];
            foreach ($menteeProgress as $mentee) {
                $data['menteeProgress'][] = [
                    'name' => $mentee['name'],
                    'progress' => round((float)($mentee['progress'] ?? 0)),
                    'lastActive' => $mentee['last_accessed'] ? $this->timeAgo($mentee['last_accessed']) : 'Tidak ada aktivitas',
                    'course' => $mentee['course']
                ];
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Analytics detail error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Courses List
     */
    public function getCoursesList($mentorId) {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }
            
            return $this->database->fetchAll("
                SELECT 
                    c.*,
                    COUNT(DISTINCT e.student_id) as mentees,
                    COUNT(DISTINCT cm.id) as modules,
                    AVG(r.rating) as rating,
                    SUM(earn.net_amount) as earnings
                FROM courses c
                LEFT JOIN enrollments e ON c.id = e.course_id
                LEFT JOIN course_modules cm ON c.id = cm.course_id
                LEFT JOIN reviews r ON c.id = r.course_id
                LEFT JOIN earnings earn ON c.id = earn.course_id AND earn.status = 'completed'
                WHERE c.mentor_id = ?
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ", [$mentorId]);
            
        } catch (Exception $e) {
            error_log("Error getting courses list: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Earnings Data
     */
    public function getEarningsData($mentorId) {
        try {
            if (!$this->database->isConnected()) {
                throw new Exception("Database not connected");
            }
            
            // Get summary
            $summary = $this->database->fetchOne("
                SELECT 
                    SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total_earnings,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_sales,
                    AVG(CASE WHEN status = 'completed' THEN net_amount ELSE NULL END) as avg_per_sale,
                    SUM(CASE WHEN status = 'completed' AND payout_status = 'pending' THEN net_amount ELSE 0 END) as available_balance
                FROM earnings 
                WHERE mentor_id = ?
            ", [$mentorId]);
            
            // Get transactions
            $transactions = $this->database->fetchAll("
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
            ", [$mentorId]);
            
            // Get monthly earnings
            $monthlyData = $this->database->fetchAll("
                SELECT 
                    MONTH(created_at) as month,
                    SUM(net_amount) as total_amount
                FROM earnings
                WHERE mentor_id = ? 
                AND status = 'completed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY MONTH(created_at)
                ORDER BY month
            ", [$mentorId]);
            
            $monthlyEarnings = array_fill(0, 12, 0);
            foreach ($monthlyData as $month) {
                $monthlyEarnings[$month['month'] - 1] = (float)$month['total_amount'];
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
            error_log("Error getting earnings data: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Helper Functions
     */
    private function getDateCondition($period, $previous = false) {
        $days = (int)$period;
        $offset = $previous ? $days : 0;
        $startOffset = $offset + $days;
        
        return " AND e.created_at >= DATE_SUB(NOW(), INTERVAL {$startOffset} DAY)" . 
               ($previous ? " AND e.created_at < DATE_SUB(NOW(), INTERVAL {$offset} DAY)" : "");
    }
    
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
     * API Endpoints untuk AJAX
     */
    public function getDashboardDataJson() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
        try {
            session_start();
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
    
    public function getAnalyticsDataJson() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
        try {
            session_start();
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
    
    public function getAnalyticsDetailDataJson() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
        try {
            session_start();
            $mentorId = $_SESSION['mentor_id'] ?? 1;
            $courseFilter = $_POST['course'] ?? $_GET['course'] ?? 'all';
            $periodFilter = $_POST['period'] ?? $_GET['period'] ?? '30';
            
            $data = $this->getAnalyticsDetailData($mentorId, $courseFilter, $periodFilter);
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch analytics detail data',
                'message' => $e->getMessage()
            ]);
        }
    }
}
<?php

class MentorController {
    private $database;
    private $db;
    
    public function __construct(Database $database) {
        $this->database = $database;
        $this->db = $database->connect();
    }
    
    /**
     * Get mentor data by ID
     */
    public function getMentorData($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT u.id, u.username, u.email, u.full_name, u.profile_picture, u.phone,
                           mp.bio, mp.website, mp.linkedin, mp.instagram, mp.youtube,
                           mp.specialization, mp.experience_years, mp.education, mp.certifications,
                           mp.hourly_rate, mp.teaching_language, mp.timezone, mp.availability_status
                    FROM users u
                    LEFT JOIN mentor_profiles mp ON u.id = mp.user_id
                    WHERE u.id = ? AND u.role = 'mentor'
                ");
                $stmt->execute([$mentorId]);
                $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mentor) {
                    return $mentor;
                }
            }
            
            // Fallback static data
            return $this->getStaticMentorData($mentorId);
            
        } catch (Exception $e) {
            error_log("Error getting mentor data: " . $e->getMessage());
            return $this->getStaticMentorData($mentorId);
        }
    }
    
    /**
     * Get dashboard data for mentor
     */
    public function getDashboardData($mentorId) {
        try {
            if ($this->db) {
                return $this->getDatabaseDashboardData($mentorId);
            }
            
            return $this->getStaticDashboardData();
            
        } catch (Exception $e) {
            error_log("Error getting dashboard data: " . $e->getMessage());
            return $this->getStaticDashboardData();
        }
    }
    
    /**
     * Get courses list for mentor
     */
    public function getCoursesList($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT c.*, 
                           COUNT(DISTINCT e.student_id) as mentees,
                           COUNT(DISTINCT m.id) as modules,
                           AVG(r.rating) as rating,
                           SUM(CASE WHEN pay.status = 'completed' THEN pay.amount ELSE 0 END) as earnings
                    FROM courses c
                    LEFT JOIN enrollments e ON c.id = e.course_id
                    LEFT JOIN course_modules m ON c.id = m.course_id
                    LEFT JOIN reviews r ON c.id = r.course_id
                    LEFT JOIN payments pay ON c.id = pay.course_id
                    WHERE c.mentor_id = ?
                    GROUP BY c.id
                    ORDER BY c.created_at DESC
                ");
                $stmt->execute([$mentorId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $this->getStaticCoursesList();
            
        } catch (Exception $e) {
            error_log("Error getting courses list: " . $e->getMessage());
            return $this->getStaticCoursesList();
        }
    }
    
    /**
     * Get earnings data for mentor
     */
    public function getEarningsData($mentorId) {
        try {
            if ($this->db) {
                return $this->getDatabaseEarningsData($mentorId);
            }
            
            return $this->getStaticEarningsData();
            
        } catch (Exception $e) {
            error_log("Error getting earnings data: " . $e->getMessage());
            return $this->getStaticEarningsData();
        }
    }
    
    /**
     * Get analytics data for mentor
     */
    public function getAnalyticsData($mentorId, $courseFilter = 'all', $period = '30') {
        try {
            if ($this->db) {
                return $this->getDatabaseAnalyticsData($mentorId, $courseFilter, $period);
            }
            
            return $this->getStaticAnalyticsData($courseFilter, $period);
            
        } catch (Exception $e) {
            error_log("Error getting analytics data: " . $e->getMessage());
            return $this->getStaticAnalyticsData($courseFilter, $period);
        }
    }
    
    /**
     * Get analytics detail data for mentor
     */
    public function getAnalyticsDetailData($mentorId, $courseFilter = 'all', $period = '30') {
        try {
            if ($this->db) {
                return $this->getDatabaseAnalyticsDetailData($mentorId, $courseFilter, $period);
            }
            
            return $this->getStaticAnalyticsDetailData($courseFilter, $period);
            
        } catch (Exception $e) {
            error_log("Error getting analytics detail data: " . $e->getMessage());
            return $this->getStaticAnalyticsDetailData($courseFilter, $period);
        }
    }
    
    // ===================== DATABASE METHODS =====================
    
    private function getDatabaseDashboardData($mentorId) {
        // Get total courses
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM courses WHERE mentor_id = ?");
        $stmt->execute([$mentorId]);
        $totalCourses = $stmt->fetchColumn();
        
        // Get total mentees
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT e.student_id) as total
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            WHERE c.mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $totalMentees = $stmt->fetchColumn();
        
        // Get average rating
        $stmt = $this->db->prepare("
            SELECT AVG(r.rating) as avg_rating
            FROM reviews r
            INNER JOIN courses c ON r.course_id = c.id
            WHERE c.mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $averageRating = $stmt->fetchColumn() ?: 0;
        
        // Get completion rate
        $stmt = $this->db->prepare("
            SELECT 
                AVG(CASE WHEN cp.completion_percentage >= 100 THEN 100 ELSE cp.completion_percentage END) as completion_rate
            FROM course_progress cp
            INNER JOIN courses c ON cp.course_id = c.id
            WHERE c.mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $completionRate = $stmt->fetchColumn() ?: 0;
        
        // Get total earnings
        $stmt = $this->db->prepare("
            SELECT SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total
            FROM earnings
            WHERE mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $totalEarnings = $stmt->fetchColumn() ?: 0;
        
        // Get monthly registrations
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(e.created_at) as month,
                COUNT(*) as registrations
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            WHERE c.mentor_id = ? 
            AND YEAR(e.created_at) = YEAR(NOW())
            GROUP BY MONTH(e.created_at)
            ORDER BY month
        ");
        $stmt->execute([$mentorId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to array indexed by month
        $monthlyRegistrations = array_fill(0, 12, 0);
        foreach ($monthlyData as $data) {
            $monthlyRegistrations[$data['month'] - 1] = (int)$data['registrations'];
        }
        
        // Get recent activities
        $stmt = $this->db->prepare("
            SELECT 
                u.username as user,
                'mendaftar kursus' as action,
                CONCAT(SUBSTRING(u.username, 1, 1)) as avatar,
                DATE_FORMAT(e.created_at, '%d %b %H:%i') as time
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            INNER JOIN users u ON e.student_id = u.id
            WHERE c.mentor_id = ?
            ORDER BY e.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$mentorId]);
        $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'totalCourses' => (int)$totalCourses,
            'totalMentees' => (int)$totalMentees,
            'averageRating' => round($averageRating, 1),
            'completionRate' => round($completionRate),
            'videoHours' => 0, // Would need video duration calculation
            'moduleCount' => 0, // Would need module count
            'totalReviews' => 0, // Would need review count
            'totalEarnings' => (float)$totalEarnings,
            'monthlyRegistrations' => $monthlyRegistrations,
            'recentActivities' => $recentActivities,
            'newRegistrations' => $monthlyRegistrations[date('n') - 1],
            'unreadMessages' => 0, // Would need message system
            'consistencyIncrease' => $this->calculateConsistencyIncrease($monthlyRegistrations)
        ];
    }
    
    private function getDatabaseEarningsData($mentorId) {
        // Get total earnings
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total_earnings,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_sales,
                AVG(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as avg_per_sale
            FROM earnings 
            WHERE mentor_id = ?
        ");
        $stmt->execute([$mentorId]);
        $earningsStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get monthly earnings
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(created_at) as month,
                SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as earnings
            FROM earnings
            WHERE mentor_id = ? 
            AND YEAR(created_at) = YEAR(NOW())
            GROUP BY MONTH(created_at)
            ORDER BY month
        ");
        $stmt->execute([$mentorId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to array indexed by month
        $monthlyEarnings = array_fill(0, 12, 0);
        foreach ($monthlyData as $data) {
            $monthlyEarnings[$data['month'] - 1] = (float)$data['earnings'];
        }
        
        // Get recent transactions
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                c.title as course_name,
                u.username as student_name
            FROM earnings e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.student_id = u.id
            WHERE e.mentor_id = ?
            ORDER BY e.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$mentorId]);
        $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'totalEarnings' => (float)($earningsStats['total_earnings'] ?: 0),
            'totalSales' => (int)($earningsStats['total_sales'] ?: 0),
            'avgPerSale' => (float)($earningsStats['avg_per_sale'] ?: 0),
            'monthlyEarnings' => $monthlyEarnings,
            'recentTransactions' => $recentTransactions,
            'availableBalance' => $this->getAvailableBalance($mentorId),
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        ];
    }
    
    private function getDatabaseAnalyticsData($mentorId, $courseFilter = 'all', $period = '30') {
        $dateCondition = $this->getDateCondition($period);
        $courseCondition = $courseFilter === 'all' ? '' : 'AND c.id = ' . (int)$courseFilter;
        
        // Get enrollment data
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(e.created_at) as month,
                COUNT(*) as enrollments
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            WHERE c.mentor_id = ? $dateCondition $courseCondition
            GROUP BY MONTH(e.created_at)
            ORDER BY month
        ");
        $stmt->execute([$mentorId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to array
        $monthlyRegistrations = array_fill(0, 12, 0);
        foreach ($monthlyData as $data) {
            $monthlyRegistrations[$data['month'] - 1] = (int)$data['enrollments'];
        }
        
        return [
            'monthlyData' => $monthlyRegistrations,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'totalRegistrations' => array_sum($monthlyRegistrations),
            'growthPercentage' => $this->calculateGrowthPercentage($monthlyRegistrations)
        ];
    }
    
    private function getDatabaseAnalyticsDetailData($mentorId, $courseFilter = 'all', $period = '30') {
        $dateCondition = $this->getDateCondition($period);
        $courseCondition = $courseFilter === 'all' ? '' : 'AND c.id = ' . (int)$courseFilter;
        
        // Get daily data for weekly activity
        $stmt = $this->db->prepare("
            SELECT 
                DAYOFWEEK(ca.date) as day_of_week,
                AVG(ca.enrollments) as avg_enrollments
            FROM course_analytics ca
            INNER JOIN courses c ON ca.course_id = c.id
            WHERE c.mentor_id = ? $dateCondition $courseCondition
            GROUP BY DAYOFWEEK(ca.date)
            ORDER BY day_of_week
        ");
        $stmt->execute([$mentorId]);
        $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to weekly activity array (Sunday = 1, Monday = 2, etc.)
        $weeklyActivity = [0, 0, 0, 0, 0, 0, 0]; // [Mon, Tue, Wed, Thu, Fri, Sat, Sun]
        foreach ($weeklyData as $data) {
            $dayIndex = $data['day_of_week'] == 1 ? 6 : $data['day_of_week'] - 2; // Convert to Mon=0, Sun=6
            $weeklyActivity[$dayIndex] = (int)$data['avg_enrollments'];
        }
        
        // Get course engagement data
        $stmt = $this->db->prepare("
            SELECT 
                c.title as course_name,
                AVG(CASE WHEN cp.completed = 1 THEN 100 ELSE cp.progress END) as engagement,
                AVG(CASE WHEN e.completion_date IS NOT NULL THEN 100 ELSE e.progress_percentage END) as completion
            FROM courses c
            LEFT JOIN course_progress cp ON c.id = cp.course_id
            LEFT JOIN enrollments e ON c.id = e.course_id
            WHERE c.mentor_id = ? $courseCondition
            GROUP BY c.id, c.title
            ORDER BY engagement DESC
            LIMIT 5
        ");
        $stmt->execute([$mentorId]);
        $courseEngagement = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'weeklyActivity' => $weeklyActivity,
            'weekLabels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'courseEngagement' => $courseEngagement
        ];
    }
    
    // ===================== STATIC DATA METHODS =====================
    
    private function getStaticMentorData($mentorId) {
        return [
            'id' => $mentorId,
            'username' => 'mentor_demo',
            'email' => 'mentor@mindcraft.com',
            'full_name' => 'Budi Santoso',
            'profile_picture' => '/assets/images/default-avatar.png',
            'phone' => '+62 812-3456-7890',
            'bio' => 'Mentor berpengalaman dengan 5+ tahun di bidang pendidikan digital',
            'website' => 'https://budisantoso.com',
            'linkedin' => 'https://linkedin.com/in/budisantoso',
            'instagram' => 'https://instagram.com/budisantoso',
            'youtube' => 'https://youtube.com/c/budisantoso',
            'specialization' => 'Web Development, UI/UX Design',
            'experience_years' => 5,
            'education' => 'S1 Teknik Informatika - Universitas Indonesia',
            'certifications' => 'Google UX Design Certificate, AWS Certified Developer',
            'hourly_rate' => 150000.00,
            'teaching_language' => 'Bahasa Indonesia, English',
            'timezone' => 'Asia/Jakarta',
            'availability_status' => 'Available'
        ];
    }
    
    private function getStaticDashboardData() {
        return [
            'totalCourses' => 12,
            'totalMentees' => 847,
            'averageRating' => 4.8,
            'completionRate' => 85,
            'videoHours' => 156,
            'moduleCount' => 48,
            'totalReviews' => 234,
            'totalEarnings' => 15750000,
            'monthlyRegistrations' => [45, 62, 78, 85, 92, 110, 125, 118, 134, 128, 145, 152],
            'recentActivities' => [
                [
                    'user' => 'Alma Nurul Salma',
                    'action' => 'mendaftar kursus',
                    'avatar' => 'A',
                    'time' => '2 menit lalu'
                ],
                [
                    'user' => 'Nishimura Riki',
                    'action' => 'menyelesaikan modul',
                    'avatar' => 'S',
                    'time' => '15 menit lalu'
                ],
                [
                    'user' => 'Niki Zefanya',
                    'action' => 'memberikan review',
                    'avatar' => 'A',
                    'time' => '1 jam lalu'
                ]
            ],
            'newRegistrations' => 12,
            'unreadMessages' => 5,
            'consistencyIncrease' => 15
        ];
    }
    
    private function getStaticCoursesList() {
        return [
            [
                'id' => 1,
                'title' => 'Kerajinan Anyaman untuk Pemula',
                'category' => 'Kerajinan',
                'status' => 'Published',
                'mentees' => 125,
                'modules' => 8,
                'rating' => 4.8,
                'earnings' => 2500000,
                'created_at' => '2024-01-15'
            ],
            [
                'id' => 2,
                'title' => 'Pengenalan Web Development',
                'category' => 'Programming',
                'status' => 'Published',
                'mentees' => 89,
                'modules' => 12,
                'rating' => 4.6,
                'earnings' => 1780000,
                'created_at' => '2024-02-20'
            ],
            [
                'id' => 3,
                'title' => 'Strategi Pemasaran Digital',
                'category' => 'Bisnis',
                'status' => 'Draft',
                'mentees' => 0,
                'modules' => 6,
                'rating' => 0,
                'earnings' => 0,
                'created_at' => '2024-03-10'
            ]
        ];
    }
    
    private function getStaticEarningsData() {
        return [
            'totalEarnings' => 15750000,
            'totalSales' => 234,
            'avgPerSale' => 67308,
            'monthlyEarnings' => [850000, 920000, 1150000, 780000, 1020000, 1350000, 1680000, 1240000, 1430000, 1150000, 1580000, 1720000],
            'recentTransactions' => [
                [
                    'id' => 1,
                    'transaction_type' => 'course_sale',
                    'course_name' => 'Kerajinan Anyaman untuk Pemula',
                    'student_name' => 'Ahmad Rizki',
                    'amount' => 150000,
                    'net_amount' => 105000,
                    'status' => 'completed',
                    'created_at' => '2024-06-20 10:30:00'
                ],
                [
                    'id' => 2,
                    'transaction_type' => 'course_sale',
                    'course_name' => 'Web Development Basics',
                    'student_name' => 'Siti Nurhaliza',
                    'amount' => 200000,
                    'net_amount' => 140000,
                    'status' => 'completed',
                    'created_at' => '2024-06-19 15:45:00'
                ]
            ],
            'availableBalance' => 2450000,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        ];
    }
    
    private function getStaticAnalyticsData($courseFilter, $period) {
        $baseData = [45, 62, 78, 85, 92, 110, 125, 118, 134, 128, 145, 152];
        $multiplier = $courseFilter === 'all' ? 1 : 0.7;
        $periodMultiplier = $period === '30' ? 1 : ($period === '90' ? 0.8 : 0.6);
        
        $monthlyData = array_map(function($val) use ($multiplier, $periodMultiplier) {
            return (int)($val * $multiplier * $periodMultiplier);
        }, $baseData);
        
        return [
            'monthlyData' => $monthlyData,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'totalRegistrations' => array_sum($monthlyData),
            'growthPercentage' => 12
        ];
    }
    
    private function getStaticAnalyticsDetailData($courseFilter, $period) {
        $multiplier = $courseFilter === 'all' ? 1 : 0.7;
        $periodMultiplier = $period === '7' ? 0.3 : ($period === '30' ? 1 : 1.5);
        
        $baseWeeklyActivity = [65, 78, 82, 95, 88, 72, 58];
        $weeklyActivity = array_map(function($val) use ($multiplier, $periodMultiplier) {
            return (int)($val * $multiplier * $periodMultiplier);
        }, $baseWeeklyActivity);
        
        return [
            'weeklyActivity' => $weeklyActivity,
            'weekLabels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'courseEngagement' => [
                ['course_name' => 'Kerajinan Anyaman untuk Pemula', 'engagement' => 85, 'completion' => 72],
                ['course_name' => 'Pengenalan Web Development', 'engagement' => 78, 'completion' => 65],
                ['course_name' => 'Strategi Pemasaran Digital', 'engagement' => 92, 'completion' => 88]
            ]
        ];
    }
    
    // ===================== NOTIFICATION METHODS =====================
    
    /**
     * Get notifications for mentor
     */
    public function getNotifications($mentorId, $type = 'all', $status = 'all', $limit = 50) {
        try {
            if ($this->db) {
                $conditions = ['user_id = ?'];
                $params = [$mentorId];
                
                if ($type !== 'all') {
                    $conditions[] = 'type = ?';
                    $params[] = $type;
                }
                
                if ($status !== 'all') {
                    $conditions[] = 'is_read = ?';
                    $params[] = $status === 'read' ? 1 : 0;
                }
                
                $whereClause = implode(' AND ', $conditions);
                
                $stmt = $this->db->prepare("
                    SELECT * FROM notifications
                    WHERE $whereClause
                    ORDER BY created_at DESC
                    LIMIT ?
                ");
                $params[] = $limit;
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $this->getStaticNotifications();
            
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return $this->getStaticNotifications();
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId, $mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    UPDATE notifications 
                    SET is_read = 1 
                    WHERE id = ? AND user_id = ?
                ");
                return $stmt->execute([$notificationId, $mentorId]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    UPDATE notifications 
                    SET is_read = 1 
                    WHERE user_id = ? AND is_read = 0
                ");
                return $stmt->execute([$mentorId]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    DELETE FROM notifications 
                    WHERE id = ? AND user_id = ?
                ");
                return $stmt->execute([$notificationId, $mentorId]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            return false;
        }
    }
    
    private function getStaticNotifications() {
        return [
            [
                'id' => 1,
                'type' => 'course_enrollment',
                'title' => 'Pendaftaran Baru',
                'message' => 'Ahmad Rizki mendaftar ke kursus "Kerajinan Anyaman untuk Pemula"',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'id' => 2,
                'type' => 'new_review',
                'title' => 'Review Baru',
                'message' => 'Siti Nurhaliza memberikan review 5 bintang untuk kursus Anda',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'id' => 3,
                'type' => 'payment_received',
                'title' => 'Pembayaran Diterima',
                'message' => 'Anda menerima pembayaran sebesar Rp 150.000',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]
        ];
    }
    
    // ===================== SETTINGS METHODS =====================
    
    /**
     * Get mentor settings
     */
    public function getMentorSettings($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT ms.*, mp.* 
                    FROM mentor_settings ms
                    LEFT JOIN mentor_profiles mp ON ms.mentor_id = mp.user_id
                    WHERE ms.mentor_id = ?
                ");
                $stmt->execute([$mentorId]);
                $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($settings) {
                    return $settings;
                }
            }
            
            return $this->getDefaultSettings($mentorId);
            
        } catch (Exception $e) {
            error_log("Error getting mentor settings: " . $e->getMessage());
            return $this->getDefaultSettings($mentorId);
        }
    }
    
    /**
     * Update mentor settings
     */
    public function updateMentorSettings($mentorId, $settings) {
        try {
            if ($this->db) {
                // Update mentor_profiles
                if (isset($settings['profile_data'])) {
                    $profileData = $settings['profile_data'];
                    $stmt = $this->db->prepare("
                        INSERT INTO mentor_profiles (user_id, full_name, bio, phone, website, linkedin, instagram, youtube)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        full_name = VALUES(full_name),
                        bio = VALUES(bio),
                        phone = VALUES(phone),
                        website = VALUES(website),
                        linkedin = VALUES(linkedin),
                        instagram = VALUES(instagram),
                        youtube = VALUES(youtube)
                    ");
                    $stmt->execute([
                        $mentorId,
                        $profileData['full_name'] ?? '',
                        $profileData['bio'] ?? '',
                        $profileData['phone'] ?? '',
                        $profileData['website'] ?? '',
                        $profileData['linkedin'] ?? '',
                        $profileData['instagram'] ?? '',
                        $profileData['youtube'] ?? ''
                    ]);
                }
                
                // Update mentor_settings
                if (isset($settings['settings_data'])) {
                    $settingsData = $settings['settings_data'];
                    $stmt = $this->db->prepare("
                        INSERT INTO mentor_settings (mentor_id, email_notifications, push_notifications, course_notifications, review_notifications, payment_notifications)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        email_notifications = VALUES(email_notifications),
                        push_notifications = VALUES(push_notifications),
                        course_notifications = VALUES(course_notifications),
                        review_notifications = VALUES(review_notifications),
                        payment_notifications = VALUES(payment_notifications)
                    ");
                    $stmt->execute([
                        $mentorId,
                        $settingsData['email_notifications'] ?? 1,
                        $settingsData['push_notifications'] ?? 1,
                        $settingsData['course_notifications'] ?? 1,
                        $settingsData['review_notifications'] ?? 1,
                        $settingsData['payment_notifications'] ?? 1
                    ]);
                }
                
                return true;
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error updating mentor settings: " . $e->getMessage());
            return false;
        }
    }
    
    private function getDefaultSettings($mentorId) {
        return [
            'mentor_id' => $mentorId,
            'email_notifications' => 1,
            'push_notifications' => 1,
            'course_notifications' => 1,
            'review_notifications' => 1,
            'payment_notifications' => 1,
            'marketing_emails' => 0,
            'profile_visibility' => 'public',
            'auto_accept_students' => 1,
            'payout_method' => 'bank_transfer',
            'minimum_payout' => 100000.00
        ];
    }
    
    // ===================== COURSE MANAGEMENT METHODS =====================
    
    /**
     * Create new course
     */
    public function createCourse($mentorId, $courseData) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO courses (mentor_id, title, category, difficulty, description, cover_image, price, is_premium, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $mentorId,
                    $courseData['title'],
                    $courseData['category'],
                    $courseData['difficulty'],
                    $courseData['description'],
                    $courseData['cover_image'] ?? null,
                    $courseData['price'] ?? 0,
                    $courseData['is_premium'] ?? 0,
                    $courseData['action'] === 'publish' ? 'Published' : 'Draft'
                ]);
                
                if ($result) {
                    return $this->db->lastInsertId();
                }
            }
            
            return rand(1000, 9999); // Simulate course ID for demo
            
        } catch (Exception $e) {
            error_log("Error creating course: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update course
     */
    public function updateCourse($courseId, $mentorId, $courseData) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    UPDATE courses 
                    SET title = ?, category = ?, difficulty = ?, description = ?, 
                        cover_image = ?, price = ?, is_premium = ?, status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND mentor_id = ?
                ");
                
                return $stmt->execute([
                    $courseData['title'],
                    $courseData['category'],
                    $courseData['difficulty'],
                    $courseData['description'],
                    $courseData['cover_image'] ?? null,
                    $courseData['price'] ?? 0,
                    $courseData['is_premium'] ?? 0,
                    $courseData['status'] ?? 'Draft',
                    $courseId,
                    $mentorId
                ]);
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error updating course: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete course
     */
    public function deleteCourse($courseId, $mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    DELETE FROM courses 
                    WHERE id = ? AND mentor_id = ?
                ");
                return $stmt->execute([$courseId, $mentorId]);
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }
    
    // ===================== WITHDRAWAL METHODS =====================
    
    /**
     * Get withdrawal history
     */
    public function getWithdrawalHistory($mentorId, $filters = []) {
        try {
            if ($this->db) {
                $conditions = ['mentor_id = ?', 'transaction_type = "withdrawal"'];
                $params = [$mentorId];
                
                if (!empty($filters['status']) && $filters['status'] !== 'all') {
                    $conditions[] = 'payout_status = ?';
                    $params[] = $filters['status'];
                }
                
                if (!empty($filters['start_date'])) {
                    $conditions[] = 'created_at >= ?';
                    $params[] = $filters['start_date'];
                }
                
                if (!empty($filters['end_date'])) {
                    $conditions[] = 'created_at <= ?';
                    $params[] = $filters['end_date'] . ' 23:59:59';
                }
                
                $whereClause = implode(' AND ', $conditions);
                
                $stmt = $this->db->prepare("
                    SELECT * FROM earnings
                    WHERE $whereClause
                    ORDER BY created_at DESC
                    LIMIT 50
                ");
                $stmt->execute($params);
                $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get summary data
                $stmt = $this->db->prepare("
                    SELECT 
                        COUNT(*) as total_withdrawals,
                        SUM(CASE WHEN payout_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN payout_status = 'paid' THEN 1 ELSE 0 END) as completed_count,
                        SUM(CASE WHEN payout_status = 'paid' THEN ABS(net_amount) ELSE 0 END) as total_withdrawn
                    FROM earnings
                    WHERE mentor_id = ? AND transaction_type = 'withdrawal'
                ");
                $stmt->execute([$mentorId]);
                $summary = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'withdrawals' => $withdrawals,
                    'summary' => $summary,
                    'availableBalance' => $this->getAvailableBalance($mentorId),
                    'monthlyData' => $this->getMonthlyWithdrawals($mentorId)
                ];
            }
            
            return $this->getStaticWithdrawalHistory();
            
        } catch (Exception $e) {
            error_log("Error getting withdrawal history: " . $e->getMessage());
            return $this->getStaticWithdrawalHistory();
        }
    }
    
    /**
     * Process withdrawal request
     */
    public function processWithdrawal($mentorId, $withdrawalData) {
        try {
            if ($this->db) {
                $this->db->beginTransaction();
                
                // Insert withdrawal record
                $stmt = $this->db->prepare("
                    INSERT INTO earnings (mentor_id, transaction_type, amount, net_amount, status, payout_status, withdrawal_method, withdrawal_account, reference_id, description)
                    VALUES (?, 'withdrawal', ?, ?, 'completed', 'pending', ?, ?, ?, ?)
                ");
                
                $referenceId = 'WD' . date('Ymd') . rand(1000, 9999);
                $amount = -abs($withdrawalData['amount']); // Negative for withdrawal
                
                $stmt->execute([
                    $mentorId,
                    $amount,
                    $amount,
                    $withdrawalData['method'],
                    $withdrawalData['account'],
                    $referenceId,
                    $withdrawalData['description'] ?? 'Withdrawal request'
                ]);
                
                $this->db->commit();
                return $referenceId;
            }
            
            return 'WD' . date('Ymd') . rand(1000, 9999); // Simulate reference ID for demo
            
        } catch (Exception $e) {
            if ($this->db) {
                $this->db->rollBack();
            }
            error_log("Error processing withdrawal: " . $e->getMessage());
            return false;
        }
    }
    
    private function getAvailableBalance($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT 
                        SUM(CASE WHEN status = 'completed' AND payout_status = 'pending' THEN net_amount ELSE 0 END) as available_balance
                    FROM earnings
                    WHERE mentor_id = ?
                ");
                $stmt->execute([$mentorId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (float)($result['available_balance'] ?: 0);
            }
            
            return 2450000; // Static demo balance
            
        } catch (Exception $e) {
            error_log("Error getting available balance: " . $e->getMessage());
            return 2450000;
        }
    }
    
    private function getMonthlyWithdrawals($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT 
                        MONTH(created_at) as month,
                        SUM(ABS(net_amount)) as amount
                    FROM earnings
                    WHERE mentor_id = ? AND transaction_type = 'withdrawal' AND YEAR(created_at) = YEAR(NOW())
                    GROUP BY MONTH(created_at)
                    ORDER BY month
                ");
                $stmt->execute([$mentorId]);
                $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $monthlyWithdrawals = array_fill(0, 12, 0);
                foreach ($monthlyData as $data) {
                    $monthlyWithdrawals[$data['month'] - 1] = (float)$data['amount'];
                }
                
                return $monthlyWithdrawals;
            }
            
            return [500000, 750000, 0, 1200000, 800000, 0, 950000, 1100000, 0, 600000, 1300000, 0];
            
        } catch (Exception $e) {
            error_log("Error getting monthly withdrawals: " . $e->getMessage());
            return [500000, 750000, 0, 1200000, 800000, 0, 950000, 1100000, 0, 600000, 1300000, 0];
        }
    }
    
    private function getStaticWithdrawalHistory() {
        return [
            'withdrawals' => [
                [
                    'id' => 1,
                    'reference_id' => 'WD202406201234',
                    'net_amount' => -1000000,
                    'withdrawal_method' => 'bank_transfer',
                    'withdrawal_account' => 'BCA - 1234567890',
                    'payout_status' => 'paid',
                    'status' => 'completed',
                    'created_at' => '2024-06-20 10:30:00',
                    'payout_date' => '2024-06-21 14:20:00',
                    'description' => 'Monthly withdrawal'
                ],
                [
                    'id' => 2,
                    'reference_id' => 'WD202406151567',
                    'net_amount' => -750000,
                    'withdrawal_method' => 'gopay',
                    'withdrawal_account' => 'GoPay - 081234567890',
                    'payout_status' => 'pending',
                    'status' => 'completed',
                    'created_at' => '2024-06-15 16:45:00',
                    'payout_date' => null,
                    'description' => 'Emergency withdrawal'
                ]
            ],
            'summary' => [
                'total_withdrawals' => 8,
                'pending_count' => 1,
                'completed_count' => 7,
                'total_withdrawn' => 6500000
            ],
            'availableBalance' => 2450000,
            'monthlyData' => [500000, 750000, 0, 1200000, 800000, 0, 950000, 1100000, 0, 600000, 1300000, 0]
        ];
    }
    
    // ===================== EARNINGS DETAIL METHODS =====================
    
    /**
     * Get detailed earnings data for pendapatan-detail page
     */
    public function getEarningsDetailData($mentorId, $filters = []) {
        try {
            if ($this->db) {
                return $this->getDatabaseEarningsDetailData($mentorId, $filters);
            }
            
            return $this->getStaticEarningsDetailData($filters);
            
        } catch (Exception $e) {
            error_log("Error getting earnings detail data: " . $e->getMessage());
            return $this->getStaticEarningsDetailData($filters);
        }
    }
    
    private function getDatabaseEarningsDetailData($mentorId, $filters) {
        $dateCondition = $this->getDateConditionFromFilters($filters);
        $courseCondition = !empty($filters['course']) && $filters['course'] !== 'all' ? 'AND e.course_id = ' . (int)$filters['course'] : '';
        
        // Get daily earnings data
        $stmt = $this->db->prepare("
            SELECT 
                DATE(e.created_at) as date,
                SUM(CASE WHEN e.status = 'completed' THEN e.net_amount ELSE 0 END) as daily_earnings,
                COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as daily_transactions
            FROM earnings e
            WHERE e.mentor_id = ? $dateCondition $courseCondition
            AND e.transaction_type = 'course_sale'
            GROUP BY DATE(e.created_at)
            ORDER BY date ASC
            LIMIT 30
        ");
        $stmt->execute([$mentorId]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get course breakdown
        $stmt = $this->db->prepare("
            SELECT 
                c.title as course_name,
                COUNT(e.id) as transaction_count,
                SUM(CASE WHEN e.status = 'completed' THEN e.net_amount ELSE 0 END) as total_earnings,
                AVG(CASE WHEN e.status = 'completed' THEN e.net_amount ELSE 0 END) as avg_earnings
            FROM earnings e
            INNER JOIN courses c ON e.course_id = c.id
            WHERE e.mentor_id = ? $dateCondition $courseCondition
            AND e.transaction_type = 'course_sale'
            GROUP BY e.course_id, c.title
            ORDER BY total_earnings DESC
        ");
        $stmt->execute([$mentorId]);
        $courseBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get detailed transactions
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                c.title as course_name,
                u.username as student_name
            FROM earnings e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.student_id = u.id
            WHERE e.mentor_id = ? $dateCondition $courseCondition
            ORDER BY e.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([$mentorId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'dailyData' => $dailyData,
            'courseBreakdown' => $courseBreakdown,
            'transactions' => $transactions
        ];
    }
    
    private function getStaticEarningsDetailData($filters) {
        // Generate mock daily data for last 30 days
        $dailyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dailyData[] = [
                'date' => $date,
                'daily_earnings' => rand(50000, 300000),
                'daily_transactions' => rand(1, 8)
            ];
        }
        
        return [
            'dailyData' => $dailyData,
            'courseBreakdown' => [
                [
                    'course_name' => 'Kerajinan Anyaman untuk Pemula',
                    'transaction_count' => 85,
                    'total_earnings' => 8500000,
                    'avg_earnings' => 100000
                ],
                [
                    'course_name' => 'Pengenalan Web Development',
                    'transaction_count' => 62,
                    'total_earnings' => 6200000,
                    'avg_earnings' => 100000
                ],
                [
                    'course_name' => 'Strategi Pemasaran Digital',
                    'transaction_count' => 47,
                    'total_earnings' => 4700000,
                    'avg_earnings' => 100000
                ]
            ],
            'transactions' => [
                [
                    'id' => 1,
                    'transaction_type' => 'course_sale',
                    'course_name' => 'Kerajinan Anyaman untuk Pemula',
                    'student_name' => 'Ahmad Rizki',
                    'amount' => 150000,
                    'platform_fee' => 45000,
                    'net_amount' => 105000,
                    'status' => 'completed',
                    'payout_status' => 'paid',
                    'created_at' => '2024-06-20 10:30:00'
                ],
                [
                    'id' => 2,
                    'transaction_type' => 'course_sale',
                    'course_name' => 'Web Development Basics',
                    'student_name' => 'Siti Nurhaliza',
                    'amount' => 200000,
                    'platform_fee' => 60000,
                    'net_amount' => 140000,
                    'status' => 'completed',
                    'payout_status' => 'pending',
                    'created_at' => '2024-06-19 15:45:00'
                ]
            ]
        ];
    }
    
    // ===================== HELPER METHODS =====================
    
    private function getDateCondition($period) {
        switch ($period) {
            case '7':
                return 'AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            case '30':
                return 'AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            case '90':
                return 'AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
            case '365':
                return 'AND created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)';
            default:
                return 'AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }
    }
    
    private function getDateConditionFromFilters($filters) {
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            return "AND DATE(created_at) BETWEEN '{$filters['start_date']}' AND '{$filters['end_date']}'";
        }
        
        if (!empty($filters['period'])) {
            return $this->getDateCondition($filters['period']);
        }
        
        return $this->getDateCondition('30');
    }
    
    private function calculateConsistencyIncrease($monthlyData) {
        if (count($monthlyData) < 2) return 0;
        
        $currentMonth = end($monthlyData);
        $previousMonth = $monthlyData[count($monthlyData) - 2];
        
        if ($previousMonth == 0) return 0;
        
        return round((($currentMonth - $previousMonth) / $previousMonth) * 100);
    }
    
    private function calculateGrowthPercentage($monthlyData) {
        if (count($monthlyData) < 2) return 0;
        
        $currentMonth = end($monthlyData);
        $previousMonth = $monthlyData[count($monthlyData) - 2];
        
        if ($previousMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return round((($currentMonth - $previousMonth) / $previousMonth) * 100);
    }
    
    // ===================== PROFILE METHODS =====================
    
    /**
     * Update mentor profile
     */
    public function updateProfile($mentorId, $profileData) {
        try {
            if ($this->db) {
                // Update users table
                if (isset($profileData['basic_info'])) {
                    $stmt = $this->db->prepare("
                        UPDATE users 
                        SET email = ?, username = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $profileData['basic_info']['email'] ?? '',
                        $profileData['basic_info']['username'] ?? '',
                        $mentorId
                    ]);
                }
                
                // Update mentor_profiles table
                if (isset($profileData['profile_info'])) {
                    $profileInfo = $profileData['profile_info'];
                    $stmt = $this->db->prepare("
                        INSERT INTO mentor_profiles (
                            user_id, full_name, bio, phone, website, linkedin, 
                            instagram, youtube, specialization, experience_years, 
                            education, certifications, hourly_rate
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        full_name = VALUES(full_name),
                        bio = VALUES(bio),
                        phone = VALUES(phone),
                        website = VALUES(website),
                        linkedin = VALUES(linkedin),
                        instagram = VALUES(instagram),
                        youtube = VALUES(youtube),
                        specialization = VALUES(specialization),
                        experience_years = VALUES(experience_years),
                        education = VALUES(education),
                        certifications = VALUES(certifications),
                        hourly_rate = VALUES(hourly_rate)
                    ");
                    
                    $stmt->execute([
                        $mentorId,
                        $profileInfo['full_name'] ?? '',
                        $profileInfo['bio'] ?? '',
                        $profileInfo['phone'] ?? '',
                        $profileInfo['website'] ?? '',
                        $profileInfo['linkedin'] ?? '',
                        $profileInfo['instagram'] ?? '',
                        $profileInfo['youtube'] ?? '',
                        $profileInfo['specialization'] ?? '',
                        $profileInfo['experience_years'] ?? 0,
                        $profileInfo['education'] ?? '',
                        $profileInfo['certifications'] ?? '',
                        $profileInfo['hourly_rate'] ?? 0
                    ]);
                }
                
                return true;
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload profile picture
     */
    public function uploadProfilePicture($mentorId, $file) {
        try {
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type'];
            }
            
            if ($file['size'] > 5 * 1024 * 1024) { // 5MB
                return ['success' => false, 'message' => 'File too large'];
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $mentorId . '_' . time() . '.' . $extension;
            $uploadPath = 'uploads/profiles/' . $filename;
            
            // Create directory if not exists
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Update database
                if ($this->db) {
                    $stmt = $this->db->prepare("
                        INSERT INTO mentor_profiles (user_id, profile_picture)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE profile_picture = VALUES(profile_picture)
                    ");
                    $stmt->execute([$mentorId, $uploadPath]);
                }
                
                return ['success' => true, 'filename' => $uploadPath];
            }
            
            return ['success' => false, 'message' => 'Upload failed'];
            
        } catch (Exception $e) {
            error_log("Error uploading profile picture: " . $e->getMessage());
            return ['success' => false, 'message' => 'Upload error'];
        }
    }
    
    // ===================== COURSE CATEGORIES =====================
    
    /**
     * Get course categories
     */
    public function getCourseCategories() {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT * FROM course_categories 
                    WHERE is_active = 1 
                    ORDER BY sort_order ASC, name ASC
                ");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $this->getStaticCategories();
            
        } catch (Exception $e) {
            error_log("Error getting course categories: " . $e->getMessage());
            return $this->getStaticCategories();
        }
    }
    
    private function getStaticCategories() {
        return [
            ['id' => 1, 'name' => 'Pendidikan', 'slug' => 'pendidikan'],
            ['id' => 2, 'name' => 'UI/UX Design', 'slug' => 'ui-ux'],
            ['id' => 3, 'name' => 'Programming', 'slug' => 'programming'],
            ['id' => 4, 'name' => 'Bisnis & Marketing', 'slug' => 'bisnis'],
            ['id' => 5, 'name' => 'Kerajinan & Seni', 'slug' => 'kerajinan'],
            ['id' => 6, 'name' => 'Kesehatan & Kebugaran', 'slug' => 'kesehatan'],
            ['id' => 7, 'name' => 'Musik & Audio', 'slug' => 'musik'],
            ['id' => 8, 'name' => 'Fotografi & Video', 'slug' => 'fotografi'],
            ['id' => 9, 'name' => 'Bahasa', 'slug' => 'bahasa'],
            ['id' => 10, 'name' => 'Hobi & Lifestyle', 'slug' => 'hobi']
        ];
    }
    
    // ===================== VALIDATION METHODS =====================
    
    /**
     * Validate mentor access
     */
    public function validateMentorAccess($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT id, user_type 
                    FROM users 
                    WHERE id = ? AND user_type = 'Mentor'
                ");
                $stmt->execute([$mentorId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            }
            
            return true; // Allow access for demo
            
        } catch (Exception $e) {
            error_log("Error validating mentor access: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate course ownership
     */
    public function validateCourseOwnership($courseId, $mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT id 
                    FROM courses 
                    WHERE id = ? AND mentor_id = ?
                ");
                $stmt->execute([$courseId, $mentorId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            }
            
            return true; // Allow access for demo
            
        } catch (Exception $e) {
            error_log("Error validating course ownership: " . $e->getMessage());
            return false;
        }
    }
    
    // ===================== UTILITY METHODS =====================
    
    /**
     * Format currency
     */
    public function formatCurrency($amount) {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1) . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1) . ' jt';
        } elseif ($amount >= 1000) {
            return 'Rp ' . number_format($amount / 1000, 0) . 'k';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }
    
    /**
     * Generate unique slug
     */
    public function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }
    
    /**
     * Log mentor activity
     */
    public function logActivity($mentorId, $action, $details = []) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO mentor_activity_log (mentor_id, action, details, ip_address, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $mentorId,
                    $action,
                    json_encode($details),
                    $_SERVER['REMOTE_ADDR'] ?? ''
                ]);
            }
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }
    
    /**
     * Send notification to mentor
     */
    public function sendNotification($mentorId, $type, $title, $message, $relatedId = null) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, type, title, message, related_id, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                return $stmt->execute([$mentorId, $type, $title, $message, $relatedId]);
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error sending notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get mentor statistics summary
     */
    public function getMentorStatsSummary($mentorId) {
        try {
            if ($this->db) {
                $stats = [];
                
                // Total courses
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM courses WHERE mentor_id = ?");
                $stmt->execute([$mentorId]);
                $stats['total_courses'] = $stmt->fetchColumn();
                
                // Total students
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT e.student_id) as total
                    FROM enrollments e
                    INNER JOIN courses c ON e.course_id = c.id
                    WHERE c.mentor_id = ?
                ");
                $stmt->execute([$mentorId]);
                $stats['total_students'] = $stmt->fetchColumn();
                
                // Total earnings
                $stmt = $this->db->prepare("
                    SELECT SUM(net_amount) as total
                    FROM earnings
                    WHERE mentor_id = ? AND status = 'completed' AND transaction_type = 'course_sale'
                ");
                $stmt->execute([$mentorId]);
                $stats['total_earnings'] = $stmt->fetchColumn() ?: 0;
                
                // Average rating
                $stmt = $this->db->prepare("
                    SELECT AVG(r.rating) as avg_rating
                    FROM reviews r
                    INNER JOIN courses c ON r.course_id = c.id
                    WHERE c.mentor_id = ?
                ");
                $stmt->execute([$mentorId]);
                $stats['avg_rating'] = round($stmt->fetchColumn() ?: 0, 1);
                
                return $stats;
            }
            
            return [
                'total_courses' => 12,
                'total_students' => 847,
                'total_earnings' => 15750000,
                'avg_rating' => 4.8
            ];
            
        } catch (Exception $e) {
            error_log("Error getting mentor stats summary: " . $e->getMessage());
            return [
                'total_courses' => 12,
                'total_students' => 847,
                'total_earnings' => 15750000,
                'avg_rating' => 4.8
            ];
        }
    }

    public function logout($mentorId) {
        try {
            // Log logout activity
            $this->logActivity($mentorId, 'logout', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'logout_time' => date('Y-m-d H:i:s')
            ]);
            
            // Clear session data
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }
            
            // Clear session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error during logout: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate session and mentor access
     */
    public function validateSession($mentorId) {
        try {
            // Check if session is active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return false;
            }
            
            // Check if mentor ID matches session
            if (!isset($_SESSION['mentor_id']) || $_SESSION['mentor_id'] != $mentorId) {
                return false;
            }
            
            // Check if user type is mentor
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Mentor') {
                return false;
            }
            
            // Check session timeout (24 hours)
            if (isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity']) > 86400) {
                return false;
            }
            
            // Update last activity
            $_SESSION['last_activity'] = time();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error validating session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get mentor session info
     */
    public function getMentorSessionInfo($mentorId) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT u.username, u.email, u.created_at,
                           mp.full_name, mp.profile_picture
                    FROM users u
                    LEFT JOIN mentor_profiles mp ON u.id = mp.user_id
                    WHERE u.id = ? AND u.user_type = 'Mentor'
                ");
                $stmt->execute([$mentorId]);
                $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mentor) {
                    return [
                        'id' => $mentorId,
                        'name' => $mentor['full_name'] ?: $mentor['username'],
                        'email' => $mentor['email'],
                        'username' => $mentor['username'],
                        'profile_picture' => $mentor['profile_picture'],
                        'member_since' => $mentor['created_at'],
                        'last_login' => $_SESSION['login_time'] ?? date('Y-m-d H:i:s'),
                        'session_duration' => $this->getSessionDuration()
                    ];
                }
            }
            
            // Fallback to session data
            return [
                'id' => $mentorId,
                'name' => $_SESSION['mentor_name'] ?? 'Mentor',
                'email' => $_SESSION['mentor_email'] ?? '',
                'username' => $_SESSION['mentor_username'] ?? 'mentor',
                'profile_picture' => $_SESSION['mentor_picture'] ?? null,
                'member_since' => date('Y-m-d H:i:s'),
                'last_login' => $_SESSION['login_time'] ?? date('Y-m-d H:i:s'),
                'session_duration' => $this->getSessionDuration()
            ];
            
        } catch (Exception $e) {
            error_log("Error getting mentor session info: " . $e->getMessage());
            return [
                'id' => $mentorId,
                'name' => 'Mentor',
                'email' => '',
                'username' => 'mentor',
                'profile_picture' => null,
                'member_since' => date('Y-m-d H:i:s'),
                'last_login' => date('Y-m-d H:i:s'),
                'session_duration' => '0 menit'
            ];
        }
    }
    
    /**
     * Calculate session duration
     */
    private function getSessionDuration() {
        if (!isset($_SESSION['login_time'])) {
            return '0 menit';
        }
        
        $loginTime = strtotime($_SESSION['login_time']);
        $currentTime = time();
        $duration = $currentTime - $loginTime;
        
        if ($duration < 60) {
            return $duration . ' detik';
        } elseif ($duration < 3600) {
            return floor($duration / 60) . ' menit';
        } else {
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            return $hours . ' jam ' . $minutes . ' menit';
        }
    }
    
    /**
     * Force logout all sessions for mentor
     */
    public function forceLogoutAllSessions($mentorId) {
        try {
            if ($this->db) {
                // Update user record to invalidate all sessions
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET last_password_change = NOW()
                    WHERE id = ? AND user_type = 'Mentor'
                ");
                $stmt->execute([$mentorId]);
                
                // Log force logout
                $this->logActivity($mentorId, 'force_logout_all', [
                    'reason' => 'Security - Force logout all sessions',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                return true;
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error forcing logout all sessions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check for concurrent sessions
     */
    public function checkConcurrentSessions($mentorId) {
        try {
            // In a real implementation, you would track active sessions
            // For now, we'll simulate this functionality
            
            return [
                'has_concurrent' => false,
                'active_sessions' => 1,
                'session_info' => [
                    [
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50),
                        'last_activity' => date('Y-m-d H:i:s'),
                        'location' => 'Unknown'
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error checking concurrent sessions: " . $e->getMessage());
            return [
                'has_concurrent' => false,
                'active_sessions' => 1,
                'session_info' => []
            ];
        }
    }
    
    /**
     * Update session activity
     */
    public function updateSessionActivity($mentorId) {
        try {
            $_SESSION['last_activity'] = time();
            $_SESSION['page_views'] = ($_SESSION['page_views'] ?? 0) + 1;
            
            // Optionally log to database
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO mentor_activity_log (mentor_id, action, details, created_at)
                    VALUES (?, 'page_view', ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    details = VALUES(details),
                    created_at = NOW()
                ");
                
                $details = json_encode([
                    'page' => $_SERVER['REQUEST_URI'] ?? '',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                $stmt->execute([$mentorId, $details]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating session activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get login attempts for security
     */
    public function getLoginAttempts($email, $timeframe = 15) {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as attempts
                    FROM login_attempts
                    WHERE email = ? 
                    AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
                    AND success = 0
                ");
                $stmt->execute([$email, $timeframe]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return (int)($result['attempts'] ?? 0);
            }
            
            return 0; // No tracking in demo mode
            
        } catch (Exception $e) {
            error_log("Error getting login attempts: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Log login attempt
     */
    public function logLoginAttempt($email, $success = false, $reason = '') {
        try {
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO login_attempts (email, ip_address, user_agent, success, reason, attempted_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $email,
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $success ? 1 : 0,
                    $reason
                ]);
                
                return true;
            }
            
            return true; // Simulate success for demo
            
        } catch (Exception $e) {
            error_log("Error logging login attempt: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate secure session token
     */
    public function generateSessionToken() {
        try {
            return bin2hex(random_bytes(32));
        } catch (Exception $e) {
            error_log("Error generating session token: " . $e->getMessage());
            return md5(uniqid(rand(), true));
        }
    }
    
    /**
     * Verify session token
     */
    public function verifySessionToken($token, $mentorId) {
        try {
            if (empty($token) || empty($mentorId)) {
                return false;
            }
            
            // Check if token matches session
            if (isset($_SESSION['session_token']) && $_SESSION['session_token'] === $token) {
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error verifying session token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enhanced activity logging with more details
     */
    public function logDetailedActivity($mentorId, $action, $details = []) {
        try {
            $activityData = array_merge([
                'action' => $action,
                'mentor_id' => $mentorId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'timestamp' => date('Y-m-d H:i:s'),
                'session_id' => session_id()
            ], $details);
            
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO mentor_activity_log (mentor_id, action, details, ip_address, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $mentorId,
                    $action,
                    json_encode($activityData),
                    $activityData['ip_address']
                ]);
            }
            
            // Also log to file for critical actions
            if (in_array($action, ['login', 'logout', 'password_change', 'force_logout'])) {
                error_log("MENTOR_ACTIVITY: " . json_encode($activityData));
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error logging detailed activity: " . $e->getMessage());
            return false;
        }
    }
}

?>
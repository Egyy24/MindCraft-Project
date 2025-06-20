<?php
class StatsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getStats() {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentee') as total_mentees,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentor') as total_mentors,
                    (SELECT COUNT(*) FROM content) as total_contents";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getChartData() {
        $data = [];
        
        // User distribution
        $stmt = $this->db->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
        $data['user_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // User growth (last 6 months)
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month,
                SUM(CASE WHEN user_type = 'Mentee' THEN 1 ELSE 0 END) as mentees,
                SUM(CASE WHEN user_type = 'Mentor' THEN 1 ELSE 0 END) as mentors
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
            ORDER BY MONTH(created_at)
        ");
        $data['user_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Content status
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM content GROUP BY status");
        $data['content_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Content category
        $stmt = $this->db->query("SELECT category, COUNT(*) as count FROM content GROUP BY category");
        $data['content_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
    }
}
?>
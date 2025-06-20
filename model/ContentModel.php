<?php

class ContentModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getContents() {
        $stmt = $this->db->query("SELECT * FROM content ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContent($id) {
        $stmt = $this->db->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createContent($data) {
        $stmt = $this->db->prepare("INSERT INTO content (user_id, thumbnail, title, content, category, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['thumbnail'],
            $data['title'],
            $data['content'] ?? '',
            $data['category'],
            $data['status']
        ]);
        
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function updateContent($id, $data) {
        $query = "UPDATE content SET thumbnail = ?, title = ?, content = ?, category = ?, status = ?";
        $params = [
            $data['thumbnail'],
            $data['title'],
            $data['content'] ?? '',
            $data['category'],
            $data['status']
        ];

        if (isset($data['user_id'])) {
            $query .= ", user_id = ?";
            $params[] = $data['user_id'];
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return ['success' => $stmt->rowCount() > 0];
    }

    public function deleteContent($id) {
        $stmt = $this->db->prepare("DELETE FROM content WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function getMentorCourses($user_id) {
        $query = "SELECT id, thumbnail, title, category, status, created_at FROM content WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCourses($user_id) {
        $query = "SELECT COUNT(*) as total_courses FROM content WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_courses'];
    }

    public function getTotalStudentsEnrolledForMentorCourses($user_id) {
        $query = "SELECT COUNT(DISTINCT t.user_id) as total_students
                  FROM transactions t
                  JOIN users u ON t.user_id = u.id ]
                  JOIN content c ON t.subscription_type = c.title 
                  WHERE c.user_id = ? AND t.status = 'completed'";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_students'] ? $row['total_students'] : 0;
    }

    public function getTotalRevenueForMentor($user_id) {
        $query = "SELECT SUM(t.total_amount) as total_revenue
                  FROM transactions t
                  JOIN users u ON t.user_id = u.id
                  JOIN content c ON t.subscription_type = c.title
                  WHERE c.user_id = ? AND t.status = 'completed'";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_revenue'] ? $row['total_revenue'] : 0;
    }
}
?>
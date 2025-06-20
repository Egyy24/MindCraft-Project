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
        $stmt = $this->db->prepare("INSERT INTO content (thumbnail, title, category, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['thumbnail'],
            $data['title'],
            $data['category'],
            $data['status']
        ]);
        
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function updateContent($id, $data) {
        $stmt = $this->db->prepare("UPDATE content SET thumbnail = ?, title = ?, category = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $data['thumbnail'],
            $data['title'],
            $data['category'],
            $data['status'],
            $id
        ]);
        
        return ['success' => $stmt->rowCount() > 0];
    }

    public function deleteContent($id) {
        $stmt = $this->db->prepare("DELETE FROM content WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => $stmt->rowCount() > 0];
    }
}
?>
<?php

class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUsers() {
        $stmt = $this->db->query("SELECT id, username, email, user_type, gender, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, user_type, gender, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $checkStmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$data['username'], $data['email']]);
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, user_type, gender) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['user_type'],
            $data['gender']
        ]);
        
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function updateUser($id, $data) {
        $checkStmt = $this->db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $checkStmt->execute([$data['username'], $data['email'], $id]);
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        if (!empty($data['password'])) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, password = ?, user_type = ?, gender = ? WHERE id = ?");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['user_type'],
                $data['gender'],
                $id
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, gender = ? WHERE id = ?");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $data['user_type'],
                $data['gender'],
                $id
            ]);
        }
        
        return ['success' => true];
    }

    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function getMentorById($id) {
        $query = "SELECT id, username, email FROM users WHERE id = ? AND user_type = 'Mentor' LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
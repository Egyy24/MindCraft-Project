<?php
// Lokasi: MindCraft-Project/config/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'mindcraft';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Koneksi Database dengan Error Handling
     */
    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Test koneksi dengan query sederhana
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $e) {
            // Log error tapi jangan tampilkan detail ke user
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Return null jika koneksi gagal - system akan menggunakan data statis
            $this->conn = null;
        }

        return $this->conn;
    }

    /**
     * Check apakah database tersedia
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    /**
     * Close connection
     */
    public function disconnect() {
        $this->conn = null;
    }

    /**
     * Get connection status
     */
    public function getConnectionStatus() {
        return [
            'connected' => $this->isConnected(),
            'host' => $this->host,
            'database' => $this->db_name,
            'time' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Execute query dengan error handling
     */
    public function executeQuery($sql, $params = []) {
        try {
            if (!$this->isConnected()) {
                return false;
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt;
            
        } catch(PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Insert data dengan return ID
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->executeQuery($sql, $params);
            if ($stmt) {
                return $this->conn->lastInsertId();
            }
            return false;
            
        } catch(PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update/Delete dengan return affected rows
     */
    public function modify($sql, $params = []) {
        try {
            $stmt = $this->executeQuery($sql, $params);
            if ($stmt) {
                return $stmt->rowCount();
            }
            return false;
            
        } catch(PDOException $e) {
            error_log("Modify Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Select single row
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->executeQuery($sql, $params);
            if ($stmt) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
            
        } catch(PDOException $e) {
            error_log("Fetch One Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Select multiple rows
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->executeQuery($sql, $params);
            if ($stmt) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
            
        } catch(PDOException $e) {
            error_log("Fetch All Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transaction methods
     */
    public function beginTransaction() {
        if ($this->isConnected()) {
            return $this->conn->beginTransaction();
        }
        return false;
    }

    public function commit() {
        if ($this->isConnected()) {
            return $this->conn->commit();
        }
        return false;
    }

    public function rollback() {
        if ($this->isConnected()) {
            return $this->conn->rollBack();
        }
        return false;
    }

    /**
     * Check if tables exist (untuk validasi struktur database)
     */
    public function validateDatabaseStructure() {
        if (!$this->isConnected()) {
            return false;
        }

        $requiredTables = [
            'users', 'courses', 'enrollments', 'course_progress', 
            'reviews', 'earnings', 'notifications', 'messages'
        ];

        try {
            foreach ($requiredTables as $table) {
                $stmt = $this->conn->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                
                if ($stmt->rowCount() === 0) {
                    error_log("Missing table: " . $table);
                    return false;
                }
            }
            return true;
            
        } catch(PDOException $e) {
            error_log("Database validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Seed initial data jika database kosong
     */
    public function seedInitialData() {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Check jika sudah ada data user
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = 'Mentor'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] == 0) {
                // Insert sample mentor
                $this->conn->prepare("
                    INSERT INTO users (username, email, password, user_type, gender) 
                    VALUES (?, ?, ?, 'Mentor', 'Laki-laki')
                ")->execute(['budi_mentor', 'budi@mindcraft.com', password_hash('password123', PASSWORD_DEFAULT)]);

                $mentorId = $this->conn->lastInsertId();

                // Insert sample courses
                $courses = [
                    ['Kerajian Anyaman untuk Pemula', 'Pendidikan', 'Pemula', 'Belajar seni anyaman tradisional Indonesia'],
                    ['Pengenalan Web Development', 'Programming', 'Pemula', 'Dasar-dasar pemrograman web modern'],
                    ['Strategi Pemasaran Digital', 'Bisnis', 'Menengah', 'Teknik pemasaran di era digital']
                ];

                foreach ($courses as $course) {
                    $this->conn->prepare("
                        INSERT INTO courses (mentor_id, title, category, difficulty, description, status, price) 
                        VALUES (?, ?, ?, ?, ?, 'Published', 299000)
                    ")->execute([$mentorId, $course[0], $course[1], $course[2], $course[3]]);
                }

                return true;
            }

            return true;

        } catch(PDOException $e) {
            error_log("Seed data error: " . $e->getMessage());
            return false;
        }
    }
}
?>
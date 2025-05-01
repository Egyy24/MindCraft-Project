<?php
class Database {
    private $host = "localhost";
    private $db_name = "mindcraft";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            header('HTTP/1.1 500 Database Error');
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
        return $this->conn;
    }
}
?>
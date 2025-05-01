<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_connect.php';

$db = new Database();
$conn = $db->connect();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$entity = isset($_GET['entity']) ? $_GET['entity'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($entity) {
    case 'users':
        handleUsers($conn, $requestMethod, $id);
        break;
        
    case 'content':
        handleContent($conn, $requestMethod, $id);
        break;
        
    case 'stats':
        handleStats($conn);
        break;
        
    case 'chart-data':
        handleChartData($conn);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}

function handleUsers($conn, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT id, username, user_type, gender, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo json_encode(['success' => true, 'data' => $user]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                }
            } else {
                $stmt = $conn->query("SELECT id, username, user_type, gender, created_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!validateUserData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users 
                                      (username, password, user_type, gender, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $data['username'],
                    $hashedPassword,
                    $data['user_type'],
                    $data['gender']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'User created successfully',
                    'id' => $conn->lastInsertId()
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$id || !validateUserData($data, false)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                // Jika password tidak diubah
                if (empty($data['password'])) {
                    $stmt = $conn->prepare("UPDATE users SET 
                                          username = ?, 
                                          user_type = ?, 
                                          gender = ?
                                          WHERE id = ?");
                    $stmt->execute([
                        $data['username'],
                        $data['user_type'],
                        $data['gender'],
                        $id
                    ]);
                } else {
                    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET 
                                          username = ?, 
                                          password = ?, 
                                          user_type = ?, 
                                          gender = ?
                                          WHERE id = ?");
                    $stmt->execute([
                        $data['username'],
                        $hashedPassword,
                        $data['user_type'],
                        $data['gender'],
                        $id
                    ]);
                }
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No changes made or user not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function handleContent($conn, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM content WHERE id = ?");
                $stmt->execute([$id]);
                $content = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($content) {
                    echo json_encode(['success' => true, 'data' => $content]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Content not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM content ORDER BY created_at DESC");
                $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $contents]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!validateContentData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("INSERT INTO content 
                                      (thumbnail, title, category, status, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $data['thumbnail'],
                    $data['title'],
                    $data['category'],
                    $data['status']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Content created successfully',
                    'id' => $conn->lastInsertId()
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$id || !validateContentData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("UPDATE content SET 
                                      thumbnail = ?, 
                                      title = ?, 
                                      category = ?, 
                                      status = ?
                                      WHERE id = ?");
                $stmt->execute([
                    $data['thumbnail'],
                    $data['title'],
                    $data['category'],
                    $data['status'],
                    $id
                ]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No changes made or content not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Content deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Content not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function validateUserData($data, $requirePassword = true) {
    $valid = isset($data['username']) && 
             isset($data['user_type']) && 
             isset($data['gender']);
             
    if ($requirePassword) {
        $valid = $valid && isset($data['password']);
    }
    
    return $valid;
}

function validateContentData($data) {
    return isset($data['thumbnail']) && 
           isset($data['title']) && 
           isset($data['category']) && 
           isset($data['status']);
}

function handleStats($conn) {
    try {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentee') as total_mentees,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentor') as total_mentors,
                    (SELECT COUNT(*) FROM content) as total_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'Published') as published_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'Draft') as draft_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'Archived') as archived_contents";
        
        $stmt = $conn->query($query);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'timestamp' => time()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function handleChartData($conn) {
    try {
        $data = [];
        
        // Distribusi user
        $stmt = $conn->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
        $data['user_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Pertumbuhan User (6 bulan terakhir)
        $stmt = $conn->query("
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
        
        // Status konten
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM content GROUP BY status");
        $data['content_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Kategori konten
        $stmt = $conn->query("SELECT category, COUNT(*) as count FROM content GROUP BY category");
        $data['content_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => time()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
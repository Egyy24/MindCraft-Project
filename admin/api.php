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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

switch ($entity) {
    case 'users':
        handleUsers($conn, $requestMethod, $id, $input);
        break;
        
    case 'content':
        handleContent($conn, $requestMethod, $id, $input);
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

function handleUsers($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT id, username, email, user_type, gender, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo json_encode(['success' => true, 'data' => $user]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                }
            } else {
                $stmt = $conn->query("SELECT id, username, email, user_type, gender, created_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;
            
        case 'POST':
            if (!validateUserData($input, true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                return;
            }
            
            try {
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $checkStmt->execute([$input['username'], $input['email']]);
                if ($checkStmt->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                    return;
                }
                
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, gender) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['username'],
                    $input['email'],
                    $hashedPassword,
                    $input['user_type'],
                    $input['gender']
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
            if (!$id || !validateUserData($input, false)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                return;
            }
            
            try {
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    return;
                }
                
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $checkStmt->execute([$input['username'], $input['email'], $id]);
                if ($checkStmt->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                    return;
                }
                
                if (!empty($input['password'])) {
                    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, user_type = ?, gender = ? WHERE id = ?");
                    $stmt->execute([
                        $input['username'],
                        $input['email'],
                        $hashedPassword,
                        $input['user_type'],
                        $input['gender'],
                        $id
                    ]);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, gender = ? WHERE id = ?");
                    $stmt->execute([
                        $input['username'],
                        $input['email'],
                        $input['user_type'],
                        $input['gender'],
                        $id
                    ]);
                }
                
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
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
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                return;
            }
            
            try {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    http_response_code(404);
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

function handleContent($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM content WHERE id = ?");
                $stmt->execute([$id]);
                $content = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($content) {
                    echo json_encode(['success' => true, 'data' => $content]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Content not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM content ORDER BY created_at DESC");
                $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $contents]);
            }
            break;
            
        case 'POST':
            if (!validateContentData($input)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("INSERT INTO content (thumbnail, title, category, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $input['thumbnail'],
                    $input['title'],
                    $input['category'],
                    $input['status']
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
            if (!$id || !validateContentData($input)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("UPDATE content SET thumbnail = ?, title = ?, category = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $input['thumbnail'],
                    $input['title'],
                    $input['category'],
                    $input['status'],
                    $id
                ]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Content not found or no changes made']);
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
                    http_response_code(404);
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
    $valid = isset($data['username']) && strlen($data['username']) >= 3 && 
             isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && 
             isset($data['user_type']) && in_array($data['user_type'], ['Mentee', 'Mentor']) && 
             isset($data['gender']) && in_array($data['gender'], ['Laki-laki', 'Perempuan']);
             
    if ($requirePassword) {
        $valid = $valid && isset($data['password']) && strlen($data['password']) >= 6;
    }
    
    return $valid;
}

function validateContentData($data) {
    $validCategories = ['Pendidikan', 'UI/UX', 'Programming', 'Bisnis'];
    $validStatuses = ['Published', 'Draft', 'Archived'];
    
    return isset($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL) &&
           isset($data['title']) && strlen($data['title']) >= 5 && 
           isset($data['category']) && in_array($data['category'], $validCategories) && 
           isset($data['status']) && in_array($data['status'], $validStatuses);
}

function handleStats($conn) {
    try {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentee') as total_mentees,
                    (SELECT COUNT(*) FROM users WHERE user_type = 'Mentor') as total_mentors,
                    (SELECT COUNT(*) FROM content) as total_contents";
        
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
        
        // User distribution
        $stmt = $conn->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
        $data['user_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // User growth (last 6 months)
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
        
        // Content status
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM content GROUP BY status");
        $data['content_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Content category
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
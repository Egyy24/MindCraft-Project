<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/ContentModel.php';
require_once __DIR__ . '/../model/StatsModel.php';

class AdminController {
    private $userModel;
    private $contentModel;
    private $statsModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
        $this->contentModel = new ContentModel($db);
        $this->statsModel = new StatsModel($db);
    }

    public function handleRequest() {
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $entity = isset($_GET['entity']) ? $_GET['entity'] : '';
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $input = json_decode(file_get_contents('php://input'), true);

        switch ($entity) {
            case 'users':
                $this->handleUsers($requestMethod, $id, $input);
                break;
            case 'content':
                $this->handleContent($requestMethod, $id, $input);
                break;
            case 'stats':
                $this->handleStats();
                break;
            case 'chart-data':
                $this->handleChartData();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                break;
        }
    }

    private function handleUsers($method, $id, $input) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $user = $this->userModel->getUser($id);
                    if ($user) {
                        echo json_encode(['success' => true, 'data' => $user]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'User not found']);
                    }
                } else {
                    $users = $this->userModel->getUsers();
                    echo json_encode(['success' => true, 'data' => $users]);
                }
                break;
            case 'POST':
                if (!$this->validateUserData($input, true)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                    return;
                }
                
                $result = $this->userModel->createUser($input);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'User created successfully',
                        'id' => $result['id']
                    ]);
                } else {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => $result['message']]);
                }
                break;
            case 'PUT':
                if (!$id || !$this->validateUserData($input, false)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                    return;
                }
                
                $result = $this->userModel->updateUser($id, $input);
                if ($result['success']) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => $result['message']]);
                }
                break;
            case 'DELETE':
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'User ID is required']);
                    return;
                }
                
                $result = $this->userModel->deleteUser($id);
                if ($result['success']) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function handleContent($method, $id, $input) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $content = $this->contentModel->getContent($id);
                    if ($content) {
                        echo json_encode(['success' => true, 'data' => $content]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Content not found']);
                    }
                } else {
                    $contents = $this->contentModel->getContents();
                    echo json_encode(['success' => true, 'data' => $contents]);
                }
                break;
            case 'POST':
                if (!$this->validateContentData($input)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                    break;
                }
                
                $result = $this->contentModel->createContent($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Content created successfully',
                    'id' => $result['id']
                ]);
                break;
            case 'PUT':
                if (!$id || !$this->validateContentData($input)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                    break;
                }
                
                $result = $this->contentModel->updateContent($id, $input);
                if ($result['success']) {
                    echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Content not found or no changes made']);
                }
                break;
            case 'DELETE':
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID is required']);
                    break;
                }
                
                $result = $this->contentModel->deleteContent($id);
                if ($result['success']) {
                    echo json_encode(['success' => true, 'message' => 'Content deleted successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Content not found']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function handleStats() {
        try {
            $stats = $this->statsModel->getStats();
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

    private function handleChartData() {
        try {
            $data = $this->statsModel->getChartData();
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

    private function validateUserData($data, $requirePassword = true) {
        $valid = isset($data['username']) && strlen($data['username']) >= 3 && 
                 isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && 
                 isset($data['user_type']) && in_array($data['user_type'], ['Mentee', 'Mentor']) && 
                 isset($data['gender']) && in_array($data['gender'], ['Laki-laki', 'Perempuan']);
                 
        if ($requirePassword) {
            $valid = $valid && isset($data['password']) && strlen($data['password']) >= 6;
        }
        
        return $valid;
    }

    private function validateContentData($data) {
        $validCategories = ['Pendidikan', 'UI/UX', 'Programming', 'Bisnis'];
        $validStatuses = ['Published', 'Draft', 'Archived'];
        
        return isset($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL) &&
               isset($data['title']) && strlen($data['title']) >= 5 && 
               isset($data['category']) && in_array($data['category'], $validCategories) && 
               isset($data['status']) && in_array($data['status'], $validStatuses);
    }
}
?>
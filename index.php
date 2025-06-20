<?php

// error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Load dependencies 
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/ContentModel.php';
require_once __DIR__ . '/../controller/MentorController.php';

// Inisialisasi db connection
$database = new Database();
$db = $database->connect();

// Router
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/MindCraft/public'; 

// Hapus base path dan query string dari URI
$path = str_replace($base_path, '', $request_uri);
$path = strtok($path, '?');

// Mengatur route
switch ($path) {
    case '/':
        echo "Welcome to MindCraft! Please navigate to /mentor/dashboard or other paths.";
        break;
    case '/mentor/dashboard':
        $controller = new MentorController($db);
        $controller->dashboard();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found: " . htmlspecialchars($path);
        break;
}
?>
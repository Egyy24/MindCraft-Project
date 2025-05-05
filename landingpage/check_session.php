<?php
require 'config.php';

header('Content-Type: application/json');

session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'user_type' => $_SESSION['user_type'],
            'gender' => $_SESSION['gender']
        ]
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
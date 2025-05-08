<?php
session_start();
require_once '../classes/Task.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['id'] ?? null;

    if(!$task_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Task ID is required']);
        exit();
    }

    $task = new Task();
    $result = $task->delete($task_id);
    
    if($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete task']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 
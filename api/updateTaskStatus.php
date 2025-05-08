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
    $status = $_POST['status'] ?? null;

    if(!$task_id || !$status) {
        http_response_code(400);
        echo json_encode(['error' => 'Task ID and status are required']);
        exit();
    }

    $valid_statuses = ['To-Do', 'In Progress', 'Done'];
    if(!in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        exit();
    }

    $task = new Task();
    $result = $task->updateStatus($task_id, $status);
    
    if($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update task status']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 